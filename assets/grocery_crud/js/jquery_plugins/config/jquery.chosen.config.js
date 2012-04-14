$(function(){
	$(".chosen-select,.chosen-multiple-select").chosen({allow_single_deselect:true});
/*	
	$(".ajax-chosen-select,.ajax-chosen-multiple-select").ajaxChosen({
	    type: 'POST',
	    url: ajax_relation_url,
	    dataType: 'json'
	}, function (data) {
		    var terms = {};
	
		    $.each(data, function (i, val) {
		        terms[i] = val;
		    });
	
		    return terms;
	});
*/	
});