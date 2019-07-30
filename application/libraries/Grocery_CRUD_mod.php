<?php

/*
	Additional features added by genya to grocery_CRUD.
	
	Using GroceryCRUD 1.6.1
*/
class Grocery_CRUD_mod extends Grocery_CRUD 
{
	protected $table_adjust			= false;
	protected $custom_columns = array();
	protected $custom_srch_table = null;	

	protected $unset_search			= false;
	protected $unset_pagnation		= false;	
	
	protected $custom_render_state = false;
	protected $additional_state_options = array(0 => NULL);
	protected $additional_states = array(0	=> 'unknown');
	protected $custom_buttons = array();
	
	protected $post_ajax_callbacks  = "";
	
	protected $dependent_dropdown_data;
	/*
		User functions / externals.
	*/		

	/**
	 * Custom columns setting function
	 * 		then in set_ajax_list_queries() if this is set performs different search.
	 *		Performs search with HAVING instead of WHERE.
	 */
    public function set_custom_column($arr_cols, $table=null) 
	{
		$this->custom_columns = $arr_cols;
		if($table != NULL)
		{
			$this->custom_srch_table = $table;
		}
		return $this;
    }	
	
	 /* Unsets the pagnation operations from the list
	 */
	public function unset_pagnation()
	{
		$this->unset_pagnation = true;

		return $this;
	}	
	
	/*
	 * Unsets the search operations from the list
	 */
	public function unset_search()
	{
		$this->unset_search = true;

		return $this;
	}		
	
	/**
	 * 
	 * Asjust tables rows, maybe with respect to another table
	 */	
	public function table_adjust()
	{
		$this->table_adjust = true;
		return $this;
	}	
	
	/**
	 * Post ajax callback function
	 * paramerters ($key => $value)
	 * 			- key is specific table
	 *			- value is js function for that specific table
	 */
    public function post_ajax_callbacks($callback_s=null) {
		if($callback_s != null){
			foreach($callback_s as $key => $value){
				$this->post_ajax_callbacks .= ",{$key}:{$value}";
			}
			$pos = strpos($this->post_ajax_callbacks, ",");
			if ($pos !== false) {
				$this->post_ajax_callbacks = substr_replace($this->post_ajax_callbacks, "", $pos, strlen(","));
			}	
		}
    }	
	
	public function add_state($new_state, $view_filename, $final_url, $validation_url = null, $callback = null, $type_of_render=null, $options=array())
	{
		/*
			Add state code for custom action.
			
			$view_filname will be check if it exists in _theme_view method.
			If $validation_url is blank the $final_url is the valdiation and final url.
			
			Validation URL gets attached to URL that is currenly visible before the action is submitted.
			Final URL is attached to the base URL with the index.php included.
		!!		- The top information only applies if dialog form is used if not have to provide complete URL's
				.
			Type of render types:
				'immediate_layout' -> will invoke the success or fail of the action. Only need callback for this.
				'custom_form' -> activate custom form.
				DEFAULT: if null the default will show the custom form.
		*/
		
		if(isset($new_state) && $new_state != NULL && $new_state != '' && is_string($new_state) && ($type_of_render == NULL || $type_of_render == 'custom_form')
			&& isset($final_url) && $final_url != NULL && $final_url != '' && array_search($new_state, $this->states) == FALSE && isset($view_filename))
		{
			$this->additional_states[] = $new_state;
			$this->additional_state_options[] = array(
													'final_url' => $final_url,
													'validation_url' => $validation_url == NULL ? $final_url : $validation_url,
													'view_filename' => $view_filename,
													'callback' => $callback,
													'type' => $type_of_render,
													);
		}
		elseif(isset($new_state) && $new_state != NULL && $new_state != '' && is_string($new_state) && $type_of_render == 'immediate_layout'
			&& array_search($new_state, $this->states) == FALSE && $callback != NULL && $callback != '')
		{
			$this->additional_states[] = $new_state;
			$this->additional_state_options[] = array(
													'final_url' => $final_url,
													'validation_url' => $validation_url == NULL ? $final_url : $validation_url,
													'view_filename' => $view_filename,
													'callback' => $callback,
													'type' => $type_of_render,
													);			
		}
		elseif($type_of_render == 'dependent_dropdown_ajax') // is used by the dependent dropdown function to process ajax requests.
		{
			$this->additional_states[] = $new_state;
			$this->additional_state_options[] = array(
													'type' => $type_of_render,
													);			
		}
		else
		{
			die("Incorrect use of add_state method.");
		}
	}

	public function add_button($button_name, $link, $alignment = NULL, $css_classes = NULL)
	{
		/*
			Add a button on top of CRUD list either on left or right.
			Have it point to a link.
			
			Arguments:
				alignments: left or right
			
			Defaults:
				alignment default will be left aligned
		*/
		
		if($button_name != '' && $button_name != NULL && $link != '' && $link != NULL)
		{
			$this->custom_buttons[] = array(
				'button_name' => $button_name,
				'link' => $link,
				'alignment' => (($alignment == NULL) ? 'left' : $alignment),
				'css_classes' => $css_classes,
			);
		}
		else
		{
			die('Incorrect use of add_button');
		}
	}

