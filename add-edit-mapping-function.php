<?php
if (!function_exists('wpesg_add_mapping')) { 
	function wpesg_add_mapping(){
		// Get All Templates List from eSign Genie 
		$alert = '';
		$check = '';
		$esg_templates_data = wpesg_get_templates_list();
		if($esg_templates_data){
			$esg_templates_array = json_decode($esg_templates_data);
			if($esg_templates_array->total_templates > 0){
				foreach($esg_templates_array->templatesList as $template){
					$esg_templates_option .='<option value="'.$template->templateId.'#$$#'.$template->templateName.'">'.$template->templateName.'</option>'; 
				}
			}
		}else{
			$check=1;
			$alert = "You need to create a eSign Genie template after login our <a href='https://www.esigngenie.com/esign/'>eSign Genie Application.</a><br />";
			
		}
		// Get All wpforms Form List
		$array = array('post_type' => 'wpforms','numberposts'=>1000);
		$post_list = get_posts($array);
		$posts = array();
	
		if(!empty($post_list)){
			foreach ( $post_list as $post ) {
			   $form_data = json_decode($post->post_content);
			   $form_data->settings->form_title;
			   $esg_wpforms_option .='<option  value="'.$post->ID.'#$$#'.$form_data->settings->form_title.'">'.$form_data->settings->form_title.'</option>'; 	 
			}
		}else{
			$check=1;
			$alert .= 'You need to create a from using Wpforms Plugin.';
		}
		echo '<div class="warp container-fluid pl-0 mt-4">
				<h4 class="wp-heading-inline">Map WP form and eSign Genie Template Fields</h4>
				<hr />
				';
		if($check){			
				echo '<div class="row">
					<div class="col-8"><p class="alert alert-danger">'.$alert.'
					</div>
				</div>';
			}			
			echo '<form method="post" action="'.get_admin_url().'admin-post.php"  class="needs-validation" novalidate>
					<div class="row">				
						<div class="col-8">
							<h5 class="wp-heading-inline"><span class="step-round">1</span> Mapping Overview <a href="#" class="ttl-bottom" data-tooltip="Select your own mapping name, existing WP form name and corresponding eSign Genie template"><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>
							<div class="form-group">
								<label for="mapping-name">Mapping Name <span class="required">*</span></label>
								<input type="text" class="form-control form-control-sm" id="mapping_name" name="mapping_name" required>
								<div class="valid-feedback">Valid.</div>
								<div class="invalid-feedback">Please fill out this field.</div>
								<input type="hidden" id="esg-filed-count" value="5">
								<input type="hidden" name="action" value="submit-form" />
							</div>
						</div>
					</div> 
					
					<div class="row">
					
						<div class="col-4"> 
							<div class="form-group">
								<label for="select-form">Select Your WP Form <span class="required">*</span></label>
								<select class="form-control form-control-sm" id="esg-form-select" name="esg_wp_select_form" required><option value="">Select WPForms</option>'.$esg_wpforms_option.'</select>
								<div class="valid-feedback">Valid.</div>
								<div class="invalid-feedback">Please fill out this field.</div>
							</div>
						</div> 
						<div class="col-4"> 
							<div class="form-group">
								<label>Select eSign Genie Template<span class="required">*</span></label>
								<select class="form-control form-control-sm" id="esg-tempalte-select" name="esg_select_template" required><option value="">Select eSign Genie Template</option>'.$esg_templates_option.'</select>
								<div class="valid-feedback">Valid.</div>
								<div class="invalid-feedback">Please fill out this field.</div>	  
							</div>
						</div>
					</div>
				<div class="row esg-dynamic-fields mt-4" style="display:none"></div>
			</form>
		</div>';	
	}
}

