<?php
	$this->set_css($this->default_theme_path.'/twitter-bootstrap/css/bootstrap.min.css');
	$this->set_css($this->default_theme_path.'/twitter-bootstrap/css/bootstrap-responsive.min.css');
	$this->set_css($this->default_theme_path.'/twitter-bootstrap/css/style.css');
	
	$this->set_css($this->default_theme_path.'/twitter-bootstrap/css/jquery-ui/flick/jquery-ui-1.9.2.custom.css');
	
	$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
	
	//	JAVASCRIPTS - JQUERY-UI
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/jquery-ui/jquery-ui-1.9.2.custom.js');
	//	JAVASCRIPTS - JQUERY LAZY-LOAD
	$this->set_js_lib($this->default_javascript_path.'/common/lazyload-min.js');
	
	if (!$this->is_IE7()) {
		$this->set_js_lib($this->default_javascript_path.'/common/list.js');
	}
	//	JAVASCRIPTS - TWITTER BOOTSTRAP
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap.min.js');
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/application.js');
	//	JAVASCRIPTS - MODERNIZR
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/modernizr/modernizr-2.6.1.custom.js');
	//	JAVASCRIPTS - TABLESORTER
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/tablesorter/jquery.tablesorter.min.js');
	//	JAVASCRIPTS - JQUERY-COOKIE
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/cookies.js');
	//	JAVASCRIPTS - JQUERY-FORM
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/jquery.form.js');
	//	JAVASCRIPTS - JQUERY-NUMERIC
	$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.numeric.min.js');
	//	JAVASCRIPTS - JQUERY-PRINT-ELEMENT
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/print-element/jquery.printElement.min.js');
	//	JAVASCRIPTS - JQUERY FANCYBOX
	$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.fancybox-1.3.4.js');
	//	JAVASCRIPTS - JQUERY EASING
	$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.easing-1.3.pack.js');
	
	//	JAVASCRIPTS - twitter-bootstrap - CONFIGURAÇÕES
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/app/twitter-bootstrap.js');
	//	JAVASCRIPTS - JQUERY-FUNCTIONS
	$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/jquery.functions.js');
?>

<script type="text/javascript">
	var base_url = "<?php echo base_url();?>",
		subject = "<?php echo $subject?>",
		ajax_list_info_url = "<?php echo $ajax_list_info_url?>",
		unique_hash = "<?php echo $unique_hash; ?>",
		message_alert_delete = "<?php echo $this->l('alert_delete'); ?>";
</script>

<!-- UTILIZADO PARA IMPRESSÃO DA LISTAGEM -->
<div id="hidden-operations"></div>

