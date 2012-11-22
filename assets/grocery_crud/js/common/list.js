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
				
				LazyLoad.loadOnce(data.js_lib_files);
				LazyLoad.load(data.js_config_files);
				
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

var load_css_file = function(css_file) {
	if ($('head').find('link[href="'+css_file+'"]').length == 0) {
		$('head').append($('<link/>').attr("type","text/css")
				.attr("rel","stylesheet").attr("href",css_file));
	}
};
