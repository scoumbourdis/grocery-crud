$(function() {
	var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' data-value='{value}' data-field='{field}' role='presentation'>Remove Tab</span></li>";

	//disable template fields
	$('[id*="{primary_key_value}"]').attr('disabled', true);
	var init_tabs = $( ".tabs" ).tabs();

	function addTab(field, label, value) {

		var id = field +'-relation-'+ value,
			li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ).replace( /\{value\}/g, value ).replace( /\{field\}/g, field)),
			tabContentTemplate = $('#'+field+"Template").clone().find('[id*="{primary_key_value}"]').attr('disabled', false).end().html(),
			tabContentHtml = tabContentTemplate.replace( /\{primary_key_value\}/g, value );

		if($("#"+id).size()>0){
			var tabs = $('#'+field+"Tabs");
			tabs.tabs({ active: $("#"+id).index('#'+field+"Tabs div.ui-tabs-panel") });
		} else {
			var tabs = $('#'+field+"Tabs");
			tabs.find( ".ui-tabs-nav" ).append( li );
			tabs.append( "<div id='" + id + "'><p>" + tabContentHtml + "</p></div>" );
			tabs.tabs( "refresh" );
			tabs.tabs({ active: (tabs.find( ".ui-tabs-nav li").size()-1) });

			$('#'+id).find('.datepicker-input').each(function(){ 
				$(this).removeClass('hasDatepicker').datepicker({
					dateFormat: js_date_format,
					showButtonPanel: true,
					changeMonth: true,
					changeYear: true
				});
			});

			$('#'+id).find('.datetime-input').each(function(){
				$(this).removeClass('hasDatepicker').datetimepicker({
					timeFormat: 'hh:mm:ss',
					dateFormat: js_date_format,
					showButtonPanel: true,
					changeMonth: true,
					changeYear: true
				});
			});

			$('#'+id).find('textarea').each(function() {
				if (typeof tinymce_options != 'undefined') {
					$(this).tinymce(tinymce_options);
				}
				if (typeof CKEDITOR != 'undefined') {
					//console.log(CKEDITOR.instances[$(this).attr('id')]);
					CKEDITOR.replace($(this).attr('id'));
				}
			});

			$('#'+id).find(".chzn-container").remove();
			$('#'+id).find(".chosen-select,.chosen-multiple-select").removeClass('chzn-done').chosen({allow_single_deselect:true});
			$('#'+id).find(".radio-uniform").each(function(){ $(this).uniform(); });
			
			$('#field-'+field).children('option[value=' + value + ']').attr('selected', true);

		}
	}


	$(".relation_table_value_selector").change(function(){
		if($(this).val()!=""){
			addTab($(this).data('field'), $(this).children('option:selected').text(), $(this).val())
		} else {
			var field = $(this).data('field');
			$('#field-'+field).val('');
			var tabs = $('#'+field+"Tabs");
			var tab_count = tabs.tabs('length');
			for (i=0; i<tab_count; i++){
				tabs.tabs( "remove" , 0 )
			}
		}
	})

	// close icon: removing the tab on click
	init_tabs.delegate( "span.ui-icon-close", "click", function() {
		var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
		$('#'+panelId).find('textarea').each(function() {
			//remove the CKEDITOR instance
			if (typeof CKEDITOR != 'undefined') {
				CKEDITOR.remove(CKEDITOR.instances[$(this).attr('id')]);
			}
		});
		$( "#" + panelId ).remove();
		init_tabs.tabs( "refresh" );
		var field = $(this).data('field');
		var value = $(this).data('value');
		$('#field-'+field).children('option[value=' + value + ']').removeAttr('selected');
	});

});
