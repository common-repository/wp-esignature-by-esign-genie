<?php
if ( ! class_exists( 'WPESG_DATABASE_QUERIES' ) ) {
	require_once('inc/wpesg_database_quries_class.php' );
	$esgdb = NEW WPESG_DATABASE_QUERIES();
}

if ( ! class_exists( 'WPESG_Mapping_Item_List' ) ) {
	require_once('wpesg_mapping_list.php' );
}
require_once('add-edit-mapping-function.php' );
class WPESG_Mapping_Admin_Menus {
	// class instance
	static $instance;
	// customer WP_List_Table object
	public $wpesg_mapping_list_obj;
	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );

	}
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}
	public function plugin_menu() {
		$hook = add_menu_page( 'eSignature for WP', 'eSignature for WP', 'manage_options', 'wpesg-mapping-list', [ $this, 'plugin_settings_page' ], WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/favicon.png', 30 );
		add_submenu_page('wpesg-mapping-list','All Mappings','All Mappings','manage_options','wpesg-mapping-list');
		add_submenu_page('wpesg-mapping-list','Add New Mapping','Add New Mapping','manage_options','wpesg-add-mapping','wpesg_add_mapping');
		add_submenu_page('wpesg-mapping-list','Settings','Settings','manage_options','wpesg-settings','wpesg_settings');
		add_submenu_page(null,'Edit Mapping','Edit Mapping','manage_options','wpesg-edit-mapping','wpesg_edit_mapping');
		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}

	/**
	* Plugin settings page
	*/
	public function plugin_settings_page() {
		$check='';
		?>
		<div class="wrap">
			<h2>All Mapping List <a herf="#" class="ttl-bottom" data-tooltip="What does mapping do? You can map WPForms fields with eSign Genie template fields. When you submit the WPForm, contract or any other type of signable documents are created from the template with field information populated from the WPForms."><img src="<?php echo WPESG_PLUGIN_PATH_ADMIN_IMAGE; ?>/question-circle-light.png" ></a></h2>
			<?php 		
				$esg_templates_data = wpesg_get_templates_list();
				if(!$esg_templates_data){
					$check=1;
					$alert = '<div class="row">
							<div class="col-8"><p class="alert alert-danger">You need to create a eSign Genie template after login our <a href="https://www.esigngenie.com/esign/">eSign Genie Application or reconnect your account from plguin setting.</a><br /></div></div>';
					echo $alert; 
				}	
			?>	
			<div class="warp mt-4"><a class="ttl-left" href="admin.php?page=wpesg-add-mapping" data-tooltip="What does mapping do? You can map WPForms fields with eSign Genie template fields. When you submit the WPForm, contract or any other type of signable documents are created from the template with field information populated from the WPForms."><button  class="btn btn-sm esg-primary">Add New Mapping</button></a><hr class="wp-header-end">
			</div>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								if($check != 1){
								$this->wpesg_mapping_list_obj->prepare_items();
								}
								$this->wpesg_mapping_list_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	* Screen options
	*/
	public function screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'eSignature for WordPress Mapping List',
			'default' => 5,
			'option'  => 'mapping_list_per_page'
		];
		add_screen_option( $option, $args );
		$this->wpesg_mapping_list_obj = new WPESG_Mapping_Item_List();
	}
	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}
	add_action( 'plugins_loaded', function () {
		WPESG_Mapping_Admin_Menus::get_instance();
	} );


if (!function_exists('esg_admin_scripts')) { 
	//Load Bootstrap CSS & JS
	function esg_admin_scripts() {
		wp_enqueue_style('esg_bootstrap',  plugins_url('"asset/admin/css/bootstrap.min.css"', __FILE__),false,null);
		wp_register_script( 'esg_admin_js',WPESG_PLUGIN_PATH_ADMIN_JS.'/main.js',array( 'jquery' ),'',true );
		wp_enqueue_script('esg_admin_js');
		wp_enqueue_style('esg-admin-css', plugins_url('"asset/admin/css/style.css"', __FILE__));
		$script_data = array(
			'image_path' => WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/loader.gif'
		);
		 wp_localize_script(
			'esg_admin_js',
			'esg_loder',
			$script_data
		);	
	}
	add_action( 'admin_enqueue_scripts', 'esg_admin_scripts' );
}
if (!function_exists('esg_frontend_scripts')) { 
	function esg_frontend_scripts() {
		wp_register_style('esg-frontend-css', plugins_url('"asset/public/css/style.css"', __FILE__));
		wp_enqueue_style( 'esg-frontend-css' );	
	}
	add_action( 'wp_enqueue_scripts', 'esg_frontend_scripts' );
}

if (!function_exists('wpesg_mapping_list')) { 
	function wpesg_mapping_list(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT DISTINCT(EMS.id),EMS.mapping_name,EMS.wp_form_type as 'Form_Type',EMS.wp_form_name as 'From_Title',EMS.esg_template_name as 'Template_Name',EMS.created_at as 'Created'   FROM `".$wpdb->prefix."wpesg_mapping_system` as EMS LEFT JOIN  `".$wpdb->prefix."wpesg_fields_mapping` as EFM ON EMS.id = EFM.mapping_id" );
		echo '<div class="warp"><h3 class="wp-heading-inline">ESG Mapping List</h3><a href="'.WPESG_CURRENT_HOST_URL.'/wp-admin/admin.php?page=wpesg-add-mapping" class="btn btn-default btn-sm">Add New</a><hr class="wp-header-end"><table class="wp-list-table widefat fixed striped ">
		<thead>
		<tr>
			<th class="manage-column column-cb check-column"></th><th scope="col" class="manage-column column-form_name column-primary sortable desc"><span>Mapping Name</span></th><th scope="col" class="manage-column column-form_name column-primary sortable desc"><span>Form Type</span></th><th scope="col" class="manage-column column-shortcode">Form Title</th><th scope="col" class="manage-column column">Template Name</th><th scope="col" class="manage-column column">Created</th></tr>
		</thead><tbody id="the-list" data-wp-lists="list:form">';
		if(!empty($result)){
		foreach($result as $field){
			echo '<tr><td class="manage-column column-cb check-column"></td><td class="form_name column-form_name has-row-actions column-primary">'.$field->mapping_name.'</td><td class="form_name column-form_name has-row-actions column-primary">'.$field->Form_Type.'</td><td class="form_name column-form_name has-row-actions column-primary">'.$field->From_Title.'</td><td class="form_name column-form_name has-row-actions column-primary">'.$field->Template_Name.'</td><td class="created column-created" data-colname="Created">'.$field->Created.'</td></tr>';
		}
		}else{
			echo '<tr class="no-items"><td class="colspanchange" colspan="5">No pages found in Trash.</td></tr>';
		}
		echo '</tbody><tfoot>
		<tr>
			<td class="manage-column column-cb check-column"></td><th scope="col" class="manage-column column-form_name column-primary sortable desc"><span>Mapping Name</span></th><th scope="col" class="manage-column column-shortcode">Form Type</th><th scope="col" class="manage-column column-shortcode">Form Title</th><th scope="col" class="manage-column column">Template Name</th><th scope="col" class="manage-column column">Created</th></tr>
		</tfoot>
		</table>
		</div>';
	}
}

