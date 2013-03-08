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
		  dismissQueue: true,
		  callback: {
		    afterShow: function() {
		        setTimeout(function(){
		            $.noty.closeAll();             
		        },7000);
		    }
		  }  
	});
}