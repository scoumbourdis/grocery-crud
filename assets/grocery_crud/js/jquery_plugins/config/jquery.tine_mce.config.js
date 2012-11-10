	$(function() {
		var tinymce_path = default_texteditor_path+'/tiny_mce/';
	
		var tinymce_options = {

				// Location of TinyMCE script
				script_url : tinymce_path +"tiny_mce.js",
				
				// General options
				theme : "advanced",
				plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

				// Theme options
				theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				entity_encoding : "raw",
				/*
				// Example content CSS (should be your site CSS)
				content_css : "css/content.css",
				*/
				// Drop lists for link/image/media/template dialogs
				template_external_list_url : tinymce_path +"lists/template_list.js",
				external_link_list_url : tinymce_path +"lists/link_list.js",
				external_image_list_url : tinymce_path +"lists/image_list.js",
				media_external_list_url : tinymce_path +"lists/media_list.js",

				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			};
		
		$('textarea.texteditor').tinymce(tinymce_options);
		
		var minimal_tinymce_options = $.extend({}, tinymce_options);
		minimal_tinymce_options.theme = "simple";
		
		$('textarea.mini-texteditor').tinymce(minimal_tinymce_options);
		
	});