if (!function_exists('wpesg_settings')) {
	function wpesg_settings(){
		global $esgdb;
		$company_id = $esgdb->get_active_company_wpesg_owner_table('wpesg_owner_details');
		$esg_plugin_auth_api_url =  WPESG_CURRENT_HOST_URL.'/wp-json/'.WPESG_PLUGIN_FOLDER.'/v1/authorization-code';
		echo '<div class="warp mt-4"><div class="container-fluid"><div class="row"><div class="col-12"><h3>eSignature for WordPress Settings</h3></div></div><div class="col-12"><hr /></div></div><div class="row"><div class="col-2"><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/eSignGenie-logo.png" style="max-width:140px;" ></div><div class="col-5"><b>Steps to set up and start using eSign Genie:</b><ol><li>Create an eSign Genie Account</li><li> Create  a template in eSign Genie that will be mapped with the WPForms:<a href="https://www.youtube.com/watch?v=_xJI9f0fFIg&t=82s" target="_blank">Demo</a></li><li>Install the \'eSignature for WordPress by eSign Genie\' plugin</li><li>Connect with eSign Genie Account</li><li>Map your WPForms fields with eSign Genie template fields</li><li>Test your WPForms by filling out and submitting</li></ol></div><div class="col-5">WordPress is requesting permission to connect to your eSign Genie account.WordPress will be able to access your eSign Genie data, but will not be able to see your eSign Genie account password.</div></div>';
		if($company_id){
			echo '<div class="row"><div class="col-10"></div><div class="col-2"><button class="btn btn-warning fr" id="esg-disconnect" data-id='.$company_id.'>Disconnect</button></div></div>';
		}else{
			$url = WPESG_AUTH_API_URL.'?client_id='.WPESG_CLIENT_ID;
		   echo '<div class="row"><div class="col-10"></div><div class="col-2"><button class="btn btn-primary fr" onclick="window.open(\''.$url.'&response_type=code&state=esgtest&redirect_uri='.$esg_plugin_auth_api_url.'\', \'_blank\', \'toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=800,height=500\')">Connect</button></div></div>';
		}   
		echo '</div></div>';
	}
}
if (!function_exists('wpesg_authorization_code')) {
	function wpesg_authorization_code(){
		if(isset($_GET['code'])){
			global $wp;
			global $esgdb;
			$current_url = home_url( $wp->request );
			$data_res = crul_requert_eccess_token($_GET['code'],$current_url);
			$response = json_decode($data_res,true);
			$owner_id = $esgdb->get_owner_esg_owner_table('wpesg_owner_details',$response['companyId'],$response['partyId']);
			$date = date('Y/m/d H:i:s');
			if($owner_id){
				$data=array('access_token'=>$response['access_token'],'refresh_token'=>$response['refresh_token'],'expires_in'=>$response['expires_in'],'status'=>1,'updated_at'=>$date);
				$esgdb-> update_wpesg_table('wpesg_owner_details',$data,array('id'=>$owner_id));
				$esgdb->update_wpesg_table('wpesg_mapping_system',array('updated_at'=>$date,'status'=>1),array('company_id'=>$response['companyId']));
			}else{
				$data=array('access_token'=>$response['access_token'],'refresh_token'=>$response['refresh_token'],'expires_in'=>$response['expires_in'],'company_id'=>$response['companyId'],'party_id'=>$response['partyId'],'status'=>1,'created_at'=>$date,'updated_at'=>$date); 
				$mapped_id = $esgdb->insert_wpesg_table('wpesg_owner_details',$data);
				$esgdb->update_wpesg_table('wpesg_mapping_system',array('updated_at'=>$date,'status'=>1),array('company_id'=>$response['companyId']));
			}
		header("Content-Type:text/html");
		echo '<script>
			   window.opener.location.reload();
			   window.close();
		  </script>';
		Exit;
		}elseif(isset($_REQUEST['error']) && $_REQUEST['error'] == 'access_denied'){
			header("Content-Type:text/html");
			echo  '<script>
			window.close();
			</script>';
			Exit;	  
		}
	}
}

add_action('rest_api_init', function () {
$wp_request_headers = array(
    'Content-Type' => 'text/html'
);
 
register_rest_route( WPESG_PLUGIN_FOLDER.'/v1', 'authorization-code/',array(
      'methods'  => 'GET',
	  'headers'   => $wp_request_headers,
      'callback' => 'wpesg_authorization_code'
	));
});


// GET ACCESS TOKEN FROM eSign Genie 
if (!function_exists('crul_requert_eccess_token')) {
	function crul_requert_eccess_token($code,$redirect_uri){
		$pass_data = array('grant_type'=>'authorization_code','client_id'=>WPESG_CLIENT_ID,'client_secret'=>WPESG_CLIENT_SECRET,'code'=>$code,'redirect_uri'=>$redirect_uri); 
		$args = array(
			'body' => $pass_data,
			'timeout' => '30',
			'redirection' => '10',
			'httpversion' => '1.0',
			'headers' => array("content-type"=>"application/x-www-form-urlencoded") 
		);
		$response = wp_remote_post( WPESG_API_URL."api/oauth2/access_token", $args );
		if ($response['response']['code'] == 200) {
			return $response['body'];
		} else {
		  return false;
		}
	}
}
// GET ACCESS TOKEN FROM Refresh TOken
if (!function_exists('crul_requert_eccess_token_by_refresh_token')) {
	function crul_requert_eccess_token_by_refresh_token($refresh_token){
		$pass_data = array('grant_type'=>'refresh_token','client_id'=>WPESG_CLIENT_ID,'client_secret'=>WPESG_CLIENT_SECRET,'refresh_token'=>$refresh_token); 
		$args = array(
			'body' => $pass_data,
			'timeout' => '30',
			'redirection' => '10',
			'httpversion' => '1.0',
			'headers' => array("content-type"=>"application/x-www-form-urlencoded") 
		);
		$response = wp_remote_post( WPESG_API_URL."api/oauth2/access_token", $args );
		if ($response['response']['code'] == 200) {
			return $response['body'];
		} else {
		  return false;
		}
	}
}


