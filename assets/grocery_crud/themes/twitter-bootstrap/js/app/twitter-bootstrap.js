/**
 * Arquivo com as configurações iniciais do Grocery CRUD
 *
 * - list_template.php
 * - list.php
 */
$(function(){

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

	var call_fancybox = function(){}
	if($('.image-thumbnail')[0]){
		var call_fancybox = function(){
			$('.image-thumbnail').fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'elastic',
				'speedIn'		:	600,
				'speedOut'		:	200,
				'overlayShow'	:	false
			});
		};
	}

	call_fancybox();

	/**
	 * Retornando a busca feita na aplicacao
	 * @param  int crud_page paginacao inicial
	 * @param  int last_page paginacao final
	 * @return false
	 */
	$('#filtering_form').submit(function(){

		var crud_page =  parseInt($('#tb_crud_page').val()),
			last_page = parseInt($('#last-page-number').html()),
			this_form = $(this);

		$('#ajax-loading').removeClass('hide');

		$('.first-button, .last-button').removeClass('disabled');
		if(crud_page > last_page){
			$('#tb_crud_page').val(last_page);
		}else if(crud_page <= 1){
			$('#tb_crud_page').val('1');
			$('.first-button').addClass('disabled');
		}else if(crud_page >= last_page){
			$('.last-button').addClass('disabled');
		}

		// Inserindo valores da quantidade de registros e pagina atual no formulario
		$('input[name="per_page"]').val($('#tb_per_page').val());
		$('input[name="page"]').val($('#tb_crud_page').val());

		$(this).ajaxSubmit({
			 url: ajax_list_info_url,
			 dataType: 'json',
			 success:    function(data){
				$('#total_items').html( data.total_results);
				displaying_and_pages();

				this_form.ajaxSubmit({
					 success:    function(data){
						$('#ajax_list').html(data);
						call_fancybox();
					 }
				});
			 }
		});
		//	Criando os cookies para a paginacao
		createCookie('crud_page_'+unique_hash, crud_page, 1);
		createCookie('per_page_'+unique_hash, $('#per_page').val(), 1);
		createCookie('hidden_ordering_'+unique_hash, $('#hidden-ordering').val(), 1);
		createCookie('hidden_sorting_'+unique_hash, $('#hidden-sorting').val(), 1);
		createCookie('search_text_'+unique_hash, $('#search_text').val(), 1);
		createCookie('search_field_'+unique_hash, $('#search_field').val(), 1);

		$('#ajax-loading').addClass('hide');
		return false;
	});

	//	Submete a busca com as informacoes a serem buscadas
	$('#crud_search').click(function(){
		$('#crud_page').val('1');
		$('#filtering_form').trigger('submit');
	});

	//	Limpa o formulario de busca E Submete  o formulario de busca vazio para que retorne a listagem original
	$('#search_clear').click(function(){
		$('#crud_page').val('1');
		$('#search_text').val('');
		$('#filtering_form').trigger('submit');
	});

	//	Verifica o modificador de quantidade de registros por paginação
	$('#tb_per_page').change(function(){
		$('#tb_crud_page').val('1');
		$('#filtering_form').trigger('submit');
	});

	//	Insere a imagem de Loading ajax
	$('#filtering_form').ajaxStart(function(){
		$('#ajax-loading').removeClass('hide');
	});
	//	Remove a imagem de loading ajax
	$('#filtering_form').ajaxStop(function(){
		$('#ajax-loading').addClass('hide');
	});
	//	Submete a busca
	$('#ajax-loading').click(function(){
		$('#filtering_form').trigger('submit');
	});
	//	Insere a visualização a partir do primeiro índice da paginação
	$('.first-button').click(function(){
		$('#tb_crud_page').val('1');
		$('#filtering_form').trigger('submit');
	});
	//	Insere a visualização a partir do índice anterior da paginação
	$('.prev-button').click(function(){
		if( $('#tb_crud_page').val() != "1")
		{
			$('#tb_crud_page').val( parseInt($('#tb_crud_page').val()) - 1 );
			$('#tb_crud_page').trigger('change');
		}
	});
	//	Insere a visualização a partir do último índice da paginação
	$('.last-button').click(function(){
		$('#tb_crud_page').val( $('#last-page-number').html());
		$('#filtering_form').trigger('submit');
	});

	//	Insere a visualização a partir do próximo índice da paginação
	$('.next-button').click(function(){
		$('#tb_crud_page').val( parseInt($('#tb_crud_page').val()) + 1 );
		$('#tb_crud_page').trigger('change');
	});

	//	Submete a busca caso o valor da paginação seja modificada manualmente
	$('#tb_crud_page').change(function(){
		$('#filtering_form').trigger('submit');
	});

	//	Modifica a ordenação da tabela com base no nome do campo no banco de dados que está no REL da classe
	//	".field-sorting" inserida no th da tabela
	$('.field-sorting').live('click', function(){
		$('#hidden-sorting').val($(this).attr('rel'));

		if($(this).hasClass('asc'))
			$('#hidden-ordering').val('desc');
		else
			$('#hidden-ordering').val('asc');

		$('#crud_page').val('1');
		$('#filtering_form').trigger('submit');
	});

	//	Chama o método para excluir o registro de informação do BD
	$('.delete-row').live('click', function(){
		var delete_url = $(this).attr('href');

		if( confirm( message_alert_delete ) )
		{
			$('#ajax-loading').removeClass('hide');
			$.ajax({
				url: delete_url,
				dataType: 'json',
				success: function(data)
				{
					if(data.success)
					{
						$('#filtering_form').trigger('submit');
						alert_message('sucess', data.success_message);
					}
					else
					{
						alert_message('sucess', data.error_message);
					}
				}
			});
			$('#ajax-loading').addClass('hide');
		}

		return false;
	});

	//	Exporta as importações da tabela para um arquivo .CSV
	$('.export-anchor').click(function(){
		var export_url = $(this).attr('data-url');

		var form_input_html = '';
		$.each($('#filtering_form').serializeArray(), function(i, field) {
		    form_input_html = form_input_html + '<input type="hidden" name="'+field.name+'" value="'+field.value+'">';
		});

		var form_on_demand = $("<form/>").attr("id","export_form").attr("method","post").attr("target","_blank")
								.attr("action",export_url).html(form_input_html);

		$('#hidden-operations').html(form_on_demand);

		$('#export_form').submit();
	});

	//	Imprime a visualização atual da tabela
	$('.print-anchor').click(function(){
		printTable($(this), $('#filtering_form').serializeArray());
	});
	$('#crud_page').numeric();

	var cookie_crud_page = readCookie('crud_page_'+unique_hash);
	var cookie_per_page  = readCookie('per_page_'+unique_hash);
	var hidden_ordering  = readCookie('hidden_ordering_'+unique_hash);
	var hidden_sorting  = readCookie('hidden_sorting_'+unique_hash);
	var cookie_search_text  = readCookie('search_text_'+unique_hash);
	var cookie_search_field  = readCookie('search_field_'+unique_hash);

	if(cookie_crud_page !== null && cookie_per_page !== null)
	{
		$('#crud_page').val(cookie_crud_page);
		$('#per_page').val(cookie_per_page);
		$('#hidden-ordering').val(hidden_ordering);
		$('#hidden-sorting').val(hidden_sorting);
		$('#search_text').val(cookie_search_text);
		$('#search_field').val(cookie_search_field);

		if(cookie_search_text !== '')
			$('#quickSearchButton').trigger('click');

		$('#filtering_form').trigger('submit');
	}

});