	/*
		Creates dependent dropdown field based on query and other field(s).
		
		Make sure this is set after add_fields and edit_fields are set, they MUST be set for this feature to work.
		
		PARAMETERS
			query:
				query needs to have id and name field!
			
			query_binding(order is crucial to ? symbols)
				Types:
					- dependent_field -> dependent_field
					- other_field -> [the field name]
					- primary_key -> NULL
	*/
	public function dependent_dropdown_field($field_name, $dependent_field, $query, $query_binding=array())
	{
		if($field_name != '' && $dependent_field != '' && $query != '')
		{
			// set data
			$this->dependent_dropdown_data[$field_name]['dependent_field'] = $dependent_field;
			$this->dependent_dropdown_data[$field_name]['query_binding'] = $query_binding;
			$this->dependent_dropdown_data[$field_name]['query'] = $query;
		}
		else
		{
			// TODO: alert in proper use
		}
	}

	/*
		Internals
	*/

	/*
		Sets up the js to perform ajax calls on change for the independent fields.
		Puts js in extra field that is hidden.
		
		Have to set add and edit fields
	*/
	protected function _form_dependent_dropdown()
	{	
		// set new hidden field
		$extra_js_fieldname = md5('extra_js_field'); // md5 to not match any fields in actual list		
		
		$dependent_dropdown_data = $this->get_dependent_dropdown_data();
		$callback_func = function($val = '', $primary_key = null) use($dependent_dropdown_data)
		{
			$js = '<script type="text/javascript">
					$(document).ready(function(){';
			foreach($dependent_dropdown_data as $field_name => $ele)
			{
				$js .= '
						//document.querySelector("#field-'.$ele['dependent_field'].'").addEventListener("change", function(e) // this does not work, while the Jquery one does
						$("#field-'.$ele['dependent_field'].'").change(function(e)
						{
							var fields = document.querySelectorAll(\'*[id^="field-"]\');					
							var all_fields_data = {};
							Array.from(fields).forEach(element => { // gather all the field data
								all_fields_data[element.name] = element.value;
							});					
							
							$.ajax(
							{
								type: "POST",
								url: "'.$this->crud_url_path.'/dependent_dropdown_ajax/'.$field_name.'",
								dataType: "json",
								data: 	{
											selected_id: $(this).children("option:selected").val(),
											all_field_data: all_fields_data,
											hello: "hey",
										},
								cache: "false",
								success: function(data)
								{
									if(data.success)
									{
										var output = "<option value=\'\'></option>";
										data.data.forEach(function(value) {
											output += "<option value=\'"+value.id+"\'>"+value.name+"</option>";															
										});	

										// save value
										let selected_save = $("#field-'.$field_name.'").val();

										$("#field-'.$field_name.'").find("option").remove();        // clear
										$("#field-'.$field_name.'").html(output);                   // insert data
										
										if(selected_save != null && selected_save != "")
										{
											$("#field-'.$field_name.'").val(selected_save);
										}
										
										$("#field-'.$field_name.'").trigger("chosen:updated");      // refresh
										
									}
									else
									{
										alert("Error with data from database");
									}
								},
								error: function()
								{
									alert("Failed to send data");
								}
							});	
						});
						
						// for if in edit
						if($("#field-'.$ele['dependent_field'].'").val() != null && $("#field-'.$ele['dependent_field'].'").val() != "")
						{
							$("#field-'.$ele['dependent_field'].'").trigger("change");
						}
				';
			}
			$js .= '});</script>';
			
			return $js;
		};

		/* TODO: Make it so that do not need to define add and edit fields, and maybe retrieve them another way.
		// get all the fields
		$edit_fields = $this->get_edit_fields();
		$add_fields = $this->get_add_fields();
		
		// process
		$edit_fields = array_map(function($e) { // possible to do with array_column in php7
			return is_object($e) ? $e->field_name : $e['field_name'];
		}, $edit_fields);
		$add_fields = array_map(function($e) { // possible to do with array_column in php7
			return is_object($e) ? $e->field_name : $e['field_name'];
		}, $add_fields);
		*/
		
		$edit_fields = $this->edit_fields;
		$add_fields = $this->add_fields;

		// add the new field
		$edit_fields[] = $extra_js_fieldname;
		$add_fields[] = $extra_js_fieldname;

		$this->add_fields($add_fields);
		$this->edit_fields($edit_fields);

		// hide the field
		$this->field_type($extra_js_fieldname, 'hidden');
		
		$this->callback_add_field($extra_js_fieldname, $callback_func);
		$this->callback_edit_field($extra_js_fieldname, $callback_func);
	}
	
	/*
		Getter method for dependent dropdown options
	*/
	protected function get_dependent_dropdown_data()
	{
		return $this->dependent_dropdown_data;
	}
	
	protected function _dependent_dropdown_ajax($field_name = '')
	{
		$post_array = $_POST;
		
		$output = array();
		$output['success'] = true;
		$output['error_msg'] = "";
		
		if($field_name != '')
		{
			$dropdown_data = $this->get_dependent_dropdown_data();
			
			$query_binding = array();
			foreach($dropdown_data[$field_name]['query_binding'] as $key => $val)
			{
				switch($key)
				{
					case 'dependent_field':
						$query_binding[] = $post_array['selected_id'];
						break;
					case 'other_field':
						$query_binding[] = $post_array['all_field_data'][$val];
						break;
					case 'primary_key':
						$query_binding[] = $primary_key;
						break;
					default:
						$output['success'] = false;
						$output['error_msg'] = "In proper set of dependent dropdown";					
				}
			}
		
			if($output['success'])
			{
				$data = $this->basic_model->get_dependent_dropdown_data($dropdown_data[$field_name]['query'], $query_binding);
				$output['data'] = $data;
			}
		}
		else
		{
			$output['success'] = false;
			$output['error_msg'] = "Error occured";			
		}
		
		echo json_encode($output);
		die();		
	}
	
	// override
	protected function showList($ajax = false, $state_info = null)
	{
		/*
			Pass some extra variables to list view.
		*/
		
		$data = $this->get_common_data();

		$data->post_ajax_callbacks = $this->post_ajax_callbacks;  
		
		$data->order_by 	= $this->order_by;

		$data->types 		= $this->get_field_types();

		$data->list = $this->get_list();
		$data->list = $this->change_list($data->list , $data->types);
		$data->list = $this->change_list_add_actions($data->list);

		$data->total_results = $this->get_total_results();		
		
        $data->dialog_forms = $this->config->dialog_forms;
		$data->columns 				= $this->get_columns();		

		$data->success_message		= $this->get_success_message_at_list($state_info);

		$data->primary_key 			= $this->get_primary_key();
		$data->add_url				= $this->getAddUrl();
		$data->edit_url				= $this->getEditUrl();
		$data->clone_url			= $this->getCloneUrl();
		$data->delete_url			= $this->getDeleteUrl();
        $data->delete_multiple_url	= $this->getDeleteMultipleUrl();
		$data->read_url				= $this->getReadUrl();
		$data->ajax_list_url		= $this->getAjaxListUrl();
		$data->ajax_list_info_url	= $this->getAjaxListInfoUrl();
		$data->export_url			= $this->getExportToExcelUrl();
		$data->print_url			= $this->getPrintUrl();
		$data->actions				= $this->actions;
		$data->unique_hash			= $this->get_method_hash();
		$data->order_by				= $this->order_by;

		$data->unset_add			= $this->unset_add;
		$data->unset_search			= $this->unset_search;
		$data->table_adjust			= $this->table_adjust;
		$data->unset_pagnation		= $this->unset_pagnation;
		$data->unset_edit			= $this->unset_edit;
		$data->unset_clone			= $this->unset_clone;
		$data->unset_read			= $this->unset_read;
		$data->unset_delete			= $this->unset_delete;
		$data->unset_export			= $this->unset_export;
		$data->unset_print			= $this->unset_print;

		$data->custom_buttons = $this->custom_buttons;

		$default_per_page = $this->config->default_per_page;
		$data->paging_options = $this->config->paging_options;
		$data->default_per_page		= is_numeric($default_per_page) && $default_per_page >1 && in_array($default_per_page,$data->paging_options)? $default_per_page : 25;

		if($data->list === false)
		{
			throw new Exception('It is impossible to get data. Please check your model and try again.', 13);
			$data->list = array();
		}

		foreach($data->list as $num_row => $row)
		{
            $data->list[$num_row]->primary_key_value = $row->{$data->primary_key};
			$data->list[$num_row]->edit_url = $data->edit_url.'/'.$row->{$data->primary_key};
			$data->list[$num_row]->delete_url = $data->delete_url.'/'.$row->{$data->primary_key};
			$data->list[$num_row]->read_url = $data->read_url.'/'.$row->{$data->primary_key};
            $data->list[$num_row]->clone_url = $data->clone_url.'/'.$row->{$data->primary_key};
		}

		
		if(!$ajax)
		{
			$this->_add_js_vars(array('dialog_forms' => $this->config->dialog_forms));

			$data->list_view = $this->_theme_view('list.php',$data,true);
			$this->_theme_view('list_template.php',$data);
		}
		else
		{ 				
			$this->set_echo_and_die();
			$this->_theme_view('list.php',$data);
		}

        if (!empty($this->upload_fields)) 
		{
            $this->load_js_fancybox();
        }
	}	

	protected function custom_immediate_layout($result)
	{
		/*
			Keys of result:
				'success' => true or false,
				'error_msg' => '',
				'success_msg' => '',
		*/
		
		@ob_end_clean();
		if($result['success'] === false)
		{
			$error_message = '<p>'.$result['error_msg'].'</p>';

			echo json_encode(array('success' => $result['success'] ,'error_message' => $error_message));
		}
		else
		{
			$success_message = '<p>'.$result['success_msg'].'</p>';

			echo json_encode(array('success' => true , 'success_message' => $success_message));
		}
		$this->set_echo_and_die();
	}
	
    protected function showCustomForm($state_info)
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);

		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();

		//$data->field_values = $this->get_edit_values($state_info->primary_key);

		//$data->add_url		= $this->getAddUrl();
		$data->list_url 	= $this->getListUrl();
		
		//$data->delete_url	= $this->getDeleteUrl($state_info);
		//$data->read_url		= $this->getReadUrl($state_info->primary_key);
		//$data->input_fields = $this->get_edit_input_fields($data->field_values);
		$data->unique_hash			= $this->get_method_hash();

		//$data->fields 		= $this->get_edit_fields();
		//$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;

		$data->is_ajax 			= $this->_is_ajax();
		$data->primary_key = $state_info->primary_key;

		if($data->primary_key !== NULL)
		{
			$data->final_url	= $this->additional_state_options[$state_info->state_code]['final_url'] . '/' . $data->primary_key;
			$data->validation_url	= $this->additional_state_options[$state_info->state_code]['validation_url'] . '/' . $data->primary_key;
		}
		else
		{
			$data->final_url	= $this->additional_state_options[$state_info->state_code]['final_url'];
			$data->validation_url	= $this->additional_state_options[$state_info->state_code]['validation_url'];			
		}
		$additional_data = NULL;
		if($this->additional_state_options[$state_info->state_code]['callback'] != NULL)
		{
			$additional_data = call_user_func($this->additional_state_options[$state_info->state_code]['callback'], $state_info->primary_key);
		}
		$data->additional_data = $additional_data;

		$this->_theme_view($this->additional_state_options[$state_info->state_code]['view_filename'], $data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");

		$this->_get_ajax_results();
	}

	//override
    public function getStateInfo()
    {
        $state_code = $this->getStateCode();
        $segment_object = $this->get_state_info_from_url();

        $first_parameter = $segment_object->first_parameter;
        $second_parameter = $segment_object->second_parameter;

        $state_info = (object)array();
		
		if(isset($this->custom_render_state) && $this->custom_render_state)
		{
			// could be custom add button on top, so dont throw exception
			$state_info = (object) array(
				'primary_key' => $first_parameter, 
				'state_code' => $state_code, 
				'segment_object' => $segment_object,
				);
		}
		else
		{
			switch ($state_code) {
				case self::STATE_LIST:
				case self::STATE_ADD:
					//for now... do nothing! Keeping this switch here in case we need any information at the future.
					break;

				case self::STATE_EDIT:
				case self::STATE_READ:
					if ($first_parameter !== null) {
						$state_info = (object) array('primary_key' => $first_parameter);
					} else {
						throw new Exception('On the state "edit" the Primary key cannot be null', 6);
						die();
					}
					break;

				case self::STATE_DELETE:
					if ($first_parameter !== null) {
						$state_info = (object) array('primary_key' => $first_parameter);
					} else {
						throw new Exception('On the state "delete" the Primary key cannot be null',7);
						die();
					}
					break;

				case self::STATE_DELETE_MULTIPLE:
					if (!empty($_POST) && !empty($_POST['ids']) && is_array($_POST['ids'])) {
						$state_info = (object) array('ids' => $_POST['ids']);
					} else {
						throw new Exception('On the state "Delete Multiple" you need send the ids as a post array.');
						die();
					}
					break;

				case self::STATE_CLONE:
					if ($first_parameter !== null) {
						$state_info = (object) array('primary_key' => $first_parameter);
					} else {
						throw new Exception('On the state "clone" the Primary key cannot be null', 20);
						die();
					}
					break;

				case self::STATE_INSERT:
					if(!empty($_POST))
					{
						$state_info = (object)array('unwrapped_data' => $_POST);
					}
					else
					{
						throw new Exception('On the state "insert" you must have post data',8);
						die();
					}
					break;

				case 6:
					if(!empty($_POST) && $first_parameter !== null)
					{
						$state_info = (object)array('primary_key' => $first_parameter,'unwrapped_data' => $_POST);
					}
					elseif(empty($_POST))
					{
						throw new Exception('On the state "update" you must have post data',9);
						die();
					}
					else
					{
						throw new Exception('On the state "update" the Primary key cannot be null',10);
						die();
					}
					break;

				case 7:
				case 8:
				case 16: //export to excel
				case 17: //print
					$state_info = (object)array();
					$data = !empty($_POST) ? $_POST : $_GET;

					if(!empty($data['per_page']))
					{
						$state_info->per_page = is_numeric($data['per_page']) ? $data['per_page'] : null;
					}
					if(!empty($data['page']))
					{
						$state_info->page = is_numeric($data['page']) ? $data['page'] : null;
					}
					//If we request an export or a print we don't care about what page we are
					if($state_code === 16 || $state_code === 17)
					{
						$state_info->page = 1;
						$state_info->per_page = 1000000; //a very big number!
					}
					if(!empty($data['order_by'][0]))
					{
						$state_info->order_by = $data['order_by'];
					}
					if(!empty($data['search_text']))
					{
						if(empty($data['search_field']))
						{
							$search_text = strip_tags($data['search_field']);
							$state_info->search = (object)array('field' => null , 'text' => $data['search_text']);
						}
						else
						{
							if (is_array($data['search_field'])) {
								$search_array = array();
								foreach ($data['search_field'] as $search_key => $search_field_name) {
									$search_array[$search_field_name] = !empty($data['search_text'][$search_key]) ? $data['search_text'][$search_key] : '';
								}
								$state_info->search	= $search_array;
							} else {
								$state_info->search	= (object)array(
									'field' => strip_tags($data['search_field']) ,
									'text' => $data['search_text'] );
							}
						}
					}
					break;

				case 9:

					break;

				case 10:
					if($first_parameter !== null)
					{
						$state_info = (object)array('primary_key' => $first_parameter);
					}
					break;

				case 11:
					$state_info->field_name = $first_parameter;
					break;

				case 12:
					$state_info->field_name = $first_parameter;
					$state_info->file_name = $second_parameter;
					break;

				case 13:
					$state_info->field_name = $_POST['field_name'];
					$state_info->search 	= $_POST['term'];
					break;

				case 14:
					$state_info->field_name = $_POST['field_name'];
					$state_info->search 	= $_POST['term'];
					break;

				case 15:
					$state_info = (object)array(
						'primary_key' 		=> $first_parameter,
						'success_message'	=> true
					);
					break;
			}
		}
		
        return $state_info;
    }

	// override
	protected function get_state_info_from_url()
	{
		/*
			Modified if custom action is invoked.
		*/
		$ci = &get_instance();

		$segment_position = count($ci->uri->segments) + 1;
		$operation = 'list';

		$segements = $ci->uri->segments;
		
		foreach($segements as $num => $value)
		{
			if($value != 'unknown' && (in_array($value, $this->states) ||  in_array($value, $this->additional_states))) // this line modified
			{
				$segment_position = (int)$num;
				$operation = $value; //I don't have a "break" here because I want to ensure that is the LAST segment with name that is in the array.
			}
		}

		$function_name = $this->get_method_name();

		if($function_name == 'index' && !in_array('index',$ci->uri->segments))
			$segment_position++;

		$first_parameter = isset($segements[$segment_position+1]) ? $segements[$segment_position+1] : null;
		$second_parameter = isset($segements[$segment_position+2]) ? $segements[$segment_position+2] : null;

		return (object)array('segment_position' => $segment_position, 'operation' => $operation, 'first_parameter' => $first_parameter, 'second_parameter' => $second_parameter);
	}

	// override
	protected function getStateCode()
	{
		/*
			Modified to get custom state if needed.
		*/
		$state_string = $this->get_state_info_from_url()->operation;

		if( $state_string != 'unknown' && in_array( $state_string, $this->states ) ) // grocery CRUD orginal states
		{
			$state_code =  array_search($state_string, $this->states);
		}
		elseif( $state_string != 'unknown' && in_array( $state_string, $this->additional_states ) ) // custom states
		{
			$this->custom_render_state = true;
			$state_code =  array_search($state_string, $this->additional_states);			
		}
		else
		{
			$state_code = 0;
		}
		
		return $state_code;
	}


	// override
	public function render()
	{
		// set the custom dependent dropdown
		$this->add_state('dependent_dropdown_ajax', '', '', null, null, 'dependent_dropdown_ajax');
		$this->_form_dependent_dropdown();		
		
		$this->pre_render();

		if( $this->state_code != 0 )
		{
			$this->state_info = $this->getStateInfo();
		}
		else
		{
			throw new Exception('The state is unknown , I don\'t know what I will do with your data!', 4);
			die();
		}
		
		if(isset($this->custom_render_state) && $this->custom_render_state)
		{	
			$state_info = $this->getStateInfo();
			$code = $state_info->state_code;
			
			switch($this->additional_state_options[$code]['type'])
			{
				case 'immediate_layout':
						$result = call_user_func($this->additional_state_options[$code]['callback'], $state_info);

						$this->custom_immediate_layout( $result );				
					break;
				case 'dependent_dropdown_ajax':
					$segment_object = $state_info->segment_object;
					$first_parameter = $segment_object->first_parameter;
					$this->_dependent_dropdown_ajax($first_parameter);
					break;
				case 'custom_form':
				default:
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					
					$this->showCustomForm($state_info);
			}
		}
		else
		{
			switch ($this->state_code) {
				case 15://success
				case 1://list
					if($this->unset_list)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					
					$this->showList(false,$state_info);

				break;

				case 2://add
					if($this->unset_add)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}
		
					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$this->showAddForm();

				break;

				case 3://edit
					if($this->unset_edit)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();

					$this->showEditForm($state_info);

				break;

				case 4://delete
					if($this->unset_delete)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}

					$state_info = $this->getStateInfo();
					$delete_result = $this->db_delete($state_info);

					$this->delete_layout( $delete_result );
				break;

				case 5://insert
					if($this->unset_add)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}

					$state_info = $this->getStateInfo();
					$insert_result = $this->db_insert($state_info);

					$this->insert_layout($insert_result);
				break;

				case 6://update
					if($this->unset_edit)
					{
						throw new Exception('This user is not allowed to do this operation', 14);
						die();
					}

					$state_info = $this->getStateInfo();
					$update_result = $this->db_update($state_info);

					$this->update_layout( $update_result,$state_info);
				break;

				case 7://ajax_list

					if($this->unset_list)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
					
					$this->showList(true);

				break;

				case 8://ajax_list_info

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
					
					$this->showListInfo();
				break;

				case 9://insert_validation

					$validation_result = $this->db_insert_validation();

					$this->validation_layout($validation_result);
				break;

				case 10://update_validation

					$validation_result = $this->db_update_validation();

					$this->validation_layout($validation_result);
				break;

				case 11://upload_file

					$state_info = $this->getStateInfo();

					$upload_result = $this->upload_file($state_info);

					$this->upload_layout($upload_result, $state_info->field_name);
				break;

				case 12://delete_file
					$state_info = $this->getStateInfo();

					$delete_file_result = $this->delete_file($state_info);

					$this->delete_file_layout($delete_file_result);
				break;
				/*
				case 13: //ajax_relation
					$state_info = $this->getStateInfo();

					$ajax_relation_result = $this->ajax_relation($state_info);

					$ajax_relation_result[""] = "";

					echo json_encode($ajax_relation_result);
					die();
				break;

				case 14: //ajax_relation_n_n
					echo json_encode(array("34" => 'Johnny' , "78" => "Test"));
					die();
				break;
				*/
				case 16: //export to excel
					//a big number just to ensure that the table characters will not be cutted.
					$this->character_limiter = 1000000;

					if($this->unset_export)
					{
						throw new Exception('You don\'t have permissions for this operation', 15);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					$this->set_ajax_list_queries($state_info);
					$this->exportToExcel($state_info);
				break;

				case 17: //print
					//a big number just to ensure that the table characters will not be cutted.
					$this->character_limiter = 1000000;

					if($this->unset_print)
					{
						throw new Exception('You don\'t have permissions for this operation', 15);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();
					
					$this->set_ajax_list_queries($state_info);
					$this->print_webpage($state_info);
					break;

				case grocery_CRUD_States::STATE_READ:
					if($this->unset_read)
					{
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}

					if($this->theme === null)
						$this->set_theme($this->default_theme);
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();

					$this->showReadForm($state_info);

					break;

				case grocery_CRUD_States::STATE_DELETE_MULTIPLE:

					if($this->unset_delete)
					{
						throw new Exception('This user is not allowed to do this operation');
						die();
					}

					$state_info = $this->getStateInfo();
					$delete_result = $this->db_multiple_delete($state_info);

					$this->delete_layout($delete_result);

					break;


				case grocery_CRUD_States::STATE_CLONE:
					if ($this->unset_clone) {
						throw new Exception('You don\'t have permissions for this operation', 14);
						die();
					}

					if ($this->theme === null) {
						$this->set_theme($this->default_theme);
					}
					$this->setThemeBasics();

					$this->set_basic_Layout();

					$state_info = $this->getStateInfo();

					$this->showCloneForm($state_info);

					break;


			}
		
		}
		
		return $this->get_layout();	
	}

	/*
		Searching additions
	*/
	
	//override
	protected function set_ajax_list_queries($state_info = null)
	{
        $field_types = $this->get_field_types();

		if(!empty($state_info->per_page))
		{
			if(empty($state_info->page) || !is_numeric($state_info->page) )
				$this->limit($state_info->per_page);
			else
			{
				$limit_page = ( ($state_info->page-1) * $state_info->per_page );
				$this->limit($state_info->per_page, $limit_page);
			}
		}

		if(!empty($state_info->order_by))
		{
			$this->order_by($state_info->order_by[0],$state_info->order_by[1]);
		}
			
		if(!empty($state_info->search))
		{
	
			if (!empty($this->relation)) {
				foreach ($this->relation as $relation_name => $relation_values) {
					$temp_relation[$this->_unique_field_name($relation_name)] = $this->_get_field_names_to_search($relation_values);
                }
            }

            if (is_array($state_info->search)) {

                foreach ($state_info->search as $search_field => $search_text) {


                    if (isset($temp_relation[$search_field])) {
                        if (is_array($temp_relation[$search_field])) {
                            $temp_where_query_array = [];

                            foreach ($temp_relation[$search_field] as $relation_field) {
                                $escaped_text = $this->basic_model->escape_str($search_text);
                                $temp_where_query_array[] = $relation_field . ' LIKE \'%' . $escaped_text . '%\'';
                            }
                            if (!empty($temp_where_query_array)) {
                                $this->where('(' . implode(' OR ', $temp_where_query_array) . ')', null);
                            }

                        } else {
                            $this->like($temp_relation[$search_field] , $search_text);
                        }
                    } elseif(isset($this->relation_n_n[$search_field])) {
                        $escaped_text = $this->basic_model->escape_str($search_text);
                        $this->having($search_field." LIKE '%".$escaped_text."%'");
                    } else {
                        $this->like($search_field, $search_text);
                    }

                }
            } elseif ($state_info->search->field !== null) {
				
				if (isset($temp_relation[$state_info->search->field])) {
					if (is_array($temp_relation[$state_info->search->field])) {
						foreach ($temp_relation[$state_info->search->field] as $search_field) {
							$this->or_like($search_field , $state_info->search->text);
                        }
                    } else {
						$this->like($temp_relation[$state_info->search->field] , $state_info->search->text);
                    }
				} elseif(isset($this->relation_n_n[$state_info->search->field])) {
					$escaped_text = $this->basic_model->escape_str($state_info->search->text);
					$this->having($state_info->search->field." LIKE '%".$escaped_text."%'");					
				}elseif(in_array($state_info->search->field, $this->custom_columns)){
					$escaped_text = $this->basic_model->escape_str($state_info->search->text);
					$srch_field = $state_info->search->field;
					if($this->custom_srch_table != NULL)
					{
						$srch_field = $this->custom_srch_table . '.' . $srch_field;
					}
					$this->having($state_info->search->field." LIKE '%".$escaped_text."%'");
				} else {
					$this->like($state_info->search->field , $state_info->search->text);
				}
			}
            // Search all field
			else
			{
				$columns = $this->get_columns();

				$search_text = $state_info->search->text;
				
				if(!empty($this->where))
					foreach($this->where as $where)
						$this->basic_model->having($where[0],$where[1],$where[2]);
		
						
                $temp_where_query_array = [];
                $basic_table = $this->get_table();

				
				foreach($columns as $column)
				{
					if(isset($temp_relation[$column->field_name]))
					{
						if(is_array($temp_relation[$column->field_name]))
						{
							foreach($temp_relation[$column->field_name] as $search_field)
							{
                                $escaped_text = $this->basic_model->escape_str($search_text);
                                $temp_where_query_array[] = $search_field . ' LIKE \'%' . $escaped_text . '%\'';
							}
						}
						else
						{
                            $escaped_text = $this->basic_model->escape_str($search_text);
                            $temp_where_query_array[] = $temp_relation[$column->field_name] . ' LIKE \'%' . $escaped_text . '%\'';
						}
					}
					elseif(isset($this->relation_n_n[$column->field_name]))
					{
						//@todo have a where for the relation_n_n statement
						$escaped_text = $this->basic_model->escape_str($search_text);
						$temp_where_query_array[] =  $column->field_name . ' LIKE \'%'.$escaped_text.'%\'';
						
					}elseif(!empty($this->custom_columns)){
						$escaped_text = $this->basic_model->escape_str($search_text);
						$temp_where_query_array[] =  $column->field_name . ' LIKE \'%'.$escaped_text.'%\'';
					
					}
					elseif (
					    isset($field_types[$column->field_name]) &&
                        !in_array($field_types[$column->field_name]->type, array('date', 'datetime', 'timestamp'))
                    ) {
                        $escaped_text = $this->basic_model->escape_str($search_text);
						$temp_where_query_array[] =  '`' . $basic_table . '`.' . $column->field_name . ' LIKE \'%' . $escaped_text . '%\'';	
							
					}
				}
				
                if (!empty($temp_where_query_array)) {
					if($this->relation_n_n == null && $this->custom_columns == null)
					{
						$this->where('(' . implode(' OR ', $temp_where_query_array) . ')', null);
					}
					else
					{
						$this->having('(' . implode(' OR ', $temp_where_query_array) . ')', null);
					}
                }
			}
		}		
	}

	/*
		Update for validation on n_n fields
	*/
	//override
	protected function db_update_validation()
	{
		$validation_result = (object)array('success'=>false);

		$field_types = $this->get_field_types();
		$required_fields = $this->required_fields;
		$unique_fields = $this->_unique_fields;
		$edit_fields = $this->get_edit_fields();

		if(!empty($required_fields))
		{
			foreach($edit_fields as $edit_field)
			{
				$field_name = $edit_field->field_name;
				if(!isset($this->validation_rules[$field_name]) && in_array( $field_name, $required_fields) )
				{
					// Workaround as Codeigniter set_rules has a bug with array and doesn't work with required fields.
					// We are basically doing the check here!
					if (array_key_exists($field_name, $this->relation_n_n) && in_array($field_name, $required_fields)) {
						if (!array_key_exists($field_name, $_POST)) {
							// This will always throw an error!
							$this->set_rules($field_name, $field_types[$field_name]->display_as, 'required');
						}
					} else if(!isset($this->validation_rules[$field_name]) && in_array( $field_name, $required_fields) ) {
						$this->set_rules($field_name, $field_types[$field_name]->display_as, 'required');
					}
				}
			}
		}


		/** Checking for unique fields. If the field value is not unique then
		 * return a validation error straight away, if not continue... */
		if(!empty($unique_fields))
		{
			$form_validation = $this->form_validation();

			$form_validation_check = false;

			foreach($edit_fields as $edit_field)
			{
				$field_name = $edit_field->field_name;
				if(in_array( $field_name, $unique_fields) )
				{
					$state_info = $this->getStateInfo();
					$primary_key = $this->get_primary_key();
					$field_name_value = $_POST[$field_name];

					$this->basic_model->where($primary_key,$state_info->primary_key);
					$row = $this->basic_model->get_row();

					//if(!isset($row->$field_name)) { // orginal, swapped for bottom since if field is in array but the value is null this exception is threwn
					if(!in_array($field_name, array_keys((array)$row))) // to check if field is in database and top isset() could give false if field is there but just null
					{
						throw new Exception("The field name doesn't exist in the database. ".
								 			"Please use the unique fields only for fields ".
											"that exist in the database");
					}

					$previous_field_name_value = $row->$field_name;


					//if(!empty($previous_field_name_value) && $previous_field_name_value != $field_name_value) {  //orginal
					if((!empty($previous_field_name_value) || $previous_field_name_value == null) && $previous_field_name_value != $field_name_value) { // done since what if orginally was null and then unique value entered
						$form_validation->set_rules( $field_name,
								$field_types[$field_name]->display_as,
								'is_unique['.$this->basic_db_table.'.'.$field_name.']');

						$form_validation_check = true;
					}
				}
			}

			if($form_validation_check && !$form_validation->run())
			{
				$validation_result->error_message = $form_validation->error_string();
				$validation_result->error_fields = $form_validation->_error_array;

				return $validation_result;
			}
		}

		if(!empty($this->validation_rules))
		{
			$form_validation = $this->form_validation();

			$edit_fields = $this->get_edit_fields();

			foreach($edit_fields as $edit_field)
			{
				$field_name = $edit_field->field_name;
				if(isset($this->validation_rules[$field_name]))
				{
					$rule = $this->validation_rules[$field_name];
					$form_validation->set_rules($rule['field'],$rule['label'],$rule['rules'],$rule['errors']);
				}
			}

			if($form_validation->run())
			{
				$validation_result->success = true;
			}
			else
			{
				$validation_result->error_message = $form_validation->error_string();
				$validation_result->error_fields = $form_validation->_error_array;
			}
		}
		else
		{
			$validation_result->success = true;
		}

		return $validation_result;
	}

	/*
		Update for validation on n_n fields
	*/
	//override
	protected function db_insert_validation()
	{
		$validation_result = (object)array('success'=>false);

		$field_types = $this->get_field_types();
		$required_fields = $this->required_fields;
		$unique_fields = $this->_unique_fields;
		$add_fields = $this->get_add_fields();

		if(!empty($required_fields))
		{
			foreach($add_fields as $add_field)
			{
				$field_name = $add_field->field_name;

                // Workaround as Codeigniter set_rules has a bug with array and doesn't work with required fields.
                // We are basically doing the check here!
                if (array_key_exists($field_name, $this->relation_n_n) && in_array($field_name, $required_fields)) {
                    if (!array_key_exists($field_name, $_POST)) {
                        // This will always throw an error!
                        $this->set_rules($field_name, $field_types[$field_name]->display_as, 'required');
                    }
                } else if(!isset($this->validation_rules[$field_name]) && in_array( $field_name, $required_fields) ) {
					$this->set_rules($field_name, $field_types[$field_name]->display_as, 'required');
				}
			}
		}

		/** Checking for unique fields. If the field value is not unique then
		 * return a validation error straight away, if not continue... */
		if(!empty($unique_fields))
		{
			$form_validation = $this->form_validation();

			foreach($add_fields as $add_field)
			{
				$field_name = $add_field->field_name;
				if(in_array( $field_name, $unique_fields) )
				{
					$form_validation->set_rules( $field_name,
							$field_types[$field_name]->display_as,
							'is_unique['.$this->basic_db_table.'.'.$field_name.']');
				}
			}

			if(!$form_validation->run())
			{
				$validation_result->error_message = $form_validation->error_string();
				$validation_result->error_fields = $form_validation->_error_array;

				return $validation_result;
			}
		}

		if(!empty($this->validation_rules))
		{
			$form_validation = $this->form_validation();

			$add_fields = $this->get_add_fields();

			foreach($add_fields as $add_field)
			{
				$field_name = $add_field->field_name;
				if(isset($this->validation_rules[$field_name]))
				{
					$rule = $this->validation_rules[$field_name];
					$form_validation->set_rules($rule['field'],$rule['label'],$rule['rules'],$rule['errors']);
				}
			}

			if($form_validation->run())
			{
				$validation_result->success = true;
			}
			else
			{
				$validation_result->error_message = $form_validation->error_string();
				$validation_result->error_fields = $form_validation->_error_array;
			}
		}
		else
		{
			$validation_result->success = true;
		}

		return $validation_result;
	}
	
	/*
		Another model driver
	*/
	
	//override
	protected function set_default_Model()
	{
		$ci = &get_instance();
		$ci->load->model('Grocery_crud_model');
		$ci->load->model('Grocery_crud_model_mod');

		$this->basic_model = new grocery_CRUD_model_mod();
	}	
}

?>