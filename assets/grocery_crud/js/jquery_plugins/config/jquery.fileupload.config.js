$(function(){
	$('.gc-file-upload').each(function(){
		var unique_id 	= $(this).attr('id');
		var uploader_url = $(this).attr('rel');
		var uploader_element = $(this);
	    $(this).fileupload({
	        dataType: 'json',
	        url: uploader_url,
	        done: function (e, data) {
	            $.each(data.result, function (index, file) {
	            	$("input[rel="+uploader_element.attr('name')+"]").val(file.name);
	            	var file_name = file.name;
					$('#file_'+unique_id).html(file_name);
					$('#file_'+unique_id).attr('href',file.url);
					$('#hidden_'+unique_id).val(file_name);
					//$('#'+uploader_id).hide();
					$('#success_'+unique_id).fadeIn('slow');
					$('#delete_url_'+unique_id).attr('rel',file_name);	            	
	            });
	        }
	    });
	});
});