if (!function_exists('wpesg_edit_mapping')){ 
	function wpesg_edit_mapping(){
		global $esgdb;
		$permission = array('FILL_FIELDS_AND_SIGN'=>'FILL FIELDS AND SIGN','FILL_FIELDS_ONLY'=>'FILL FIELDS ONLY','SIGN_ONLY'=>'SIGN ONLY','VIEW_ONLY'=>'VIEW ONLY');
		$id= esc_sql($_GET['itemId']);
		$action = sanitize_text_field($_GET['action']);
		$mappingsys = $esgdb->get_active_mapping_wpesg_mapping_system_table_by_mapped_id("wpesg_mapping_system",$id);
		if($mappingsys->signInSequence == 1){
			$checked = "checked";
			$readonly = '';
		}else{
			$checked = "";
			$readonly = 'readonly';
		}
		if($mappingsys->sign_option == 'Send Email'){
			$sclass = 'checked';
		}elseif($mappingsys->sign_option == 'Preview and Send'){
			$eclass = 'checked';
		}elseif($mappingsys->sign_option == 'Sign and send'){
			$ssclass = 'checked';
		}else{
			$sclass='';
			$eclass='';
			$ssclass = '';
		}
		$mainFields = $esgdb->get_active_mapping_wpesg_fields_mapping_table_by_mapped_id("wpesg_fields_mapping","wpesg_mapping_system",$id);
		$row = count($mainFields);
		$partyFields = $esgdb->get_wpesg_party_details_by_mapped_id("wpesg_party_mapping","wpesg_party_require_param",$id);
		$partyIds = $esgdb->get_wpesg_party_count_by_mapped_id("wpesg_party_require_param",$id);
		$rowParty = count($partyIds);
		$esg_templates_data = wpesg_get_templates_list();
			if($esg_templates_data){
				$esg_templates_array = json_decode($esg_templates_data);
				if($esg_templates_array->total_templates > 0){
					foreach($esg_templates_array->templatesList as $template){
						if($template->templateId == $mappingsys->esg_template_id){
							$esg_templates_option .='<option value="'.$template->templateId.'#$$#'.$template->templateName.'" selected="selected">'.$template->templateName.'</option>';
						}else{
							$esg_templates_option .='<option value="'.$template->templateId.'#$$#'.$template->templateName.'">'.$template->templateName.'</option>';	
						}				
					}
				}
			}
			// Get All wpforms Form List
			$array = array('post_type' => 'wpforms','numberposts'=>1000);
			$post_list = get_posts($array);
			$posts = array();
			foreach ( $post_list as $post ){
			   $form_data = json_decode($post->post_content);
			   $form_data->settings->form_title;
			   if($mappingsys->wp_form_id == $post->ID){
					$esg_wpforms_option .='<option  value="'.$post->ID.'#$$#'.$form_data->settings->form_title.'"  selected="selected">'.$form_data->settings->form_title.'</option>'; 
			   }else{
					$esg_wpforms_option .='<option  value="'.$post->ID.'#$$#'.$form_data->settings->form_title.'" >'.$form_data->settings->form_title.'</option>';    
			   }
			 
			}
			echo '<div class="warp container-fluid pl-0 mt-4">
			<h4 class="wp-heading-inline">Map WP form and eSign Genie Template Fields</h4>
			<hr />
			<form method="post" action="'.get_admin_url().'admin-post.php"  class="needs-validation" novalidate>
				
						<div class="row">
							<div class="col-8">
								<h5 class="wp-heading-inline"><span class="step-round">1</span> Mapping Overview <a href="#" class="ttl-bottom" data-tooltip="Select your own mapping name, existing WP form name and corresponding eSign Genie template"><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>
								<div class="form-group">
									<label for="mapping-name">Mapping Name <span class="required">*</span></label>
									<input type="text" class="form-control form-control-sm" id="mapping_name" value="'.$mappingsys->mapping_name.'"  name="mapping_name" required>
									<div class="valid-feedback">Valid.</div>
									<div class="invalid-feedback">Please fill out this field.</div>
									<input type="hidden" id="esg-filed-count" value="5">
									<input type="hidden" name="itemId" id="esg-filed-itemId" value="'.$id.'">
									<input type="hidden" name="action" value="submit-edit-form" />
								</div>
							</div>
						</div> 
						<div class="row">
							<div class="col-4"> 
								<div class="form-group">
									<label for="select-form">Select Your WP Form<span class="required">*</span></label>
									<select class="form-control form-control-sm" id="esg-form-select" name="esg_wp_select_form" required><option value="">Select Wpforms</option>'.$esg_wpforms_option.'</select>
									<div class="valid-feedback">Valid.</div>
									<div class="invalid-feedback">Please fill out this field.</div>
								</div>
							</div> 
							<div class="col-4"> 
								<div class="form-group">
									<label>Select eSign Genie Template <span class="required">*</span></label>
									<select class="form-control form-control-sm" id="esg-tempalte-select" name="esg_select_template" required><option value="">Select eSign Genie Template</option>'.$esg_templates_option.'</select>
									<div class="valid-feedback">Valid.</div>
									<div class="invalid-feedback">Please fill out this field.</div>	  
								</div>
							</div>
						</div>
					
				<div class="row esg-dynamic-fields mt-4" >
				
				<div class="col-12">

					<div class="row" >
						<div class="col-12">
							<h5><span class="step-round">2</span> Map Your Form Fields with eSign Genie Template Fields <a href="#" data-tooltip="What does mapping do? You can map WPForms fields with eSign Genie template fields. When you submit the WPForm, contract or any other type of signable documents are created from the template with field information populated from the WPForms."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
						</div>
					</div>
						 
					<div class="row" >
						<div class="col-4" > 
							<div class="form-group">
							<label for="select-form">WPForms Fields</label>
							<div class="esg-form-field-select">'.
								wpesg_wpforms_field_list($mappingsys->wp_form_id, 'template-map', 1, false, $row,$mainFields)
							.'</div>
							</div>
						</div> 
						<div class="col-4"> 
							<div class="form-group">
								<label>eSign Genie Template Fields</label>
								<div id="esg-template-filed-select">'.
									wpesg_tempalte_field_list($mappingsys->esg_template_id, $row,$mainFields).'
								</div>
							</div>
						</div> 
						
					</div> 
					<div class="row">
						<div class="col-3"> 
							<div class="form-group">
								<div id="esg-add-new-field" class="btn btn-sm esg-primary">+ Add field</div>
							</div>
						</div>
					</div>	 	
					<div class="row mt-4">
						<div class="col-12">
							<h5><span class="step-round">3</span> Map Signer Party Fields <a href="#" data-tooltip="You can map the fields on your form, so the signer name on the eSign Genie contract/document can be auto-populated from the WPForms fields based on this mapping."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
						</div>
					</div>	
					
					<div class="col-12 signInSequence-check">
					    <input type="hidden" id="esign-party-delete-list" name="esign_party_delete_list" value="">
						<input type="checkbox" id="signInSequence" name="signInSequence" value="1" '.$checked.'>	Enforce Signing Sequence <a href="#" data-tooltip="Selecting this checkbox will enable the invitation to be sent in sequential order. The next party will receive the folder notification for esignature only when the previous party has completed the task. If there is only one signer/recipient, no need to check this field."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a>
					</div>
					
					<div class="row" >
						<div class="col-12">
						<table class="table table table-bordered table-striped  table-responsive" id="esg_template_party" ><tr><th>S.N.</th><th>Party Sequence*</th><th>First Name*</th><th>Last Name*</th><th>Email*</th><th>Country Code</th><th>Mobile</th><th>Party Permission*</th><th>Action</th></tr>';
						
						$i = 0;
						while($i < $rowParty){
							$no = $i+1;
							if($no == 1){
								$disabled = "disabled";
							}else{
								$disabled = "";
							}
							foreach($permission as $key=>$values){
								if($key == $partyIds[$i]->party_permission){
									$poption .= '<option value="'.$key.'" selected=selected>'.$values.'</option>';
								}else{
									$poption .= '<option value="'.$key.'">'.$values.'</option>';
								}
							}
								if(isset($partyIds[$i]->party_sequence)){ 
									$party_sequence = $partyIds[$i]->party_sequence;
								}else{
									$party_sequence = $no;
								}
							$html .='<tr id="delete-party-'.$no.'" data-id="'.$partyIds[$i]->id.'" class="delete-party-row">
								<td class="sn">'.$no.'</td>
								<td>
									<input type="number" class="form-control party_sequence" min=1 max='.$rowParty.' name="esg_template_party['.$no.'][]" value="'.$party_sequence.'"  required '.$readonly.'>
									<div class="valid-feedback">Valid.</div>
									<div class="invalid-feedback">Enter valid Sequence no.</div>
								</td>
								<td>
									'.
										wpesg_party_section_fields('required',$mappingsys->wp_form_id,$no,'firstName',$partyFields,$partyIds[$i]->id)
									.'
								</td>
								<td>
									'.
										wpesg_party_section_fields('required',$mappingsys->wp_form_id,$no,'lastName',$partyFields,$partyIds[$i]->id)
									.'
								</td>
								<td>
									'.
										wpesg_party_section_fields('required',$mappingsys->wp_form_id,$no,'emailId',$partyFields,$partyIds[$i]->id)
									.'
								</td>
								<td>
									'.
										wpesg_party_section_fields('',$mappingsys->wp_form_id,$no,'dialingCode',$partyFields,$partyIds[$i]->id)
									.'
								</td>
								<td>
									'.
										wpesg_party_section_fields('',$mappingsys->wp_form_id,$no,'mobileNumber',$partyFields,$partyIds[$i]->id)
									.'
								</td>
								<td>
									<select class="form-control  form-control-sm"  name="esg_template_party['.$no.'][]" required><option value="">Select Party Permission</option>'.$poption.'</select>
									<div class="valid-feedback">Valid.</div>
									<div class="invalid-feedback">Select Party Permission</div>
								</td>
								
								<td>
									<button data-count='.$no.' class="btn btn-sm btn-danger" '.$disabled.'>Delete</button>
								</td>
							</tr>';
					
							$i++;
						}					
					$html .= '</table></div></div> 
					<div class="row" >
						<div class="col-12"> 
							<div class="form-group">
							<div id="add-party" data-no='.$no.' onclick="add_esg_template_party()" class="btn btn-sm  esg-primary">+ Add Party</div>
							</div>
						</div>
					</div>	
					<div class="row mt-4" >
						<div class="col-10">
							<h5><span class="step-round">4</span> Submit Action <a href="#" data-tooltip="Submit action will be used on any submit button on the WP Form."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
						</div>
					</div>			
					<div class="row">
						
						<div class="col-4"> 
						<div class="form-group">
					    <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="send-email" name="send_option" '.$sclass.' value="Send Email" required>
							<label class="custom-control-label" for="send-email">Send <a href="#" data-tooltip="Send option creates and sends the document to the signer via email with a link to sign the document. The signer will be able to click on the link and sign the document."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>
						</div>
					</div>	
					<div class="col-4">
					    <div class="form-group">
                        <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="preview-send" name="send_option" '.$eclass.' value="Preview and Send">
							<label class="custom-control-label" for="preview-send">Preview and Send  <a href="#" data-tooltip="Preview and Send option creates and displays the draft document to the person creating it before sending it to the signer. The signer will be able to click on the link and sign the document."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>					
						</div>					
					</div>
					<div class="col-4"> 
					    <div class="form-group">
					     <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="sign-send" name="send_option" '.$ssclass.' value="Sign and send">
							<label class="custom-control-label" for="sign-send">Sign and Send to Next Party  <a href="#" data-tooltip="Sign and Send option creates and displays the document to the signer (e.g., customer who is filling out the WPForms). The signer will be able to esign the document, and the next recipient(s) will receive a notification to sign based on the signing sequence. If there is only one signer, the document will be executed immediately after signing."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>
						</div>
					</div>
				</div>
				<div class="col-12 notebox"> 
					<i>Note: When the users complete the sending/signing steps after the submit action, they will be taken to the redirect URL set up on the WP Form.</i>
				</div>
				<div class="col-12"> 
						<hr />
				</div>
				<div class="col-12"><div class="form-group text-right"><input type="submit" name="save_option" class="btn esg-btn-outline active" value="Save Template Mapping" ></div>
					</div>
                 	
				</div>	
			</form>
		</div>';
		echo $html;
	}
}

