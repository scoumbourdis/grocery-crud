$(function(){
	$('.file-upload').each(function(){
		var uploader_url = $(this).attr('rel');
	    $(this).fileupload({
	        dataType: 'json',
	        url: uploader_url,
	        done: function (e, data) {
	            $.each(data.result, function (index, file) {
	               alert("SUCESS UPLOAD || file name: "+file.name);
	            });
	        }
	    });
	});
});