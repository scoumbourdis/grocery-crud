var js_libraries = [];

var add_edit_button_listener = function() {
	var dialog_height = $(window).height() - 80;

	$('.edit_button,.add_button').unbind('click');
	$('.edit_button,.add_button').click(function(){
		$.ajax({
			url: $(this).attr("href"),
			data: {
				is_ajax: 'true'
			},
			type: 'post',
			dataType: 'json',
			success: function(data){
				
				$.each(data.js_files,function(index,js_file){
					if($.inArray(js_file,js_libraries) === -1) {
						load_js_file(js_file);
						
						if($.inArray(js_file,data.js_lib_files) !== -1) {
							js_libraries.push(js_file);
							load_js_file(js_file);			
						}
						
					}
				});
				
				$.each(data.css_files,function(index,css_file){
					load_css_file(css_file);
				});				
				
				$("<div/>").html(data.output).dialog({
					width: 910,
					modal: true,
					height: dialog_height,
					close: function(){
						$(this).remove();
					}
				});
			}
		});
		return false;
	});	
}

var load_js_file = function(js_file) {
	var script = document.createElement('script'); 
	script.type = 'text/javascript'; 
	script.src = js_file;
	document.body.appendChild(script);
};

var load_css_file = function(css_file) {
	if ($('head').find('link[href="'+css_file+'"]').length == 0) {
		$('head').append($('<link/>').attr("type","text/css")
				.attr("rel","stylesheet").attr("href",css_file));
	}
};
