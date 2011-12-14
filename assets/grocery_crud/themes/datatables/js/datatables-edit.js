$(function(){
	$("#FormLoading").ajaxStart(function(){
		   $(this).show();
	});
	$("#FormLoading").ajaxStop(function(){
		   $(this).fadeOut('slow');
	});	
	
	$('#crudForm').submit(function(){		
		$(this).ajaxSubmit({
			url: validation_url,
			dataType: 'json',
			success: function(data){				
				if(data.success)
				{					
					$('#crudForm').ajaxSubmit({
						dataType: 'text',
						cache: 'false',
						success: function(result){
							data = $.parseJSON( result );
							if(data.success)
							{	
								$('#report-error').hide().html('');									
								$('.field_error').each(function(){
									$(this).removeClass('field_error');
								});									
								
								$('#report-success').html(data.success_message);
								$('#report-success').slideDown('slow');
							}
							else
							{
								alert(message_update_error);
							}
						},
						error: function(){
								alert( message_update_error );
						}
					});
				}
				else
				{
					$('.field_error').each(function(){
						$(this).removeClass('field_error');
					});
					$('#report-error').slideUp('fast');
					$('#report-error').html(data.error_message);
					$.each(data.error_fields, function(index,value){
						$('input[name='+index+']').addClass('field_error');
					});
							
					$('#report-error').slideDown('normal');
					$('#report-success').slideUp('fast').html('');
					
				}
			}
		});
		return false;
	});
	
	$('.ui-input-button').button();
	$('.gotoListButton').button({
        icons: {
        	primary: "ui-icon-triangle-1-w"
    	}
	});
	
});	

function goToList()
{
	if( confirm( message_alert_edit_form ) )
	{
		window.location = list_url;
	}

	return false;	
}