$(function(){

	var save_and_close = false;

	//	Salva as informações e retorna a listagem inicial
	$('#save-and-go-back-button').click(function(){
		save_and_close = true;
		$('#crudForm').trigger('submit');
	});

	//	Submete o formulário para inserir os dados no BD
	$('#crudForm').submit(function(){
		$(this).ajaxSubmit({
			url: validation_url,
			dataType: 'json',
			cache: 'false',
			beforeSend: function(){
				$("#ajax-loading").fadeIn('fast');
			},
			afterSend: function(){
				$("#ajax-loading").fadeOut('fast');
			},
			success: function(data){
				$("#ajax-loading").fadeOut('fast');
				if(data.success)
				{
					$('#crudForm').ajaxSubmit({
						dataType: 'text',
						cache: 'false',
						beforeSend: function(){
							$("#ajax-loading").addClass('show loading');
						},
						success: function(result){
							data = $.parseJSON( result );
							if(data.success)
							{
								if(save_and_close)
								{
									window.location = data.success_list_url;
									return true;
								}

								$('.form-input-box').each(function(){
									$(this).removeClass('error');
								});
								clearForm();
								alert_message('success', data.success_message);
							}
							else
							{
								alert_message('error', message_insert_error);
							}
						},
						error: function(){
							alert_message('error', message_insert_error);
						}
					});
				}
				else
				{
					$('.form-input-box').removeClass('error');
					alert_message('error', data.error_message);

					$.each(data.error_fields, function(index,value){
						$('input[name='+index+']').addClass('error');
					});
				}
			},
			error: function(){
				$("#ajax-loading").fadeOut('fast');
				alert_message('error', message_insert_error);
			}
		});
		return false;
	});
});

//	Mensagens para a aplicação
var alert_message = function(type_message, text_message){
	$('.alert-'+type_message).remove();
	$('#message-box').prepend('<div class="alert alert-'+type_message+' fade in"><a class="close" data-dismiss="alert" href="#"> x </a>'+text_message+'</div>');
	$('html, body').animate({
		scrollTop:0
	}, 600);
	window.setTimeout( function(){
        $('.alert-'+type_message).slideUp();
    }, 7000);
	$("#ajax-loading").addClass('hide');
	return false;
};

//	Retornar para a tabela de listagem de dados inicial
function goToList()
{
	if( confirm( message_alert_add_form ) )
		window.location = list_url;

	return false;
}
//	Simula o efeito RESET no formulário de inserção de conteudo
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
	$('.open-file, .gc-file-upload, .hidden-upload-input').each(function(){
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