if (!function_exists('wpesg_wpforms_field_list')){ 
// Wpforms get filed List
	function wpesg_wpforms_field_list($id,$type='template-map',$party_count=1, $check=false, $row = 5,$mainFields=''){
		$html = '';
		$form_name = "select_Wpforms_field[]";	
		if($id != ''){
			$esg_wpforms_data = get_post($id);
			if($esg_wpforms_data){
				$esg_form_data_array = json_decode($esg_wpforms_data->post_content);
				for($i=1;$i<=$row;$i++){
					$html .= '<div class="form-group" id="select_Wpforms_field_row_'.$i.'">';
					$html .= '<select class="form-control  form-control-sm" id="" name="'.$form_name.'"><option value="">Select Form Field</option>';
					foreach($esg_form_data_array->fields as $field){
						if($field->type != 'pagebreak' && $field->type != 'html' && $field->type != 'file-upload' && $field->type != 'pagebreak'){
						$fseleted = '';
						$mseleted = '';
						$lseleted = '';
						$seleted = '';
						
						if($mainFields[$i-1]->form_field_id == $field->id && $mainFields != '' && $field->type != 'radio' && $field->type != 'checkbox' && $field->type != 'address' && $field->type != 'date-time'){
							if($mainFields[$i-1]->form_sub_field == 'first'){
								$fseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'middle'){
								$mseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'last'){
								$lseleted = 'selected';
							}else{
								$seleted = 'selected';
							}
						}
						
						if($field->type == 'address'){
							if($mainFields[$i-1]->form_sub_field == 'address1'){
								$ad1seleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'address2'){
								$ad2seleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'city'){
								$adcseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'state'){
								$adseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'postal'){
								$adpseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'country'){
								$adcueleted = 'selected';
							}else{
								$ad1seleted = '';
								$ad2seleted = '';
								$adcseleted = '';
								$adseleted = '';
								$adpseleted = '';
								$adcueleted = '';
							}							
						}
						if($field->type == 'date-time'){
							if($mainFields[$i-1]->form_sub_field == 'date'){
								$dseleted = 'selected';
							}elseif($mainFields[$i-1]->form_sub_field == 'time'){
								$tseleted = 'selected';
							}else{
								$dseleted = '';
								$tseleted = '';
							}						
						}
						
						
												
						if($field->type == 'name'){
							if($field->format == 'simple'){				
								$html .= '<option value="'.$field->id.'" '.$seleted.'>'.$field->label.'</option>';
							}elseif($field->format == 'first-last'){
								$html .= '<option value="'.$field->id.'-first" '.$fseleted.'>First '.$field->label.'</option>';
								$html .= '<option value="'.$field->id.'-last" '.$lseleted.'>Last '.$field->label.'</option>';
							}else{
								$html .= '<option value="'.$field->id.'-first" '.$fseleted.'>First '.$field->label.'</option>';
								$html .= '<option value="'.$field->id.'-middle" '.$mseleted.'>Middle '.$field->label.'</option>';
								$html .= '<option value="'.$field->id.'-last" '.$lseleted.'>Last '.$field->label.'</option>';
							}
						}elseif($field->type == 'address'){
							if($field->address2_hide == 1 || $field->postal_hide == 1){
                               if($field->address2_hide == 1){								
								$html .= '<option value="'.$field->id.'-address1" '.$ad1seleted.'>Address Line 1('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-city" '.$adcseleted.'>City ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-state" '.$adseleted.'>State ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-postal" '.$adpseleted.'>Postal ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-country" '.$adcueleted.'>Country ('.$field->label.')</option>';
							   }else{
								$html .= '<option value="'.$field->id.'-address1" '.$ad1seleted.'>Address Line 1('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-address2" '.$ad2seleted.'>Address Line 2('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-city" '.$adcseleted.'>City ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-state" '.$adseleted.'>State ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-country" '.$adcueleted.'>Country ('.$field->label.')</option>';
							   }
							}else{
								$html .= '<option value="'.$field->id.'-address1" '.$ad1seleted.'>Address Line 1('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-address2" '.$ad2seleted.'>Address Line 2('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-city" '.$adcseleted.'>City ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-state" '.$adseleted.'>State ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-postal" '.$adpseleted.'>Postal ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-country" '.$adcueleted.'>Country ('.$field->label.')</option>';
							}
						}elseif($field->type == 'date-time'){
							if($field->format == 'date'){				
								$html .= '<option value="'.$field->id.'-date" '.$fseleted.'>Date ('.$field->label.')</option>';
							}elseif($field->format == 'date-time'){
								$html .= '<option value="'.$field->id.'-date" '.$dseleted.'>Date ('.$field->label.')</option>';
								$html .= '<option value="'.$field->id.'-time" '.$tseleted.'>Time ('.$field->label.')</option>';
							}else{
								$html .= '<option value="'.$field->id.'-time" '.$tseleted.'>Time ('.$field->label.')</option>';
							}
						}elseif($field->type == 'radio' || $field->type == 'checkbox' || $field->type == 'payment-multiple'){
								foreach($field->choices as $choice){
									$label = wp_strip_all_tags($choice->label);
								    if ($mainFields[$i-1]->form_field_id == $field->id && $mainFields[$i-1]->form_sub_field == $label){
								          $seleted = 'selected';
								    }else{
										$seleted = '';
									}
									
									$html .= '<option value="'.$field->id.'-'.$choice->label.'" '.$seleted.'>'.$choice->label.' ('.$field->label.')</option>';
								}
						}else{
							$html .= '<option value="'.$field->id.'-'.$field->type.'" '.$seleted.'>'.$field->label.'</option>';
						}
					}	
					}	
					$html .= '</select></div>';
				}
			}	
		}
		return $html;
	}
	add_action( 'wp_ajax_wpesg_wpforms_field_list', 'wpesg_wpforms_field_list' );
}

