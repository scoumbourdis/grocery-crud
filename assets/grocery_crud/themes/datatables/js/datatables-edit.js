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
								alert('An error occured on Saving');
							}
						}
						/* ,error:function (xhr, ajaxOptions, thrownError){
		                    alert(xhr.status);
		                    alert(thrownError);
		                }  
		                */  
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
	if( confirm('The data you have entered may not be saved. Are you sure you want to go back to list?') )
	{
		window.location = list_url;
	}

	return false;	
}