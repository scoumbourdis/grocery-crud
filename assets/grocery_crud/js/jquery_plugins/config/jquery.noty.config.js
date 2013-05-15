function success_message(success_message)
{
	noty({
		  text: success_message,
		  type: 'success',
		  dismissQueue: true,
		  layout: 'top',
		  callback: {
		    afterShow: function() {

		        setTimeout(function(){
		        	$.noty.closeAll();
		        },7000);
		    }
		  }
	});
}

function error_message(error_message)
{
	noty({
		  text: error_message,
		  type: 'error',
		  layout: 'top',
		  dismissQueue: true
	});
}

function form_success_message(success_message)
{
	$('#report-success').slideUp('fast');
	$('#report-success').html(success_message);

	if ($('#report-success').closest('.ui-dialog').length !== 0) {
		$('.go-to-edit-form').click(function(){

			fnOpenEditForm($(this));

			return false;
		});
	}

	$('#report-success').slideDown('normal');
	$('#report-error').slideUp('fast').html('');
}

function form_error_message(error_message)
{
	$('#report-error').slideUp('fast');
	$('#report-error').html(error_message);
	$('#report-error').slideDown('normal');
	$('#report-success').slideUp('fast').html('');
}