if (!function_exists('wpesg_set_dynamic_fields_list')){ 
	function wpesg_set_dynamic_fields_list(){	
		global $esgdb;
		$html = '';
		if(isset($_GET['total_row']) && !empty($_GET['total_row'])){
			$row = (Int)$_GET['total_row'];
		}else{
			$row = 5;
		}
		if(isset($_GET['total_form']) && !empty($_GET['total_form'])){
			$rowParty = (Int)$_GET['total_form'];
		}else{
			$rowParty = 5;
		}
		if(isset($_REQUEST['id_form']) && !empty($_REQUEST['id_form'])){
			$id_form = esc_sql($_REQUEST['id_form']);
			$check = $esgdb->check_form_mapped_status('wpesg_mapping_system',$id_form,'wpForms');
		}else{
			$check = false;
		}
		
		if($check){
			echo '<div class="alert alert-warning">Selected WP form already mapped with eSign Genie template.Please select another WP form</div>';
			die;
		}
		if(isset($_GET['total_Party_form']) && !empty($_GET['total_Party_form'])){
			$rowParty1 = (Int)$_GET['total_Party_form'];
		}else{
			$id_esg_template = esc_sql($_GET['id_esg_template']);
			$party_count = wpesg_get_template_details_by_id($id_esg_template);
			$party_count = json_decode($party_count);		
			$party_count = count($party_count->template->templatePartyPermissions);
			$rowParty1 = $party_count;
		}
		if($id_form != '' && $id_esg_template != ''){	
			$html ='
				<div class="col-12">
					<div class="row" >
						<div class="col-12">
						<h5><span class="step-round">2</span>  Map Your Form Fields with eSign Genie Template Fields <a href="#" title="What does mapping do? You can map WPForms fields with eSign Genie template fields. When you submit the WPForm, contract or any other type of signable documents are created from the template with field information populated from the WPForms."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
						</div>
					</div>
							 
					<div class="row" >
						<div class="col-4" > 
							<div class="form-group">
								<label for="select-form">WPForms Fields</label>
								<div class="esg-form-field-select">'.
								wpesg_wpforms_field_list($id_form, 'template-map', 1, false, $row)
							.'</div>
						</div>
					</div> 
					<div class="col-4"> 
						<div class="form-group">
							<label>eSign Genie Template Fields</label>
							<div id="esg-template-filed-select">'.
								wpesg_tempalte_field_list($id_esg_template, $row).'
							</div>
						</div>
					</div> 
					<div class="col-4"></div>
				</div> 
				<div class="row">
					<div class="col-3"> 
						<div class="form-group">
							<button id="esg-add-new-field" class="btn btn-sm esg-primary">+ Add field</button>
						</div>
					</div>
				</div>	 	
				<div class="row mt-4">
					<div class="col-12">
						<h5><span class="step-round">3</span> Map Signer Party Fields <a href="#" data-tooltip="You can map the fields on your form, so the signer name on the eSign Genie contract/document can be auto-populated from the WPForms fields based on this mapping."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
					</div>
				</div>
				
					<div class="col-12 signInSequence-check">
					    <input type="hidden" id="esign-party-delete-list" name="esign_party_delete_list" value="">
						<input type="checkbox" id="signInSequence" name="signInSequence" value="1">	Enforce Signing Sequence
					<a href="#" data-tooltip="Selecting this checkbox will enable the invitation to be sent in sequential order. The next party will receive the folder notification for esignature only when the previous party has completed the task. If there is only one signer/recipient, no need to check this field."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></div>
				
				<div class="row" >
					<div class="col-12">
					<table class="table table table-bordered table-striped  table-responsive" id="esg_template_party" ><tr><th>S.N.</th><th>Party Sequence*</th><th>First Name*</th><th>Last Name*</th><th>Email*</th><th>Country Code</th><th>Mobile</th><th>Party Permission*</th><th>Action</th></tr>';
					$i = 0;
					while($i < $rowParty1){
						$no = $i+1;
						if($no == 1){
							$disabled = "disabled";
						}else{
							$disabled ='';
						}
						$html .='<tr id="delete-party-'.$no.'" class="delete-party-row" data-id=0>
							<td class="sn">'.$no.'</td>
							<td>
								<input type="number" class="form-control party_sequence" min=1 max='.$no.'  name="esg_template_party['.$no.'][0]" value="'.$no.'"  required readonly="readonly">
								<div class="valid-feedback">Valid.</div>
								<div class="invalid-feedback">Enter valid Sequence no.</div>
							</td>
							<td>
								'.
									wpesg_party_section_fields('required',$id_form,$no,'firstName')
								.'
							</td>
							<td>
								'.
									wpesg_party_section_fields('required',$id_form,$no,'lastName')
								.'
							</td>
							<td>
								'.
									wpesg_party_section_fields('required',$id_form,$no,'emailId')
								.'
							</td>
							<td>
								'.
									wpesg_party_section_fields('',$id_form,$no,'dialingCode')
								.'
							</td>
							<td>
								'.
									wpesg_party_section_fields('',$id_form,$no,'mobileNumber')
								.'
							</td>
							<td>
								<select class="form-control  form-control-sm"  name="esg_template_party['.$no.'][1]" required><option value="">Select Party Permission</option><option value="FILL_FIELDS_AND_SIGN">FILL FIELDS AND SIGN</option><option value="FILL_FIELDS_ONLY">FILL FIELDS ONLY</option><option value="SIGN_ONLY">SIGN ONLY</option><option value="VIEW_ONLY">VIEW ONLY</option></select>
								<div class="valid-feedback">Valid.</div>
								<div class="invalid-feedback">Select Party Permission.</div>
							</td>
							
							<td>
								<button  data-count='.$no.'  class="btn btn-sm btn-danger" '.$disabled.'>Delete</button>
							</td>
						</tr>';	
						$i++;
					} 
				$html .= '</table></div></div> 
				<div class="row" >
					<div class="col-12"> 
						<div class="form-group">
						<div id="add-party" data-no='.$no.' onclick="add_esg_template_party()" class="btn btn-sm esg-primary">+ Add Party</div>
						</div>
					</div>
				</div>	
				<div class="row mt-4" >
					<div class="col-12">
						<h5><span class="step-round">4</span> Submit Action <a href="#" data-tooltip="Submit action will be used on any submit button on the WP Form."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></h5>		
					</div>
				</div>			
				<div class="row">
					
					<div class="col-4"> 
					 <div class="form-group">
					    <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="send-email" name="send_option" value="Send Email" required>
							<label class="custom-control-label" for="send-email">Send <a href="#" data-tooltip="Send option creates and sends the document to the signer via email with a link to sign the document. The signer will be able to click on the link and sign the document."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>
					</div>	
					</div>	
					<div class="col-4">
					    <div class="form-group">
                        <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="preview-send" name="send_option" value="Preview and Send">
							<label class="custom-control-label" for="preview-send">Preview and Send  <a href="#" data-tooltip="Preview and Send option creates and displays the draft document to the person creating it before sending it to the signer. The signer will be able to click on the link and sign the document."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>					
						</div>					
					</div>
					<div class="col-4"> 
					     <div class="form-group">
					     <div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="sign-send" name="send_option" value="Sign and send">
							<label class="custom-control-label" for="sign-send">Sign and Send to Next Party  <a href="#" data-tooltip="Sign and Send option creates and displays the document to the signer (e.g., customer who is filling out the WPForms). The signer will be able to esign the document, and the next recipient(s) will receive a notification to sign based on the signing sequence. If there is only one signer, the document will be executed immediately after signing."><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/question-circle-light.png" ></a></label>
						</div>
						</div>
					</div>
				</div>
				<div class="col-12 notebox">
				<i>Note: When the users complete the sending/signing steps after the submit action, they will be taken to the redirect URL set up on the WP Form.</i>
				</div>
				<div class="col-12"> 
					<hr />
				</div>
					<div class="col-12"><div class="form-group text-right"><input type="submit" name="save_option" class="btn esg-btn-outline active" value="Save Template Mapping" ></div>
                </div>   				
			</div>';	
		
		}
		echo $html;
		die;	
	}
	add_action( 'wp_ajax_wpesg_set_dynamic_fields_list', 'wpesg_set_dynamic_fields_list' );
}

