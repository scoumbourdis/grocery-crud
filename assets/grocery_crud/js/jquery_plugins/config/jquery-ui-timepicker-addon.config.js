$(function(){
    $('.datetime-input').datetimepicker({
    	timeFormat: 'HH:mm:ss',
		dateFormat: js_date_format,
		showButtonPanel: true,
		changeMonth: true,
		changeYear: true
    });
    
	$('.datetime-input-clear').button();
	
	$('.datetime-input-clear').click(function(){
		$(this).parent().find('.datetime-input').val("");
		return false;
	});	

});