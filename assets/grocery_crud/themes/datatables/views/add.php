<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	$this->set_css($this->default_theme_path.'/datatables/css/datatables.css');
	$this->set_js($this->default_theme_path.'/flexigrid/js/jquery.form.js');	
	$this->set_js($this->default_theme_path.'/datatables/js/datatables-add.js');
	$this->set_css($this->default_css_path.'/ui/simple/jquery-ui-1.8.23.custom.css');
	$this->set_js($this->default_javascript_path.'/jquery_plugins/ui/jquery-ui-1.8.23.custom.min.js');	
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
	
	var upload_a_file_string = '<?php echo $this->l('form_upload_a_file');?>';
</script>
<div class='ui-widget-content ui-corner-all datatables'>
	<h3 class="ui-accordion-header ui-helper-reset ui-state-default form-title">
		<div class='floatL form-title-left'>
			<a href="#"><?php echo $this->l('form_add'); ?> <?php echo $subject?></a>
		</div>	
		<div class='clear'></div>
	</h3>
<div class='form-content form-div'>
	<?php echo form_open( $insert_url, 'method="post" id="crudForm" autocomplete="off" enctype="multipart/form-data"'); ?>
		<div>
			<?php
			$counter = 0; 
				foreach($fields as $field)
				{
					$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
					$counter++;
			?>
			<div class='form-field-box <?php echo $even_odd?>' id="<?php echo $field->field_name; ?>_field_box">
				<div class='form-display-as-box' id="<?php echo $field->field_name; ?>_display_as_box">
					<?php echo $input_fields[$field->field_name]->display_as?><?php echo ($input_fields[$field->field_name]->required)? "<span class='required'>*</span> " : ""?> :
				</div>
				<div class='form-input-box' id="<?php echo $field->field_name; ?>_input_box">
					<?php echo $input_fields[$field->field_name]->input?>
				</div>
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
			<div class='line-1px'></div>
			<div id='report-error' class='report-div error'></div>
			<div id='report-success' class='report-div success'></div>							
		</div>	
		<div class='buttons-box'>
			<div class='form-button-box'>
				<input type='submit' value='<?php echo $this->l('form_save'); ?>' class='ui-input-button'/>
			</div>
<?php 	if(!$this->unset_back_to_list) { ?>				
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_save_and_go_back'); ?>' class='ui-input-button' id="save-and-go-back-button"/>
			</div>								
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_cancel'); ?>' onclick="javascript: goToList()" class='ui-input-button' />
			</div>
<?php   } ?>			
			<div class='form-button-box loading-box'>
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
</script>