//GET SINGLE TEMPLATE DETAILS USING TEMPLATE ID FROM eSign Genie
if (!function_exists('wpesg_get_template_details_by_id')) {
	function wpesg_get_template_details_by_id($esg_tempalte_id){
		global $esgdb;
		$data = $esgdb->get_token_wpesg_owner_table('wpesg_owner_details');
		if($data){
			$args = array(
				'timeout' => '30',
				'redirection' => '10',
				'headers' => array(
				'Authorization' => 'Bearer ' .$data,
				'content-type' => 'application/json'
				)
			);
		$response = wp_remote_get( WPESG_API_URL."api/templates/mytemplate?templateId=".$esg_tempalte_id, $args );
			if($response['response']['code'] == 200) {
				return $response['body'];
			}else{
			  return false;
			}
		}else{
			return false;
		}	
		die;
	}
}
//GET ALL TEMPLATE FROM eSign Genie
if (!function_exists('wpesg_get_templates_list')) {
	function wpesg_get_templates_list(){
		global $esgdb;
		$data = $esgdb->get_token_wpesg_owner_table('wpesg_owner_details');	
		$own_data = $esgdb->get_expires_in_wpesg_owner_table('wpesg_owner_details',$data);
        $expdate = date('Y-m-d', strtotime('-5 day', strtotime($own_data->expires_in)));
		$cdate = date('Y-m-d');
		if($cdate > $expdate){
		    $data_res = crul_requert_eccess_token_by_refresh_token($own_data->refresh_token);
			$response = json_decode($data_res,true);
			if(isset($response['access_token'])){
			$predata=array('access_token'=>$response['access_token'],'refresh_token'=>$response['refresh_token'],'expires_in'=>$response['expires_in'],'updated_at'=>date('Y/m/d H:i:s')); 
	         $esgdb-> update_wpesg_table('wpesg_owner_details',$predata,array('id'=>$own_data->id));	
		     $data = $response['access_token'];
			}		  
		}
		if($data){
			$args = array(
			'timeout' => '30',
			'redirection' => '10',
			'httpversion' => '1.0',
			'headers' => array("Authorization"=>"Bearer $data","content-type"=>"application/json") 
			);
		$response = wp_remote_get( WPESG_API_URL."api/templates/list", $args );
		if($response['response']['code'] == 200) {
				return $response['body'];
			}else{
			  return false;
			}
		}else{
			 return false;
		}	
	}
}


// Disconnect Button Ajax Call
if (!function_exists('wpesg_disconnect_ajax_call')) {
	function wpesg_disconnect_ajax_call(){
		global $esgdb;
		$esgdb->update_wpesg_table('wpesg_owner_details',array('status'=>0),array('company_id'=>esc_sql($_REQUEST['company_id'])));
		$esgdb->update_wpesg_table('wpesg_mapping_system',array('status'=>0),array('company_id'=>esc_sql($_REQUEST['company_id'])));
		echo 1;
		exit;
	}
	add_action( 'wp_ajax_wpesg_disconnect_ajax_call', 'wpesg_disconnect_ajax_call' );
}
// Wpforms get filed List
if (!function_exists('wpesg_wpforms_fields_list_for_party')) {
	function wpesg_wpforms_fields_list_for_party(){
		$html = '';
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != ''){
			global $esgdb;
			$esg_wpforms_data = get_post($_REQUEST['id']);
			$esg_form_data_array = json_decode($esg_wpforms_data->post_content);		
			for($i=1;$i<=5;$i++){
			$html .= '<div class="form-group" id="select_Wpforms_field_party_row_'.$i.'"><select class="form-control  form-control-sm" id="" name="select_Wpforms_field_for_party[1][]"><option value="">Select Form Field</option>';
				foreach($esg_form_data_array->fields as $field){
					if($field->type == 'name'){
						if($field->format == 'simple'){				
							$html .= '<option value="'.$field->id.'">'.$field->label.'</option>';
						}elseif($field->format == 'first-last'){
							$html .= '<option value="'.$field->id.'-first">First '.$field->label.'</option>';
							$html .= '<option value="'.$field->id.'-last">Last '.$field->label.'</option>';
						}else{
							$html .= '<option value="'.$field->id.'-first">First '.$field->label.'</option>';
							$html .= '<option value="'.$field->id.'-middle">Middle '.$field->label.'</option>';
							$html .= '<option value="'.$field->id.'-last">Last '.$field->label.'</option>';
						}
					}else{
						$html .= '<option value="'.$field->id.'">'.$field->label.'</option>';
					}
				}	
				$html .= '</select></div>';
			}

		}
		echo $html;
		die;
	}
}

