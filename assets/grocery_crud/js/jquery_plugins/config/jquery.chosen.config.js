$(function(){
	$(".chosen-select,.chosen-multiple-select").chosen({allow_single_deselect:true});
	
	/*
	$(".chosen-select,.chosen-multiple-select").ajaxChosen({
	    type: 'POST',
	    url: '/index.php/examples2/test2',
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