<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	grocery_CRUD::set_css('assets/grocery_crud/themes/flexigrid/css/flexigrid.css');
	grocery_CRUD::set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/cookies.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/flexigrid.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/jquery.form.js');
	grocery_CRUD::set_js('assets/grocery_crud/themes/flexigrid/js/jquery.numeric.js');
	
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';

	var subject = '<?php echo $subject?>';
	var ajax_list_info_url = '<?php echo $ajax_list_info_url?>';
	var unique_hash = '<?php echo $unique_hash; ?>';
</script>
<div id='report-error' class='report-div error'></div>
<div id='report-success' class='report-div success'></div>	
<div class="flexigrid" style='width: 100%;'>
	<div class="mDiv">
		<div class="ftitle">
			<?php echo $subject_plural?>
		</div>
		<div title="Minimize/Maximize Table" class="ptogtitle">
			<span></span>
		</div>
	</div>
	<div id='main-table-box'>
	<?php if(!$unset_add){?>
	<div class="tDiv">
		<div class="tDiv2">
        	<a href='<?php echo $add_url?>' title='Add <?php echo $subject?>' class='add-anchor'>
			<div class="fbutton">
				<div>
					<span class="add">Add</span>
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
			Search <input type="text" class="qsbsearch_fieldox" name="search_text" size="30" id='search_text'>
			<select name="search_field">
				<option value="">All</option>
				<?php foreach($columns as $column){?>
				<option value="<?php echo $column->field_name?>"><?php echo $column->display_as?>&nbsp;&nbsp;</option>
				<?php }?>
			</select>
            <input type="button" value="Search" id='crud_search'> 
		</div>
        <div class='search-div-clear-button'>
        	<input type="button" value="Clear" id='search_clear'>
        </div>
	</div>
	<div class="pDiv">
		<div class="pDiv2">
			<div class="pGroup">
				<div class="pSearch pButton" id='quickSearchButton'>
					<span></span>
				</div>
			</div>
			<div class="btnseparator">
			</div>
			<div class="pGroup">
				<select name="per_page" id='per_page'>
					<option value="10">10&nbsp;&nbsp;</option>
					<option selected="selected" value="25">25&nbsp;&nbsp;</option>
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
				<span class="pcontrol">Page <input name='page' type="text" value="1" size="4" id='crud_page'> of <span id='last-page-number'><?php echo ceil($total_results / 25)?></span></span>
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
				<span class="pPageStat">Displaying <span id='page-starts-from'>1</span> to 
				<span id='page-ends-to'><?php echo $total_results < 25 ? $total_results : 25?></span> of 
				<span id='total_items'><?php echo $total_results?></span> items</span>
			</div>
		</div>
		<div style="clear: both;">
		</div>
	</div>
	</form>
	</div>
</div>
