<?php
	//For view all the languages go to the folder assets/grocery_crud/languages/
	$config['grocery_crud_default_language']	= 'english';
	
	// There are only three choices: "uk-date" (dd/mm/yyyy), "us-date" (mm/dd/yyyy) or "sql-date" (yyyy-mm-dd) 
	$config['grocery_crud_date_format']			= 'uk-date';
	
	/*
	//If the set_relation data is bigger than the specified number, call all the data with ajax every time the user types a letter.
	$config['grocery_crud_set_relation_max_data_without_ajax'] = 500;
	
	$config['grocery_crud_image_upload_allow_file_types'] 		= 'gif|jpeg|jpg|png';
	$config['grocery_crud_image_upload_max_file_size'] 			= '10MB'; //ex. '10MB' (Mega Bytes), '1067KB' (Kilo Bytes), '5000B' (Bytes)
	$config['grocery_crud_image_upload_default_dir'] 			= 'assets/uploads/images';
	$config['grocery_crud_image_upload_default_url'] 			= 'assets/uploads/images';
	$config['grocery_crud_image_upload_max_width'] 				= 1024; //pixels
	$config['grocery_crud_image_upload_max_height'] 			= 768; //pixels
	$config['grocery_crud_image_upload_thumb_width'] 			= 100; //pixels
	$config['grocery_crud_image_upload_thumb_height'] 			= 75; //pixels
	$config['grocery_crud_image_upload_create_thumbnail'] 		= true;
	*/
	$config['grocery_crud_file_upload_allow_file_types'] 		= 'gif|jpeg|jpg|png|tiff|doc|docx|txt|odt|xls|xlsx|pdf|ppt|pptx|pps|ppsx|mp3|m4a|ogg|wav|mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2';
	$config['grocery_crud_file_upload_max_file_size'] 			= '20MB'; //ex. '10MB' (Mega Bytes), '1067KB' (Kilo Bytes), '5000B' (Bytes)
	/*
	$config['grocery_crud_file_upload_default_path_folder']		= 'assets/uploads/files';
	$config['grocery_crud_file_upload_default_url'] 			= 'assets/uploads/files';
	*/
	