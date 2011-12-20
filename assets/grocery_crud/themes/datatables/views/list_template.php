<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	$this->set_css('assets/grocery_crud/themes/datatables/css/demo_table_jui.css');
	$this->set_css('assets/grocery_crud/themes/datatables/css/ui/simple/jquery-ui-1.8.10.custom.css');
	$this->set_css('assets/grocery_crud/themes/datatables/css/datatables.css');	
	$this->set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
	$this->set_js('assets/grocery_crud/themes/datatables/js/jquery-ui-1.8.10.custom.min.js');
	$this->set_js('assets/grocery_crud/themes/datatables/js/jquery.dataTables.min.js');
	$this->set_js('assets/grocery_crud/themes/datatables/js/datatables.js');
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';
	var subject = '<?php echo $subject?>';

	var displaying_paging_string = "<?php echo str_replace( array('{start}','{end}','{results}'),
		array('_START_', '_END_', '_TOTAL_'),
		$this->l('list_displaying')
	   ); ?>";
	var filtered_from_string 	= "<?php echo str_replace('{total_results}','_MAX_',$this->l('list_filtered_from') ); ?>";
	var show_entries_string 	= "<?php echo str_replace('{paging}','_MENU_',$this->l('list_show_entries') ); ?>";
	var search_string 			= "<?php echo $this->l('list_search'); ?>";
	var list_no_items 			= "<?php echo $this->l('list_no_items'); ?>";
	var list_zero_entries 			= "<?php echo $this->l('list_zero_entries'); ?>";

	var list_loading 			= "<?php echo $this->l('list_loading'); ?>";

	var paging_first 	= "<?php echo $this->l('list_paging_first'); ?>";
	var paging_previous = "<?php echo $this->l('list_paging_previous'); ?>";
	var paging_next 	= "<?php echo $this->l('list_paging_next'); ?>";
	var paging_last 	= "<?php echo $this->l('list_paging_last'); ?>";
	
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
	<span class="ui-button-text"><?php echo $this->l('list_add'); ?> <?php echo $subject?></span>
</a>
<?php }?>
<div style='height:10px;'></div>
<?php echo $list_view?>