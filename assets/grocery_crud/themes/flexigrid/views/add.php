<?php 
$field_prefix="field-" . $table_name . "-";
?>

<script id="add_php_script" type="text/javascript">
	
var load_css_file = function(css_file) {
	if ($('head').find('link[href="'+css_file+'"]').length == 0) {
		$('head').append($('<link/>').attr("type","text/css")
				.attr("rel","stylesheet").attr("href",css_file));
	}
};
	
var ei_table_fnOpenEditForm = function(this_element,table_name,reload_after,express_form){
	var href_url = this_element.attr("href");
	var dialog_height = $(window).height() - 80;
	//Close all
	$(".ui-dialog-content").dialog("close");
	
	//OBTAIN default_values_view and
	var default_values_view = "";
	
	//http://localhost/ebre-inventory/index.php/main/defaultvalues_view/{table_name}
	var defaultvalues_view_url="<?php echo base_url($defaultvalues_view_url);?>" + "/" + table_name;
	//$.getScript(defaultvalues_view_url);
	$.ajax({
		url: defaultvalues_view_url,
		data: {
			is_ajax: 'true'
		},
		type: 'post',
		dataType: 'json',
		success: function (data) {
			default_values_view=data.output;
		}
	});
	
	$.ajax({
		url: href_url,
		data: {
			is_ajax: 'true',
			express_form: express_form
		},
		type: 'post',
		dataType: 'json',
		beforeSend: function() {
			this_element.closest('.flexigrid').addClass('loading-opacity');
		},
		complete: function(){
			this_element.closest('.flexigrid').removeClass('loading-opacity');
		},
		success: function (data) {
			if (typeof CKEDITOR !== 'undefined' && typeof CKEDITOR.instances !== 'undefined') {
					$.each(CKEDITOR.instances,function(index){
						delete CKEDITOR.instances[index];
					});
			}

			LazyLoad.loadOnce(data.js_lib_files);
			LazyLoad.load(data.js_config_files);

			$.each(data.css_files,function(index,css_file){
				load_css_file(css_file);
			});
			
			data.output = default_values_view + data.output;
			$("<div id='dialog'/>").html(data.output).dialog({
				width: 910,
				modal: true,
				height: dialog_height,
				close: function(){
					if (reload_after)	{
						$('#' + table_name + '_link_reload').trigger('click');
					}
					
					$(this).remove();
				},
				open: function(){
					var this_dialog = $(this);

					$('#'+table_name+'_cancel-button').click(function(){
						this_dialog.dialog("close");
					});

				}
			});
		}
	});
	
};	

var set_default_dropdown_field_value = function(table_name,field_name,default_field_value) {
	field_prefix = "field-" + table_name + "-";
	field_id = field_prefix + field_name;
	field_jquery_selector = '#' + field_prefix + field_name;

	if (document.getElementById(field_id) != null) {
		var element = document.getElementById(field_id);
		element.value = default_field_value; 
		$(field_jquery_selector).trigger('liszt:updated');
		$(field_jquery_selector).trigger('change');
		return;
	}
}

var set_last_dropdown_field_value = function(table_name,field_name,chosen_table_name) {
	//Execute ajax to get last added value
	
	get_last_added_value_base_url='<?php echo base_url($get_last_added_value_url);?>';
	ajax_url= get_last_added_value_base_url + "/" + chosen_table_name;
	get_last_added_value="";

	field_prefix = "field-" + table_name + "-";
	field_jquery_selector = '#' + field_prefix + field_name;
	console.log("field_jquery_selector:" . field_jquery_selector);
	$.ajax({
		url: ajax_url,
		data: {
			is_ajax: 'true'
		},
		type: 'post',
		dataType: 'json',
		success: function (data) {
			get_last_added_value=data.output;
			//Update chosen and set again default value
			$(field_jquery_selector + ' option[value="' + data.output  + '"]' ).attr('selected', 'selected');
			$(field_jquery_selector).trigger('liszt:updated');
			$(field_jquery_selector).chosen().trigger('change');
		}
	});
}

