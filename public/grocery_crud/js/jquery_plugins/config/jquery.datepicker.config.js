$(function(){
	$('.datepicker-input').datepicker({
			dateFormat: 'yy-mm-dd',
			showButtonPanel: true,
			changeMonth: true,
			changeYear: true
	});
	$('.datepicker-input-clear').button();
	
	$('.datepicker-input-clear').click(function(){
		$(this).parent().find('.datepicker-input').val("");
		return false;
	});
});