if (!function_exists('wpesg_add_field_list')) {
	function wpesg_add_field_list(){
		$form_field_html = '';
		$temp_field_html = '';
		$html = array();
		if(isset($_REQUEST['data']['formId']) && $_REQUEST['data']['formId'] != ''){
			$esg_wpforms_data = get_post($_REQUEST['data']['formId']);
			$esg_form_data_array = json_decode($esg_wpforms_data->post_content);
			$form_field_html .= '<div class="form-group" id="select_Wpforms_field_row_'.$_REQUEST['data']['fieldCount'].'"><select class="form-control  form-control-sm" id="" name="select_Wpforms_field[]"><option value="">Select Your Form</option>';
			foreach($esg_form_data_array->fields as $field){
                if($field->type != 'pagebreak' && $field->type != 'html' && $field->type != 'file-upload' && $field->type != 'pagebreak'){				
				if($field->type == 'name'){
					if($field->format == 'simple'){				
					//$form_field_html .= '<option value="'.$field->id.'">'.$field->label.'</option>';
					}elseif($field->format == 'first-last'){
						$form_field_html .= '<option value="'.$field->id.'-first">First '.$field->label.'</option>';
						$form_field_html .= '<option value="'.$field->id.'-last">Last '.$field->label.'</option>';
					}else{
						$form_field_html .= '<option value="'.$field->id.'-first">First '.$field->label.'</option>';
						$form_field_html .= '<option value="'.$field->id.'-middle">Middle '.$field->label.'</option>';
						$form_field_html .= '<option value="'.$field->id.'-last">Last '.$field->label.'</option>';
					}
				}elseif($field->type == 'address'){
							if($field->address2_hide == 1 || $field->postal_hide == 1){
                               if($field->address2_hide == 1){								
								$form_field_html .= '<option value="'.$field->id.'-address1" >Address Line 1('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-city" >City ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-state" >State ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-postal" >Postal ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-country" >Country ('.$field->label.')</option>';
							   }else{
								$form_field_html .= '<option value="'.$field->id.'-address1" >Address Line 1('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-address2" >Address Line 2('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-city" >City ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-state" >State ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-country" >Country ('.$field->label.')</option>';
							   }
							}else{
								$form_field_html .= '<option value="'.$field->id.'-address1" >Address Line 1('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-address2" >Address Line 2('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-city" >City ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-state" >State ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-postal" >Postal ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-country">Country ('.$field->label.')</option>';
							}
						}elseif($field->type == 'date-time'){
							if($field->format == 'date'){				
								$form_field_html .= '<option value="'.$field->id.'-date" >Date ('.$field->label.')</option>';
							}elseif($field->format == 'date-time'){
								$form_field_html .= '<option value="'.$field->id.'-date" >Date ('.$field->label.')</option>';
								$form_field_html .= '<option value="'.$field->id.'-time" >Time ('.$field->label.')</option>';
							}else{
								$form_field_html .= '<option value="'.$field->id.'-time" >Time ('.$field->label.')</option>';
							}
						}elseif($field->type == 'radio' || $field->type == 'checkbox' || $field->type == 'payment-multiple'){
								foreach($field->choices as $choice){
									$label = wp_strip_all_tags($choice->label);
									$form_field_html .= '<option value="'.$field->id.'-'.$label.'" '.$seleted.'>'.$label.' ('.$field->label.')</option>';
								}
						}else{
					$form_field_html .= '<option value="'.$field->id.'-'.$field->type.'">'.$field->label.'</option>';
				}
			}	
			}	
			$form_field_html .= '</select></div>';
		}
		$html['wpforms'] = $form_field_html;
		if(isset($_REQUEST['data']['tempId']) && $_REQUEST['data']['tempId'] != ''){
			$data = wpesg_get_template_details_by_id(esc_sql($_REQUEST['data']['tempId']));
			if($data){
				$esg_tempalte_fields_details = json_decode($data,true);
				foreach($esg_tempalte_fields_details['allfields'] as $key => $field){
					if($field['fieldType'] == 'signfield' || $field['fieldType'] == 'initialfield' || ($field['fieldType'] == 'textfield' && $field['textfieldName'] == 'Signer Name') || ($field['fieldType'] == 'textfield' && $field['textfieldName'] == 'Signer Email')  || ($field['fieldType'] == 'datefield' && $field['datefieldName'] == 'Date Signed')   || $field['fieldType'] == 'buttonfield'){	
					  unset($esg_tempalte_fields_details['allfields'][$key]);
					}
				}
			}else{
				$temp_field_html = 'eSign Genie SERVER RESPONSE ERROR';		 
			}
			$temp_field_html .= '<div class="form-group" id="select_template_field_row_'.$_REQUEST['data']['fieldCount'].'"><div class="input-group"><select class="form-control  form-control-sm" id="" name="select_template_field[]"><option value="">Select eSign Genie Template field</option>';
			foreach($esg_tempalte_fields_details['allfields'] as $esg_temp_field){
					
				if($esg_temp_field['fieldType'] == 'checkboxfield'){
						$temp_field_html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].' ('.$esg_temp_field['cbgroup'].')</option>';
				 }elseif($esg_temp_field['fieldType'] == 'datefield'){
						 $temp_field_html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].'||'.$esg_temp_field['dateFormat'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'  ('.$esg_temp_field['dateFormat'].')</option>';
				}elseif($esg_temp_field['fieldType'] == 'dropdownfield'){
						$temp_field_html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field['dropdownFieldName'].'" '.$seleted.'>'.$esg_temp_field['dropdownFieldName'].'</option>';
				}else{
						$temp_field_html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'</option>';
				}
			
			}	
			$temp_field_html .= '</select><span class="template-del"><div class="btnic esg-delete-field-group" onclick="deleteFiledsgroup('.$_REQUEST['data']['fieldCount'].')"><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/trash-alt-light.png"></div></span></div></div>';
		}
		$html['esgtemp'] = $temp_field_html;
		echo json_encode($html);
		die;
	}
	add_action( 'wp_ajax_wpesg_add_field_list', 'wpesg_add_field_list' );
}
/*geek start*/

