<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	$this->set_css($this->default_theme_path.'/flexigrid/css/flexigrid.css');
	$this->set_js($this->default_theme_path.'/flexigrid/js/jquery.form.js');	
	$this->set_js($this->default_theme_path.'/flexigrid/js/flexigrid-add.js');
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
	
	var upload_a_file_string = '<?php echo $this->l('form_upload_a_file');?>';
</script>
<div class="flexigrid crud-form" style='width: 100%;'>	
	<div class="mDiv">
		<div class="ftitle">
			<div class='ftitle-left'>
				<?php echo $this->l('form_add'); ?> <?php echo $subject?>
			</div>
<?php 	if(!$this->unset_back_to_list) { ?>				
			<div class='ftitle-right'>
				<a href='<?php echo $list_url?>' onclick='javascript: return goToList()' ><?php echo $this->l('form_back_to_list'); ?></a>
			</div>
<?php 	} ?>			
			<div class='clear'></div>
		</div>
		<div title="Minimize/Maximize Table" class="ptogtitle">
			<span></span>
		</div>
	</div>
<div id='main-table-box'>
	<?php echo form_open( $insert_url, 'method="post" id="crudForm" autocomplete="off" enctype="multipart/form-data"'); ?>
		<div class='form-div'>
			<?php
			$counter = 0; 
				foreach($fields as $field)
				{
					$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
					$counter++;
			?>
			<div class='form-field-box <?php echo $even_odd?>' id="<?php echo $field->field_name; ?>_field_box">
				<div class='form-display-as-box' id="<?php echo $field->field_name; ?>_display_as_box">
					<?php echo $input_fields[$field->field_name]->display_as; ?><?php echo ($input_fields[$field->field_name]->required)? "<span class='required'>*</span> " : ""; ?> :
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
			
			
			<div id='report-error' class='report-div error'></div>
			<div id='report-success' class='report-div success'></div>							
		</div>	
		<div class="pDiv">
			<div class='form-button-box'>
				<input type='submit' value='<?php echo $this->l('form_save'); ?>'/>
			</div>
<?php 	if(!$this->unset_back_to_list) { ?>				
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_save_and_go_back'); ?>' id="save-and-go-back-button"/>
			</div>					
			<div class='form-button-box'>
				<input type='button' value='<?php echo $this->l('form_cancel'); ?>' onclick="javascript: goToList()" />
			</div>
<?php 	} ?>						
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
</script>