if (!function_exists('wpesg_tempalte_field_list')){ 
// Wpforms get filed List
	function wpesg_tempalte_field_list($id, $row = 5,$mainFields =''){
		$esg_form_field_option = '';
		$html = '';
		if($id != ''){
			$data = wpesg_get_template_details_by_id($id);
			if($data){
				$esg_tempalte_fields_details = json_decode($data,true);
				foreach($esg_tempalte_fields_details['allfields'] as $key => $field){
					if($field['fieldType'] == 'signfield' || $field['fieldType'] == 'initialfield' || ($field['fieldType'] == 'textfield' && $field['textfieldName'] == 'Signer Name') || ($field['fieldType'] == 'textfield' && $field['textfieldName'] == 'Signer Email')  || ($field['fieldType'] == 'datefield' && $field['datefieldName'] == 'Date Signed')  || $field['fieldType'] == 'buttonfield'){	
						unset($esg_tempalte_fields_details['allfields'][$key]);
					}
				}
			}else{
				$html = 'eSign Genie SERVER RESPONSE ERROR';		 
			}
			for($i=1;$i<=$row;$i++){
				$html .= '<div class="form-group" id="select_template_field_row_'.$i.'"><div class="input-group"><select class="form-control  form-control-sm" id="" name="select_template_field[]"><option value="">Select eSign Genie Template field</option>';
				//echo '<pre>';
				//print_r($esg_tempalte_fields_details['allfields']); 
				//print_r($mainFields); 
				//die;
				foreach($esg_tempalte_fields_details['allfields'] as $esg_temp_field){
					$seleted = '';
					if($esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'] == $mainFields[$i-1]->template_field_name && $mainFields != ''){
						$seleted = 'selected';
					}
					if($esg_temp_field['fieldType'] == 'datefield' && $esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].'||'.$esg_temp_field['dateFormat'] == $mainFields[$i-1]->template_field_name && $mainFields != ''){
					    $seleted = 'selected';
					}
					
					if($esg_temp_field['fieldType'] == 'dropdownfield' && $esg_temp_field['fieldType'].'$$'.$esg_temp_field['dropdownFieldName'] == $mainFields[$i-1]->template_field_name && $mainFields != ''){
					    $seleted = 'selected';
					}
					if($esg_temp_field['fieldType'] == 'checkboxfield'){
						$html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].' ('.$esg_temp_field['cbgroup'].')</option>';
					}elseif($esg_temp_field['fieldType'] == 'datefield'){
					          $html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].'||'.$esg_temp_field['dateFormat'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].' ('.$esg_temp_field['dateFormat'].')</option>';
					
					}elseif($esg_temp_field['fieldType'] == 'dropdownfield'){
					          $html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field['dropdownFieldName'].'" '.$seleted.'>'.$esg_temp_field['dropdownFieldName'].'</option>';
					
					}else{
						$html .= '<option value="'.$esg_temp_field['fieldType'].'$$'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'" '.$seleted.'>'.$esg_temp_field[$esg_temp_field['fieldType'].'Name'].$esg_temp_field['cbname'].'</option>';
					}
				}	
				$html .= '</select><span class="template-del"><div class="btnic esg-delete-field-group" onclick="deleteFiledsgroup('.$i.')"><img src="'.WPESG_PLUGIN_PATH_ADMIN_IMAGE.'/trash-alt-light.png"></div></span></div></div>';
			}
		}
		return $html;	
	}
	add_action( 'wp_ajax_wpesg_tempalte_field_list', 'wpesg_tempalte_field_list' );
}