if (!function_exists('handle_wpesg_edit_form_action')) {
	function handle_wpesg_edit_form_action(){
		global $esgdb;
		global $wpdb;
		if (isset($_POST['signInSequence'])) {
			$signInSequence = esc_sql($_POST['signInSequence']);
		}else{
			$signInSequence= false;
		}
		
		$company_id = $esgdb->get_active_company_wpesg_owner_table('wpesg_owner_details');
			if($company_id){
			    //echo '<pre>';
				//print_r($_POST['select_Wpforms_field']);
				//print_r($_POST['select_template_field']);
				
				$cdate = date('Y-m-d H:i:s');
				$esg_wp_select_form = explode("#$$#",$_POST['esg_wp_select_form']);
				$esg_select_template = explode("#$$#",$_POST['esg_select_template']);
				$data_mapping_system=array('mapping_name'=>esc_sql($_POST['mapping_name']),'wp_form_type'=>'wpForms','wp_form_id'=>$esg_wp_select_form[0],'wp_form_name'=>$esg_wp_select_form[1],'esg_template_id'=>$esg_select_template[0],'esg_template_name'=>$esg_select_template[1],'company_id'=>$company_id,'sign_option'=>esc_sql($_POST['send_option']),'status'=>1,'signInSequence'=>$signInSequence,'created_at'=>$cdate,'updated_at'=>$cdate); 
				$esgdb->update_wpesg_table( "wpesg_mapping_system",$data_mapping_system, array('id' =>esc_sql($_POST['itemId'])));
				$mapping_id = esc_sql($_POST['itemId']); 
				if($mapping_id){			
					$esgdb->delete_wpesg_table("wpesg_fields_mapping", array('mapping_id'=>$mapping_id));
					foreach($_POST['select_Wpforms_field'] as $key => $filed){
						// echo $key." key";
						if($filed != '' &&  $_POST['select_template_field'][$key] != '' ){
							$mix_field = explode('-',$filed);
							if(isset($mix_field[1])){
								 $sub_filed = stripslashes(wp_strip_all_tags($mix_field[1]));
								if(isset($mix_field[2])){
								  $sub_filed .= '-'.stripslashes(wp_strip_all_tags($mix_field[2]));
								}
							}else{
								$sub_filed ='';
							}
						$data_fields_mapping=array('mapping_id'=>$mapping_id,'form_field_id'=>$mix_field[0],'form_sub_field'=>$sub_filed,'template_field_name'=>esc_sql($_POST['select_template_field'][$key]),'created_at'=>$cdate,'updated_at'=>$cdate);
						//print_r($data_fields_mapping);
						$field_id = $esgdb->insert_wpesg_table( "wpesg_fields_mapping", $data_fields_mapping);
					}
				}
				
				 $esgdb->delete_multiple_rows_wpesg_table('wpesg_party_require_param',$_POST['esign_party_delete_list']);
				foreach($_POST['select_Wpforms_party_field'] as $key => $fileds){
				    
					if(!empty($fileds)){
					   $data_party_require_parm=array('mapping_id'=>$mapping_id,'party_permission'=>esc_sql($_POST['esg_template_party'][$key][1]),'party_sequence'=>esc_sql($_POST['esg_template_party'][$key][0]),'updated_at'=>$cdate);
						$partyFields = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'wpesg_party_require_param WHERE mapping_id = '.esc_sql($_POST["itemId"]), ARRAY_A);
						if(is_array($fileds)){
							$party_mix = explode('-',$fileds[0]);
							if($party_mix[0] == 0){
								$partyFields[$key-1]['id'] = $esgdb->insert_wpesg_table( "wpesg_party_require_param", $data_party_require_parm);
							}else{
								$esgdb->update_wpesg_table( "wpesg_party_require_param", $data_party_require_parm, array('id' => $partyFields[$key-1]['id']));
							}
						}
						if($partyFields[$key-1]['id']){
							$party_id = $partyFields[$key-1]['id'];
							$esgdb->delete_wpesg_table("wpesg_party_mapping", array('party_id' => $party_id));
							foreach($fileds as $int_key => $field ){
								if($field){
										$party_mix_field = explode('-',$field);
									if(isset($party_mix_field[3])){
										$party_sub_filed = $party_mix_field[3];
									}else{
										$party_sub_filed ='';
									}
									$data_party_mapping=array('party_id'=>$party_id,'form_field_id'=>$party_mix_field[1],'form_sub_field'=>$party_sub_filed,'party_filed_name'=>$party_mix_field[2],'created_at'=>$cdate,'updated_at'=>$cdate);
									$field_id = $esgdb->insert_wpesg_table( "wpesg_party_mapping", $data_party_mapping);
								}
							
							}
						}
				 
					}
				}
			
			}	
			// redirect after insert alert
			wp_redirect(admin_url('admin.php?page=wpesg-mapping-list'));
			die();
		}else{
			echo 'Not active user';  
		}	
	}
	add_action('admin_post_submit-edit-form', 'handle_wpesg_edit_form_action');
}

/*geek end*/
if (!function_exists('handle_wpesg_form_action')) {
	function handle_wpesg_form_action(){
		if (isset($_POST['signInSequence'])) {
			$signInSequence = esc_sql($_POST['signInSequence']);
		}else{
			$signInSequence= '0';
		}
		global $esgdb;
		
			$company_id = $esgdb->get_active_company_wpesg_owner_table('wpesg_owner_details');
		if($company_id){
			$cdate = date('Y-m-d H:i:s');
			$esg_wp_select_form = explode("#$$#",$_POST['esg_wp_select_form']);
			$esg_select_template = explode("#$$#",$_POST['esg_select_template']);
			$data_mapping_system = array('mapping_name'=>esc_sql($_POST['mapping_name']),'wp_form_type'=>'wpForms','wp_form_id'=>$esg_wp_select_form[0],'wp_form_name'=>$esg_wp_select_form[1],'esg_template_id'=>$esg_select_template[0],'esg_template_name'=>$esg_select_template[1],'company_id'=>$company_id,'sign_option'=>esc_sql($_POST['send_option']),'status'=>1,'signInSequence'=>$signInSequence,'created_at'=>$cdate,'updated_at'=>$cdate); 
			$mapping_id = $esgdb->insert_wpesg_table( "wpesg_mapping_system", $data_mapping_system);
			if($mapping_id){
			
				foreach($_POST['select_Wpforms_field'] as $key => $filed){
					if($filed != '' &&  $_POST['select_template_field'][$key] != '' ){
						$mix_field = explode('-',$filed);
						if(isset($mix_field[1])){
							$sub_filed = stripslashes(wp_strip_all_tags($mix_field[1]));
						if(isset($mix_field[2])){
						  $sub_filed .= '-'.stripslashes(wp_strip_all_tags($mix_field[2]));
						}
						}else{
							$sub_filed ='';
						}
						$data_fields_mapping=array('mapping_id'=>$mapping_id,'form_field_id'=>$mix_field[0],'form_sub_field'=>$sub_filed,'template_field_name'=>esc_sql($_POST['select_template_field'][$key]),'created_at'=>$cdate,'updated_at'=>$cdate);
						$field_id = $esgdb->insert_wpesg_table( "wpesg_fields_mapping", $data_fields_mapping);
					}
				}
			
				foreach($_POST['select_Wpforms_party_field'] as $key => $fileds){
					if(!empty($fileds) &&  $_POST['select_Wpforms_party_field'][$key] != '' ){
						$data_party_require_parm=array('mapping_id'=>$mapping_id,'party_permission'=>esc_sql($_POST['esg_template_party'][$key][1]),'party_sequence'=>esc_sql($_POST['esg_template_party'][$key][0]),'created_at'=>$cdate,'updated_at'=>$cdate);
						$party_id = $esgdb->insert_wpesg_table( "wpesg_party_require_param", $data_party_require_parm);
						if($party_id){
							foreach($fileds as $int_key => $field ){
								if($field){
									$party_mix_field = explode('-',$field);
								if(isset($party_mix_field[3])){
									$party_sub_filed = $party_mix_field[3];
								}else{
									$party_sub_filed ='';
								}
									$data_party_mapping=array('party_id'=>$party_id,'form_field_id'=>$party_mix_field[1],'form_sub_field'=>$party_sub_filed,'party_filed_name'=>$party_mix_field[2],'created_at'=>$cdate,'updated_at'=>$cdate);
									$field_id = $esgdb->insert_wpesg_table( "wpesg_party_mapping", $data_party_mapping);
								}
							
							}
						}
					}
				}
			}	
			// redirect after insert alert
			wp_redirect(admin_url('admin.php?page=wpesg-mapping-list'));
			die();
		}else{
			echo 'Not active user';  
		}	
	}
	add_action('admin_post_submit-form', 'handle_wpesg_form_action');
}

