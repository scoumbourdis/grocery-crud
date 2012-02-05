var string_upload_file = 'Uploading file';
var string_delete_file = 'Deleting file';
var string_progress = 'Progress: ';
$(function(){
	$('.gc-file-upload').each(function(){
		var unique_id 	= $(this).attr('id');
		var uploader_url = $(this).attr('rel');
		var uploader_element = $(this);
		var delete_url 	= $('#delete_url_'+unique_id).attr('href');
	    $(this).fileupload({
	        dataType: 'json',
	        url: uploader_url,
	        cache: false,
			beforeSend: function(){
	    		$('#upload-state-message-'+unique_id).html(string_upload_file);
				$("#loading-"+unique_id).show();
				$("#upload-button-"+unique_id).slideUp("fast");
			},		        
	        done: function (e, data) {
				$("#loading-"+unique_id).hide();
				$("#progress-"+unique_id).html('');
	            $.each(data.result, function (index, file) {
	            	$('#upload-state-message-'+unique_id).html('');
	            	$("input[rel="+uploader_element.attr('name')+"]").val(file.name);
	            	var file_name = file.name;
					$('#file_'+unique_id).html(file_name);
					$('#file_'+unique_id).attr('href',file.url);
					$('#hidden_'+unique_id).val(file_name);
					//$('#'+uploader_id).hide();
					$('#success_'+unique_id).fadeIn('slow');
					$('#delete_url_'+unique_id).attr('rel',file_name);
					$('#upload-button-'+unique_id).slideUp('fast');
	            });
	        },
	        progress: function (e, data) {
                $("#progress-"+unique_id).html(string_progress + parseInt(data.loaded / data.total * 100, 10) + '%');
            }	        
	    });
		$('#delete_'+unique_id).click(function(){
			if( confirm('Are you sure you want to delete this file?') )
			{
				var file_name = $('#delete_url_'+unique_id).attr('rel');
				$.ajax({
					url: delete_url+"/"+file_name,
					cache: false,
					success:function(){
						$('#upload-state-message-'+unique_id).html('');
						$("#loading-"+unique_id).hide();
					
						$('#upload-button-'+unique_id).slideDown('fast');
						$("input[rel="+uploader_element.attr('name')+"]").val('');
						$('#success_'+unique_id).slideUp('fast');
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