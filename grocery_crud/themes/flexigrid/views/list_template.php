<?php  
	if (!defined('BASEPATH')) exit('No direct script access allowed');

	$this->set_css($this->default_theme_path.'/flexigrid/css/flexigrid.css');
	$this->set_js($this->default_javascript_path.'/jquery-1.7.1.min.js');
	$this->set_js($this->default_theme_path.'/flexigrid/js/cookies.js');
	$this->set_js($this->default_theme_path.'/flexigrid/js/flexigrid.js');
	$this->set_js($this->default_theme_path.'/flexigrid/js/jquery.form.js');
	$this->set_js($this->default_theme_path.'/flexigrid/js/jquery.numeric.js');
	
?>
<script type='text/javascript'>
	var base_url = '<?php echo base_url();?>';

	var subject = '<?php echo $subject?>';
	var ajax_list_info_url = '<?php echo $ajax_list_info_url?>';
	var unique_hash = '<?php echo $unique_hash; ?>';

	var message_alert_delete = "<?php echo $this->l('alert_delete'); ?>";

</script>
<div id='report-error' class='report-div error'></div>
<div id='report-success' class='report-div success report-list' <?php if($success_message !== null){?>style="display:block"<?php }?>>
<?php if($success_message !== null){?>
	<p><?php echo $success_message; ?></p>
<?php }?>
</div>	
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
		<div class="tDiv3">
        	<a class="export-anchor">
				<div class="fbutton">
					<div>
						<span class="export">Export</span>
					</div>
				</div>
            </a>
			<div class="btnseparator"></div>
        	<a class="print-anchor">
				<div class="fbutton">
					<div>
						<span class="print">Print</span>
					</div>
				</div>
            </a>
			<div class="btnseparator"></div>						
		</div>
		<div class='clear'></div>
	</div>
	<?php }?>
	<div id='ajax_list'>
		<?php echo $list_view?>
	</div>
	<?php echo form_open( $ajax_list_url, 'method="post" id="filtering_form" autocomplete = "off"'); ?>	
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
					<?php foreach($paging_options as $option){?>
						<option value="<?php echo $option; ?>" <?php if($option == $default_per_page){?>selected="selected"<?php }?>><?php echo $option; ?>&nbsp;&nbsp;</option>
					<?php }?>
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
				<span id='last-page-number'><?php echo ceil($total_results / $default_per_page)?></span></span>
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
					<?php $paging_ends_to = "<span id='page-ends-to'>". ($total_results < $default_per_page ? $total_results : $default_per_page) ."</span>"; ?>
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
	<?php echo form_close(); ?>
	</div>
</div>
