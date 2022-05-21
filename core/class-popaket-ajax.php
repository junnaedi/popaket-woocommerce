<?php

class Popaket_Ajax {

    protected $popaket_api;

    public function __construct() {
        $this->popaket_api = new Popaket_API();
        // on state change
        add_action( 'wp_ajax_popaket_on_state_change', array( $this, 'on_state_change' ) );
        add_action( 'wp_ajax_nopriv_popaket_on_state_change', array( $this, 'on_state_change' ) );
        // on city change
        add_action( 'wp_ajax_popaket_on_city_change', array( $this, 'on_city_change' ) );
        add_action( 'wp_ajax_nopriv_popaket_on_city_change', array( $this, 'on_city_change' ) );
        // on district change
        add_action( 'wp_ajax_popaket_on_district_change', array( $this, 'on_district_change' ) );
        add_action( 'wp_ajax_nopriv_popaket_on_district_change', array( $this, 'on_district_change' ) );
    }

    public function on_state_change() {
        if ( ! $this->popaket_api ) {
            wp_send_json( array(
                'success' => false,
                'message' => 'Popaket disabled or not configured'
            ) );
            wp_die();
        }

        $state        = isset( $_POST['state'] ) ? $_POST['state'] : '';
        $state        = explode( '|', $state )[0];
        $api_province = $this->popaket_api->get_province( $state );

        $city         = isset( $api_province->data[0]->province_id ) ? $api_province->data[0]->province_id : '';
        $api_city     = $this->popaket_api->get_city( $city );

        wp_send_json( array(
            'success' => true,
            'data'    => $api_city->data,
            'message' => 'City Detail successfuly loaded',
        ) );
        wp_die();
    }

    public function on_city_change() {
        if ( ! $this->popaket_api ) {
            wp_send_json( array(
                'success' => false,
                'message' => 'Popaket disabled or not configured'
            ) );
            wp_die();
        }

        $city         = isset( $_POST['city'] ) ? $_POST['city'] : '';
        $city         = explode( '|', $city )[0];
        $api_district = $this->popaket_api->get_district( $city );

        wp_send_json( array(
            'success' => true,
            'data'    => $api_district->data,
            'message' => 'District Detail successfuly loaded',
        ) );
        wp_die();
    }

    public function on_district_change() {
        if ( ! $this->popaket_api ) {
            wp_send_json( array(
                'success' => false,
                'message' => 'Popaket disabled or not configured'
            ) );
            wp_die();
        }

        $district        = isset( $_POST['district'] ) ? $_POST['district'] : '';
        $district        = explode( '|', $district )[0];
        $api_subdistrict = $this->popaket_api->get_subdistrict( $district );

        wp_send_json( array(
            'success' => true,
            'data'    => $api_subdistrict->data,
            'message' => 'Sub District Detail successfuly loaded',
        ) );
        wp_die();
    }
}

new Popaket_Ajax();