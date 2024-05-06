<?php
defined('ABSPATH') or die('Access denied');

class Irizweb_Data_API {
    private $namespace = 'irizweb-data-api/v1';
    private $rest_base = 'data';
    private $table_name = 'irizdata';
    private $api_key = 'd41d8cd98f00b204e9800998ecf8427e';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
    }

    // Registering endpoints
    public function register_endpoints() {
        // View endpoint without authentication
        // Uncomment the line #21 if this requires authentication
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_table_data' ),
            //'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Data post endpoint with authentication
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'insert_data' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => $this->get_endpoint_args(),
        ) );
    }

    // Simple authentication by validating API key
    public function check_permission( $request ) {
        $api_key = $request->get_header( 'DATA-API-KEY' );

        if ( $api_key !== $this->api_key ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid API key.' ), array( 'status' => 403 ) );
        }

        return true;
    }

    // Post arguments and sanitization
    public function get_endpoint_args() {
        return array(
            'name'    => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'email'   => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_email',
            ),
            'address' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'city' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'state' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'country' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            
        );
    }

    // Querying records for view endpoint
    public function get_table_data( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name", ARRAY_A ) );

        return new WP_REST_Response( $data, 200 );
    }

    // Inserting data from post request
    public function insert_data( $request ) {
        $data = $request->get_params();

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $insert_data = array(
            'iriz_name'    => $data['name'],
            'iriz_email'   => $data['email'],
            'iriz_address' => $data['address'],
            'iriz_city' => $data['city'],
            'iriz_state' => $data['state'],
            'iriz_country' => $data['country'],
        );

        $result = $wpdb->insert( $table_name, $insert_data );

        if ( $result === false ) {
            return new WP_REST_Response( array( 'status' => 500,'message' => 'Failed to insert data' ), 500 );
        }

        return new WP_REST_Response( array( 'status' => 200, 'message' => 'Data inserted successfully' ), 200 );
    }
}
