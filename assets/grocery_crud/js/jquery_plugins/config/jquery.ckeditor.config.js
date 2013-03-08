$(function(){
	$( 'textarea.texteditor' ).ckeditor({toolbar:'Full'});
	$( 'textarea.mini-texteditor' ).ckeditor({toolbar:'Basic',width:700});
});