var refresh_chosen = function(table_name,field_name,chosen_table_name,chosen_field_name,default_value)	{
	//AJAX REQUEST TO OBTAIN CHOSEN VALUES IN DATABASE
	get_dropdown_values_base_url='<?php echo base_url('index.php/main/get_dropdown_values/');?>';
	ajax_url= get_dropdown_values_base_url + "/" + chosen_table_name + "/" +chosen_field_name;

	field_prefix = 'field-' + table_name + '-';
	field_jquery_selector = '#' + field_prefix + field_name;
	$.ajax({
		url: ajax_url,
		data: {
			is_ajax: 'true'
		},
		type: 'post',
		dataType: 'json',
		success: function (data) {
			$(field_jquery_selector).empty();
			$(field_jquery_selector).append( new Option("",""));
			$.each( data.output, function( key, value ) {
				$(field_jquery_selector).append( new Option(value.name,value[data.key]) );
			});
			
			//Update chosen and set again default value
			$(field_jquery_selector + ' option[value="' + default_value  + '"]' ).attr('selected', 'selected');
			$(field_jquery_selector).trigger('liszt:updated');
			$(field_jquery_selector).chosen().trigger('change');
		}
	});
}

var get_default_field_value = function(field_name) {
	switch (field_name)	{
		case 'fieldexternalIDType': 
			return 1;
            break;
		case 'TODO': 
			return "TODO";
            break;
	}
	return 1;
}
	
$(function(){
	
<?php 
//Register Chosen change event for relation/dropdown fields
foreach($field_values as $key => $value) { 
	if ($input_fields[$key]->crud_type == "relation") {?>
	
$('#<?php echo $field_prefix . $key ;?>').chosen().change(function(event) {
	relation_table = '<?php echo $input_fields[$key]->extras["1"];?>'; 
	read_url= '<?php echo base_url('index.php/main/');?>' + "/" + relation_table + "/read/";
	selectedValue = $(this).find("option:selected").val();
	$("#<?php echo $key ;?>_link_read").attr("href", read_url + selectedValue)
}).trigger('change'); // added trigger to calculate initial state

<?php 
	}
}
?>

	var activate_all_chosens = function(){
		$(".chosen-select,.chosen-multiple-select").chosen({allow_single_deselect:true});
	};
	
	var activate_all_multiselects = function(){
		$(".multiselect").multiselect();
	}
	
	var activate_all_editors = function(){
		if (typeof CKEDITOR !== 'undefined' && typeof CKEDITOR.instances !== 'undefined') {
			$.each(CKEDITOR.instances,function(index){
				delete CKEDITOR.instances[index];
			});
		}
		
		var editor='ckeditor';
		switch (editor)	{
			case 'ckeditor': 
			    //CKEDITOR
				$( 'textarea.texteditor' ).ckeditor({toolbar:'Full'});
				$( 'textarea.mini-texteditor' ).ckeditor({toolbar:'Basic',width:700});
				break;
			case 'tinymce': 
				//TINY_MCE
				$('textarea.texteditor').tinymce(tinymce_options);
				$('textarea.mini-texteditor').tinymce(minimal_tinymce_options);
				break;
			case 'markitup': 
				//markitup
				$('.texteditor').markItUp(mySettings);
				$( 'textarea.mini-texteditor' ).markItUp(mySettings);
				break;
		}
	};
	
	$(".ajax_refresh_and_loading").on("click", function(event){
		$( "a[id$='_reload']" ).trigger('click');
	})
	
	$("#<?php echo $table_name;?>_reset-button").on("click", function(event){
		//alert("Click on refresh!");
		//REFRESH
		
		$.ajax({
			url: "http://localhost/ebre-inventory/index.php/main/inventory_object/add",
			data: {
				is_ajax: 'true'
			},
			type: 'post',
			dataType: 'json',
			success: function (data) {
				//alert(data.output);
				
				//alert ($("<div>" + data.output + "</div>").find( '#<?php echo $table_name;?>_form-div' ).html());
				
				//alert(form_div);
				$('#<?php echo $table_name;?>_form-div').html( 
						$("<div>" + data.output + "</div>").find( '#<?php echo $table_name;?>_form-div' ).html());
				//window.location.href = window.location.href;
				
				activate_all_chosens();
				activate_all_multiselects();
				activate_all_editors();
				set_form_default_values();				
			}
		});
		
	})
	
});


/** INIT EXPRESS IMPLEMENTATION *********/
//Check if express is requested by anchor #express_form
//By default forms are normal (not express)
express_form=false;

//Express form could be forced by view variable 
<?php if (isset($express_form)): ?>
  express_form=<?php echo $express_form ? 'true' : 'false';?>;
