$(function(){
	$('.quickSearchButton').click(function(){
		$(this).closest('.flexigrid').find('.quickSearchBox').slideToggle('normal');
		
	});
	$('.ptogtitle').click(function(){
		if ($(this).hasClass('vsble')) {
			$(this).removeClass('vsble');
			$(this).closest('.flexigrid').find('.main-table-box').slideDown("slow");
		} else {
			$(this).addClass('vsble');
			$(this).closest('.flexigrid').find('.main-table-box').slideUp("slow");
		}
	});
	
	var call_fancybox = function(){
		$('.image-thumbnail').fancybox({
			'transitionIn'	:	'elastic',
			'transitionOut'	:	'elastic',
			'speedIn'		:	600, 
			'speedOut'		:	200, 
			'overlayShow'	:	false
		});		
	};
	
	call_fancybox();
	add_edit_button_listener();
	
	$('.filtering_form').submit(function(){		
		var crud_page =  parseInt($(this).closest('.flexigrid').find('.crud_page').val(), 10);
		var last_page = parseInt($('#last-page-number').html(), 10);
		
		if (crud_page > last_page) {
			$(this).closest('.flexigrid').find('.crud_page').val(last_page);
		}
		if (crud_page <= 0) {
			$(this).closest('.flexigrid').find('.crud_page').val('1');
		}
		
		var this_form = $(this);
		
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
						add_edit_button_listener();
					 }
				}); 
			 }
		});
		
		createCookie('crud_page_'+unique_hash,crud_page,1);
		createCookie('per_page_'+unique_hash,$('#per_page').val(),1);
		createCookie('hidden_ordering_'+unique_hash,$('#hidden-ordering').val(),1);
		createCookie('hidden_sorting_'+unique_hash,$('#hidden-sorting').val(),1);
		createCookie('search_text_'+unique_hash,$(this).closest('.flexigrid').find('.search_text').val(),1);
		createCookie('search_field_'+unique_hash,$('#search_field').val(),1);
		
		return false;
	});
	
	$('.crud_search').click(function(){
		$(this).closest('.flexigrid').find('.crud_page').val('1');
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('#search_clear').click(function(){
		$(this).closest('.flexigrid').find('.crud_page').val('1');
		$(this).closest('.flexigrid').find('.search_text').val('');
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('#per_page').change(function(){
		$(this).closest('.flexigrid').find('.crud_page').val('1');
		
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.filtering_form').ajaxStart(function(){
		$('#ajax_refresh_and_loading').addClass('loading');
	});
	
	$('.filtering_form').ajaxStop(function(){
		$('#ajax_refresh_and_loading').removeClass('loading');
	});
	
	$('#ajax_refresh_and_loading').click(function(){
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.first-button').click(function(){
		$(this).closest('.flexigrid').find('.crud_page').val('1');
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.prev-button').click(function(){
		if( $(this).closest('.flexigrid').find('.crud_page').val() != "1")
		{
			$(this).closest('.flexigrid').find('.crud_page').val( parseInt($(this).closest('.flexigrid').find('.crud_page').val()) - 1 );
			$(this).closest('.flexigrid').find('.crud_page').trigger('change');
		}
	});
	
	$('.last-button').click(function(){
		$(this).closest('.flexigrid').find('.crud_page').val( $('#last-page-number').html());
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.next-button').click(function(){
		$(this).closest('.flexigrid').find('.crud_page').val( parseInt($(this).closest('.flexigrid').find('.crud_page').val()) + 1 );
		$(this).closest('.flexigrid').find('.crud_page').trigger('change');
	});
	
	$('.crud_page').change(function(){
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.field-sorting').live('click', function(){
		$('#hidden-sorting').val($(this).attr('rel'));
		
		if($(this).hasClass('asc'))
			$('#hidden-ordering').val('desc');
		else
			$('#hidden-ordering').val('asc');
		
		$(this).closest('.flexigrid').find('.crud_page').val('1');
		$(this).closest('.flexigrid').find('.filtering_form').trigger('submit');
	});
	
	$('.delete-row').live('click', function(){
		var delete_url = $(this).attr('href');
		
		if( confirm( message_alert_delete ) )
		{
			$.ajax({
				url: delete_url,
				dataType: 'json',
				success: function(data)
				{					
					if(data.success)
					{
						$('#ajax_refresh_and_loading').trigger('click');
						
						success_message(data.success_message);
					}
					else
					{
						error_message(data.error_message);							
						
					}
				}
			});
		}
		
		return false;
	});
	
	$('.export-anchor').click(function(){
		var export_url = $(this).attr('data-url');
		
		var form_input_html = '';
		$.each($(this).closest('.flexigrid').find('.filtering_form').serializeArray(), function(i, field) {
		    form_input_html = form_input_html + '<input type="hidden" name="'+field.name+'" value="'+field.value+'">';
		});
		
		var form_on_demand = $("<form/>").attr("id","export_form").attr("method","post").attr("target","_blank")
								.attr("action",export_url).html(form_input_html);
		
		$('#hidden-operations').html(form_on_demand);
		
		$('#export_form').submit();
	});
	
	$('.print-anchor').click(function(){
		var print_url = $(this).attr('data-url');
		
		var form_input_html = '';
		$.each($(this).closest('.flexigrid').find('.filtering_form').serializeArray(), function(i, field) {
		    form_input_html = form_input_html + '<input type="hidden" name="'+field.name+'" value="'+field.value+'">';
		});
		
		var form_on_demand = $("<form/>").attr("id","print_form").attr("method","post").attr("action",print_url).html(form_input_html);
		
		$('#hidden-operations').html(form_on_demand);
		
		var _this_button = $(this);
		
		$('#print_form').ajaxSubmit({
			beforeSend: function(){
				_this_button.find('.fbutton').addClass('loading');
				_this_button.find('.fbutton>div').css('opacity','0.4');
			},
			complete: function(){
				_this_button.find('.fbutton').removeClass('loading');
				_this_button.find('.fbutton>div').css('opacity','1');
			},
			success: function(html_data){
				$("<div/>").html(html_data).printElement();
			}
		});
	});	
	
	$('.crud_page').numeric();
	
	$('.flexigrid').each(function(){
		
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
	
});

function displaying_and_pages()
{
	if ($('#crud_page').val() == 0) {
		$('#crud_page').val('1');	
	}
	
	var crud_page 		= parseInt( $('#crud_page').val()) ;
	var per_page	 	= parseInt( $('#per_page').val() );
	var total_items 	= parseInt( $('#total_items').html() );
	
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