/**
 * Print the table visualization's
 * @param  obj class_name objeto de click
 * @param  obj filtering_form Classe serializada para verificação
 * @return void
 */
function printTable(class_name, filtering_form){
	var print_url = class_name.attr('data-url');

	var form_input_html = '';
	$.each( filtering_form, function(i, field) {
	    form_input_html += '<input type="hidden" name="'+field.name+'" value="'+field.value+'">';
	});

	var form_on_demand = $("<form/>").attr("id", "print_form").attr("method", "post").attr("action", print_url).html(form_input_html);
	$('#hidden-operations').html(form_on_demand);

	var _this_button = $(this);

	$('#print_form').ajaxSubmit({
		beforeSend: function(){
			class_name.find('.fbutton').addClass('loading');
			class_name.find('.fbutton>div').css('opacity','0.4');
		},
		complete: function(){
			class_name.find('.fbutton').removeClass('loading');
			class_name.find('.fbutton>div').css('opacity','1');
		},
		success: function(html_data){
			$("<div/>").html(html_data).printElement();
		}
	});

	return;
}

/**
 * Mostra a visualização e paginação da tabela
 * @return void
 */
function displaying_and_pages()
{
	if($('#crud_page').val() == 0)
		$('#crud_page').val('1');

	var crud_page 		= parseInt( $('#crud_page').val()),
		per_page	 	= parseInt( $('#per_page').val() ),
		total_items 	= parseInt( $('#total_items').html() )
	;

	$('#last-page-number').html( Math.ceil( total_items / per_page) );

	if(total_items == 0)
		$('#page-starts-from').html( '0');
	else
		$('#page-starts-from').html( (crud_page - 1)*per_page + 1 );

	if(crud_page*per_page > total_items)
		$('#page-ends-to').html( total_items );
	else
		$('#page-ends-to').html( crud_page*per_page );
}