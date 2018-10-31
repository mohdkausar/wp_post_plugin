<?php
/*
Plugin Name: REST API POSTS
Description: Fetch all the posts
Version: 1.0
Author: Kausar

*/

/**
 * Use * for origin
 */
add_action( 'rest_api_init', function() {
    
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function( $value ) {
		header( 'Accept: */*' );
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );
		return $value;
	});
}, 15 );

if($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
}

//////////////////////////////////////////////ACCESS CONTROL ORIGIN


define( 'DASHBOARD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DASHBOARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//add_action('rest_api_init',function(){ob_start('ob_gzhandler');},15);
add_action( 'init', 'dashboard_init' );


/**
 * CLASS FILES 
 */
require_once(DASHBOARD_PLUGIN_PATH.'core/classes/dashboard-class.php'); // Has top level helpers
/**
 * ROUTE FILES
 */
require_once(DASHBOARD_PLUGIN_PATH.'routes/users-routes.php');

function dashboard_init() {
	
    $users_routes = new users_routes();
    $users_routes->register_routes();
}

/* Changing API prefix */
add_filter( 'rest_url_prefix', 'change_api_slug'); 
function change_api_slug( $slug ) { return 'api'; }

/* Check api call & validate current owner */
add_filter( 'rest_pre_dispatch', 'initial_validation', 10, 3);

function initial_validation($me, $server, $request){
    $token = "AF1MQPOMY9K3BTMDWKA4SA3QOIF1PTM5ON6LAL1N";

    if($_SERVER['REQUEST_URI'] === '/dash/api/dashboard/users/v1/login'){
        
        if($_SERVER['HTTP_AUTHORIZATION'] === $token){
            //Do Nothing
        }else{
            return array(
                'result'    => false,
                'message'   => 'APIs have been disabled. Wrong Token.',
                'url'       => $_SERVER['REQUEST_URI'],
                'token'     => $_SERVER['HTTP_AUTHORIZATION']
            );
        }

    }else{

    }

    return $result;
}