<div class="twitter-bootstrap">
	<div id="main-table-box">
		<br/>
		<div id="options-content" class="span12">
			<?php
			if(!$unset_add || !$unset_export || !$unset_print){?>
				<?php if(!$unset_add){?>
					<a href="<?php echo $add_url?>" title="<?php echo $this->l('list_add'); ?> <?php echo $subject?>" class="add-anchor btn">
						<i class="icon-plus"></i>
						<?php echo $this->l('list_add'); ?> <?php echo $subject?>
					</a>
	 			<?php
	 			}
	 			if(!$unset_export) { ?>
		 			<a class="export-anchor btn" data-url="<?php echo $export_url; ?>" rel="external">
		 				<i class="icon-download"></i>
		 				<?php echo $this->l('list_export');?>
		 			</a>
	 			<?php
	 			}
	 			if(!$unset_print) { ?>
		 			<a class="print-anchor btn" data-url="<?php echo $print_url; ?>">
		 				<i class="icon-print"></i>
		 				<?php echo $this->l('list_print');?>
		 			</a>
	 			<?php
	 			}
	 		} ?>
 			<a class="btn" data-toggle="modal" href="#filtering-form-search" >
 				<i class="icon-search"></i>
 				<?php echo $this->l('list_search');?>
 			</a>
 		</div>
		<br/>

		<!-- CONTENT FOR ALERT MESSAGES -->
		<div id="message-box" class="span12">
			<div class="alert alert-sucess <?php echo ($success_message !== null) ? '' : 'hide'; ?>">
				<a class="close" data-dismiss="alert" href="#"> x </a>
				<?php echo ($success_message !== null) ? $success_message : ''; ?>
			</div>
		</div>

		<div id="ajax_list">
			<?php echo $list_view; ?>
		</div>

		<div class="pGroup span12">
			<select name="tb_per_page" id="tb_per_page">
				<?php foreach($paging_options as $option){?>
					<option value="<?php echo $option; ?>" <?php echo ($option == $default_per_page) ? 'selected="selected"' : ''; ?> ><?php echo $option; ?></option>
				<?php }?>
			</select>

			<span class="pPageStat">
				<?php
				$paging_starts_from = '<span id="page-starts-from">1</span>';
				$paging_ends_to = '<span id="page-ends-to">'. ($total_results < $default_per_page ? $total_results : $default_per_page) .'</span>';
				$paging_total_results = '<span id="total_items" class="badge badge-info">'.$total_results.'</span>';
				echo str_replace( array('{start}','{end}','{results}'), array($paging_starts_from, $paging_ends_to, $paging_total_results), $this->l('list_displaying')); ?>
			</span>

			<span class="pcontrol">
				<?php echo $this->l('list_page'); ?>
				<input name="tb_crud_page" type="text" value="1" size="4" id="tb_crud_page">
				<?php echo $this->l('list_paging_of'); ?>
				<span id="last-page-number"><?php echo ceil($total_results / $default_per_page); ?></span>
			</span>

			<div class="hide loading" id="ajax-loading"><?php echo $this->l('form_update_loading'); ?></div>

			<ul class="pager">
				<li class="previous first-button"><a href="javascript:void(0);">&laquo; <?php echo $this->l('list_paging_first'); ?></a></li>
				<li class="prev-button"><a href="javascript:void(0);">&laquo; <?php echo $this->l('list_paging_previous'); ?></a></li>
				<li class="next-button"><a href="javascript:void(0);"><?php echo $this->l('list_paging_next'); ?> &raquo;</a></li>
				<li class="next last-button"><a href="javascript:void(0);"><?php echo $this->l('list_paging_last'); ?> &raquo;</a></li>
			</ul>
		</div>
	</div>
</div>


<div class="modal hide" id="filtering-form-search">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">✕</button>
        <h3><?php echo $this->l('list_search') . ' ' . $subject; ?></h3>
    </div>
        <div class="modal-body" style="text-align:center;">
        <div class="row-fluid">
            <div class="span10 offset1">
                <div id="modalTab">
                    <div class="tab-content">
						<?php echo form_open( $ajax_list_url, 'method="post" id="filtering_form" autocomplete = "off"'); ?>
						<div class="sDiv" id="quickSearchBox">
							<div class="sDiv2">
								<input type="hidden" name="page" value="1" size="4" id="crud_page">
								<input type="hidden" name="per_page" id="per_page" value="<?php echo $default_per_page; ?>" />
								<input type="hidden" name="order_by[0]" id="hidden-sorting" value="<?php if(!empty($order_by[0])){?><?php echo $order_by[0]?><?php }?>" />
								<input type="hidden" name="order_by[1]" id="hidden-ordering"  value="<?php if(!empty($order_by[1])){?><?php echo $order_by[1]?><?php }?>"/>

								<?php echo $this->l('list_search');?>: <input type="text" class="qsbsearch_fieldox" name="search_text" size="30" id="search_text">
								<select name="search_field" id="search_field">
									<option value=""><?php echo $this->l('list_search_all');?></option>
									<?php foreach($columns as $column){?>
										<option value="<?php echo $column->field_name?>"><?php echo $column->display_as; ?></option>
									<?php }?>
								</select>

								<input type="button" class="btn btn-primary" data-dismiss="modal" value="<?php echo $this->l('list_search');?>" id="crud_search">
								<input type="button" class="btn btn-inverse" data-dismiss="modal" value="<?php echo $this->l('list_clear_filtering');?>" id="search_clear">
							</div>
						</div>
						<?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>