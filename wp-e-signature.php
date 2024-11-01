<?php
/*
Plugin Name: eSignature for WordPress
Plugin URI: 
Description: eSignature for WordPress by eSign Genie allows you to send a pre-existing eSign Genie template as a document to the recipient parties for electronic signatures instantly. It is easy to map the WPForms field to eSign Genie template fields and pre-fill the document with values from the WPForms effortlessly while signing on any documents without leaving the WP website.
Author: eSign Genie
Author URI: 
Domain Path:
Requires at least: 
Requires PHP: 
Version: 1.2.1
*/

// Security Check If this file called directly, abord  
if ( ! defined( 'WPINC' ) ) {
	die;
}


// Define Plugin Version
if ( ! defined( 'WPESG_PLUGIN_FOLDER' ) ) {
	 define( 'WPESG_PLUGIN_FOLDER', 'wp-e-signature');
}


// Define Plugin Version
if ( ! defined( 'WPESG_PLUGIN_VERSION' ) ) {
	 define( 'WPESG_PLUGIN_VERSION', '1.2.1');
}

// Define Plugin Version
if ( ! defined( 'WPESG_PLUGIN_BASE' ) ) {
	 define( 'WPESG_PLUGIN_BASE', __FILE__ );
}

// Define Plugin DIR Path
if ( ! defined( 'WPESG_PLUGIN_PATH' ) ) {
	 define( 'WPESG_PLUGIN_PATH',plugin_dir_path( __FILE__ ) );
}

// Define Plugin Base Path
if ( ! defined( 'WPESG_PLUGIN_BASENAME' ) ) {
	define( 'WPESG_PLUGIN_BASENAME', plugin_basename( WPESG_PLUGIN_BASE ) );
}

// Define Plugin URL
if ( ! defined( 'WPESG_PLUGIN_URL' ) ) {
	 define( 'WPESG_PLUGIN_URL',plugin_dir_url( __FILE__ ) );
}

// Define Plugin Include Folder Path
if ( ! defined( 'WPESG_PLUGIN_PATH_INCLUDE' ) ) {
	 define( 'WPESG_PLUGIN_PATH_INCLUDE',plugin_dir_path( __FILE__ ) . 'inc' );
}

// Define Plugin Admin Js Path
if ( ! defined( 'WPESG_PLUGIN_PATH_ADMIN_JS' ) ) {
	 define( 'WPESG_PLUGIN_PATH_ADMIN_JS',WPESG_PLUGIN_URL.'asset/admin/js' );
}

// Define Plugin Admin CSS Path
if ( ! defined( 'WPESG_PLUGIN_PATH_ADMIN_CSS' ) ) {
	 define( 'WPESG_PLUGIN_PATH_ADMIN_CSS',WPESG_PLUGIN_URL.'asset/admin/css' );
}
// Define Plugin Admin Js Path
if ( ! defined( 'WPESG_PLUGIN_PATH_ADMIN_IMAGE' ) ) {
	 define( 'WPESG_PLUGIN_PATH_ADMIN_IMAGE',WPESG_PLUGIN_URL.'asset/admin/images' );
}

// Define Current HOST URL
if ( ! defined( 'WPESG_AUTH_URL' ) ) {
	 define( 'WPESG_AUTH_URL', 'https://www.esigngenie.com/wp-plugin-auth.php');
}

	if(get_wpesg_apis_auth()){
		$result = get_wpesg_apis_auth();	
		$de_result = base64_decode($result);	
		$mix_auth = explode('&&&',$de_result);
		// Define ESG Client ID 
		if ( ! defined( 'WPESG_CLIENT_ID' ) ) {
			 define( 'WPESG_CLIENT_ID',$mix_auth[1]);
		}

		// Define Plugin client_secret
		if ( ! defined( 'WPESG_CLIENT_SECRET' ) ) {
			 define( 'WPESG_CLIENT_SECRET',$mix_auth[2] );
		}
		// Define Current HOST URL
		if ( ! defined( 'WPESG_API_URL' ) ) {
			 define( 'WPESG_API_URL', $mix_auth[3]);
		}
		// Define Current HOST URL
		if ( ! defined( 'WPESG_AUTH_API_URL' ) ) {
			 define( 'WPESG_AUTH_API_URL', $mix_auth[3].'oauth2/authorize');
		}
	}

// Define Current HOST URL
if ( ! defined( 'WPESG_CURRENT_HOST_URL' ) ) {
	 define( 'WPESG_CURRENT_HOST_URL', site_url());
}

// Create Table for our plugin.
require WPESG_PLUGIN_PATH. 'inc/db.php';

if ( ! class_exists( 'WPESG_Mapping_Admin_Menus' ) ) {
	// Create Table for our plugin.
	require WPESG_PLUGIN_PATH. 'settings.php';
}
if (!function_exists('esg_plugin_action_links')) { 
	add_filter( 'plugin_action_links', 'esg_plugin_action_links', 10, 2 );
	function esg_plugin_action_links( $links, $file ) {
		
		if ( $file != WPESG_PLUGIN_BASENAME ) {
			return $links;
		}

		$settings_link = '<a href="' . menu_page_url( 'wpesg-settings', false ) . '">'
			. esc_html( __( 'Settings', 'wp-e-signature' ) ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}
}
//exit( wp_redirect( menu_page_url( 'wpesg-settings', false ) ) );
//SEND FORM DATA To eSign Genie TEMPLATE 
 
	function get_wpesg_apis_auth(){
		$args = array(
			'timeout' => '30',
			'redirection' => '10',
			'httpversion' => '1.0',
			'headers' => array("token"=>"W678NH90356") 
		);
		$response = wp_remote_post( WPESG_AUTH_URL, $args );
		if($response['response']['code'] == 200){
			return $response['body'];
		}else{
		  return false;
		}
	}