if (!function_exists('wpesg_party_section_fields')){
	// Party Section get filed List
	function wpesg_party_section_fields($required ='',$id,$party_count=1,$partyPram,$partyFields='',$partyid=0){
		$html = '';
		if($id != ''){
			$form_name = "select_Wpforms_party_field[$party_count][]";
			$esg_wpforms_data = get_post($id);
			if($esg_wpforms_data){
				$esg_form_data_array = json_decode($esg_wpforms_data->post_content);
				$html .= '<select class="form-control  form-control-sm" id="" name="'.$form_name.'" '.$required.'><option value="">Select Form Field</option>';
				foreach($esg_form_data_array->fields as $field){
					if($field->type != 'pagebreak' && $field->type != 'html' && $field->type != 'file-upload' && $field->type != 'pagebreak' && $field->type != 'radio' && $field->type != 'checkbox' && $field->type != 'payment-multiple' && $field->type != 'address' && $field->type != 'date-time'){
					$fseleted = '';
					$mseleted = '';
					$lseleted = '';
					$seleted = '';
					foreach($partyFields as $partyField){					
						if($partyField->form_field_id == $field->id && $partyField->party_filed_name == $partyPram && $partyField->party_id == $partyid && $field->type != 'radio' && $field->type != 'payment-multiple' && $field->type != 'checkbox' && $field->type != 'address' && $field->type != 'date-time'){
							if($partyField->form_sub_field == 'first'){
								$fseleted = 'selected';
							}elseif($partyField->form_sub_field == 'middle'){
								$mseleted = 'selected';
							}elseif($partyField->form_sub_field == 'last'){
								$lseleted = 'selected';
							}else{
								$seleted = 'selected';
							}
						}
					}
					if($field->type == 'name'){
							if($field->format == 'simple'){				
								$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'" '.$seleted.'>'.$field->label.'</option>';
							}elseif($field->format == 'first-last'){
								$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'-first" '.$fseleted.'>First '.$field->label.'</option>';
								$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'-last" '.$lseleted.'>Last '.$field->label.'</option>';
							}else{
								$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'-first" '.$fseleted.'>First '.$field->label.'</option>';
								$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'-middle" '.$mseleted.'>Middle '.$field->label.'</option>';
								$html .= '<option value="'.$field->id.'-last" '.$lseleted.'>Last '.$field->label.'</option>';
							}
						}else{
							$html .= '<option value="'.$partyid.'-'.$field->id.'-'.$partyPram.'-'.$field->type.'" '.$seleted.'>'.$field->label.'</option>';
						}
					
				}	
				}  
				$html .= '</select>';
			}		
			return $html;
		}
	}
}

