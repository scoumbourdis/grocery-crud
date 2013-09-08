$(function(){
		
		$('.ptogtitle').click(function(){
			if($(this).hasClass('vsble'))
			{
				$(this).removeClass('vsble');
				$('#main-table-box #crudForm_' + table_name).slideDown("slow");
			}
			else
			{
				$(this).addClass('vsble');
				$('#main-table-box #crudForm_' + table_name).slideUp("slow");
			}
		});

		var save_and_close = false;
		
		$('#'+table_name+'_form-button-save').click(function(){
			$('#crudForm_' + table_name).trigger('submit');
		});

		$('#'+table_name+'_save-and-go-back-button').click(function(){
			save_and_close = true;
			$('#crudForm_' + table_name).trigger('submit');
		});

		$('#crudForm_' + table_name).submit(function(e){
			e.preventDefault();
			var my_crud_form = $(this);

			$(this).ajaxSubmit({
				url: validation_url,
				dataType: 'json',
				cache: 'false',
				beforeSend: function(){
					$("#FormLoading").show();
				},
				success: function(data){
					$("#FormLoading").hide();
					if(data.success)
					{
						$('#crudForm_' + table_name).ajaxSubmit({
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
									var data_unique_hash = my_crud_form.closest(".flexigrid").attr("data-unique-hash");
																	
									$('.flexigrid[data-unique-hash='+data_unique_hash+']').find('.ajax_refresh_and_loading').trigger('click');

									if(save_and_close)
									{
										if ($('#'+table_name+'_save-and-go-back-button').closest('.ui-dialog').length === 0) {
											window.location = data.success_list_url;
										} else {
											$(".ui-dialog-content").dialog("close");
											success_message(data.success_message);	
										}
										return true;
									}

									$('.field_error').each(function(){
										$(this).removeClass('field_error');
									});
									//clearForm();
									form_success_message_table(data.success_message,table_name);
								}
								else
								{
									alert( message_insert_error );
								}
							},
							error: function(){
								alert( message_insert_error );
								$("#FormLoading").hide();
							}
						});
					}
					else
					{
						$('.field_error').removeClass('field_error');
						form_error_message_table(data.error_message,table_name);
						$.each(data.error_fields, function(index,value){
							$('input[id=field-'+ table_name + '-' +index+']').addClass('field_error');
						});

					}
				},
				error: function(){
					error_message (message_insert_error);
					$("#FormLoading").hide();
				}
			});
			return false;
		});

		if( $('#'+table_name+'_cancel-button').closest('.ui-dialog').length === 0 ) {

			$('#'+table_name+'_cancel-button').click(function(){
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
		$('#crudForm_' + table_name).find(':input').each(function() {
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
			$(this).trigger("liszt:updated");
		});
	}
