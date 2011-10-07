<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	grocery_CRUD::set_css('assets/grocery_crud/themes/datatables/css/demo_table_jui.css');
	grocery_CRUD::set_css('assets/grocery_crud/themes/datatables/css/ui/simple/jquery-ui-1.8.10.custom.css');
	grocery_CRUD::set_css('assets/grocery_crud/themes/datatables/css/datatables.css');	
	grocery_CRUD::set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/datatables/js/jquery-ui-1.8.10.custom.min.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/datatables/js/jquery.dataTables.min.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/datatables/js/datatables.js');
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
	var subject = '<?php echo $subject?>';
</script>
<?php 
	if(!empty($actions)){
?>
	<style type="text/css">
		<?php foreach($actions as $action_unique_id => $action){?>
			<?php if(!empty($action->image_url)){ ?>
				.<?php echo $action_unique_id; ?>{ 
					background: url('<?php echo $action->image_url; ?>') !important;
				}
			<?php }?>
		<?php }?>
	</style>		
<?php 
	}
?>
<div id='report-error' class='report-div error report-list'></div>
<div id='report-success' class='report-div success report-list'></div>	
<?php if(!$unset_add){?>
<a role="button" class="edit_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" href="<?php echo $add_url?>">
	<span class="ui-button-icon-primary ui-icon ui-icon-circle-plus"></span>
	<span class="ui-button-text">Add <?php echo $subject?></span>
</a>
<?php }?>
<div style='height:10px;'></div>
<?php echo $list_view?>