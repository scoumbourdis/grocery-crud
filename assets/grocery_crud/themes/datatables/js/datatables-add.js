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

		var save_and_close = false;
		var reload_datagrid = function () {
			$('.refresh-data').trigger('click');
		};

		$('#save-and-go-back-button').click(function(){
			save_and_close = true;

			$('#crudForm').trigger('submit');
		});

		$('#crudForm').submit(function(){
			$(this).ajaxSubmit({
				url: validation_url,
				dataType: 'json',
				beforeSend: function(){
					$("#FormLoading").show();
				},
				success: function(data){
					$("#FormLoading").hide();
					if(data.success)
					{
						$('#crudForm').ajaxSubmit({
							dataType: 'text',
							cache: 'false',
							beforeSend: function(){
								$("#FormLoading").show();
							},
							success: function(result){
								$("#FormLoading").fadeOut("slow");
								data = $.parseJSON( result );
								if(data.success)
								{
									if(save_and_close)
									{
										if ($('#save-and-go-back-button').closest('.ui-dialog').length === 0) {
											window.location = data.success_list_url;
										} else {
											$(".ui-dialog-content").dialog("close");
											success_message(data.success_message);
											reload_datagrid();
										}

										return true;
									}

									$('.field_error').removeClass('field_error');

									form_success_message(data.success_message);
									reload_datagrid();

									clearForm();
								}
								else
								{
									form_error_message('An error has been occured at the insert.');
								}
							}
						});
					}
					else
					{
						$('.field_error').removeClass('field_error');
						form_error_message(data.error_message);
						$.each(data.error_fields, function(index,value){
							$('#crudForm input[name='+index+']').addClass('field_error');
						});

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

		if( $('#cancel-button').closest('.ui-dialog').length === 0 ) {

			$('#cancel-button').click(function(){
				if( confirm( message_alert_add_form ) )
				{
					window.location = list_url;
				}

				return false;
			});

		}
	});

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

		/* Clear upload inputs  */
		$('.open-file,.gc-file-upload,.hidden-upload-input').each(function(){
			$(this).val('');
		});

		$('.upload-success-url').hide();
		$('.fileinput-button').fadeIn("normal");
		/* -------------------- */

		$('.remove-all').each(function(){
			$(this).trigger('click');
		});

		$('.chosen-multiple-select, .chosen-select, .ajax-chosen-select').each(function(){
			$(this).trigger("chosen:updated");
		});
	}