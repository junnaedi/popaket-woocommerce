<?php

class Popaket_API {
    protected $config;

    public function __construct() {
        $this->config = get_option( 'woocommerce_popaket_settings' );
    }

    public function get_endpoint( $which ) {
        $endpoints = array(
            'sandbox'    => 'https://client-api-sandbox.popaket.com/',
            'production' => 'https://client-api.popaket.com/',
        );

        $popaket_endpoints = [
            'auth'         => $endpoints[$this->config['status']] . 'auth/v1/token',
            'location'     => $endpoints[$this->config['status']] . 'location/v1/postalcode',
            'rates'        => $endpoints[$this->config['status']] . 'shipment/v2/rates',
            'smart_rates'  => $endpoints[$this->config['status']] . 'shipment/v2/rates/services',
            'orders'       => $endpoints[$this->config['status']] . 'shipment/v1/orders',
            'provinces'    => $endpoints[$this->config['status']] . 'location/v1/provinces',
            'cities'       => $endpoints[$this->config['status']] . 'location/v1/cities',
            'districts'    => $endpoints[$this->config['status']] . 'location/v1/districts',
            'subdistricts' => $endpoints[$this->config['status']] . 'location/v1/sub-districts',
        ];

        return $popaket_endpoints[ $which ];
    }

    public function get_token() {
        $token = get_transient( 'woocommerce_popaket_token' );

        if ( ! $token ) {
            $token = $this->get_new_token();
        }

        return $token;
    }

    public function get_new_token() {
        delete_option( 'woocommerce_popaket_token' );
        delete_transient( 'woocommerce_popaket_token' );
        $response = wp_remote_post(
            $this->get_endpoint( 'auth' ),
            [
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'body' => json_encode( [
                    'client_key'    => $this->config['client_key'],
                    'client_secret' => $this->config['client_secret'],
                ] ),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $body = json_decode( $response['body'] );
        if ( ! $body->data->token ) {
            return false;
        }

        set_transient( 'woocommerce_popaket_token', $body->data->token, $body->data->expires );
        return $body->data->token;
    }

    /**
     * Get location detail by adding full address or postcode
     *
     * @param string $full_address
     * @param string $postal_code
     * @return array
     */
    public function get_location( $postal_code = '' ) {
        $response = wp_remote_post(
            $this->get_endpoint( 'location' ),
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'body' => json_encode([
                    'postal_code' => $postal_code,
                ]),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    /**
     * Get rate by postcode
     *
     * @param string $origin_postcode
     * @param string $destination_postcode
     * @return array
     */
    public function get_rate( $meta = [] ) {
        $args = wp_parse_args( $meta, [
            'origin_latitude' => 0,
            'origin_longitude' => 0,
            'origin_postal_code' => '',
            'origin_sub_district_name' => '',
            'destination_latitude' => 0,
            'destination_longitude' => 0,
            'destination_postal_code' => '',
            'destination_sub_district_name' => '',
            'is_cod' => false,
            'is_use_insurance' => false,
            'item_price' => '',
            'package_type_id' => 1,
            'shipment_type' => 'PICKUP',
            'weight' => $this->config['default_weight'],
            'height' => 0,
            'length' => 0,
            'width' => 0,
        ] );

        /** Calculate weight */
        if ( $args['weight'] <= 1.2 ) {
            $args['weight'] = 1;
        }

        $response = wp_remote_post(
            $this->get_endpoint( 'rates' ),
            [
                'timeout' => 10000,
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'body' => json_encode( $args ),
                'data_format' => 'body',
            ]
        );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_smart_rate( $meta = [] ) {
        $args = wp_parse_args( $meta, [
            'origin_latitude' => 0,
            'origin_longitude' => 0,
            'origin_postal_code' => '',
            'origin_sub_district_name' => '',
            'destination_latitude' => 0,
            'destination_longitude' => 0,
            'destination_postal_code' => '',
            'destination_sub_district_name' => '',
            'is_cod' => false,
            'is_use_insurance' => false,
            'item_price' => '',
            'package_type_id' => 1,
            'shipment_type' => 'PICKUP',
            'weight' => $this->config['default_weight'],
            'height' => 0,
            'length' => 0,
            'width' => 0,
        ] );

        /** Calculate weight */
        if ( $args['weight'] <= 1.2 ) {
            $args['weight'] = 1;
        }

        $response = wp_remote_post(
            $this->get_endpoint( 'smart_rates' ),
            [
                'timeout' => 10000,
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'body' => json_encode( $args ),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        return json_decode( $response['body'] );
    }

    public function order( $data ) {
        $response = wp_remote_post(
            $this->get_endpoint( 'orders' ),
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'body' => json_encode( $data ),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_awb_number( $client_order_no ) {
        $response = wp_remote_get(
            $this->get_endpoint( 'orders' ) . '/' . $client_order_no . '/awb',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function cancel_order( $awb, $reason ) {
        $response = wp_remote_post(
            $this->get_endpoint( 'orders' ) . '/' . $awb . '/cancel',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'body' => json_encode( [
                    'cancel_reason' => $reason,
                ] ),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function generate_label( $awb_no ) {
        $response = wp_remote_get(
            $this->get_endpoint( 'orders' ) . '/' . $awb_no . '/label',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function track( $awb_no ) {
        $response = wp_remote_get(
            $this->get_endpoint( 'orders' ) . '/' . $awb_no . '/track',
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_province( $province = '' ) {
        $params = '';

        if ( $province ) {
            $params = http_build_query( [
                'province_name' => $province,
            ] );
        }

        $response = wp_remote_get(
            $this->get_endpoint( 'provinces' ) . '?' . $params,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_city( $province_id = '' ) {
        $params = '';
        if ( $province_id ) {
            $params = http_build_query( [
                'province_id' => $province_id,
            ] );
        }

        $response = wp_remote_get(
            $this->get_endpoint( 'cities' ) . '?' . $params,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_district( $city_id = '' ) {
        $params = '';
        if ( $city_id ) {
            $params = http_build_query( [
                'city_id' => $city_id,
            ] );
        }

        $response = wp_remote_get(
            $this->get_endpoint( 'districts' ) . '?' . $params,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }

    public function get_subdistrict( $district_id = '' ) {
        $params = '';
        if ( $district_id ) {
            $params = http_build_query( [
                'district_id' => $district_id,
            ] );
        }

        $response = wp_remote_get(
            $this->get_endpoint( 'subdistricts' ) . '?' . $params,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $this->get_token(),
                ],
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'] );
    }
}