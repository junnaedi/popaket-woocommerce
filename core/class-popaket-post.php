<?php

class Popaket_Post {
    public function __construct() {
        add_action( 'admin_post_popaket_save_settings', array( $this, 'save_settings' ) );
    }

    public function save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'popaket-woocommerce' ) );
        }

        $popaket_settings = get_option( 'woocommerce_popaket_settings' );

        update_option( 'woocommerce_popaket_settings', [
            'title' => isset( $popaket_settings['title'] ) ? $popaket_settings['title'] : 'Popaket',
            'enabled' => isset( $popaket_settings['enabled'] ) && $popaket_settings['enabled'] == 'yes' ? 'yes' : 'no',
            'status' => isset( $_POST['status'] ) ? $_POST['status'] : 'sandbox',
            'client_key' => isset( $_POST['client_key'] ) ? $_POST['client_key'] : '',
            'client_secret' => isset( $_POST['client_secret'] ) ? $_POST['client_secret'] : '',
            'origin_address' => isset( $_POST['origin_address'] ) ? $_POST['origin_address'] : '',
            'origin_addess_note' => isset( $_POST['origin_addess_note'] ) ? $_POST['origin_addess_note'] : '',
            'origin_postal_code' => isset( $_POST['origin_postal_code'] ) ? $_POST['origin_postal_code'] : '',
            'sender_name' => isset( $_POST['sender_name'] ) ? $_POST['sender_name'] : '',
            'sender_phone' => isset( $_POST['sender_phone'] ) ? $_POST['sender_phone'] : '',
            'sender_email' => isset( $_POST['sender_email'] ) ? $_POST['sender_email'] : wp_get_current_user()->user_email,
            'default_weight' => isset( $_POST['default_weight'] ) ? $_POST['default_weight'] : '',
            'enabled_courier' => isset( $_POST['enabled_courier'] ) ? $_POST['enabled_courier'] : [],
            'maps_api_key' => isset( $_POST['maps_api_key'] ) ? $_POST['maps_api_key'] : '',
            'use_smart_api' => isset( $_POST['use_smart_api'] ) ? $_POST['use_smart_api'] : 'no',
            'use_insurance' => isset( $_POST['use_insurance'] ) ? $_POST['use_insurance'] : 'no',
        ] );

        wp_safe_redirect( admin_url( 'admin.php?page=popaket' ) );
    }
}

new Popaket_Post();