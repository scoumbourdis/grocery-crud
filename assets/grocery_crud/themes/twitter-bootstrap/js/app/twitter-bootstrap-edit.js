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

							$("#ajax-loading").fadeOut("slow");
							data = $.parseJSON( result );
							if(data.success)
							{
								if(save_and_close)
								{
									window.location = data.success_list_url;
									return true;
								}
								alert_message('sucess', data.success_message);
							}
							else
							{
								alert_message('error', message_update_error);
							}
						},
						error: function(){
							alert_message('error', message_update_error);
						}
					});
				}
				else
				{
					$('.field_error').each(function(){
						$(this).removeClass('field_error');
					});

					alert_message('error', data.error_message);

					$.each(data.error_fields, function(index,value){
						$('input[name='+index+']').addClass('field_error');
					});
				}
			},
			error: function(){
				$("#ajax-loading").fadeOut('fast');
				alert_message('error', message_update_error);
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
	$("#ajax-loading").addClass('hide');
	window.setTimeout( function(){
        $('.alert-'+type_message).slideUp();
    }, 7000);
	return false;
};

//	Retornar para a tabela de listagem de dados inicial
function goToList()
{
	if( confirm( message_alert_edit_form ) )
	{
		window.location = list_url;
	}

	return false;
}