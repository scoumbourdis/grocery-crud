$(function(){
		$('.ptogtitle').click(function(){
			if($(this).hasClass('vsble'))
			{
				$(this).removeClass('vsble');
				$('#main-table-box').slideDown("slow");
			}
			else
			{
				$(this).addClass('vsble');
				$('#main-table-box').slideUp("slow");
			}
		});
		
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
									clearForm();
									$('#report-success').html(data.success_message);
									$('#report-success').slideDown('slow');
								}
								else
								{
									alert('An error has been occured at the insert.');
								}
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
		if( confirm( message_alert_add_form ) )
		{
			window.location = list_url;
		}

		return false;	
	}
	
	function clearForm()
	{
		$('#crudForm').find(':input').each(function() {
	        switch(this.type) {
	            case 'password':
	            case 'select-multiple':
	            case 'select-one':
	            case 'text':
	            case 'textarea':
	                $(this).val('');
	                break;
	            case 'checkbox':
	            case 'radio':
	                this.checked = false;
	        }
	    });
		
		$('.remove-all').each(function(){
			$(this).trigger('click');
		});
	}