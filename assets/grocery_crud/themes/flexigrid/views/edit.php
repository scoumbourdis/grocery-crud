<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	grocery_CRUD::set_css('assets/grocery_crud/themes/flexigrid/css/flexigrid.css');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/jquery.form.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/flexigrid-edit.js');
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
</script>
<div class="flexigrid" style='width: 100%;'>
	<div class="mDiv">
		<div class="ftitle">
			<div class='ftitle-left'>
				Edit <?php echo $subject?>
			</div>
			<div class='ftitle-right'>
				<a href='<?php echo $list_url?>' onclick='javascript: return goToList()'>Back to list</a>
			</div>
			<div class='clear'></div>				
		</div>
		<div title="Minimize/Maximize Table" class="ptogtitle">
			<span></span>
		</div>	
	</div>
<div id='main-table-box'>	
	<form action='<?php echo $update_url?>' method='post' id='crudForm' autocomplete='off' enctype="multipart/form-data">
	<div class='form-div'>
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
		<?php if(!empty($hidden_fields)){?>
		<!-- Start of hidden inputs -->
			<?php 
				foreach($hidden_fields as $hidden_field){
					echo $hidden_field->input;
				}
			?>
		<!-- End of hidden inputs -->
		<?php }?>		
		<div id='report-error' class='report-div error'></div>
		<div id='report-success' class='report-div success'></div>		
	</div>
	<div class="pDiv">
		<div class='form-button-box'>
			<input type='submit' value='Update Changes' />
		</div>		
		<div class='form-button-box'>
			<input type='button' value='Cancel' onclick='javascript: return goToList()' />
		</div>
		<div class='form-button-box'>
			<div class='small-loading' id='FormLoading'>Loading...</div>
		</div>		
		<div class='clear'></div>	
	</div>
	</form>	
</div>
</div>	
<script>
	var validation_url = '<?php echo $validation_url?>';
	var list_url = '<?php echo $list_url?>';
</script>