if (!function_exists('wpesg_add_party')){
	function wpesg_add_party(){
		$html = '';
		if($_REQUEST['id_form'] != '' && $_REQUEST['party_count'] != ''){	
		$no = sanitize_text_field($_REQUEST['party_count']);
		$id_form = esc_sql($_REQUEST["id_form"]);
		if($no == 1){
					 $disabled = "disabled";
					}else{
							 $disabled = "";
					}
		$html = '<tr id="delete-party-'.$no.'" class="delete-party-row" data-id=0>
					<td class="sn">'.$no.'</td>
					<td>
						<input type="number" class="form-control party_sequence" min=1  max='.$no.' name="esg_template_party['.$no.'][0]" value="'.$no.'"  required '.$_REQUEST['sequence'].' >
						<div class="valid-feedback">Valid.</div>
						<div class="invalid-feedback">Enter Valid Sequence no.</div>
					</td>
					<td>
						'.
							wpesg_party_section_fields('required',$id_form,$no,'firstName')
						.'
						<div class="valid-feedback">Valid.</div>
						<div class="invalid-feedback">Map First Name</div>	
					</td>
					<td>
						'.
							wpesg_party_section_fields('required',$id_form,$no,'lastName')
						.'
					</td>
					<td>
						'.
							wpesg_party_section_fields('required',$id_form,$no,'emailId')
						.'
						<div class="valid-feedback">Valid.</div>
						<div class="invalid-feedback">Map Email</div>
					</td>
					<td>
						'.
							wpesg_party_section_fields('',$id_form,$no,'dialingCode')
						.'
					</td>
					<td>
						'.
							wpesg_party_section_fields('',$id_form,$no,'mobileNumber')
						.'
					</td>
					<td>
						<select class="form-control  form-control-sm"  name="esg_template_party['.$no.'][1]" required><option value="">Select Party Permission</option><option value="FILL_FIELDS_AND_SIGN">FILL FIELDS AND SIGN</option><option value="FILL_FIELDS_ONLY">FILL FIELDS ONLY</option><option value="SIGN_ONLY">SIGN ONLY</option><option value="VIEW_ONLY">VIEW ONLY</option></select>
						<div class="valid-feedback">Valid.</div>
						<div class="invalid-feedback">Select Party Permission</div>
					</td>
					
					<td>
						<button data-count='.$no.' class="btn btn-sm btn-danger" '.$disabled.'>Delete</button>
					</td>
				</tr>';
		}
		echo $html;
		die;		
	}
	add_action( 'wp_ajax_wpesg_add_party', 'wpesg_add_party');
}