<?php endif; ?>

//Express form could be forced by anchor
if(window.location.hash) {
	var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
	if (hash == "express_form") {
		express_form=true;
	}
} 

/** END EXPRESS IMPLEMENTATION *********/



</script>

<?php

	$this->set_css($this->default_theme_path.'/flexigrid/css/flexigrid.css');
	$this->set_js_lib($this->default_theme_path.'/flexigrid/js/jquery.form.js');
	$this->set_js_config($this->default_theme_path.'/flexigrid/js/flexigrid-add.js');

	$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.noty.js');
	$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/config/jquery.noty.config.js');

//echo $input_fields;
//print_r($input_fields);

function check_if_express($input_fields) {
	foreach ($input_fields as $key => $value) {
		if ($value->express)
			return true;
	}
    return false;
}
?>

<div class="flexigrid crud-form" style='width: 100%;' data-unique-hash="<?php echo $unique_hash; ?>">
	<div class="mDiv">
		<div class="ftitle">
			<div class='ftitle-left' id="ftitle-left_<?php echo $table_name;?>">
				<?php echo $this->l('form_add'); ?> <?php echo $subject?>
			</div>
			<div class='clear'></div>
		</div>
		<div title="<?php echo $this->l('minimize_maximize');?>" class="ptogtitle">
			<span></span>
		</div>
	</div>
