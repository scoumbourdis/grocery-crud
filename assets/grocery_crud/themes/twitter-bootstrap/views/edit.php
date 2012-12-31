<?php

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
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-transition.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-alert.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-modal.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-dropdown.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-scrollspy.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-tab.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-tooltip.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-popover.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-button.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-collapse.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-carousel.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-typeahead.js');
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/libs/bootstrap/bootstrap-affix.js');
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
$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.fancybox.pack.js');
//	JAVASCRIPTS - JQUERY EASING
$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.easing-1.3.pack.js');

//	JAVASCRIPTS - JQUERY UI
//$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);
//$this->load_js_jqueryui();

//	JAVASCRIPTS - twitter-bootstrap - CONFIGURAÇÕES
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/app/twitter-bootstrap-edit.js');
//	JAVASCRIPTS - JQUERY-FUNCTIONS
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/jquery.functions.js');
?>
<div class="twitter-bootstrap crud-form">

	<h2><?php echo $this->l('form_edit'); ?> <?php echo $subject?></h2>

	<!-- CONTENT FOR ALERT MESSAGES -->
	<div id="message-box"></div>
	<div id="main-table-box span12">
		<?php echo form_open( $update_url, 'method="post" id="crudForm" autocomplete="off" enctype="multipart/form-data"'); ?>
		<div class='form-div'>
			<?php
			$counter = 0;
			foreach($fields as $field)
			{
				$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
				$counter++;
				?>
				<div class="form-field-box <?php echo $even_odd?>" id="<?php echo $field->field_name; ?>_field_box">
					<div class="form-display-as-box" id="<?php echo $field->field_name; ?>_display_as_box">
						<?php echo $input_fields[$field->field_name]->display_as?><?php echo ($input_fields[$field->field_name]->required)? '<span class="required">*</span>' : ""?> :
					</div>
					<div class="form-input-box" id="<?php echo $field->field_name; ?>_input_box">
						<?php echo $input_fields[$field->field_name]->input?>
					</div>
					<div class="clear"></div>
				</div>
				<?php }
				//	Hidden Elements
				if(!empty($hidden_fields)){
					foreach($hidden_fields as $hidden_field){
						echo $hidden_field->input;
					}
				}?>
			</div>
			<div class="span12">
				<input type="submit" value="<?php echo $this->l('form_update_changes'); ?>" class="btn btn-large btn-primary"/>
				<?php 	if(!$this->unset_back_to_list) { ?>
					<input type="button" value="<?php echo $this->l('form_update_and_go_back'); ?>" id="save-and-go-back-button" class="btn btn-large btn-primary"/>
					<input type="button" value="<?php echo $this->l('form_cancel'); ?>" onclick="javascript: return goToList()" class="btn btn-large" />
				<?php 	} ?>

				<div class="hide loading" id="ajax-loading"><?php echo $this->l('form_update_loading'); ?></div>

			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
<script>
	var validation_url = "<?php echo $validation_url?>",
		list_url = "<?php echo $list_url?>",
		message_alert_edit_form = "<?php echo $this->l('alert_edit_form')?>",
		message_update_error = "<?php echo $this->l('update_error')?>";
</script>