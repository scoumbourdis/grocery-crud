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

$this->set_js_config($this->default_theme_path.'/twitter-bootstrap/js/app/twitter-bootstrap-add.js');
//	JAVASCRIPTS - JQUERY-FUNCTIONS
$this->set_js($this->default_theme_path.'/twitter-bootstrap/js/jquery.functions.js');
?>

<div class="twitter-bootstrap crud-form">

	<h2 class="span12"><?php echo $this->l('form_add'); ?> <?php echo $subject?></h2>

	<!-- CONTENT FOR ALERT MESSAGES -->
	<div id="message-box" class="span12"></div>
	<div id="main-table-box span12">
		<?php
		echo form_open( $insert_url, 'method="post" id="crudForm" class="form-div span12" autocomplete="off" enctype="multipart/form-data"');
			foreach($fields as $field)
			{
				?>
				<div class="form-field-box" id="<?php echo $field->field_name; ?>_field_box">
					<div class="form-display-as-box" id="<?php echo $field->field_name; ?>_display_as_box">
						<?php echo $input_fields[$field->field_name]->display_as; ?><?php echo ($input_fields[$field->field_name]->required)? '<span class="required">*</span>' : ""; ?> :
					</div>
					<div class="form-input-box control-group" id="<?php echo $field->field_name; ?>_input_box">
						<?php echo $input_fields[$field->field_name]->input?>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
			//	Hidden Elements
			foreach($hidden_fields as $hidden_field){
				echo $hidden_field->input;
			}
			?>

			<input type="button" value="<?php echo $this->l('form_save'); ?>"  class="btn btn-large btn-primary submit-form"/>
			<?php 	if(!$this->unset_back_to_list) { ?>
				<input type="button" value="<?php echo $this->l('form_save_and_go_back'); ?>" id="save-and-go-back-button"  class="btn btn-large btn-primary"/>
				<input type="button" value="<?php echo $this->l('form_cancel'); ?>" class="btn btn-large return-to-list" />
			<?php 	} ?>

			<div class="hide loading" id="ajax-loading"><?php echo $this->l('form_update_loading'); ?></div>
		<?php echo form_close(); ?>
	</div>
</div>
<script>
	var validation_url = "<?php echo $validation_url?>",
		list_url = "<?php echo $list_url?>",
		message_alert_add_form = "<?php echo $this->l('alert_add_form')?>",
		message_insert_error = "<?php echo $this->l('insert_error')?>";
</script>