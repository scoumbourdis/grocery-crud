$(function(){	
	$('.grocery-crud-uploader').each(function(){
		var uploader_id = $(this).attr('id');
		var unique_id 	= $(this).attr('rel');
		var field_name 	= $('#hidden_'+unique_id).attr('name');
		var upload_url 	= $('#url_'+unique_id).attr('href');
		var delete_url 	= $('#delete_url_'+unique_id).attr('href');
		
		var uploader = new qq.FileUploader({
			element: document.getElementById(uploader_id),
			action: upload_url,
			onComplete: function(id, fileName, responseJSON){
				$('#file_'+unique_id).html(responseJSON.file_name);
				$('#file_'+unique_id).attr('href',responseJSON.full_url);
				$('#hidden_'+unique_id).val(responseJSON.file_name);
				$('#'+uploader_id).hide();
				$('#success_'+unique_id).fadeIn('slow');
				$('#delete_url_'+unique_id).attr('rel',responseJSON.file_name);
			},
			onSubmit: function(id, fileName){
				$('#'+uploader_id+' .qq-upload-button:first').hide();
			},
			onCancel: function(id, fileName){
				$('#'+uploader_id+' .qq-upload-button:first').show();
			},
			multiple: false,
			debug: true
		});  

		$('#delete_'+unique_id).click(function(){
			if( confirm('Are you sure you want to delete this file?') )
			{
				var file_name = $('#delete_url_'+unique_id).attr('rel');
				$.ajax({
					url: delete_url+"/"+file_name,
					success:function(){
						$('#'+uploader_id+' .qq-upload-list:first').html('');
						$('#hidden_'+unique_id).val('');
						$('#'+uploader_id+' .qq-upload-button:first').show();
						$('#success_'+unique_id).hide();
						$('#'+uploader_id).fadeIn('slow');					
					}
				});
			}
			else
			{
				
			}
			
			return false;
			});				
		});
});