<div id='main-table-box'>
	<div class="pDiv">
		<div class="pDiv2">
			<div class="pGroup">
				<div class="pReload pButton ajax_refresh_and_loading" id='ajax_refresh_and_loading' title="<?php echo lang("Reload");?>">
					<span></span>
				</div>
			</div>
			
			<div class="btnseparator"> </div>
			
			
			<?php if (check_if_express($input_fields)): ?>
			
			<div class="pGroup">
				
				&nbsp;<a href="#" id="express_<?php echo $table_name;?>" onclick="event.preventDefault();
					$('#express_<?php echo $table_name;?>').toggle();
					$('#noexpress_<?php echo $table_name;?>').toggle();
					
					<?php 
						foreach ($input_fields as $key => $value) {
							if (!$value->express) 
								echo "$('#" . $key . "_" . $table_name . "_field_box').toggle();";
							}
					?>
					
					return false;"><?php echo lang("show_express_form");?></a>
			          <a href="#" id="noexpress_<?php echo $table_name;?>" style="display:none;" onclick="
			          $('#express_<?php echo $table_name;?>').toggle();
					  $('#noexpress_<?php echo $table_name;?>').toggle();
					  <?php 
						foreach ($input_fields as $key => $value) {
							if (!$value->express) 
								echo "$('#" . $key . "_" . $table_name . "_field_box').toggle();";
							}
					  ?>
			          return false;"><?php echo lang("hide_express_form");?></a>
			    </span>  
			</div>
			
			<?php endif; ?>

		</div>
	<div class="clear"></div>		

	</div>
		
		
		<div class='form-div' id='<?php echo $table_name;?>_form-div'>
			<?php echo form_open( $insert_url, 'method="post" id="crudForm_' . $table_name . '" enctype="multipart/form-data" class="crudForm"'); ?>
			<?php
			$counter = 0;
				foreach($fields as $field)
				{
					$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
					$counter++;
			?>
			
			<div class='form-field-box <?php echo $even_odd?>' id="<?php echo $field->field_name; ?>_<?php echo $table_name;?>_field_box">
				<div class='form-display-as-box' id="<?php echo $field->field_name; ?>_<?php echo $table_name;?>_display_as_box">
					<?php echo $input_fields[$field->field_name]->display_as; ?><?php echo ($input_fields[$field->field_name]->required)? "<span class='required'>*</span> " : ""; ?> :
				</div>
				<div class='form-input-box' id="<?php echo $field->field_name; ?>_<?php echo $table_name;?>_input_box">
					<?php echo $input_fields[$field->field_name]->input?> 
				</div>
					<?php if ($grocery_crud_details_relation): ?>
					
						<?php if (($input_fields[$field->field_name]->crud_type == "relation" ) && !$is_ajax): ?>
							<?php
								$relation_table =  trim($input_fields[$field->field_name]->extras["1"]); 
								$fielname_in_extras =  $input_fields[$field->field_name]->extras["2"];
								$unset_dropdowndetails="";
								if ( array_key_exists ( "5" , $input_fields[$field->field_name]->extras ))
									$unset_dropdowndetails = $input_fields[$field->field_name]->extras["5"];
								$fielname_in_extras =  str_replace("}","",str_replace("{","",$fielname_in_extras));
							?>
							<?php if($unset_dropdowndetails==""): ?>	
								&nbsp;<a id="<?php echo $field->field_name;?>_link_add" href="<?php echo base_url('index.php/main/'. $relation_table . '/add');?>" style="font-size:75%;" onclick="event.preventDefault();ei_table_fnOpenEditForm($(this),'<?php echo $relation_table;?>',true,true);return false;"><?php echo $this->l("Add");?></a> | 
								<a id="<?php echo $field->field_name;?>_link_last_added_value" href="#" style="font-size:75%;" onclick="set_last_dropdown_field_value('<?php echo $table_name;?>','<?php echo $field->field_name;?>','<?php echo $relation_table;?>');return false;"><?php echo $this->l("Last Added Value");?></a> | 
								<a id="<?php echo $field->field_name;?>_link_read" href="<?php echo base_url('index.php/main/'. $relation_table . '/read');?>/<?php echo $field_values->{$field->field_name};?>" style="font-size:75%;" onclick="event.preventDefault();ei_table_fnOpenEditForm($(this),'<?php echo $field->field_name;?>',false);return false;"> <?php echo $this->l("Details");?> </a> | 
								<a id="<?php echo $field->field_name;?>_link_reload" href="#" style="font-size:75%;" onclick="event.preventDefault();refresh_chosen('<?php echo $table_name;?>','<?php echo $field->field_name;?>','<?php echo $relation_table;?>','<?php echo $fielname_in_extras;?>','<?php echo $field_values->{$field->field_name};?>');return false;"><?php echo $this->l("Reload");?></a> 
								
								<?php if ($field_values->{$field->field_name} != ""): ?>
								| <a id="<?php echo $field->field_name;?>_link_default" href="#" style="font-size:75%;" onclick="set_default_dropdown_field_value('<?php echo $table_name;?>','<?php echo $field->field_name;?>','<?php echo $field_values->{$field->field_name};?>');return false;" ><?php echo $this->l("Default value");?></a>
								<?php endif; ?>
								
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<div class='clear'></div>
			</div>
			<?php }?>
			<!-- Start of hidden inputs -->
				<?php
					foreach($hidden_fields as $hidden_field){
						echo $hidden_field->input;
					}
				?>
			<!-- End of hidden inputs -->
			<?php if ($is_ajax) { ?><input type="hidden" name="is_ajax" value="true" /><?php }?>

			<div id='<?php echo $table_name;?>_report-error' class='report-div error'></div>
			<div id='<?php echo $table_name;?>_report-success' class='report-div success'></div>
		</div>
		
		<div class="pDiv">
			<div class='form-button-box'>
				<input id="<?php echo $table_name;?>_form-button-save" type='button' value='<?php echo $this->l('form_save'); ?>'  class="btn btn-large"/>
			</div>
<?php 	if(!$this->unset_back_to_list) { ?>
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_save_and_go_back'); ?>' id="<?php echo $table_name;?>_save-and-go-back-button"  class="btn btn-large"/>
			</div>
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_cancel'); ?>' class="btn btn-large" id="<?php echo $table_name;?>_cancel-button" />
			</div>
<?php 	} ?>

			<div class='form-button-box'>
				<input type='button' value='<?php echo "Reset"; ?>' class="btn btn-large" id="<?php echo $table_name;?>_reset-button" />
			</div>
			
			<div class='form-button-box'>
				<div class='small-loading' id='FormLoading'><?php echo $this->l('form_insert_loading'); ?></div>
			</div>
			<div class='clear'></div>
		</div>
	<?php echo form_close(); ?>
</div>
</div>
<script>
	var validation_url = '<?php echo $validation_url?>';
	var list_url = '<?php echo $list_url?>';

	var message_alert_add_form = "<?php echo $this->l('alert_add_form')?>";
	var message_insert_error = "<?php echo $this->l('insert_error')?>";
	
	var table_name = "<?php echo $table_name;?>";
	
	if (express_form) {
		$('#express_<?php echo $table_name;?>').trigger('click');
	}
</script>
