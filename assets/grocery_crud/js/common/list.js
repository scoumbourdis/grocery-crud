var js_libraries = [];

var fnOpenEditForm = function(this_element){

	var href_url = this_element.attr("href");

	var dialog_height = $(window).height() - 80;

	//Close all
	$(".ui-dialog-content").dialog("close");

	$.ajax({
		url: href_url,
		data: {
			is_ajax: 'true'
		},
		type: 'post',
		dataType: 'json',
		beforeSend: function() {
			this_element.closest('.flexigrid').addClass('loading-opacity');
			this_element.closest('.dataTablesContainer').addClass('loading-opacity');
		},
		complete: function(){
			this_element.closest('.flexigrid').removeClass('loading-opacity');
			this_element.closest('.dataTablesContainer').removeClass('loading-opacity');
		},
		success: function (data) {
			if (typeof CKEDITOR !== 'undefined' && typeof CKEDITOR.instances !== 'undefined') {
					$.each(CKEDITOR.instances,function(index){
						delete CKEDITOR.instances[index];
					});
			}

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
				},
				open: function(){
					var this_dialog = $(this);

					$('#cancel-button').click(function(){
						this_dialog.dialog("close");
					});

				}
			});
		}
	});
};

var add_edit_button_listener = function () {

	//If dialog AJAX forms is turned on from grocery CRUD config
	if (dialog_forms) {

		$('.edit_button,.add_button').unbind('click');
		$('.edit_button,.add_button').click(function(){

			fnOpenEditForm($(this));

			return false;
		});

	}
}

var load_css_file = function(css_file) {
	if ($('head').find('link[href="'+css_file+'"]').length == 0) {
		$('head').append($('<link/>').attr("type","text/css")
				.attr("rel","stylesheet").attr("href",css_file));
	}
};
