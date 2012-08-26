$(function(){
    $('.datetime-input').datetimepicker({timeFormat: 'hh:mm:ss'});
    
	$('.datetime-input-clear').button();
	
	$('.datetime-input-clear').click(function(){
		$(this).parent().find('.datetime-input').val("");
		return false;
	});	

});