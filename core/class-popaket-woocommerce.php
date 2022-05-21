<?php

class Popaket_WooCommerce {

    protected $config;

    protected $api;

    public function __construct() {
      $this->config = get_option( 'woocommerce_popaket_settings' );
      $this->api    = new \Popaket_API();

      $this->load_dependencies();

      add_action( 'admin_menu', array( $this, 'admin_page' ) );
      add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
      add_action( 'woocommerce_shipping_init', array( $this, 'popaket_init' ) );
      add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
      add_filter( 'woocommerce_checkout_fields', array( $this, 'popaket_checkout_fields' ) );
      /** Hook when woocommerce updating shipping packages */
      add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'add_destination_extra_field_to_shipping_packages' ) );
      /** Hook when checkout data processed */
      add_filter( 'woocommerce_checkout_posted_data', array( $this, 'popaket_checkout_data' ) );
      add_action( 'woocommerce_order_details_after_order_table_items', array( $this, 'popaket_view_order' ) );
      add_action( 'woocommerce_after_order_notes', array( $this, 'popaket_checkout_before_order_review_heading' ) );
      add_action( 'woocommerce_checkout_create_order', array( $this, 'when_creating_order' ), 10, 2 );

      /** creating virtual page */
      add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ){
          $wp_rewrite->rules = array_merge(
              ['tracking/(\w+)/?$' => 'index.php?tracking=$matches[1]'],
              $wp_rewrite->rules
          );
      } );
      add_filter( 'query_vars', function( $query_vars ){
          $query_vars[] = 'tracking';
          return $query_vars;
      } );
      add_action( 'template_redirect', function(){
          $custom = get_query_var( 'tracking' );
          if ( $custom ) {

              $awb_number = $custom;
              // query post type order by awb_number
              $args = array(
                  'post_type' => 'shop_order',
                  'post_status' => 'any',
                  'meta_query' => array(
                      array(
                          'key' => '_popaket_details',
                          'compare' => 'EXISTS',
                      )
                  )
              );

              $order_details = [];
              $order_meta = [];
              $query = new \WP_Query( $args );
              if ( $query->have_posts() ) {
                  while ( $query->have_posts() ) {
                      $query->the_post();
                      $order = wc_get_order( get_the_ID() );
                      $order_meta = $order->get_meta( '_popaket_details' );
                      if ( is_array( $order_meta ) ) {
                        foreach ( $order_meta as $key => $value ) {
                          if ( $value['awb_number'] == $awb_number ) {
                            $order_meta = $value;
                            $order_details = $order->get_data();
                            break;
                          }
                        }
                      }
                  }
              }
              wp_reset_postdata();

              $tracking_data = false;
              if ( ! empty( $order_meta ) ) {
                $tracking_data = $this->api->track( $awb_number );
              }

              include POPAKET_WOOCOMMERCE_PATH . 'templates/tracking.php';
              die;
          }
      } );
    }

    public function load_dependencies() {
      require_once POPAKET_WOOCOMMERCE_PATH . 'core/class-popaket-ajax.php';
      require_once POPAKET_WOOCOMMERCE_PATH . 'core/class-popaket-post.php';
    }

    public function admin_page() {
      $icon = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjEwNSAxMjUgMTY1IDE2NSIgd2lkdGg9IjE2NSIgaGVpZ2h0PSIxNjUiPgogIDxwYXRoIGQ9Ik0gMjQxLjg3NiAyMTQuMzEzIEMgMjQxLjM5NSAyMTUuMjA3IDI0MC45MDYgMjE2LjA4NCAyNDAuMzkyIDIxNi45NTMgQyAyMzUuNjgzIDIyNC44NzkgMjI5LjE4MyAyMzEuNTkyIDIyMS40MTQgMjM2LjU1MyBMIDE1Ni4zMSAyNzEuODA1IEwgMTUyLjA5NCAyNzQuMDgyIEwgMTMwLjc1NiAyODUuNjM2IEwgMTMwLjc1NiAyMjcuODkxIEMgMTM0LjQ4MiAyMTEuNjQ4IDE0OS42MTYgMTk3LjY4MiAxNjkuMzE5IDE5MC4xMDkgTCAxNjkuMzE5IDE5NC4zMjUgQyAxNjkuMzE1IDE5NS4zNjYgMTY5LjU4NyAxOTYuMzkgMTcwLjEwNyAxOTcuMjkyIEMgMTcwLjYyNyAxOTguMTk0IDE3MS4zNzYgMTk4Ljk0MiAxNzIuMjc4IDE5OS40NjEgTCAxODcuNzgyIDIwOC40MDkgQyAxODguNjggMjA4LjkyOCAxODkuNjk3IDIwOS4yIDE5MC43MzMgMjA5LjIgQyAxOTEuNzY5IDIwOS4yIDE5Mi43ODcgMjA4LjkyOCAxOTMuNjg0IDIwOC40MDkgTCAyMDkuMTggMTk5LjQ2MSBDIDIxMC4wODQgMTk4Ljk0MyAyMTAuODM1IDE5OC4xOTUgMjExLjM1NiAxOTcuMjkzIEMgMjExLjg3NyAxOTYuMzkxIDIxMi4xNSAxOTUuMzY3IDIxMi4xNDggMTk0LjMyNSBMIDIxMi4xNDggMTc2LjQyOSBDIDIxMi4xNDggMTc2LjE3OCAyMTIuMTMxIDE3NS45MjcgMjEyLjA5NyAxNzUuNjc5IEMgMjExLjk4MiAxNzQuNzczIDIxMS42NTkgMTczLjkwNyAyMTEuMTUzIDE3My4xNDcgQyAyMTAuNjQ3IDE3Mi4zODggMjA5Ljk3MiAxNzEuNzU2IDIwOS4xOCAxNzEuMzAyIEwgMjA1LjUyMSAxNjkuMTg1IEwgMTk4LjkwMyAxNjUuMzY0IEwgMTkzLjY4NCAxNjIuMzQ1IEMgMTkyLjc4NSAxNjEuODMyIDE5MS43NjggMTYxLjU2MiAxOTAuNzMzIDE2MS41NjIgQyAxODkuNjk4IDE2MS41NjIgMTg4LjY4MSAxNjEuODMyIDE4Ny43ODIgMTYyLjM0NSBMIDE4NC4xMzIgMTY0LjQ2MiBMIDE5OC45NDUgMTczLjAxNCBMIDE5OS4zMjQgMTczLjIzMyBMIDIwMi42MDQgMTc1LjEyMiBDIDIwMy41MDggMTc1LjY0IDIwNC4yNTggMTc2LjM4OCAyMDQuNzggMTc3LjI5IEMgMjA1LjMwMSAxNzguMTkyIDIwNS41NzQgMTc5LjIxNiAyMDUuNTcyIDE4MC4yNTggTCAyMDUuNTcyIDE4Mi4xNjQgTCAxOTguOTQ1IDE4NS45OTMgTCAxOTguOTQ1IDE4NC4wNzkgQyAxOTguOTQ4IDE4My4wNCAxOTguNjc2IDE4Mi4wMTkgMTk4LjE1OCAxODEuMTE4IEMgMTk3LjY0IDE4MC4yMTggMTk2Ljg5NCAxNzkuNDcgMTk1Ljk5NCAxNzguOTUxIEwgMTc3LjU1NiAxNjguMjY2IEwgMTcyLjI5NSAxNzEuMzAyIEMgMTcxLjUwNSAxNzEuNzU3IDE3MC44MzEgMTcyLjM4OSAxNzAuMzI3IDE3My4xNDkgQyAxNjkuODIyIDE3My45MDggMTY5LjUgMTc0Ljc3NCAxNjkuMzg2IDE3NS42NzkgQyAxNjkuMzUyIDE3NS45MjcgMTY5LjMzNSAxNzYuMTc4IDE2OS4zMzYgMTc2LjQyOSBMIDE2OS4zMzYgMTc4LjM3NyBDIDE1Ni40ODcgMTgwLjE4MiAxNDIuNSAxODYuMDI3IDEzMC43NTYgMTk0LjAxMyBMIDEzMC43NTYgMTgyLjUyNyBDIDEzMS40MDkgMTczLjMyOCAxMzQuMjEgMTY0LjQxMSAxMzguOTM0IDE1Ni40OTIgQyAxMzkuNDY1IDE1NS42NDkgMTM5Ljk4NyAxNTQuNzYzIDE0MC41NDQgMTUzLjk2MiBDIDE0OC45NDEgMTQxLjM1MiAxNjEuODYxIDEzMi40NTEgMTc2LjYzMyAxMjkuMDk5IEMgMTkxLjQwNSAxMjUuNzQ4IDIwNi44OTkgMTI4LjIwMiAyMTkuOTE0IDEzNS45NTQgQyAyMzIuOTI5IDE0My43MDYgMjQyLjQ2OCAxNTYuMTY0IDI0Ni41NiAxNzAuNzUzIEMgMjUwLjY1MiAxODUuMzQxIDI0OC45ODQgMjAwLjk0NSAyNDEuOTAxIDIxNC4zMzggTCAyNDEuODc2IDIxNC4zMTMgWiIgZmlsbD0id2hpdGUiLz4KICA8cGF0aCBkPSJNIDI0MS44NzYgMjE0LjMxMyBDIDI0MS4zOTUgMjE1LjIwNyAyNDAuOTA2IDIxNi4wODQgMjQwLjM5MiAyMTYuOTUzIEMgMjM1LjY4MyAyMjQuODc5IDIyOS4xODMgMjMxLjU5MiAyMjEuNDE0IDIzNi41NTMgTCAxNTYuMzEgMjcxLjgwNSBMIDE1Mi4wOTQgMjc0LjA4MiBMIDEzMC43NTYgMjg1LjYzNiBMIDEzMC43NTYgMjI3Ljg5MSBDIDEzNC40ODIgMjExLjY0OCAxNDkuNjE2IDE5Ny42ODIgMTY5LjMxOSAxOTAuMTA5IEwgMTY5LjMxOSAxOTQuMzI1IEMgMTY5LjMxNSAxOTUuMzY2IDE2OS41ODcgMTk2LjM5IDE3MC4xMDcgMTk3LjI5MiBDIDE3MC42MjcgMTk4LjE5NCAxNzEuMzc2IDE5OC45NDIgMTcyLjI3OCAxOTkuNDYxIEwgMTg3Ljc4MiAyMDguNDA5IEMgMTg4LjY4IDIwOC45MjggMTg5LjY5NyAyMDkuMiAxOTAuNzMzIDIwOS4yIEMgMTkxLjc2OSAyMDkuMiAxOTIuNzg3IDIwOC45MjggMTkzLjY4NCAyMDguNDA5IEwgMjA5LjE4IDE5OS40NjEgQyAyMTAuMDg0IDE5OC45NDMgMjEwLjgzNSAxOTguMTk1IDIxMS4zNTYgMTk3LjI5MyBDIDIxMS44NzcgMTk2LjM5MSAyMTIuMTUgMTk1LjM2NyAyMTIuMTQ4IDE5NC4zMjUgTCAyMTIuMTQ4IDE3Ni40MjkgQyAyMTIuMTQ4IDE3Ni4xNzggMjEyLjEzMSAxNzUuOTI3IDIxMi4wOTcgMTc1LjY3OSBDIDIxMS45ODIgMTc0Ljc3MyAyMTEuNjU5IDE3My45MDcgMjExLjE1MyAxNzMuMTQ3IEMgMjEwLjY0NyAxNzIuMzg4IDIwOS45NzIgMTcxLjc1NiAyMDkuMTggMTcxLjMwMiBMIDIwNS41MjEgMTY5LjE4NSBMIDE5OC45MDMgMTY1LjM2NCBMIDE5My42ODQgMTYyLjM0NSBDIDE5Mi43ODUgMTYxLjgzMiAxOTEuNzY4IDE2MS41NjIgMTkwLjczMyAxNjEuNTYyIEMgMTg5LjY5OCAxNjEuNTYyIDE4OC42ODEgMTYxLjgzMiAxODcuNzgyIDE2Mi4zNDUgTCAxODQuMTMyIDE2NC40NjIgTCAxOTguOTQ1IDE3My4wMTQgTCAxOTkuMzI0IDE3My4yMzMgTCAyMDIuNjA0IDE3NS4xMjIgQyAyMDMuNTA4IDE3NS42NCAyMDQuMjU4IDE3Ni4zODggMjA0Ljc4IDE3Ny4yOSBDIDIwNS4zMDEgMTc4LjE5MiAyMDUuNTc0IDE3OS4yMTYgMjA1LjU3MiAxODAuMjU4IEwgMjA1LjU3MiAxODIuMTY0IEwgMTk4Ljk0NSAxODUuOTkzIEwgMTk4Ljk0NSAxODQuMDc5IEMgMTk4Ljk0OCAxODMuMDQgMTk4LjY3NiAxODIuMDE5IDE5OC4xNTggMTgxLjExOCBDIDE5Ny42NCAxODAuMjE4IDE5Ni44OTQgMTc5LjQ3IDE5NS45OTQgMTc4Ljk1MSBMIDE3Ny41NTYgMTY4LjI2NiBMIDE3Mi4yOTUgMTcxLjMwMiBDIDE3MS41MDUgMTcxLjc1NyAxNzAuODMxIDE3Mi4zODkgMTcwLjMyNyAxNzMuMTQ5IEMgMTY5LjgyMiAxNzMuOTA4IDE2OS41IDE3NC43NzQgMTY5LjM4NiAxNzUuNjc5IEMgMTY5LjM1MiAxNzUuOTI3IDE2OS4zMzUgMTc2LjE3OCAxNjkuMzM2IDE3Ni40MjkgTCAxNjkuMzM2IDE3OC4zNzcgQyAxNTYuNDg3IDE4MC4xODIgMTQyLjUgMTg2LjAyNyAxMzAuNzU2IDE5NC4wMTMgTCAxMzAuNzU2IDE4Mi41MjcgQyAxMzEuNDA5IDE3My4zMjggMTM0LjIxIDE2NC40MTEgMTM4LjkzNCAxNTYuNDkyIEMgMTM5LjQ2NSAxNTUuNjQ5IDEzOS45ODcgMTU0Ljc2MyAxNDAuNTQ0IDE1My45NjIgQyAxNDguOTQxIDE0MS4zNTIgMTYxLjg2MSAxMzIuNDUxIDE3Ni42MzMgMTI5LjA5OSBDIDE5MS40MDUgMTI1Ljc0OCAyMDYuODk5IDEyOC4yMDIgMjE5LjkxNCAxMzUuOTU0IEMgMjMyLjkyOSAxNDMuNzA2IDI0Mi40NjggMTU2LjE2NCAyNDYuNTYgMTcwLjc1MyBDIDI1MC42NTIgMTg1LjM0MSAyNDguOTg0IDIwMC45NDUgMjQxLjkwMSAyMTQuMzM4IEwgMjQxLjg3NiAyMTQuMzEzIFoiIHN0eWxlPSJmaWxsOiByZ2IoMTY3LCAxNzAsIDE3Myk7Ii8+Cjwvc3ZnPg==";
      add_menu_page( 'Popaket settings', 'Popaket', 'manage_options', 'popaket', array( $this, 'popaket_template' ), $icon, '40' );
    }

    public function enqueue_scripts() {
        if ( class_exists( 'WooCommerce' ) ) {
            wp_enqueue_script( 'popaket-checkout', POPAKET_WOOCOMMERCE_URL . 'assets/js/popaket-checkout.js', array( 'jquery' ), '1.0.0', true );
            wp_enqueue_style( 'popaket-maps', POPAKET_WOOCOMMERCE_URL . 'assets/css/checkout.css', array(), '1.0.0' );
          }
          
        wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
        wp_enqueue_style( 'popaket', POPAKET_WOOCOMMERCE_URL . 'assets/css/admin.css', array(), '1.0.0' );

        wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );
        wp_enqueue_script( 'popaket-admin', POPAKET_WOOCOMMERCE_URL . 'assets/js/popaket-admin.js', array( 'jquery', 'select2' ), '1.0.0', true );

        wp_enqueue_script( 'popaket-maps', POPAKET_WOOCOMMERCE_URL . 'assets/js/popaket-maps.js', array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'popaket-maps', 'popaket_maps', array(
            'api_key' => isset( $this->config['maps_api_key'] ) ? $this->config['maps_api_key'] : '',
        ) );
    }

    public function popaket_init() {
      require_once POPAKET_WOOCOMMERCE_PATH . 'core/class-popaket-shipping.php';
    }

    public function popaket_template() {
      require_once POPAKET_WOOCOMMERCE_PATH . 'templates/popaket.php';
    }

    public function add_shipping_method( $methods ) {
      $methods['popaket'] = 'WC_Popaket_Shipping_Method';
      return $methods;
    }

    public function popaket_checkout_fields( $fields ) {
        $fields['shipping']['shipping_district'] = array(
            'label'       => __( 'District', 'everpro' ),
            'required'    => true,
            'class'       => array( 'form-row-wide' ),
            'placeholder' => __( 'Input District', 'everpro' ),
            'priority'    => 70,
        );
        $fields['shipping']['shipping_subdistrict'] = array(
            'label'       => __( 'Sub District', 'everpro' ),
            'required'    => true,
            'class'       => array( 'form-row-wide' ),
            'placeholder' => __( 'Input Sub district', 'everpro' ),
            'priority'    => 70,
        );
        $fields['shipping']['shipping_phone'] = array(
            'label'    => __( 'No. Telepon / WhatsApp', 'everpro' ),
            'required' => true,
            'class'    => array( 'form-row-wide' ),
            'priority' => 30,
        );
        if ( $this->config['use_insurance'] == 'yes' ) {
          $fields['shipping']['shipping_insurance'] = array(
              'type'     => 'checkbox',
              'label'    => __( 'Pakai Asuransi Pengiriman?', 'everpro' ),
              'class'    => array( 'form-row-wide' ),
              'priority' => 70,
          );
        }
      return $fields;
    }

    public function add_destination_extra_field_to_shipping_packages( $packages ) {
        $post_data = [];
        if ( isset( $_POST['post_data'] ) ) {
            parse_str( $_POST['post_data'], $post_data );
        }
        
        foreach ( $packages as $key => $value ) {
            $packages[$key]['destination']['state'] = WC()->customer->get_shipping_state();
            $packages[$key]['destination']['city'] = isset( $post_data['shipping_city'] ) && $post_data['shipping_city'] ? explode( '|', $post_data['shipping_district'] )[1] : '';
            $packages[$key]['destination']['district'] = isset( $post_data['shipping_district'] ) && $post_data['shipping_district'] ? explode( '|', $post_data['shipping_district'] )[1] : '';
            $packages[$key]['destination']['sub_district'] = isset( $post_data['shipping_subdistrict'] ) && $post_data['shipping_subdistrict'] ? explode( '|', $post_data['shipping_subdistrict'] )[1] : '';
            $packages[$key]['destination']['latitude'] = isset( $post_data['shipping_latitude'] ) && $post_data['shipping_latitude'] ? $post_data['shipping_latitude'] : '';
            $packages[$key]['destination']['longitude'] = isset( $post_data['shipping_longitude'] ) && $post_data['shipping_longitude'] ? $post_data['shipping_longitude'] : '';
            $packages[$key]['destination']['use_insurance'] = ( isset( $post_data['shipping_insurance'] ) && $post_data['shipping_insurance'] == 1 ) ? true : false;
        }

        return $packages;
    }

    public function popaket_checkout_data( $post_data ) {
      $post_data['billing_email'] = isset( $post_data['billing_email'] ) ? $post_data['billing_email'] : ( isset( $this->config['sender_email'] ) ? $this->config['sender_email'] : '' );
      $post_data['shipping_state'] = WC()->customer->get_shipping_state();
      $post_data['shipping_city'] = count( explode( '|', $post_data['shipping_city'] ) ) > 1 ? explode( '|', $post_data['shipping_city'] )[1] : $post_data['shipping_city'];
      $post_data['shipping_district'] = count( explode( '|', $post_data['shipping_district'] ) ) > 1 ? explode( '|', $post_data['shipping_district'] )[1] : $post_data['shipping_district'];
      $post_data['shipping_subdistrict'] = count( explode( '|', $post_data['shipping_subdistrict'] ) ) > 1 ? explode( '|', $post_data['shipping_subdistrict'] )[1] : $post_data['shipping_subdistrict'];
      $post_data['shipping_insurance'] = ( isset( $post_data['shipping_insurance'] ) && $post_data['shipping_insurance'] == 1 ) ? true : false;

      return $post_data;
    }

    public function popaket_view_order( $order ) {
      $popaket_details = $order->get_meta( '_popaket_details' );
      if ( $popaket_details ) :
        ?>
        <tr>
          <td>No. Resi</td>
          <td>
            <?php
              if ( is_array( $popaket_details ) ) {
                $first = true;
                foreach( $popaket_details as $key => $value ) {
                  if ( $first ) {
                    echo '<a href="#" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom" data-bs-content="Copy" onclick="navigator.clipboard.writeText(\'' . $value['awb_number'] . '\');">' . $value['awb_number'] . '</a>';
                    $first = false;
                  } else {
                    echo ', <a href="#" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom" data-bs-content="Copy" onclick="navigator.clipboard.writeText(\'' . $value['awb_number'] . '\');">' . $value['awb_number'] . '</a>';
                  }
                }
              }
            ?>
          </td>
        </tr>
        <?php
      endif;
    }

    public function popaket_checkout_before_order_review_heading() {
      ?>
      <input type="hidden" name="shipping_latitude" id="shipping_latitude">
      <input type="hidden" name="shipping_longitude" id="shipping_longitude">
      <?php
    }

    public function when_creating_order( $order, $data ) {
      $order->update_meta_data( 'popaket_insurance_order', $data['shipping_insurance'] );
      if ( $data['shipping_insurance'] ) {
        $insurance_amount = 0;
        $shipping = $order->get_items( 'shipping' );
        foreach ( $shipping as $item_id => $item ) {
          foreach ( $item->get_meta_data() as $meta ) {
              if ( $meta->key == 'insurance_price' ) {
                $insurance_amount += $meta->value;
              }
          }
        }
        $item_fee = new WC_Order_Item_Fee();
        $item_fee->set_name( 'Asuransi Pengiriman' );
        $item_fee->set_total( $insurance_amount );
        $item_fee->set_tax_class( '' );
        $item_fee->set_total_tax( 0 );
        $item_fee->set_taxes( array() );
        $item_fee->set_total( $insurance_amount );

        $order->add_item( $item_fee );
        $order->calculate_totals();
      }
    }
}