<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	$this->set_css('assets/grocery_crud/themes/datatables/css/datatables.css');
	$this->set_js('assets/grocery_crud/themes/flexigrid/js/jquery.form.js');	
	$this->set_js('assets/grocery_crud/themes/datatables/js/datatables-edit.js');
	$this->set_css('assets/grocery_crud/css/ui/simple/jquery-ui-1.8.10.custom.css');
	$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery-ui-1.8.10.custom.min.js');	
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
	
	var upload_a_file_string = '<?php echo $this->l('form_upload_a_file');?>';	
</script>
<div class='ui-widget-content ui-corner-all datatables'>
	<h3 class="ui-accordion-header ui-helper-reset ui-state-default form-title">
		<div class='floatL form-title-left'>
			<a href="#"><?php echo $this->l('form_edit'); ?> <?php echo $subject?></a>
		</div> 
		<div class='floatR'>
			<a href='<?php echo $list_url?>' onclick='javascript: return goToList()' class='gotoListButton' >
				<?php echo $this->l('form_back_to_list'); ?>
			</a>
		</div>
		<div class='clear'></div>
	</h3>
<div class='form-content form-div'>
	<form action='<?php echo $update_url?>' method='post' id='crudForm' autocomplete='off' enctype="multipart/form-data">
		<div>
		<?php
			$counter = 0; 
			foreach($fields as $field)
			{
				$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
				$counter++;
		?>
			<div class='form-field-box <?php echo $even_odd?>'>
				<div class='form-display-as-box'>
					<?php echo $input_fields[$field->field_name]->display_as?><?php echo ($input_fields[$field->field_name]->required)? "* " : ""?> :
				</div>
				<div class='form-input-box'>
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
				<input type='submit' value='<?php echo $this->l('form_update_changes'); ?>' class='ui-input-button' />
			</div>		
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_cancel'); ?>' onclick='javascript: return goToList()' class='ui-input-button' />
			</div>
			<div class='form-button-box loading-box'>
				<div class='small-loading' id='FormLoading'><?php echo $this->l('form_update_loading'); ?></div>
			</div>
			<div class='clear'></div>	
		</div>
	</form>	
</div>
</div>
<script>
	var validation_url = '<?php echo $validation_url?>';
	var list_url = '<?php echo $list_url?>';

	var message_alert_edit_form = "<?php echo $this->l('alert_edit_form')?>";
	var message_update_error = "<?php echo $this->l('update_error')?>";	
</script>