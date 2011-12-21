<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	$this->set_css('assets/grocery_crud/themes/flexigrid/css/flexigrid.css');
	$this->set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
	$this->set_js('assets/grocery_crud/themes/flexigrid/js/cookies.js');
	$this->set_js('assets/grocery_crud/themes/flexigrid/js/flexigrid.js');
	$this->set_js('assets/grocery_crud/themes/flexigrid/js/jquery.form.js');
	$this->set_js('assets/grocery_crud/themes/flexigrid/js/jquery.numeric.js');
	
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';

	var subject = '<?php echo $subject?>';
	var ajax_list_info_url = '<?php echo $ajax_list_info_url?>';
	var unique_hash = '<?php echo $unique_hash; ?>';

	var message_alert_delete = "<?php echo $this->l('alert_delete'); ?>";
</script>
<div id='report-error' class='report-div error'></div>
<div id='report-success' class='report-div success'></div>	
<div class="flexigrid" style='width: 100%;'>
	<div class="mDiv">
		<div class="ftitle">
			&nbsp;
		</div>
		<div title="Minimize/Maximize Table" class="ptogtitle">
			<span></span>
		</div>
	</div>
	<div id='main-table-box'>
	<?php if(!$unset_add){?>
	<div class="tDiv">
		<div class="tDiv2">
        	<a href='<?php echo $add_url?>' title='<?php echo $this->l('list_add'); ?> <?php echo $subject?>' class='add-anchor'>
			<div class="fbutton">
				<div>
					<span class="add"><?php echo $this->l('list_add'); ?> <?php echo $subject?></span>
				</div>
			</div>
            </a>
			<div class="btnseparator">
			</div>
		</div>
		<div class='clear'></div>
	</div>
	<?php }?>
	<div id='ajax_list'>
		<?php echo $list_view?>
	</div>
	<form action='<?php echo $ajax_list_url?>' method='post' id='filtering_form' autocomplete = "off" >
	<div class="sDiv" id='quickSearchBox'>
		<div class="sDiv2">
			<?php echo $this->l('list_search');?>: <input type="text" class="qsbsearch_fieldox" name="search_text" size="30" id='search_text'>
			<select name="search_field" id="search_field">
				<option value=""><?php echo $this->l('list_search_all');?></option>
				<?php foreach($columns as $column){?>
				<option value="<?php echo $column->field_name?>"><?php echo $column->display_as?>&nbsp;&nbsp;</option>
				<?php }?>
			</select>
            <input type="button" value="<?php echo $this->l('list_search');?>" id='crud_search'> 
		</div>
        <div class='search-div-clear-button'>
        	<input type="button" value="<?php echo $this->l('list_clear_filtering');?>" id='search_clear'>
        </div>
	</div>
	<div class="pDiv">
		<div class="pDiv2">
			<div class="pGroup">
				<div class="pSearch pButton" id='quickSearchButton' title="<?php echo $this->l('list_search');?>">
					<span></span>
				</div>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<select name="per_page" id='per_page'>
					<option selected="selected" value="10">10&nbsp;&nbsp;</option>
					<option value="25">25&nbsp;&nbsp;</option>
					<option value="50">50&nbsp;&nbsp;</option>
					<option value="75">75&nbsp;&nbsp;</option>
					<option value="100">100&nbsp;&nbsp;</option>
				</select>
				<input type='hidden' name='order_by[0]' id='hidden-sorting' value='<?php if(!empty($order_by[0])){?><?php echo $order_by[0]?><?php }?>' />
				<input type='hidden' name='order_by[1]' id='hidden-ordering'  value='<?php if(!empty($order_by[1])){?><?php echo $order_by[1]?><?php }?>'/>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<div class="pFirst pButton first-button">
					<span></span>
				</div>
				<div class="pPrev pButton prev-button">
					<span></span>
				</div>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<span class="pcontrol"><?php echo $this->l('list_page'); ?> <input name='page' type="text" value="1" size="4" id='crud_page'> 
				<?php echo $this->l('list_paging_of'); ?> 
				<span id='last-page-number'><?php echo ceil($total_results / 25)?></span></span>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<div class="pNext pButton next-button" >
					<span></span>
				</div>
				<div class="pLast pButton last-button">
					<span></span>
				</div>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<div class="pReload pButton" id='ajax_refresh_and_loading'>
					<span></span>
				</div>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<span class="pPageStat">
					<?php $paging_starts_from = "<span id='page-starts-from'>1</span>"; ?>
					<?php $paging_ends_to = "<span id='page-ends-to'>". ($total_results < 10 ? $total_results : 10) ."</span>"; ?>
					<?php $paging_total_results = "<span id='total_items'>$total_results</span>"?>
					<?php echo str_replace( array('{start}','{end}','{results}'),
											array($paging_starts_from, $paging_ends_to, $paging_total_results),
											$this->l('list_displaying')
										   ); ?>   					
				</span>
			</div>
		</div>
		<div style="clear: both;">
		</div>
	</div>
	</form>
	</div>
</div>