if (!function_exists('wpf_wpesg_dev_process_complete')) {
	function wpf_wpesg_dev_process_complete( $fields, $entry, $form_data, $entry_id ){
		$data = prepare_wpesg_template_data(absint($form_data['id']),$fields,json_encode($entry),$form_data['settings']['confirmations'][1]['redirect']);
		$form_data['settings']['confirmations'][1]['redirect'] = $data;
		return $form_data;	
	}
	add_action( 'wpforms_process_complete', 'wpf_wpesg_dev_process_complete', 10, 4 );
}

if (!function_exists('prepare_wpesg_template_data')) {
	function prepare_wpesg_template_data($form_id,$entry_fields,$entryJson,$redircturl){
		global $esgdb;
		$company_id = $esgdb->get_active_company_wpesg_owner_table('wpesg_owner_details');
		if($company_id){
			$esg_mapping_stytem_results = $esgdb->get_active_mapping_wpesg_mapping_system_table('wpesg_mapping_system',$company_id,$form_id);
			if($esg_mapping_stytem_results){
				$data['folderName'] = $esg_mapping_stytem_results->mapping_name;
				$data['templateIds'][0] = $esg_mapping_stytem_results->esg_template_id;
				if($esg_mapping_stytem_results->sign_option == 'Send Email'){
					$data['sendNow'] = true;
				}
				elseif($esg_mapping_stytem_results->sign_option == 'Preview and Send'){
					$data['createEmbeddedSendingSession'] = true;
					$data['createEmbeddedSigningSessionForAllParties'] = true;
					$data['sendSuccessUrl'] = $redircturl;
				}
				elseif($esg_mapping_stytem_results->sign_option = 'Sign and Send') {
					$data['createEmbeddedSigningSession'] = true;
					$data['createEmbeddedSigningSessionForAllParties'] = false;
					$data['signSuccessUrl'] = $redircturl;
				}
				
				if($esg_mapping_stytem_results->signInSequence == '1'){
					$data['signInSequence'] = true;
				}else{
					$data['signInSequence'] = false;
				}
				$wpesg_fields_mapping_results = $esgdb->get_active_mapping_wpesg_fields_mapping_table('wpesg_fields_mapping',$esg_mapping_stytem_results->id);
				if($wpesg_fields_mapping_results){
					//echo '<pre>';
					//print_r($wpesg_fields_mapping_results);
					//print_r($entry_fields);
					//die;
					foreach($wpesg_fields_mapping_results as $key=>$result){
					  $mixFiledname = explode("$$",$result->template_field_name);
					  
					  if($entry_fields[$result->form_field_id]['type'] == 'name' ){
						if($result->form_sub_field == 'first'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['first'];
						}elseif($result->form_sub_field == 'middle'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['middle'];
						}elseif($result->form_sub_field == 'last'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['last'];
						}else{
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['value'];
						} 					
					  }elseif($entry_fields[$result->form_field_id]['type'] == 'address'){
					    
						if($result->form_sub_field == 'address1'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['address1'];
						}elseif($result->form_sub_field == 'address2'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['address2'];
						}elseif($result->form_sub_field == 'city'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['city'];
						}elseif($result->form_sub_field == 'state'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['state'];
						}elseif($result->form_sub_field == 'country'){
							$data['fields'][$mixFiledname[1]] = (string)wpesg_get_Country_FullName($entry_fields[$result->form_field_id]['country']);
						}elseif($result->form_sub_field == 'postal'){
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['postal'];
						}else{
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['value'];
						} 
					  
					  }elseif($entry_fields[$result->form_field_id]['type'] == 'checkbox' || $entry_fields[$result->form_field_id]['type'] == 'radio' || $entry_fields[$result->form_field_id]['type'] == 'payment-multiple'){
						  if( $entry_fields[$result->form_field_id]['type'] == 'payment-multiple'){
							  $mixent = explode("-",$entry_fields[$result->form_field_id]['value']);
							  if(strpos(trim($mixent[0]),wp_strip_all_tags($result->form_sub_field)) !== false){
									$data['fields'][$mixFiledname[1]] = "true";
							  }
						  }elseif(strpos($entry_fields[$result->form_field_id]['value'],wp_strip_all_tags($result->form_sub_field)) !== false){
							  $data['fields'][$mixFiledname[1]] = "true";
						  }
					  }elseif($mixFiledname[0] == 'datefield' || $entry_fields[$result->form_field_id]['type'] == 'date-time'){
						   $mixdate = explode("||",$mixFiledname[1]);
						  if($result->form_sub_field == 'date'){
							$data['fields'][$mixdate[0]] = (string)$entry_fields[$result->form_field_id]['date'];
						}elseif($result->form_sub_field == 'time'){
							$data['fields'][$mixdate[0]] = (string)$entry_fields[$result->form_field_id]['time'];
						}else{
							$data['fields'][$mixdate[0]] = (string)$entry_fields[$result->form_field_id]['value'];
						} 
					  }else{
							$data['fields'][$mixFiledname[1]] = (string)$entry_fields[$result->form_field_id]['value'];
					  }
					}			
				}
				$esg_party_require_parm_results = $esgdb->get_active_party_wpesg_party_require_parm_table('wpesg_party_require_param',$esg_mapping_stytem_results->id);
				if($esg_party_require_parm_results){
					$Checkseding = false;
					foreach($esg_party_require_parm_results as $key=>$party_result){
						$data['parties'][$key]['permission'] = $party_result->party_permission;			  
						$data['parties'][$key]['sequence'] = (int)$party_result->party_sequence;	
						$wpesg_party_mapping_table_results = $esgdb->get_active_party_wpesg_party_mapping_table('wpesg_party_mapping',$party_result->id);
						 
						foreach($wpesg_party_mapping_table_results as $party_field_result){
							if($party_field_result->party_filed_name != '' && $party_field_result->party_filed_name != 'dialingCode'){
								if($entry_fields[$party_field_result->form_field_id]['type'] == 'name' ){
									if($party_field_result->form_sub_field == 'first'){
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['first'];
									}elseif($party_field_result->form_sub_field == 'middle'){
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['middle'];
									}elseif($party_field_result->form_sub_field == 'last'){
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['last'];
									}else{
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['value'];
									} 					
								  }else{
                                      
									  if($party_field_result->party_filed_name == 'mobileNumber'){
										  $data['parties'][$key][$party_field_result->party_filed_name] = preg_replace('/[^0-9,.]/', '', $entry_fields[$party_field_result->form_field_id]['value']);

									 }elseif($party_field_result->party_filed_name == 'emailId' && $esg_mapping_stytem_results->sign_option == 'Sign and Send' &&  $Checkseding === false){
										 $data['embeddedSignersEmailIds'][0] = $entry_fields[$party_field_result->form_field_id]['value'];
										 $data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['value'];
									     $Checkseding = true;  
									 }else{
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['value'];
									 }
									
								  }
							}elseif($party_field_result->party_filed_name == 'dialingCode'){
								if (strpos($entry_fields[$party_field_result->form_field_id]['value'], '+') === 0) {
										$data['parties'][$key][$party_field_result->party_filed_name] = $entry_fields[$party_field_result->form_field_id]['value'];
									}elseif($entry_fields[$party_field_result->form_field_id]['value'] != ''){
										$data['parties'][$key][$party_field_result->party_filed_name] = '+'.$entry_fields[$party_field_result->form_field_id]['value'];
									}
							}
						}			  
					}
				}
				//echo '<pre>';
				//print_r($data);
				//die;
				$esgResponse = wpesg_send_data_to_templates(json_encode($data));
				$esgResponsearry = json_decode($esgResponse);
				if(isset($esgResponsearry->embeddedSessionURL)){
					$embeddedSessionURL = $esgResponsearry->embeddedSessionURL;
				}else{
					$embeddedSessionURL = $esgResponsearry->embeddedSigningSessions[0]->embeddedSessionURL;
				}
				$data = array('folderId'=>$esgResponsearry->folder->folderId,'folderCompanyId'=>$esgResponsearry->folder->folderCompanyId,'wp_form_id'=>$form_id,'template_id'=>$esg_mapping_stytem_results->esg_template_id,'folderSentDate'=>$esgResponsearry->folder->folderSentDate);
				foreach($esgResponsearry->folder->folderDocumentIds as $key=>$res){
					$folderids[$key] = $res;	
				}

				foreach($esgResponsearry->folder->folderRecipientParties as $key=>$party){
					$partyIds[$key] = $party->partyId;
				}
				if(is_array($folderids)){
					$data['folderDocumentIds'] = implode(',',$folderids);
				}else{
					$data['folderDocumentIds'] = $folderids;
				}
				if(is_array($partyIds)){
					$data['partyIds'] = implode(',',$partyIds);
				}else{
					$data['partyIds'] = $partyIds;
				}
				$data['wp_form_entries'] = $entryJson;
				$esgdb->insert_wpesg_table('wpesg_template_response',$data);
				if(isset($embeddedSessionURL) && $esg_mapping_stytem_results->sign_option != 'Send Email'){
					wp_redirect($embeddedSessionURL);
					die();			
				}else{
					return true;
				}
				//return $embeddedSessionURL;
			}
			
		}
	} 
}

