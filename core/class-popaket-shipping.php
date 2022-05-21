<?php

if ( ! class_exists( 'WC_Popaket_Shipping_Method' ) ) {
    class WC_Popaket_Shipping_Method extends WC_Shipping_Method {
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */

        protected $api;

        protected $config;

        public function __construct() {
            $this->id                 = 'popaket';
            $this->title              = __( 'Popaket' );
            $this->method_title       = __( 'Popaket' );
            $this->method_description = __( 'Indonesian Shipping agregator' );

            $this->api = new Popaket_API();

            $this->config = get_option( 'woocommerce_popaket_settings' );

            $this->init();

            $this->form_fields = array(
                    'enabled' => array(
                        'title' 		=> __( 'Enable / Disable' ),
                        'type' 			=> 'checkbox',
                        'label' 		=> __( 'Enable Popaket Shipping' ),
                        'default' 		=> 'no',
                    ),
                    'status' => array(
                        'title' 		=> __( 'Status' ),
                        'type' 			=> 'select',
                        'options'		=> array(
                            'sandbox'		=> __( 'Sandbox' ),
                            'production'	=> __( 'Production' ),
                        ),
                        'default' 		=> 'sandbox',
                    ),
                    'title' => array(
                        'title' 		=> __( 'Title' ),
                        'type' 			=> 'text',
                        'description' 	=> __( 'This controls the title which the user sees during checkout.' ),
                        'default'		=> __( 'Popaket' ),
                        'desc_tip'		=> true
                    ),
                    'client_key' => array(
                        'title'   => __( 'Client Key', 'everpro' ),
                        'type'    => 'text',
                        'default' => ''
                    ),
                    'client_secret' => array(
                        'title'   => __( 'Client Secret', 'everpro' ),
                        'type'    => 'text',
                        'default' => ''
                    ),
            );

            $this->enabled = $this->get_option( 'enabled' );
            $this->title   = $this->get_option( 'title' );
        }

        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init() {
            // Load the settings API
            $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
            $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * calculate_shipping function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping( $package = array() ) {
            $destination = $package['destination'];
            $customer = new \WC_Customer( get_current_user_id() );

            /** Get Origin Postcode */
            $origin_postcode = $this->config['origin_postal_code'];
            $origin_sub_district = '';
            $origin_latitude = '';
            $origin_longitude = '';
            if ( isset( $package['vendor'] ) ) {
                $vendor              = $package['vendor'];
                $origin_postcode     = $vendor->get_postcode();
                $origin_sub_district = $vendor->get_subdistrict();
                $origin_latitude     = $vendor->get_latitude();
                $origin_longitude    = $vendor->get_longitude();
            }
            /** Get Destination Postcode */
            $destination_postcode = $destination['postcode'];

            /** Get weight of cart items */
            $weight = 0;
            $height = 0;
            $length = 0;
            $width = 0;
            foreach ( $package['contents'] as $item_id => $values ) {
                $_product = $values['data'];
                $weight += $this->get_weight_in_kg( $_product->get_weight() ) * $values['quantity'];
                $height += $this->get_dimension_in_cm( floatval( $_product->get_height() ) ) * $values['quantity'];
                $length = ( floatval( $_product->get_length() ) > $length ) ? $this->get_dimension_in_cm( floatval( $_product->get_length() ) ) : $length;
                $width = ( floatval( $_product->get_width() ) > $width ) ? $this->get_dimension_in_cm( floatval( $_product->get_width() ) ) : $width;
            }

            /** check if cart payment method is COD */
            $cod = false;
            $payment_method = WC()->session->get( 'chosen_payment_method' );
            if ( $payment_method == 'cod' ) {
                $cod = true;
            }

            if ( ! empty( $origin_postcode ) && ! empty( $destination_postcode ) ) {
                $rate_args = [
                    'origin_latitude' => floatval( $origin_latitude ),
                    'origin_longitude' => floatval( $origin_longitude ),
                    'origin_postal_code' => $origin_postcode,
                    'origin_sub_district_name' => $origin_sub_district,
                    'destination_latitude' => floatval( $destination['latitude'] ),
                    'destination_longitude' => floatval( $destination['longitude'] ),
                    'destination_postal_code' => $destination_postcode,
                    'destination_sub_district_name' => $destination['sub_district'],
                    'is_cod' => $cod,
                    'is_use_insurance' => true,
                    'item_price' => $package['contents_cost'],
                    'package_type_id' => 1,
                    'shipment_type' => 'PICKUP',
                    'weight' => $weight,
                    'height' => round( $height ),
                    'length' => round( $length ),
                    'width' => round( $width ),
                ];

                if ( $this->config['use_smart_api'] == 'yes' ) {
                    $response = $this->api->get_smart_rate( $rate_args );
                    if ( $response && $response->status_code == 200 ) {
                        $this->process_smart_rate_shipping( $response->data );
                    }
                } else {
                    $response = $this->api->get_rate( $rate_args );
                    if ( $response && $response->status_code == 200 ) {
                        $this->process_basic_rate_shipping( $response->data );
                    }
                }
                
            }
        }

        public function process_basic_rate_shipping( $response ) {
            foreach ( $response as $key => $shipping_type ) {
                $shipping_type_name = str_replace( '_', ' ', $key );
                $shipping_type_name = ucwords( $shipping_type_name );
                foreach ( $shipping_type as $package ) {
                    if ( in_array( $package->logistic_name, $this->config['enabled_courier'] ) || empty( $this->config['enabled_courier'] ) ) {
                        $this->add_rate( array(
                            'id'       => 'popaket_' . $key . $package->rate_code,
                            'label'    => $package->logistic_name . ' - ' . $shipping_type_name,
                            'cost'     => $package->price,
                            'meta_data' => array(
                                'logistic_logo_url'         => $package->logistic_logo_url,
                                'rate_code'                 => $package->rate_code,
                                'min_duration'              => $package->min_duration,
                                'max_duration'              => $package->max_duration,
                                'duration_type'             => $package->duration_type,
                                'insurance_price'           => $package->insurance_price,
                                'weight'                    => $package->weight,
                                'volume_weight'             => $package->volume_weight,
                                'is_available_pickup_today' => $package->is_available_pickup_today,
                            ),
                        ) );
                    }
                }
            }
        }

        public function process_smart_rate_shipping( $response ) {
            foreach ( $response as $key => $paket ) {
                if ( (array) $paket ) {
                    $shipping_type_name = str_replace( '_', ' ', $key );
                    $shipping_type_name = ucwords( $shipping_type_name );
                    $this->add_rate( array(
                        'id'       => 'popaket_' . $key . $paket->rate_code,
                        'label'    => $paket->logistic_name . ' - ' . $shipping_type_name,
                        'cost'     => $paket->price,
                        'meta_data' => array(
                            'logistic_logo_url'         => $paket->logistic_logo_url,
                            'rate_code'                 => $paket->rate_code,
                            'min_duration'              => $paket->min_duration,
                            'max_duration'              => $paket->max_duration,
                            'duration_type'             => $paket->duration_type,
                            'insurance_price'           => $paket->insurance_price,
                            'weight'                    => $paket->weight,
                            'volume_weight'             => $paket->volume_weight,
                            'is_available_pickup_today' => $paket->is_available_pickup_today,
                        ),
                    ) );
                }
            }
        }

        public function get_weight_in_kg( ?int $weight = 0 ) {
            $weight_unit = get_option( 'woocommerce_weight_unit' );
        
            switch ( $weight_unit ) {
                case 'g':
                return $weight / 1000;
                case 'lbs':
                return $weight / 2.2046;
                case 'oz':
                return $weight / 35.274;
                default:
                return $weight;
            }
        }

        public function get_dimension_in_cm( ?int $dimension_value = 0 ) {
            $dimension = get_option( 'woocommerce_dimension_unit' );
        
            switch ( $dimension ) {
                case 'mm':
                return $dimension_value / 10;
                case 'cm':
                return $dimension_value;
                case 'm':
                return $dimension_value * 100;
                default:
                return $dimension_value;
            }
        }
    }
}