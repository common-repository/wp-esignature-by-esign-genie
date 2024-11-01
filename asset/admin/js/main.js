jQuery('document').ready(function(){	
  jQuery("#esg-disconnect").click(function(){
	 var id = jQuery(this).data('id');
       jQuery.ajax({
        url: ajaxurl,
        data: {
            'action': 'wpesg_disconnect_ajax_call',
			'company_id': id
        },
        success:function(data) {
          location.reload(true);
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });  
  });	
  
    jQuery("#esg-form-select").change(function(){
		if(jQuery(this).val() != '' && jQuery("#esg-tempalte-select").val() != ''){
			var mix_form_id = jQuery(this).val().split("#$$#");
			var id_form = mix_form_id[0];
			var mix_esg_temp_id = jQuery("#esg-tempalte-select").val().split("#$$#");
			var id_esg_template = mix_esg_temp_id[0];
		}else{
			jQuery(".esg-dynamic-fields").hide();
			return false;
		}
		jQuery(".esg-dynamic-fields").show();
		jQuery(".esg-dynamic-fields").html('<div class="col-8 text-center" ><img src="'+esg_loder.image_path+'" width="50px"></div>');
       jQuery.ajax({
			url: ajaxurl,
			data: {
				'action': 'wpesg_set_dynamic_fields_list',
				'id_form': id_form,
				'id_esg_template': id_esg_template
			},
			success:function(data) {
				//console.log(data);
			  jQuery(".esg-dynamic-fields").html(data);
			  jQuery(".esg-dynamic-fields").show();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});  
	});
  
    jQuery("#esg-tempalte-select").change(function(){
		if(jQuery(this).val() != '' && jQuery("#esg-form-select").val() != ''){
			var mix_esg_temp_id = jQuery(this).val().split("#$$#");
			var id_esg_template = mix_esg_temp_id[0];
			var mix_form_id = jQuery("#esg-form-select").val().split("#$$#");
			var id_form = mix_form_id[0];
		}else{
			jQuery(".esg-dynamic-fields").hide();
			return false;
		}
	    jQuery(".esg-dynamic-fields").show();
		jQuery(".esg-dynamic-fields").html('<div class="col-8 text-center" ><img src="'+esg_loder.image_path+'" width="50px"></div>');
       jQuery.ajax({
			url: ajaxurl,
			data: {
				'action': 'wpesg_set_dynamic_fields_list',
				'id_form': id_form,
				'id_esg_template': id_esg_template
			},
			success:function(data) {
				console.log('Testing');
			  jQuery(".esg-dynamic-fields").html(data);
			  jQuery(".esg-dynamic-fields").show();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});   
	});
	jQuery(document).on("click", "#esg-add-new-field" , function() {
		jQuery('#esg-add-new-field').attr('disabled', 'disabled');
		var tempId = jQuery("#esg-tempalte-select").val();
		var formId = jQuery("#esg-form-select").val();
		var esgFieldCount = jQuery("#esg-filed-count").val();
		var postData = {"tempId":tempId,"formId":formId,"fieldCount":esgFieldCount};
       jQuery.ajax({
			url: ajaxurl,
			dataType : 'json', 
			data: {
				'action': 'wpesg_add_field_list',
				'data': postData
			},
			success:function(data) {
				console.log(data);
			  jQuery(".esg-form-field-select").append(data.wpforms)
			  jQuery("#esg-template-filed-select").append(data.esgtemp);
			  jQuery("#esg-filed-count").val(parseInt(esgFieldCount)+1);
			  jQuery('#esg-add-new-field').removeAttr('disabled');
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});  
	});	
	
});



// Disable form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Get the forms we want to add validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();	  
        }
        form.classList.add('was-validated');
		
      }, false);
    });
  }, false);
  
})();

 
   function deleteFiledsgroup(id){
	   jQuery(this).remove();
	   jQuery("#select_template_field_row_"+id).remove();
	   jQuery("#select_Wpforms_field_row_"+id).remove();
       jQuery("#esg-filed-count").val(parseInt(id)-1);
   }
   
  function add_esg_template_party(){
	var oid = jQuery("#add-party").attr("data-no");
	var sequence = 'readonly';
	   oid = parseInt(oid)+1;
	jQuery('#add-party').attr('disabled', 'disabled');
	if(jQuery("#signInSequence").prop("checked") == true){
	    sequence = ''; 
	}
	if(jQuery("#esg-form-select").val() != '' && oid != ''){
			var mix_form_id = jQuery("#esg-form-select").val().split("#$$#");
			var id_form = mix_form_id[0];
		}
	
       jQuery.ajax({
			url: ajaxurl,
			data: {
				'action': 'wpesg_add_party',
				'id_form': id_form,
				'party_count': oid,
				'sequence' : sequence
			},
			success:function(data) {
			  jQuery(".party_sequence").attr({"max" : parseInt(oid)});	
			  jQuery("#esg_template_party").append(data);
			  jQuery("#add-party").attr("data-no",oid);
			  var precount =  parseInt(oid)-1;
			  jQuery(".delete_esg_party_"+precount).hide();
			  jQuery('#add-party').removeAttr('disabled');
			  console.log("Add:"+jQuery("#add-party").data('no'));
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});	 
		
	}
	
	function delete_esg_party(id){
		var precount =  parseInt(id)-1;
		jQuery('.btn-danger').attr('disabled', 'disabled');
		 if(jQuery("#delete-party-"+id).data('id') != 0){
			 var preid = jQuery("#esign-party-delete-list").val(); 
			 if(preid != ''){
				 var nids = preid.concat(','+jQuery("#delete-party-"+id).data('id'));
				 jQuery("#esign-party-delete-list").val(nids); 
			 }else{
				 jQuery("#esign-party-delete-list").val(jQuery("#delete-party-"+id).data('id')); 
			 }
			 
		 }
	    //alert(parseInt(jQuery("#add-party").data('count'))-1);
		var cunt = parseInt(jQuery("#add-party").data('no'))-1;
		 jQuery("#add-party").attr("data-no",cunt);
		// console.log("delete:"+jQuery("#add-party").data('count'));
		var i=1;
		var j=1;
		var k=1;
		var l=1;
		jQuery(".delete_esg_party_"+precount).show();
		jQuery("#delete-party-"+id).remove();
		jQuery( ".party_sequence" ).each(function() {
			jQuery(this).val(i);
			i++;
		});
		jQuery( ".btn-danger" ).each(function() {
			jQuery(this).attr("data-count",k);;
			k++;
		});
		jQuery( ".delete-party-row" ).each(function() {
			jQuery(this).attr("id","delete-party-"+l);
			l++;
		});
		jQuery( ".sn" ).each(function() {
			console.log(j);
			jQuery( this ).text(j );
			j++;
		});
		jQuery(".party_sequence").attr({"max" : parseInt(i)-1});
		jQuery('.btn-danger').removeAttr('disabled');
		
		
	}
	jQuery(document).on("click", ".btn-danger" , function(){
		jQuery(this).unbind( "click" );
		id = jQuery(this).data('count');
	   var precount =  parseInt(id)-1;
		 if(jQuery("#delete-party-"+id).data('id') != 0){
			 var preid = jQuery("#esign-party-delete-list").val(); 
			 if(preid != ''){
				 var nids = preid.concat(','+jQuery("#delete-party-"+id).data('id'));
				 jQuery("#esign-party-delete-list").val(nids); 
			 }else{
				 jQuery("#esign-party-delete-list").val(jQuery("#delete-party-"+id).data('id')); 
			 }
			 
		 }
		var i=1;
		var j=1;
		var k=1;
		var l=1;
		jQuery("#delete-party-"+id).remove();
		jQuery( ".party_sequence" ).each(function() {
			jQuery(this).val(i);
			i++;
		});
		jQuery( ".btn-danger" ).each(function() {
			jQuery(this).attr("data-count",k);
			k++;
		});
		jQuery( ".delete-party-row" ).each(function() {
			jQuery(this).attr("id","delete-party-"+l);
			l++;
		});
		
		jQuery( ".sn" ).each(function() {
			console.log(j);
			jQuery( this ).text(j );
			j++;
		});
		jQuery(".party_sequence").attr({"max" : parseInt(i)-1});
		jQuery("#add-party").attr("data-no",i-1); 
		 
	});
   jQuery(document).on("change", "#signInSequence" , function(){
			if(jQuery(this).prop("checked") == true){
			jQuery('.party_sequence').removeAttr('readonly');
		}else{
			jQuery('.party_sequence').attr('readonly', 'readonly');
		}
		
		
	});
   