//SEND FORM DATA To eSign Genie TEMPLATE 
if (!function_exists('wpesg_send_data_to_templates')) {
	function wpesg_send_data_to_templates($predata){
		global $esgdb;
		
		$code = $esgdb->get_token_wpesg_owner_table('wpesg_owner_details');
		if($code){
		$args = array(
			'body' => $predata,
			'timeout' => '30',
			'redirection' => '10',
			'httpversion' => '1.0',
			'headers' => array("Authorization"=>"Bearer $code","content-type"=>"application/json") 
		);
		$response = wp_remote_post( WPESG_API_URL."api/templates/createFolder", $args );
		if($response['response']['code'] == 200){
				return $response['body'];
			}else{
				return false;
			}	
		}else{
			return false;
		}	
		die;
	}
}
if (!function_exists('wpesg_get_Country_FullName')) {
	function wpesg_get_Country_FullName($key){
	$countryarray = array("AF"=>"Afghanistan","AL"=>"Albania","DZ"=>"Algeria","AS"=>"American Samoa","AD"=>"Andorra","AO"=>"Angola","AI"=>"Anguilla","AQ"=>"Antarctica","AG"=>"Antigua and Barbuda","AR"=>"Argentina","AM"=>"Armenia","AW"=>"Aruba","AU"=>"Australia","AT"=>"Austria","AZ"=>"Azerbaijan","BS"=>"Bahamas","BH"=>"Bahrain","BD"=>"Bangladesh","BB"=>"Barbados","BY"=>"Belarus","BE"=>"Belgium","BZ"=>"Belize","BJ"=>"Benin","BM"=>"Bermuda","BT"=>"Bhutan","BO"=>"Bolivia (Plurinational State of)","BA"=>"Bosnia and Herzegovina","BW"=>"Botswana","BV"=>"Bouvet Island","BR"=>"Brazil","IO"=>"British Indian Ocean Territory","BN"=>"Brunei Darussalam","BG"=>"Bulgaria","BF"=>"Burkina Faso","BI"=>"Burundi","CV"=>"Cabo Verde","KH"=>"Cambodia","CM"=>"Cameroon","CA"=>"Canada","KY"=>"Cayman Islands","CF"=>"Central African Republic","TD"=>"Chad","CL"=>"Chile","CN"=>"China","CX"=>"Christmas Island","CC"=>"Cocos (Keeling) Islands","CO"=>"Colombia","KM"=>"Comoros","CG"=>"Congo","CD"=>"Congo (Democratic Republic of the)","CK"=>"Cook Islands","CR"=>"Costa Rica","HR"=>"Croatia","CU"=>"Cuba","CW"=>"Curaçao","CY"=>"Cyprus","CZ"=>"Czech Republic","CI"=>"Côte d'Ivoire","DK"=>"Denmark","DJ"=>"Djibouti","DM"=>"Dominica","DO"=>"Dominican Republic","EC"=>"Ecuador","EG"=>"Egypt","SV"=>"El Salvador","GQ"=>"Equatorial Guinea","ER"=>"Eritrea","EE"=>"Estonia","ET"=>"Ethiopia","FK"=>"Falkland Islands (Malvinas)","FO"=>"Faroe Islands","FJ"=>"Fiji","FI"=>"Finland","FR"=>"France","GF"=>"French Guiana","PF"=>"French Polynesia","TF"=>"French Southern Territories","GA"=>"Gabon","GM"=>"Gambia","GE"=>"Georgia","DE"=>"Germany","GH"=>"Ghana","GI"=>"Gibraltar","GR"=>"Greece","GL"=>"Greenland","GD"=>"Grenada","GP"=>"Guadeloupe","GU"=>"Guam","GT"=>"Guatemala","GG"=>"Guernsey","GN"=>"Guinea","GW"=>"Guinea-Bissau","GY"=>"Guyana","HT"=>"Haiti","HM"=>"Heard Island and McDonald Islands","HN"=>"Honduras","HK"=>"Hong Kong","HU"=>"Hungary","IS"=>"Iceland","IN"=>"India","ID"=>"Indonesia","IR"=>"Iran (Islamic Republic of)","IQ"=>"Iraq","IE"=>"Ireland (Republic of)","IM"=>"Isle of Man","IL"=>"Israel","IT"=>"Italy","JM"=>"Jamaica","JP"=>"Japan","JE"=>"Jersey","JO"=>"Jordan","KZ"=>"Kazakhstan","KE"=>"Kenya","KI"=>"Kiribati","KP"=>"Korea (Democratic People's Republic of)","KR"=>"Korea (Republic of)","KW"=>"Kuwait","KG"=>"Kyrgyzstan","LA"=>"Lao People's Democratic Republic","LV"=>"Latvia","LB"=>"Lebanon","LS"=>"Lesotho","LR"=>"Liberia","LY"=>"Libya","LI"=>"Liechtenstein","LT"=>"Lithuania","LU"=>"Luxembourg","MO"=>"Macao","MG"=>"Madagascar","MW"=>"Malawi","MY"=>"Malaysia","MV"=>"Maldives","ML"=>"Mali","MT"=>"Malta","MH"=>"Marshall Islands","MQ"=>"Martinique","MR"=>"Mauritania","MU"=>"Mauritius","YT"=>"Mayotte","MX"=>"Mexico","FM"=>"Micronesia (Federated States of)","MD"=>"Moldova (Republic of)","MC"=>"Monaco","MN"=>"Mongolia","ME"=>"Montenegro","MS"=>"Montserrat","MA"=>"Morocco","MZ"=>"Mozambique","MM"=>"Myanmar","NA"=>"Namibia","NR"=>"Nauru","NP"=>"Nepal","NL"=>"Netherlands","NC"=>"New Caledonia","NZ"=>"New Zealand","NI"=>"Nicaragua","NE"=>"Niger","NG"=>"Nigeria","NU"=>"Niue","NF"=>"Norfolk Island","MK"=>"North Macedonia (Republic of)","MP"=>"Northern Mariana Islands","NO"=>"Norway","OM"=>"Oman","PK"=>"Pakistan","PW"=>"Palau","PS"=>"Palestine (State of)","PA"=>"Panama","PG"=>"Papua New Guinea","PY"=>"Paraguay","PE"=>"Peru","PH"=>"Philippines","PN"=>"Pitcairn","PL"=>"Poland","PT"=>"Portugal","PR"=>"Puerto Rico","QA"=>"Qatar","RO"=>"Romania","RU"=>"Russian Federation","RW"=>"Rwanda","RE"=>"Réunion","BL"=>"Saint Barthélemy","SH"=>"Saint Helena, Ascension and Tristan da Cunha","KN"=>"Saint Kitts and Nevis","LC"=>"Saint Lucia","MF"=>"Saint Martin (French part)","PM"=>"Saint Pierre and Miquelon","VC"=>"Saint Vincent and the Grenadines","WS"=>"Samoa","SM"=>"San Marino","ST"=>"Sao Tome and Principe","SA"=>"Saudi Arabia","SN"=>"Senegal","RS"=>"Serbia","SC"=>"Seychelles","SL"=>"Sierra Leone","SG"=>"Singapore","SX"=>"Sint Maarten (Dutch part)","SK"=>"Slovakia","SI"=>"Slovenia","SB"=>"Solomon Islands","SO"=>"Somalia","ZA"=>"South Africa","GS"=>"South Georgia and the South Sandwich Islands","SS"=>"South Sudan","ES"=>"Spain","LK"=>"Sri Lanka","SD"=>"Sudan","SR"=>"Suriname","SJ"=>"Svalbard and Jan Mayen","SZ"=>"Swaziland","SE"=>"Sweden","CH"=>"Switzerland","SY"=>"Syrian Arab Republic","TW"=>"Taiwan, Province of China","TJ"=>"Tajikistan","TZ"=>"Tanzania (United Republic of)","TH"=>"Thailand","TL"=>"Timor-Leste","TG"=>"Togo","TK"=>"Tokelau","TO"=>"Tonga","TT"=>"Trinidad and Tobago","TN"=>"Tunisia","TR"=>"Turkey","TM"=>"Turkmenistan","TC"=>"Turks and Caicos Islands","TV"=>"Tuvalu","UG"=>"Uganda","UA"=>"Ukraine","AE"=>"United Arab Emirates","GB"=>"United Kingdom of Great Britain and Northern Ireland","UM"=>"United States Minor Outlying Islands","US"=>"United States of America","UY"=>"Uruguay","UZ"=>"Uzbekistan","VU"=>"Vanuatu","VA"=>"Vatican City State","VE"=>"Venezuela (Bolivarian Republic of)","VN"=>"Viet Nam","VG"=>"Virgin Islands (British)","VI"=>"Virgin Islands (U.S.)","WF"=>"Wallis and Futuna","EH"=>"Western Sahara","YE"=>"Yemen","ZM"=>"Zambia","ZW"=>"Zimbabwe","AX"=>"Åland Islands");
		
		if(array_key_exists($key,$countryarray)){
			return $countryarray[$key];		
		}else{
			return $key;
		}	
	}
}

?>
