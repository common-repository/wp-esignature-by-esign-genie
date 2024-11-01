<?php
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
Class WPESG_DATABASE_QUERIES{
	function insert_wpesg_table($table, $data){
		global $wpdb;
		$insert = $wpdb->replace( $wpdb->prefix.$table, $data);
		// print_r($wpdb->last_query);echo "<br><br>";
		if($insert){
			$lastid = $wpdb->insert_id;
			return $lastid; 
		}else{
			return false; 
		}
	}

	function get_id_esg_owner_table($table){
		global $wpdb;
		$result = $wpdb->get_row("SELECT id FROM $wpdb->prefix$table WHERE status=1");
		return $result;
	}

	function get_token_wpesg_owner_table($table){
		global $wpdb;
		$result = $wpdb->get_var("SELECT access_token FROM $wpdb->prefix$table WHERE status=1");
		return $result;
	}
	function get_expires_in_wpesg_owner_table($table,$token){
		global $wpdb;
		$result = $wpdb->get_row("SELECT id,expires_in,refresh_token FROM $wpdb->prefix$table WHERE status=1 and access_token='".$token."'");
		return $result;
	}
	function get_owner_esg_owner_table($table,$company_id,$party_id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->prefix$table WHERE company_id=$company_id AND party_id=$party_id");
		return $result;
	}
	
	function check_form_mapped_status($table,$wp_form_id,$wp_form_type){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->prefix$table WHERE wp_form_id='".$wp_form_id."' AND wp_form_type='".$wp_form_type."' AND status=1");
		return $result;
	}

	function update_wpesg_table($table, $data, $where){
		global $wpdb;
		$result = $wpdb->update($wpdb->prefix.$table, $data, $where);
		return $result; 
		
	}

	function delete_wpesg_table($table, $where){

		global $wpdb;
		$result = $wpdb->delete($wpdb->prefix.$table, $where);
		if($result){
			$lastid = $wpdb->insert_id;
			return $lastid; 
		}else{
			return $result; 
		}
	}
	function delete_multiple_rows_wpesg_table($table, $ids){
 		global $wpdb;
		$id_ar = explode(',', $ids);
		$result =  $wpdb->query("DELETE FROM ".$wpdb->prefix.$table." WHERE id IN(".$ids.")");
			//print_r($wpdb->last_query);
			return $result; 
	}
	
	function get_active_company_wpesg_owner_table($table){
		global $wpdb;
		$result = $wpdb->get_var("SELECT company_id FROM $wpdb->prefix$table WHERE status=1");
		return $result;
	}
	
	function get_active_mapping_wpesg_mapping_system_table($table,$company_id,$wp_form_id){
		global $wpdb;
		$result = $wpdb->get_row("SELECT id,mapping_name,wp_form_type,wp_form_id,wp_form_name,esg_template_id,company_id,sign_option, signInSequence FROM $wpdb->prefix$table WHERE status=1 AND company_id=$company_id AND wp_form_id=$wp_form_id order by id DESC Limit 1");
		return $result;
	}

	function check_mapped_wp_form($table,$company_id,$wp_form_id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->prefix$table WHERE status=1 AND company_id=$company_id AND wp_form_id=$wp_form_id");
		return $result;
	}
	
	function check_mapped_wpesg_template($table,$company_id,$esg_template_id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->prefix$table WHERE status=1 AND company_id=$company_id AND esg_template_id=$esg_template_id");
		return $result;
	}

	function get_active_mapping_wpesg_fields_mapping_table($table,$mapping_id){
		global $wpdb;
		$result = $wpdb->get_results("SELECT id,form_field_id,form_sub_field,template_field_name FROM $wpdb->prefix$table WHERE mapping_id=$mapping_id");
		return $result;
	}
	

	function get_active_party_wpesg_party_require_parm_table($table,$mapping_id){
		global $wpdb;
		$result = $wpdb->get_results("SELECT id,party_permission,party_sequence FROM $wpdb->prefix$table WHERE mapping_id=$mapping_id");
		return $result;
	}
	
	function get_active_party_wpesg_party_mapping_table($table,$party_id){
		global $wpdb;
/*
		echo "SELECT form_field_id,party_filed_name FROM $wpdb->prefix$table WHERE party_id=$party_id";
		 die();*/

		$result = $wpdb->get_results("SELECT form_field_id,party_filed_name,form_sub_field FROM $wpdb->prefix$table WHERE party_id=$party_id");
		return $result;
	}
	
	function get_active_mapping_wpesg_mapping_system_table_by_mapped_id($table,$mapped_id){
		global $wpdb;
		$result = $wpdb->get_row("SELECT id,mapping_name,wp_form_type,wp_form_id,wp_form_name,esg_template_id,company_id,sign_option, signInSequence FROM $wpdb->prefix$table WHERE status=1 AND id= $mapped_id");
		return $result;
	}
	
	function get_active_mapping_wpesg_fields_mapping_table_by_mapped_id($table1,$table2,$mapped_id){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}$table1 AS field_mapping JOIN {$wpdb->prefix}$table2 AS mappind_system ON field_mapping.mapping_id= mappind_system.id WHERE mapping_id= $mapped_id");
		return $result;
	}
	
	function get_wpesg_party_details_by_mapped_id($table1,$table2,$mapped_id){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}$table1 AS party_mapping JOIN {$wpdb->prefix}$table2 AS party_require_param  ON  party_mapping.party_id = party_require_param.id WHERE party_require_param.mapping_id = $mapped_id");
		return $result;	
	}
	function get_wpesg_party_count_by_mapped_id($table,$mapped_id){
		global $wpdb;
		$result = $wpdb->get_results("SELECT id,party_sequence,party_permission FROM {$wpdb->prefix}$table WHERE mapping_id = $mapped_id");
		return $result;	
	}


}

?>