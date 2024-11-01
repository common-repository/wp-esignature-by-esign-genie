<?php
/* function esg_mapping_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "wpesg_mapping_system";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
       id mediumint(9) NOT NULL AUTO_INCREMENT,
       wp_table_type varchar(255) NOT NULL,
      wp_table_id mediumint(9) NOT NULL,
      template_id date NOT NULL,
      created_date mediumint(9) NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
 
	
}*/
if (!function_exists('create_wpesg_owner_datails_table')) { 
	function create_wpesg_owner_datails_table(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_owner_details";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`access_token` varchar(255) NOT NULL,
		`refresh_token` varchar(255) NOT NULL,
		`expires_in` varchar(255) NOT NULL,
		`company_id` mediumint(9) NOT NULL,
		`party_id` mediumint(9) NOT NULL,
		`status` char(1) NOT NULL,
		`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );
	} 
}

if (!function_exists('create_wpesg_mapping_system')) { 
	function create_wpesg_mapping_system(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_mapping_system";
		$ref_table_name = $wpdb->prefix . "wpesg_owner_details";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`mapping_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
		`wp_form_type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
		`wp_form_id` mediumint(9) NOT NULL,
		`wp_form_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
		`esg_template_id` mediumint(9) NOT NULL,
		`esg_template_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
		`company_id` mediumint(9) NOT NULL,
		`sign_option` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
		`status` char(1) NOT NULL,
		`signInSequence` char(1),
		`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );
	} 
}


if (!function_exists('create_wpesg_fields_mapping')) { 
	function create_wpesg_fields_mapping(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_fields_mapping";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`mapping_id` mediumint(9) NOT NULL,
		`form_field_id` mediumint(9) NOT NULL,
		`form_sub_field` varchar(100) NOT NULL,
		`template_field_name` varchar(255) NOT NULL,
		`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );
	} 
}
if (!function_exists('create_wpesg_party_require_parm')) { 
	function create_wpesg_party_require_parm(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_party_require_param";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`mapping_id` mediumint(9) NOT NULL,
		`party_permission` varchar(255) NOT NULL,
		`party_sequence` smallint(6) NOT NULL,
		`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );
	} 
}

if (!function_exists('create_wpesg_party_mapping')) {
	function create_wpesg_party_mapping(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_party_mapping";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`party_id` mediumint(9) NOT NULL,
		`form_field_id` mediumint(9) NOT NULL,
		`form_sub_field` varchar(100) NOT NULL,
		`party_filed_name` varchar(255) NOT NULL,
		`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
		)$charset_collate;";
		dbDelta( $sql );
	} 
}

if (!function_exists('create_wpwpesg_template_response')) {	
	function create_wpwpesg_template_response(){
		global $wpdb;
		$table_name = $wpdb->prefix . "wpesg_template_response";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`folderId` int(11) NOT NULL,
		`folderCompanyId` int(11) NOT NULL,
		`folderDocumentIds` varchar(255) NOT NULL,
		`folderSentDate` varchar(255) NULL,
		`partyIds` varchar(255) NOT NULL,
		`template_id` varchar(255) NOT NULL,
		`wp_form_id` mediumint(1) NOT NULL,
		`wp_form_entries` longtext NOT NULL
		)$charset_collate;";
		dbDelta( $sql );
	}
}

if (!function_exists('esg_create_table')) {	
	function esg_create_table(){
		create_wpesg_owner_datails_table();
		create_wpesg_mapping_system();
		create_wpesg_fields_mapping();
		create_wpesg_party_require_parm();
		create_wpesg_party_mapping();
		create_wpwpesg_template_response();
	}
	register_activation_hook( WPESG_PLUGIN_BASE, 'esg_create_table' );
}

if (!function_exists('general_admin_notice')) {
	function general_admin_notice(){
		if (is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php')) {
			 define( 'WPForms_ACTIVE',True);
		}else{
			 echo '<div class="notice notice-warning is-dismissible">
				 <p>'.__('WP eSignature Plugin require the following plugin: <em><a href="'.site_url().'/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=wpforms-lite&amp;TB_iframe=true&amp;width=772&amp;height=362" class="thickbox open-plugin-details-modal">WpForms plugin</a></em>', 'ap').'</p>
			 </div>';
			 define( 'WPForms_ACTIVE',False);
		}
	}
	add_action('admin_notices', 'general_admin_notice');
}