function show_upload_button(unique_id, uploader_element)
{
	$('#upload-state-message-'+unique_id).html('');
	$("#loading-"+unique_id).hide();

	$('#upload-button-'+unique_id).slideDown('fast');
	$("input[rel="+uploader_element.attr('name')+"]").val('');
	$('#success_'+unique_id).slideUp('fast');	
}

function load_fancybox(elem)
{
	elem.fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
	});		
}

$(function(){
	$('.gc-file-upload').each(function(){
		var unique_id 	= $(this).attr('id');
		var uploader_url = $(this).attr('rel');
		var uploader_element = $(this);
		var delete_url 	= $('#delete_url_'+unique_id).attr('href');
		eval("var file_upload_info = upload_info_"+unique_id+"");
		
	    $(this).fileupload({
	        dataType: 'json',
	        url: uploader_url,
	        cache: false,
	        acceptFileTypes:  file_upload_info.accepted_file_types,
			beforeSend: function(){
	    		$('#upload-state-message-'+unique_id).html(string_upload_file);
				$("#loading-"+unique_id).show();
				$("#upload-button-"+unique_id).slideUp("fast");
			},
	        limitMultiFileUploads: 1,
	        maxFileSize: file_upload_info.max_file_size,			
			send: function (e, data) {						
				
				var errors = '';
				
			    if (data.files.length > 1) {
			    	errors += error_max_number_of_files + "\n" ;
			    }
				
	            $.each(data.files,function(index, file){
		            if (!(data.acceptFileTypes.test(file.type) || data.acceptFileTypes.test(file.name))) {
		            	errors += error_accept_file_types + "\n";
		            }
		            if (data.maxFileSize && file.size > data.maxFileSize) {
		            	errors +=  error_max_file_size + "\n";
		            }
		            if (typeof file.size === 'number' && file.size < data.minFileSize) {
		            	errors += error_min_file_size + "\n";
		            }			            	
	            });	
	            
	            if(errors != '')
	            {
	            	alert(errors);
	            	return false;
	            }
				
			    return true;
			},
	        done: function (e, data) {
				if(typeof data.result.success != 'undefined' && data.result.success)
				{
					$("#loading-"+unique_id).hide();
					$("#progress-"+unique_id).html('');
		            $.each(data.result.files, function (index, file) {
		            	$('#upload-state-message-'+unique_id).html('');
		            	$("input[rel="+uploader_element.attr('name')+"]").val(file.name);
		            	var file_name = file.name;
						
		            	var is_image = (file_name.substr(-4) == '.jpg'  
		            						|| file_name.substr(-4) == '.png' 
		            						|| file_name.substr(-5) == '.jpeg' 
		            						|| file_name.substr(-4) == '.gif' 
		            						|| file_name.substr(-5) == '.tiff')
							? true : false;
		            	if(is_image)
		            	{
		            		$('#file_'+unique_id).addClass('image-thumbnail');
		            		load_fancybox($('#file_'+unique_id));
		            		$('#file_'+unique_id).html('<img src="'+file.url+'" height="50" />');
		            	}
		            	else
		            	{
		            		$('#file_'+unique_id).removeClass('image-thumbnail');
		            		$('#file_'+unique_id).unbind("click");
		            		$('#file_'+unique_id).html(file_name);
		            	}
		            	
						$('#file_'+unique_id).attr('href',file.url);
						$('#hidden_'+unique_id).val(file_name);

						$('#success_'+unique_id).fadeIn('slow');
						$('#delete_url_'+unique_id).attr('rel',file_name);
						$('#upload-button-'+unique_id).slideUp('fast');
		            });
				}
				else if(typeof data.result.message != 'undefined')
				{
					alert(data.result.message);
					show_upload_button(unique_id, uploader_element);
				}
				else
				{
					alert(error_on_uploading);
					show_upload_button(unique_id, uploader_element);
				}
	        },
	        autoUpload: true,
	        error: function()
	        {
	        	alert(error_on_uploading);
	        	show_upload_button(unique_id, uploader_element);
	        },
	        fail: function(e, data)
	        {
	            // data.errorThrown
	            // data.textStatus;
	            // data.jqXHR;	        	
	        	alert(error_on_uploading);
	        	show_upload_button(unique_id, uploader_element);
	        },	        
	        progress: function (e, data) {
                $("#progress-"+unique_id).html(string_progress + parseInt(data.loaded / data.total * 100, 10) + '%');
            }	        
	    });
		$('#delete_'+unique_id).click(function(){
			if( confirm(message_prompt_delete_file) )
			{
				var file_name = $('#delete_url_'+unique_id).attr('rel');
				$.ajax({
					url: delete_url+"/"+file_name,
					cache: false,
					success:function(){
						show_upload_button(unique_id, uploader_element);
					},
					beforeSend: function(){
						$('#upload-state-message-'+unique_id).html(string_delete_file);
						$('#success_'+unique_id).hide();
						$("#loading-"+unique_id).show();
						$("#upload-button-"+unique_id).slideUp("fast");
					}
				});
			}
			
			return false;
		});		    
	    
	});
});