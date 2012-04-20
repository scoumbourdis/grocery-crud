var default_per_page = typeof default_per_page !== 'undefined' ? default_per_page : 25;

$(document).ready(function() {
	oTable = $('#groceryCrudTable').dataTable({
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"bStateSave": true,
		"iDisplayLength": default_per_page,
		"oLanguage":{
		    "sProcessing":   list_loading,
		    "sLengthMenu":   show_entries_string,
		    "sZeroRecords":  list_no_items,
		    "sInfo":         displaying_paging_string,
		    "sInfoEmpty":   list_zero_entries,
		    "sInfoFiltered": filtered_from_string,
		    "sSearch":       search_string+":",
		    "oPaginate": {
		        "sFirst":    paging_first,
		        "sPrevious": paging_previous,
		        "sNext":     paging_next,
		        "sLast":     paging_last
		    }		
		}

	});

	$('a[role=button]').live("mouseover mouseout", function(event) {
		  if ( event.type == "mouseover" ) {
			  $(this).addClass('ui-state-hover');
		  } else {
			  $(this).removeClass('ui-state-hover');
		  }
	});	
	
	$('th.actions').unbind('click');
	$('th.actions>div').html($('th.actions>div').text());
	
} ); 

function delete_row(delete_url , row_id)
{	
	if(confirm(message_alert_delete))
	{
		$.ajax({
			url: delete_url,
			dataType: 'json',
			success: function(data)
			{					
				if(data.success)
				{
					$('#ajax_refresh_and_loading').trigger('click');
					$('#report-success').html( data.success_message ).slideUp('fast').slideDown('slow');						
					$('#report-error').html('').slideUp('fast');
					$('tr#row-'+row_id).addClass('row_selected');
					var anSelected = fnGetSelected( oTable );
					oTable.fnDeleteRow( anSelected[0] );					
				}
				else
				{
					$('#report-error').html( data.error_message ).slideUp('fast').slideDown('slow');						
					$('#report-success').html('').slideUp('fast');						
					
				}
			}
		});
	}
	
	return false;
}

function fnGetSelected( oTableLocal )
{
	var aReturn = new Array();
	var aTrs = oTableLocal.fnGetNodes();
	
	for ( var i=0 ; i<aTrs.length ; i++ )
	{
		if ( $(aTrs[i]).hasClass('row_selected') )
		{
			aReturn.push( aTrs[i] );
		}
	}
	return aReturn;
}