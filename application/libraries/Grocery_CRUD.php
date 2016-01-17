<?php
/**
 * PHP grocery CRUD
 *
 * A Codeigniter library that creates a CRUD automatically with just few lines of code.
 *
 * Copyright (C) 2010 - 2014  John Skoumbourdis.
 *
 * LICENSE
 *
 * Grocery CRUD is released with dual licensing, using the GPL v3 (license-gpl3.txt) and the MIT license (license-mit.txt).
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @version    	1.5.4
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */

// ------------------------------------------------------------------------

/**
 * grocery Field Types
 *
 * The types of the fields and the default reactions
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @license     https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @link		http://www.grocerycrud.com/documentation
 */
class grocery_CRUD_Field_Types
{
	/**
	 * Gets the field types of the main table.
	 * @return array
	 */
	public function get_field_types()
	{
		if ($this->field_types !== null) {
			return $this->field_types;
		}

		$types	= array();
		foreach($this->basic_model->get_field_types_basic_table() as $field_info)
		{
			$field_info->required = !empty($this->required_fields) && in_array($field_info->name,$this->required_fields) ? true : false;

			$field_info->display_as =
				isset($this->display_as[$field_info->name]) ?
					$this->display_as[$field_info->name] :
					ucfirst(str_replace("_"," ",$field_info->name));

			if($this->change_field_type !== null && isset($this->change_field_type[$field_info->name]))
			{
				$field_type 			= $this->change_field_type[$field_info->name];

				if (isset($this->relation[$field_info->name])) {
					$field_info->crud_type = "relation_".$field_type->type;
				}
				elseif (isset($this->upload_fields[$field_info->name])) {
					$field_info->crud_type = "upload_file_".$field_type->type;
				} else {
					$field_info->crud_type 	= $field_type->type;
					$field_info->extras 	=  $field_type->extras;
				}

				$real_type				= $field_info->crud_type;
			}
			elseif(isset($this->relation[$field_info->name]))
			{
				$real_type				= 'relation';
				$field_info->crud_type 	= 'relation';
			}
			elseif(isset($this->upload_fields[$field_info->name]))
			{
				$real_type				= 'upload_file';
				$field_info->crud_type 	= 'upload_file';
			}
			else
			{
				$real_type = $this->get_type($field_info);
				$field_info->crud_type = $real_type;
			}

			switch ($real_type) {
				case 'text':
					if(!empty($this->unset_texteditor) && in_array($field_info->name,$this->unset_texteditor))
						$field_info->extras = false;
					else
						$field_info->extras = 'text_editor';
				break;

				case 'relation':
				case 'relation_readonly':
					$field_info->extras 	= $this->relation[$field_info->name];
				break;

				case 'upload_file':
				case 'upload_file_readonly':
					$field_info->extras 	= $this->upload_fields[$field_info->name];
				break;

				default:
					if(empty($field_info->extras))
						$field_info->extras = false;
				break;
			}

			$types[$field_info->name] = $field_info;
		}

		if(!empty($this->relation_n_n))
		{
			foreach($this->relation_n_n as $field_name => $field_extras)
			{
				$is_read_only = $this->change_field_type !== null
								&& isset($this->change_field_type[$field_name])
								&& $this->change_field_type[$field_name]->type == 'readonly'
									? true : false;
				$field_info = (object)array();
				$field_info->name		= $field_name;
				$field_info->crud_type 	= $is_read_only ? 'readonly' : 'relation_n_n';
				$field_info->extras 	= $field_extras;
				$field_info->required	= !empty($this->required_fields) && in_array($field_name,$this->required_fields) ? true : false;;
				$field_info->display_as =
					isset($this->display_as[$field_name]) ?
						$this->display_as[$field_name] :
						ucfirst(str_replace("_"," ",$field_name));

				$types[$field_name] = $field_info;
			}
		}

		if(!empty($this->add_fields))
			foreach($this->add_fields as $field_object)
			{
				$field_name = isset($field_object->field_name) ? $field_object->field_name : $field_object;

				if(!isset($types[$field_name]))//Doesn't exist in the database? Create it for the CRUD
				{
					$extras = false;
					if($this->change_field_type !== null && isset($this->change_field_type[$field_name]))
					{
						$field_type = $this->change_field_type[$field_name];
						$extras 	=  $field_type->extras;
					}

					$field_info = (object)array(
						'name' => $field_name,
						'crud_type' => $this->change_field_type !== null && isset($this->change_field_type[$field_name]) ?
											$this->change_field_type[$field_name]->type :
											'string',
						'display_as' => isset($this->display_as[$field_name]) ?
												$this->display_as[$field_name] :
												ucfirst(str_replace("_"," ",$field_name)),
						'required'	=> !empty($this->required_fields) && in_array($field_name,$this->required_fields) ? true : false,
						'extras'	=> $extras
					);

					$types[$field_name] = $field_info;
				}
			}

		if(!empty($this->edit_fields))
			foreach($this->edit_fields as $field_object)
			{
				$field_name = isset($field_object->field_name) ? $field_object->field_name : $field_object;

				if(!isset($types[$field_name]))//Doesn't exist in the database? Create it for the CRUD
				{
					$extras = false;
					if($this->change_field_type !== null && isset($this->change_field_type[$field_name]))
					{
						$field_type = $this->change_field_type[$field_name];
						$extras 	=  $field_type->extras;
					}

					$field_info = (object)array(
						'name' => $field_name,
						'crud_type' => $this->change_field_type !== null && isset($this->change_field_type[$field_name]) ?
											$this->change_field_type[$field_name]->type :
											'string',
						'display_as' => isset($this->display_as[$field_name]) ?
												$this->display_as[$field_name] :
												ucfirst(str_replace("_"," ",$field_name)),
						'required'	=> in_array($field_name,$this->required_fields) ? true : false,
						'extras'	=> $extras
					);

					$types[$field_name] = $field_info;
				}
			}

		$this->field_types = $types;

		return $this->field_types;
	}

	public function get_primary_key()
	{
		return $this->basic_model->get_primary_key();
	}

	/**
	 * Get the html input for the specific field with the
	 * current value
	 *
	 * @param object $field_info
	 * @param string $value
	 * @return object
	 */
	protected function get_field_input($field_info, $value = null)
	{
			$real_type = $field_info->crud_type;

			$types_array = array(
					'integer',
					'text',
					'true_false',
					'string',
					'date',
					'datetime',
					'enum',
					'set',
					'relation',
					'relation_readonly',
					'relation_n_n',
					'upload_file',
					'upload_file_readonly',
					'hidden',
					'password',
					'readonly',
					'dropdown',
					'multiselect'
			);

			if (in_array($real_type,$types_array)) {
				/* A quick way to go to an internal method of type $this->get_{type}_input .
				 * For example if the real type is integer then we will use the method
				 * $this->get_integer_input
				 *  */
				$field_info->input = $this->{"get_".$real_type."_input"}($field_info,$value);
			}
			else
			{
				$field_info->input = $this->get_string_input($field_info,$value);
			}

		return $field_info;
	}

	protected function change_list_value($field_info, $value = null)
	{
		$real_type = $field_info->crud_type;

		switch ($real_type) {
			case 'hidden':
			case 'invisible':
			case 'integer':

			break;
			case 'true_false':
				if(is_array($field_info->extras) && array_key_exists($value,$field_info->extras)) {
					$value = $field_info->extras[$value];
				} else if(isset($this->default_true_false_text[$value])) {
					$value = $this->default_true_false_text[$value];
				}
			break;
			case 'string':
				$value = $this->character_limiter($value,$this->character_limiter,"...");
			break;
			case 'text':
				$value = $this->character_limiter(strip_tags($value),$this->character_limiter,"...");
			break;
			case 'date':
				if(!empty($value) && $value != '0000-00-00' && $value != '1970-01-01')
				{
					list($year,$month,$day) = explode("-",$value);

					$value = date($this->php_date_format, mktime (0, 0, 0, (int)$month , (int)$day , (int)$year));
				}
				else
				{
					$value = '';
				}
			break;
			case 'datetime':
				if(!empty($value) && $value != '0000-00-00 00:00:00' && $value != '1970-01-01 00:00:00')
				{
					list($year,$month,$day) = explode("-",$value);
					list($hours,$minutes) = explode(":",substr($value,11));

					$value = date($this->php_date_format." - H:i", mktime ((int)$hours , (int)$minutes , 0, (int)$month , (int)$day ,(int)$year));
				}
				else
				{
					$value = '';
				}
			break;
			case 'enum':
				$value = $this->character_limiter($value,$this->character_limiter,"...");
			break;

			case 'multiselect':
				$value_as_array = array();
				foreach(explode(",",$value) as $row_value)
				{
					$value_as_array[] = array_key_exists($row_value,$field_info->extras) ? $field_info->extras[$row_value] : $row_value;
				}
				$value = implode(",",$value_as_array);
			break;

			case 'relation_n_n':
				$value = $this->character_limiter(str_replace(',',', ',$value),$this->character_limiter,"...");
			break;

			case 'password':
				$value = '******';
			break;

			case 'dropdown':
				$value = array_key_exists($value,$field_info->extras) ? $field_info->extras[$value] : $value;
			break;

			case 'upload_file':
				if(empty($value))
				{
					$value = "";
				}
				else
				{
					$is_image = !empty($value) &&
					( substr($value,-4) == '.jpg'
							|| substr($value,-4) == '.png'
							|| substr($value,-5) == '.jpeg'
							|| substr($value,-4) == '.gif'
							|| substr($value,-5) == '.tiff')
							? true : false;

					$file_url = base_url().$field_info->extras->upload_path."/$value";

					$file_url_anchor = '<a href="'.$file_url.'"';
					if($is_image)
					{
						$file_url_anchor .= ' class="image-thumbnail"><img src="'.$file_url.'" height="50px">';
					}
					else
					{
						$file_url_anchor .= ' target="_blank">'.$this->character_limiter($value,$this->character_limiter,'...',true);
					}
					$file_url_anchor .= '</a>';

					$value = $file_url_anchor;
				}
			break;

			default:
				$value = $this->character_limiter($value,$this->character_limiter,"...");
			break;
		}

		return $value;
	}

	/**
	 * Character Limiter of codeigniter (I just don't want to load the helper )
	 *
	 * Limits the string based on the character count.  Preserves complete words
	 * so the character count may not be exactly as specified.
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @param	string	the end character. Usually an ellipsis
	 * @return	string
	 */
	function character_limiter($str, $n = 500, $end_char = '&#8230;')
	{
		if (strlen($str) < $n)
		{
			return $str;
		}

		// a bit complicated, but faster than preg_replace with \s+
		$str = preg_replace('/ {2,}/', ' ', str_replace(array("\r", "\n", "\t", "\x0B", "\x0C"), ' ', $str));

		if (strlen($str) <= $n)
		{
			return $str;
		}

		$out = '';
		foreach (explode(' ', trim($str)) as $val)
		{
			$out .= $val.' ';

			if (strlen($out) >= $n)
			{
				$out = trim($out);
				return (strlen($out) === strlen($str)) ? $out : $out.$end_char;
			}
		}
	}

	protected function get_type($db_type)
	{
		$type = false;
		if(!empty($db_type->type))
		{
			switch ($db_type->type) {
				case '1':
				case '3':
				case 'int':
				case 'tinyint':
				case 'mediumint':
				case 'longint':
					if( $db_type->db_type == 'tinyint' && $db_type->db_max_length ==  1)
						$type = 'true_false';
					else
						$type = 'integer';
				break;
				case '254':
				case 'string':
				case 'enum':
					if($db_type->db_type != 'enum')
						$type = 'string';
					else
						$type = 'enum';
				break;
				case 'set':
					if($db_type->db_type != 'set')
						$type = 'string';
					else
						$type = 'set';
				break;
				case '252':
				case 'blob':
				case 'text':
				case 'mediumtext':
				case 'longtext':
					$type = 'text';
				break;
				case '10':
				case 'date':
					$type = 'date';
				break;
				case '12':
				case 'datetime':
				case 'timestamp':
					$type = 'datetime';
				break;
			}
		}
		return $type;
	}
}

// ------------------------------------------------------------------------

/**
 * Grocery Model Driver
 *
 * Drives the model - I'ts so easy like you drive a bicycle :-)
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @version    	1.5.4
 * @link		http://www.grocerycrud.com/documentation
 */
class grocery_CRUD_Model_Driver extends grocery_CRUD_Field_Types
{
	/**
	 * @var Grocery_crud_model
	 */
	public $basic_model = null;

	protected function set_default_Model()
	{
		$ci = &get_instance();
		$ci->load->model('Grocery_crud_model');

		$this->basic_model = new Grocery_crud_model();
	}

	protected function get_total_results()
	{
		if(!empty($this->where))
			foreach($this->where as $where)
				$this->basic_model->where($where[0],$where[1],$where[2]);

		if(!empty($this->or_where))
			foreach($this->or_where as $or_where)
				$this->basic_model->or_where($or_where[0],$or_where[1],$or_where[2]);

		if(!empty($this->like))
			foreach($this->like as $like)
				$this->basic_model->like($like[0],$like[1],$like[2]);

		if(!empty($this->or_like))
			foreach($this->or_like as $or_like)
				$this->basic_model->or_like($or_like[0],$or_like[1],$or_like[2]);

		if(!empty($this->having))
			foreach($this->having as $having)
				$this->basic_model->having($having[0],$having[1],$having[2]);

		if(!empty($this->or_having))
			foreach($this->or_having as $or_having)
				$this->basic_model->or_having($or_having[0],$or_having[1],$or_having[2]);

		if(!empty($this->relation))
			foreach($this->relation as $relation)
				$this->basic_model->join_relation($relation[0],$relation[1],$relation[2]);

		if(!empty($this->relation_n_n))
		{
			$columns = $this->get_columns();
			foreach($columns as $column)
			{
				//Use the relation_n_n ONLY if the column is called . The set_relation_n_n are slow and it will make the table slower without any reason as we don't need those queries.
				if(isset($this->relation_n_n[$column->field_name]))
				{
					$this->basic_model->set_relation_n_n_field($this->relation_n_n[$column->field_name]);
				}
			}

		}

		return $this->basic_model->get_total_results();
	}

	public function set_model($model_name)
	{
		$ci = &get_instance();
		$ci->load->model('Grocery_crud_model');

		$ci->load->model($model_name);

		$temp = explode('/',$model_name);
		krsort($temp);
		foreach($temp as $t)
		{
			$real_model_name = $t;
			break;
		}

		$this->basic_model = $ci->$real_model_name;
	}

	protected function set_ajax_list_queries($state_info = null)
	{
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
                            foreach ($temp_relation[$search_field] as $relation_field) {
                                $this->or_like($relation_field , $search_text);
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
				} else {
					$this->like($state_info->search->field , $state_info->search->text);
				}
			}
			else
			{
				$columns = $this->get_columns();

				$search_text = $state_info->search->text;

				if(!empty($this->where))
					foreach($this->where as $where)
						$this->basic_model->having($where[0],$where[1],$where[2]);

				foreach($columns as $column)
				{
					if(isset($temp_relation[$column->field_name]))
					{
						if(is_array($temp_relation[$column->field_name]))
						{
							foreach($temp_relation[$column->field_name] as $search_field)
							{
								$this->or_like($search_field, $search_text);
							}
						}
						else
						{
							$this->or_like($temp_relation[$column->field_name], $search_text);
						}
					}
					elseif(isset($this->relation_n_n[$column->field_name]))
					{
						//@todo have a where for the relation_n_n statement
					}
					else
					{
						$this->or_like($column->field_name, $search_text);
					}
				}
			}
		}
	}

	protected function table_exists($table_name = null)
	{
		if($this->basic_model->db_table_exists($table_name))
			return true;
		return false;
	}

	protected function get_relation_array($relation_info, $primary_key_value = null, $limit = null)
	{
		list($field_name , $related_table , $related_field_title, $where_clause, $order_by)  = $relation_info;

		if($primary_key_value !== null)
		{
			$primary_key = $this->basic_model->get_primary_key($related_table);

			//A where clause with the primary key is enough to take the selected key row
			$where_clause = array($primary_key => $primary_key_value);
		}

		$relation_array = $this->basic_model->get_relation_array($field_name , $related_table , $related_field_title, $where_clause, $order_by, $limit);

		return $relation_array;
	}

	protected function get_relation_total_rows($relation_info)
	{
		list($field_name , $related_table , $related_field_title, $where_clause)  = $relation_info;

		$relation_array = $this->basic_model->get_relation_total_rows($field_name , $related_table , $related_field_title, $where_clause);

		return $relation_array;
	}

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
				if(!isset($this->validation_rules[$field_name]) && in_array( $field_name, $required_fields) )
				{
					$this->set_rules( $field_name, $field_types[$field_name]->display_as, 'required');
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
					$form_validation->set_rules($rule['field'],$rule['label'],$rule['rules']);
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

	protected function form_validation()
	{
		if($this->form_validation === null)
		{
			$this->form_validation = new grocery_CRUD_Form_validation();
			$ci = &get_instance();
			$ci->load->library('form_validation');
			$ci->form_validation = $this->form_validation;
		}
		return $this->form_validation;
	}

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
					$this->set_rules( $field_name, $field_types[$field_name]->display_as, 'required');
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

					if(!isset($row->$field_name)) {
						throw new Exception("The field name doesn't exist in the database. ".
								 			"Please use the unique fields only for fields ".
											"that exist in the database");
					}

					$previous_field_name_value = $row->$field_name;

					if(!empty($previous_field_name_value) && $previous_field_name_value != $field_name_value) {
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
					$form_validation->set_rules($rule['field'],$rule['label'],$rule['rules']);
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

	protected function db_insert($state_info)
	{
		$validation_result = $this->db_insert_validation();

		if($validation_result->success)
		{
			$post_data = $state_info->unwrapped_data;

			$add_fields = $this->get_add_fields();

			if($this->callback_insert === null)
			{
				if($this->callback_before_insert !== null)
				{
					$callback_return = call_user_func($this->callback_before_insert, $post_data);

					if(!empty($callback_return) && is_array($callback_return))
						$post_data = $callback_return;
					elseif($callback_return === false)
						return false;
				}

				$insert_data = array();
				$types = $this->get_field_types();
				foreach($add_fields as $num_row => $field)
				{
					/* If the multiselect or the set is empty then the browser doesn't send an empty array. Instead it sends nothing */
					if(isset($types[$field->field_name]->crud_type) && ($types[$field->field_name]->crud_type == 'set' || $types[$field->field_name]->crud_type == 'multiselect') && !isset($post_data[$field->field_name]))
					{
						$post_data[$field->field_name] = array();
					}

					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
					{
						if(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && is_array($post_data[$field->field_name]) && empty($post_data[$field->field_name]))
						{
							$insert_data[$field->field_name] = null;
						}
						elseif(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && $post_data[$field->field_name] === '')
						{
							$insert_data[$field->field_name] = null;
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'date')
						{
							$insert_data[$field->field_name] = $this->_convert_date_to_sql_date($post_data[$field->field_name]);
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'readonly')
						{
							//This empty if statement is to make sure that a readonly field will never inserted/updated
						}
						elseif(isset($types[$field->field_name]->crud_type) && ($types[$field->field_name]->crud_type == 'set' || $types[$field->field_name]->crud_type == 'multiselect'))
						{
							$insert_data[$field->field_name] = !empty($post_data[$field->field_name]) ? implode(',',$post_data[$field->field_name]) : '';
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'datetime'){
							$insert_data[$field->field_name] = $this->_convert_date_to_sql_date(substr($post_data[$field->field_name],0,10)).
																		substr($post_data[$field->field_name],10);
						}
						else
						{
							$insert_data[$field->field_name] = $post_data[$field->field_name];
						}
					}
				}

				$insert_result =  $this->basic_model->db_insert($insert_data);

				if($insert_result !== false)
				{
					$insert_primary_key = $insert_result;
				}
				else
				{
					return false;
				}

				if(!empty($this->relation_n_n))
				{
					foreach($this->relation_n_n as $field_name => $field_info)
					{
						$relation_data = isset( $post_data[$field_name] ) ? $post_data[$field_name] : array() ;
						$this->db_relation_n_n_update($field_info, $relation_data  ,$insert_primary_key);
					}
				}

				if($this->callback_after_insert !== null)
				{
					$callback_return = call_user_func($this->callback_after_insert, $post_data, $insert_primary_key);

					if($callback_return === false)
					{
						return false;
					}

				}
			}else
			{
					$callback_return = call_user_func($this->callback_insert, $post_data);

					if($callback_return === false)
					{
						return false;
					}
			}

			if(isset($insert_primary_key))
				return $insert_primary_key;
			else
				return true;
		}

		return false;

	}

	protected function db_update($state_info)
	{
		$validation_result = $this->db_update_validation();

		$edit_fields = $this->get_edit_fields();

		if($validation_result->success)
		{
			$post_data 		= $state_info->unwrapped_data;
			$primary_key 	= $state_info->primary_key;

			if($this->callback_update === null)
			{
				if($this->callback_before_update !== null)
				{
					$callback_return = call_user_func($this->callback_before_update, $post_data, $primary_key);

					if(!empty($callback_return) && is_array($callback_return))
					{
						$post_data = $callback_return;
					}
					elseif($callback_return === false)
					{
						return false;
					}

				}

				$update_data = array();
				$types = $this->get_field_types();
				foreach($edit_fields as $num_row => $field)
				{
					/* If the multiselect or the set is empty then the browser doesn't send an empty array. Instead it sends nothing */
					if(isset($types[$field->field_name]->crud_type) && ($types[$field->field_name]->crud_type == 'set' || $types[$field->field_name]->crud_type == 'multiselect') && !isset($post_data[$field->field_name]))
					{
						$post_data[$field->field_name] = array();
					}

					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
					{
						if(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && is_array($post_data[$field->field_name]) && empty($post_data[$field->field_name]))
						{
							$update_data[$field->field_name] = null;
						}
						elseif(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && $post_data[$field->field_name] === '')
						{
							$update_data[$field->field_name] = null;
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'date')
						{
							$update_data[$field->field_name] = $this->_convert_date_to_sql_date($post_data[$field->field_name]);
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'readonly')
						{
							//This empty if statement is to make sure that a readonly field will never inserted/updated
						}
						elseif(isset($types[$field->field_name]->crud_type) && ($types[$field->field_name]->crud_type == 'set' || $types[$field->field_name]->crud_type == 'multiselect'))
						{
							$update_data[$field->field_name] = !empty($post_data[$field->field_name]) ? implode(',',$post_data[$field->field_name]) : '';
						}
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'datetime'){
							$update_data[$field->field_name] = $this->_convert_date_to_sql_date(substr($post_data[$field->field_name],0,10)).
																		substr($post_data[$field->field_name],10);
						}
						else
						{
							$update_data[$field->field_name] = $post_data[$field->field_name];
						}
					}
				}

				if($this->basic_model->db_update($update_data, $primary_key) === false)
				{
					return false;
				}

				if(!empty($this->relation_n_n))
				{
					foreach($this->relation_n_n as $field_name => $field_info)
					{
						if (   $this->unset_edit_fields !== null
							&& is_array($this->unset_edit_fields)
							&& in_array($field_name,$this->unset_edit_fields)
						) {
								continue;
						}

						$relation_data = isset( $post_data[$field_name] ) ? $post_data[$field_name] : array() ;
						$this->db_relation_n_n_update($field_info, $relation_data ,$primary_key);
					}
				}

				if($this->callback_after_update !== null)
				{
					$callback_return = call_user_func($this->callback_after_update, $post_data, $primary_key);

					if($callback_return === false)
					{
						return false;
					}

				}
			}
			else
			{
				$callback_return = call_user_func($this->callback_update, $post_data, $primary_key);

				if($callback_return === false)
				{
					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	protected function _convert_date_to_sql_date($date)
	{
		$date = substr($date,0,10);
		if(preg_match('/\d{4}-\d{2}-\d{2}/',$date))
		{
			//If it's already a sql-date don't convert it!
			return $date;
		}elseif(empty($date))
		{
			return '';
		}

		$date_array = preg_split( '/[-\.\/ ]/', $date);
		if($this->php_date_format == 'd/m/Y')
		{
			$sql_date = date('Y-m-d',mktime(0,0,0,$date_array[1],$date_array[0],$date_array[2]));
		}
		elseif($this->php_date_format == 'm/d/Y')
		{
			$sql_date = date('Y-m-d',mktime(0,0,0,$date_array[0],$date_array[1],$date_array[2]));
		}
		else
		{
			$sql_date = $date;
		}

		return $sql_date;
	}

	protected function _get_field_names_to_search(array $relation_values)
	{
		if(!strstr($relation_values[2],'{'))
			return $this->_unique_join_name($relation_values[0]).'.'.$relation_values[2];
		else
		{
			$relation_values[2] = ' '.$relation_values[2].' ';
			$temp1 = explode('{',$relation_values[2]);
			unset($temp1[0]);

			$field_names_array = array();
			foreach($temp1 as $field)
				list($field_names_array[]) = explode('}',$field);

			return $field_names_array;
		}
	}

    protected function _unique_join_name($field_name)
    {
    	return 'j'.substr(md5($field_name),0,8); //This j is because is better for a string to begin with a letter and not a number
    }

    protected function _unique_field_name($field_name)
    {
    	return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not a number
    }

    protected function db_multiple_delete($state_info)
    {
        foreach ($state_info->ids as $delete_id) {
            $result = $this->db_delete((object)array('primary_key' => $delete_id));
            if (!$result) {
                return false;
            }
        }

        return true;
    }

	protected function db_delete($state_info)
	{
		$primary_key_value 	= $state_info->primary_key;

		if($this->callback_delete === null)
		{
			if($this->callback_before_delete !== null)
			{
				$callback_return = call_user_func($this->callback_before_delete, $primary_key_value);

				if($callback_return === false)
				{
					return false;
				}

			}

			if(!empty($this->relation_n_n))
			{
				foreach($this->relation_n_n as $field_name => $field_info)
				{
					$this->db_relation_n_n_delete( $field_info, $primary_key_value );
				}
			}

			$delete_result = $this->basic_model->db_delete($primary_key_value);

			if($delete_result === false)
			{
				return false;
			}

			if($this->callback_after_delete !== null)
			{
				$callback_return = call_user_func($this->callback_after_delete, $primary_key_value);

				if($callback_return === false)
				{
					return false;
				}

			}
		}
		else
		{
			$callback_return = call_user_func($this->callback_delete, $primary_key_value);

			if($callback_return === false)
			{
				return false;
			}
		}

		return true;
	}

	protected function db_relation_n_n_update($field_info, $post_data , $primary_key_value)
	{
		$this->basic_model->db_relation_n_n_update($field_info, $post_data , $primary_key_value);
	}

	protected function db_relation_n_n_delete($field_info, $primary_key_value)
	{
		$this->basic_model->db_relation_n_n_delete($field_info, $primary_key_value);
	}

	protected function get_list()
	{
		if(!empty($this->order_by))
			$this->basic_model->order_by($this->order_by[0],$this->order_by[1]);

		if(!empty($this->where))
			foreach($this->where as $where)
				$this->basic_model->where($where[0],$where[1],$where[2]);

		if(!empty($this->or_where))
			foreach($this->or_where as $or_where)
				$this->basic_model->or_where($or_where[0],$or_where[1],$or_where[2]);

		if(!empty($this->like))
			foreach($this->like as $like)
				$this->basic_model->like($like[0],$like[1],$like[2]);

		if(!empty($this->or_like))
			foreach($this->or_like as $or_like)
				$this->basic_model->or_like($or_like[0],$or_like[1],$or_like[2]);

		if(!empty($this->having))
			foreach($this->having as $having)
				$this->basic_model->having($having[0],$having[1],$having[2]);

		if(!empty($this->or_having))
			foreach($this->or_having as $or_having)
				$this->basic_model->or_having($or_having[0],$or_having[1],$or_having[2]);

		if(!empty($this->relation))
			foreach($this->relation as $relation)
				$this->basic_model->join_relation($relation[0],$relation[1],$relation[2]);

		if(!empty($this->relation_n_n))
		{
			$columns = $this->get_columns();
			foreach($columns as $column)
			{
				//Use the relation_n_n ONLY if the column is called . The set_relation_n_n are slow and it will make the table slower without any reason as we don't need those queries.
				if(isset($this->relation_n_n[$column->field_name]))
				{
					$this->basic_model->set_relation_n_n_field($this->relation_n_n[$column->field_name]);
				}
			}

		}

		if($this->theme_config['crud_paging'] === true)
		{
			if($this->limit === null)
			{
				$default_per_page = $this->config->default_per_page;
				if(is_numeric($default_per_page) && $default_per_page >1)
				{
					$this->basic_model->limit($default_per_page);
				}
				else
				{
					$this->basic_model->limit(10);
				}
			}
			else
			{
				$this->basic_model->limit($this->limit[0],$this->limit[1]);
			}
		}

		$results = $this->basic_model->get_list();

		return $results;
	}

	protected function get_edit_values($primary_key_value)
	{
		$values = $this->basic_model->get_edit_values($primary_key_value);

		if(!empty($this->relation_n_n))
		{
			foreach($this->relation_n_n as $field_name => $field_info)
			{
				$values->$field_name = $this->get_relation_n_n_selection_array($primary_key_value, $field_info);
			}
		}

		return $values;
	}

	protected function get_relation_n_n_selection_array($primary_key_value, $field_info)
	{
		return $this->basic_model->get_relation_n_n_selection_array($primary_key_value, $field_info);
	}

	protected function get_relation_n_n_unselected_array($field_info, $selected_values)
	{
		return $this->basic_model->get_relation_n_n_unselected_array($field_info, $selected_values);
	}

	protected function set_basic_db_table($table_name = null)
	{
		$this->basic_model->set_basic_table($table_name);
	}

	protected function upload_file($state_info)
	{
		if(isset($this->upload_fields[$state_info->field_name]) )
		{
			if($this->callback_upload === null)
			{
				if($this->callback_before_upload !== null)
				{
					$callback_before_upload_response = call_user_func($this->callback_before_upload, $_FILES,  $this->upload_fields[$state_info->field_name]);

					if($callback_before_upload_response === false)
						return false;
					elseif(is_string($callback_before_upload_response))
						return $callback_before_upload_response;
				}

				$upload_info = $this->upload_fields[$state_info->field_name];

				header('Pragma: no-cache');
				header('Cache-Control: private, no-cache');
				header('Content-Disposition: inline; filename="files.json"');
				header('X-Content-Type-Options: nosniff');
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
				header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

				$allowed_files = $this->config->file_upload_allow_file_types;

		                $reg_exp = '';
		                if(!empty($upload_info->allowed_file_types)){
		                    $reg_exp = '/(\\.|\\/)('.$upload_info->allowed_file_types.')$/i';
		                }else{
		                    $reg_exp = '/(\\.|\\/)('.$allowed_files.')$/i';
		                }

				$max_file_size_ui = $this->config->file_upload_max_file_size;
				$max_file_size_bytes = $this->_convert_bytes_ui_to_bytes($max_file_size_ui);

				$options = array(
					'upload_dir' 		=> $upload_info->upload_path.'/',
					'param_name'		=> $this->_unique_field_name($state_info->field_name),
					'upload_url'		=> base_url().$upload_info->upload_path.'/',
					'accept_file_types' => $reg_exp,
					'max_file_size'		=> $max_file_size_bytes
				);
				$upload_handler = new UploadHandler($options);
				$upload_handler->default_config_path = $this->default_config_path;
				$uploader_response = $upload_handler->post();

				if(is_array($uploader_response))
				{
					foreach($uploader_response as &$response)
					{
						unset($response->delete_url);
						unset($response->delete_type);
					}
				}

				if($this->callback_after_upload !== null)
				{
					$callback_after_upload_response = call_user_func($this->callback_after_upload, $uploader_response ,  $this->upload_fields[$state_info->field_name] , $_FILES );

					if($callback_after_upload_response === false)
						return false;
					elseif(is_string($callback_after_upload_response))
						return $callback_after_upload_response;
					elseif(is_array($callback_after_upload_response))
						$uploader_response = $callback_after_upload_response;
				}

				return $uploader_response;
			}
			else
			{
				$upload_response = call_user_func($this->callback_upload, $_FILES, $this->upload_fields[$state_info->field_name] );

				if($upload_response === false)
				{
					return false;
				}
				else
				{
					return $upload_response;
				}
			}
		}
		else
		{
			return false;
		}
	}

	protected function delete_file($state_info)
	{

		if(isset($state_info->field_name) && isset($this->upload_fields[$state_info->field_name]))
		{
			$upload_info = $this->upload_fields[$state_info->field_name];

			if(file_exists("{$upload_info->upload_path}/{$state_info->file_name}"))
			{
				if( unlink("{$upload_info->upload_path}/{$state_info->file_name}") )
				{
					$this->basic_model->db_file_delete($state_info->field_name, $state_info->file_name);

					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$this->basic_model->db_file_delete($state_info->field_name, $state_info->file_name);
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	protected function ajax_relation($state_info)
	{
		if(!isset($this->relation[$state_info->field_name]))
			return false;

		list($field_name, $related_table, $related_field_title, $where_clause, $order_by)  = $this->relation[$state_info->field_name];

		return $this->basic_model->get_ajax_relation_array($state_info->search, $field_name, $related_table, $related_field_title, $where_clause, $order_by);
	}
}


/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * Grocery CRUD is released with dual licensing, using the GPL v3 (license-gpl3.txt) and the MIT license (license-mit.txt).
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */

// ------------------------------------------------------------------------

/**
 * PHP grocery Layout
 *
 * Here you manage all the HTML Layout
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @version    	1.5.4
 */
class grocery_CRUD_Layout extends grocery_CRUD_Model_Driver
{
	private $theme_path 				= null;
	private $views_as_string			= '';
	private $echo_and_die				= false;
	protected $theme 					= null;
	protected $default_true_false_text 	= array('inactive' , 'active');

	protected $css_files				= array();
	protected $js_files					= array();
	protected $js_lib_files				= array();
	protected $js_config_files			= array();

	protected function set_basic_Layout()
	{
		if(!file_exists($this->theme_path.$this->theme.'/views/list_template.php'))
		{
			throw new Exception('The template does not exist. Please check your files and try again.', 12);
			die();
		}
	}

	protected function showList($ajax = false, $state_info = null)
	{
		$data = $this->get_common_data();

		$data->order_by 	= $this->order_by;

		$data->types 		= $this->get_field_types();

		$data->list = $this->get_list();
		$data->list = $this->change_list($data->list , $data->types);
		$data->list = $this->change_list_add_actions($data->list);

		$data->total_results = $this->get_total_results();

		$data->columns 				= $this->get_columns();

		$data->success_message		= $this->get_success_message_at_list($state_info);

		$data->primary_key 			= $this->get_primary_key();
		$data->add_url				= $this->getAddUrl();
		$data->edit_url				= $this->getEditUrl();
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
		$data->unset_edit			= $this->unset_edit;
		$data->unset_read			= $this->unset_read;
		$data->unset_delete			= $this->unset_delete;
		$data->unset_export			= $this->unset_export;
		$data->unset_print			= $this->unset_print;

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
	}

	protected function exportToExcel($state_info = null)
	{
		$data = $this->get_common_data();

		$data->order_by 	= $this->order_by;
		$data->types 		= $this->get_field_types();

		$data->list = $this->get_list();
		$data->list = $this->change_list($data->list , $data->types);
		$data->list = $this->change_list_add_actions($data->list);

		$data->total_results = $this->get_total_results();

		$data->columns 				= $this->get_columns();
		$data->primary_key 			= $this->get_primary_key();

		@ob_end_clean();
		$this->_export_to_excel($data);
	}

	protected function _export_to_excel($data)
	{
		/**
		 * No need to use an external library here. The only bad thing without using external library is that Microsoft Excel is complaining
		 * that the file is in a different format than specified by the file extension. If you press "Yes" everything will be just fine.
		 * */

		$string_to_export = "";
		foreach($data->columns as $column){
			$string_to_export .= $column->display_as."\t";
		}
		$string_to_export .= "\n";

		foreach($data->list as $num_row => $row){
			foreach($data->columns as $column){
				$string_to_export .= $this->_trim_export_string($row->{$column->field_name})."\t";
			}
			$string_to_export .= "\n";
		}

		// Convert to UTF-16LE and Prepend BOM
		$string_to_export = "\xFF\xFE" .mb_convert_encoding($string_to_export, 'UTF-16LE', 'UTF-8');

		$filename = "export-".date("Y-m-d_H:i:s").".xls";

		header('Content-type: application/vnd.ms-excel;charset=UTF-16LE');
		header('Content-Disposition: attachment; filename='.$filename);
		header("Cache-Control: no-cache");
		echo $string_to_export;
		die();
	}

	protected function print_webpage($state_info = null)
	{
		$data = $this->get_common_data();

		$data->order_by 	= $this->order_by;
		$data->types 		= $this->get_field_types();

		$data->list = $this->get_list();
		$data->list = $this->change_list($data->list , $data->types);
		$data->list = $this->change_list_add_actions($data->list);

		$data->total_results = $this->get_total_results();

		$data->columns 				= $this->get_columns();
		$data->primary_key 			= $this->get_primary_key();

		@ob_end_clean();
		$this->_print_webpage($data);
	}

	protected function _print_webpage($data)
	{
		$string_to_print = "<meta charset=\"utf-8\" /><style type=\"text/css\" >
		#print-table{ color: #000; background: #fff; font-family: Verdana,Tahoma,Helvetica,sans-serif; font-size: 13px;}
		#print-table table tr td, #print-table table tr th{ border: 1px solid black; border-bottom: none; border-right: none; padding: 4px 8px 4px 4px}
		#print-table table{ border-bottom: 1px solid black; border-right: 1px solid black}
		#print-table table tr th{text-align: left;background: #ddd}
		#print-table table tr:nth-child(odd){background: #eee}
		</style>";
		$string_to_print .= "<div id='print-table'>";

		$string_to_print .= '<table width="100%" cellpadding="0" cellspacing="0" ><tr>';
		foreach($data->columns as $column){
			$string_to_print .= "<th>".$column->display_as."</th>";
		}
		$string_to_print .= "</tr>";

		foreach($data->list as $num_row => $row){
			$string_to_print .= "<tr>";
			foreach($data->columns as $column){
				$string_to_print .= "<td>".$this->_trim_print_string($row->{$column->field_name})."</td>";
			}
			$string_to_print .= "</tr>";
		}

		$string_to_print .= "</table></div>";

		echo $string_to_print;
		die();
	}

	protected function _trim_export_string($value)
	{
		$value = str_replace(array("&nbsp;","&amp;","&gt;","&lt;"),array(" ","&",">","<"),$value);
		return  strip_tags(str_replace(array("\t","\n","\r"),"",$value));
	}

	protected function _trim_print_string($value)
	{
		$value = str_replace(array("&nbsp;","&amp;","&gt;","&lt;"),array(" ","&",">","<"),$value);

		//If the value has only spaces and nothing more then add the whitespace html character
		if(str_replace(" ","",$value) == "")
			$value = "&nbsp;";

		return strip_tags($value);
	}

	protected function set_echo_and_die()
	{
		$this->echo_and_die = true;
	}

	protected function unset_echo_and_die()
	{
		$this->echo_and_die = false;
	}

	protected function showListInfo()
	{
		$this->set_echo_and_die();

		$total_results = (int)$this->get_total_results();
		@ob_end_clean();
		echo json_encode(array('total_results' => $total_results));
		die();
	}

	protected function change_list_add_actions($list)
	{
		if(empty($this->actions))
			return $list;

		$primary_key = $this->get_primary_key();

		foreach($list as $num_row => $row)
		{
			$actions_urls = array();
			foreach($this->actions as $unique_id => $action)
			{
				if(!empty($action->url_callback))
				{
					$actions_urls[$unique_id] = call_user_func($action->url_callback, $row->$primary_key, $row);
				}
				else
				{
					$actions_urls[$unique_id] =
						$action->url_has_http ?
							$action->link_url.$row->$primary_key :
							site_url($action->link_url.'/'.$row->$primary_key);
				}
			}
			$row->action_urls = $actions_urls;
		}

		return $list;
	}

	protected function change_list($list,$types)
	{
		$primary_key = $this->get_primary_key();
		$has_callbacks = !empty($this->callback_column) ? true : false;
		$output_columns = $this->get_columns();
		foreach($list as $num_row => $row)
		{
			foreach($output_columns as $column)
			{
				$field_name 	= $column->field_name;
				$field_value 	= isset( $row->{$column->field_name} ) ? $row->{$column->field_name} : null;
				if( $has_callbacks && isset($this->callback_column[$field_name]) )
					$list[$num_row]->$field_name = call_user_func($this->callback_column[$field_name], $field_value, $row);
				elseif(isset($types[$field_name]))
					$list[$num_row]->$field_name = $this->change_list_value($types[$field_name] , $field_value);
				else
					$list[$num_row]->$field_name = $field_value;
			}
		}

		return $list;
	}

	protected function showAddForm()
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);

		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();

		$data->list_url 		= $this->getListUrl();
		$data->insert_url		= $this->getInsertUrl();
		$data->validation_url	= $this->getValidationInsertUrl();
		$data->input_fields 	= $this->get_add_input_fields();

		$data->fields 			= $this->get_add_fields();
		$data->hidden_fields	= $this->get_add_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
		$data->unique_hash			= $this->get_method_hash();
		$data->is_ajax 			= $this->_is_ajax();

		$this->_theme_view('add.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");

		$this->_get_ajax_results();
	}

	protected function showEditForm($state_info)
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);

		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();

		$data->field_values = $this->get_edit_values($state_info->primary_key);

		$data->add_url		= $this->getAddUrl();

		$data->list_url 	= $this->getListUrl();
		$data->update_url	= $this->getUpdateUrl($state_info);
		$data->delete_url	= $this->getDeleteUrl($state_info);
		$data->read_url		= $this->getReadUrl($state_info->primary_key);
		$data->input_fields = $this->get_edit_input_fields($data->field_values);
		$data->unique_hash			= $this->get_method_hash();

		$data->fields 		= $this->get_edit_fields();
		$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;

		$data->validation_url	= $this->getValidationUpdateUrl($state_info->primary_key);
		$data->is_ajax 			= $this->_is_ajax();

		$this->_theme_view('edit.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");

		$this->_get_ajax_results();
	}

	protected function showReadForm($state_info)
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);

		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();

		$data->field_values = $this->get_edit_values($state_info->primary_key);

		$data->add_url		= $this->getAddUrl();

		$data->list_url 	= $this->getListUrl();
		$data->update_url	= $this->getUpdateUrl($state_info);
		$data->delete_url	= $this->getDeleteUrl($state_info);
		$data->read_url		= $this->getReadUrl($state_info->primary_key);
		$data->input_fields = $this->get_read_input_fields($data->field_values);
		$data->unique_hash			= $this->get_method_hash();

		$data->fields 		= $this->get_read_fields();
		$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;

		$data->validation_url	= $this->getValidationUpdateUrl($state_info->primary_key);
		$data->is_ajax 			= $this->_is_ajax();

		$this->_theme_view('read.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");

		$this->_get_ajax_results();
	}

	protected function delete_layout($delete_result = true)
	{
		@ob_end_clean();
		if($delete_result === false)
		{
			$error_message = '<p>'.$this->l('delete_error_message').'</p>';

			echo json_encode(array('success' => $delete_result ,'error_message' => $error_message));
		}
		else
		{
			$success_message = '<p>'.$this->l('delete_success_message').'</p>';

			echo json_encode(array('success' => true , 'success_message' => $success_message));
		}
		$this->set_echo_and_die();
	}

	protected function get_success_message_at_list($field_info = null)
	{
		if($field_info !== null && isset($field_info->success_message) && $field_info->success_message)
		{
			if(!empty($field_info->primary_key) && !$this->unset_edit)
			{
				return $this->l('insert_success_message')." <a class='go-to-edit-form' href='".$this->getEditUrl($field_info->primary_key)."'>".$this->l('form_edit')." {$this->subject}</a> ";
			}
			else
			{
				return $this->l('insert_success_message');
			}
		}
		else
		{
			return null;
		}
	}

	protected function insert_layout($insert_result = false)
	{
		@ob_end_clean();
		if($insert_result === false)
		{
			echo json_encode(array('success' => false));
		}
		else
		{
			$success_message = '<p>'.$this->l('insert_success_message');

			if(!$this->unset_back_to_list && !empty($insert_result) && !$this->unset_edit)
			{
				$success_message .= " <a class='go-to-edit-form' href='".$this->getEditUrl($insert_result)."'>".$this->l('form_edit')." {$this->subject}</a> ";

				if (!$this->_is_ajax()) {
					$success_message .= $this->l('form_or');
				}
			}

			if(!$this->unset_back_to_list && !$this->_is_ajax())
			{
				$success_message .= " <a href='".$this->getListUrl()."'>".$this->l('form_go_back_to_list')."</a>";
			}

			$success_message .= '</p>';

			echo json_encode(array(
					'success' => true ,
					'insert_primary_key' => $insert_result,
					'success_message' => $success_message,
					'success_list_url'	=> $this->getListSuccessUrl($insert_result)
			));
		}
		$this->set_echo_and_die();
	}

	protected function validation_layout($validation_result)
	{
		@ob_end_clean();
		echo json_encode($validation_result);
		$this->set_echo_and_die();
	}

	protected function upload_layout($upload_result, $field_name)
	{
		@ob_end_clean();
		if($upload_result !== false && !is_string($upload_result) && empty($upload_result[0]->error))
		{
			echo json_encode(
					(object)array(
							'success' => true,
							'files'	=> $upload_result
					));
		}
		else
		{
			$result = (object)array('success' => false);
			if(is_string($upload_result))
				$result->message = $upload_result;
			if(!empty($upload_result[0]->error))
				$result->message = $upload_result[0]->error;

			echo json_encode($result);
		}

		$this->set_echo_and_die();
	}

	protected function delete_file_layout($upload_result)
	{
		@ob_end_clean();
		if($upload_result !== false)
		{
			echo json_encode( (object)array( 'success' => true ) );
		}
		else
		{
			echo json_encode((object)array('success' => false));
		}

		$this->set_echo_and_die();
	}

	public function set_css($css_file)
	{
		$this->css_files[sha1($css_file)] = base_url().$css_file;
	}

	public function set_js($js_file)
	{
		$this->js_files[sha1($js_file)] = base_url().$js_file;
	}

	public function set_js_lib($js_file)
	{
		$this->js_lib_files[sha1($js_file)] = base_url().$js_file;
		$this->js_files[sha1($js_file)] = base_url().$js_file;
	}

	public function set_js_config($js_file)
	{
		$this->js_config_files[sha1($js_file)] = base_url().$js_file;
		$this->js_files[sha1($js_file)] = base_url().$js_file;
	}

	public function is_IE7()
	{
		return isset($_SERVER['HTTP_USER_AGENT'])
					&& (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false)
					? true : false;
	}

	public function get_css_files()
	{
		return $this->css_files;
	}

	public function get_js_files()
	{
		return $this->js_files;
	}

	public function get_js_lib_files()
	{
		return $this->js_lib_files;
	}

	public function get_js_config_files()
	{
		return $this->js_config_files;
	}

	/**
	 * Load Javascripts
	 **/
	protected function load_js_fancybox()
	{
		$this->set_css($this->default_css_path.'/jquery_plugins/fancybox/jquery.fancybox.css');

		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.fancybox-1.3.4.js');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.easing-1.3.pack.js');
	}

	protected function load_js_chosen()
	{
		$this->set_css($this->default_css_path.'/jquery_plugins/chosen/chosen.css');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.chosen.min.js');
	}

	protected function load_js_jqueryui()
	{
		$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);
	}

	protected function load_js_uploader()
	{
		$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/file-uploader.css');
		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/jquery.fileupload-ui.css');

		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/tmpl.min.js');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/load-image.min.js');

		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.iframe-transport.js');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.fileupload.js');
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.fileupload.config.js');
	}

	protected function get_layout()
	{
		$js_files = $this->get_js_files();
		$css_files =  $this->get_css_files();

		$js_lib_files = $this->get_js_lib_files();
		$js_config_files = $this->get_js_config_files();

		if ($this->unset_jquery) {
			unset($js_files[sha1($this->default_javascript_path.'/'.grocery_CRUD::JQUERY)]);
		}

		if ($this->unset_jquery_ui) {
			unset($css_files[sha1($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS)]);
			unset($js_files[sha1($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS)]);
		}

		if ($this->unset_bootstrap) {
			unset($js_files[sha1($this->default_theme_path.'/bootstrap/js/bootstrap/dropdown.js')]);
			unset($js_files[sha1($this->default_theme_path.'/bootstrap/js/bootstrap/modal.js')]);
			unset($js_files[sha1($this->default_theme_path.'/bootstrap/js/bootstrap/dropdown.min.js')]);
			unset($js_files[sha1($this->default_theme_path.'/bootstrap/js/bootstrap/modal.min.js')]);
			unset($css_files[sha1($this->default_theme_path.'/bootstrap/css/bootstrap/bootstrap.css')]);
			unset($css_files[sha1($this->default_theme_path.'/bootstrap/css/bootstrap/bootstrap.min.css')]);
		}

		if($this->echo_and_die === false)
		{
			/** Initialize JavaScript variables */
			$js_vars =  array(
					'default_javascript_path'	=> base_url().$this->default_javascript_path,
					'default_css_path'			=> base_url().$this->default_css_path,
					'default_texteditor_path'	=> base_url().$this->default_texteditor_path,
					'default_theme_path'		=> base_url().$this->default_theme_path,
					'base_url'				 	=> base_url()
			);
			$this->_add_js_vars($js_vars);

			return (object)array(
					'js_files' => $js_files,
					'js_lib_files' => $js_lib_files,
					'js_config_files' => $js_config_files,
					'css_files' => $css_files,
					'output' => $this->views_as_string,
			);
		}
		elseif($this->echo_and_die === true)
		{
			echo $this->views_as_string;
			die();
		}
	}

	protected function update_layout($update_result = false, $state_info = null)
	{
		@ob_end_clean();
		if($update_result === false)
		{
			echo json_encode(array('success' => $update_result));
		}
		else
		{
			$success_message = '<p>'.$this->l('update_success_message');
			if(!$this->unset_back_to_list && !$this->_is_ajax())
			{
				$success_message .= " <a href='".$this->getListUrl()."'>".$this->l('form_go_back_to_list')."</a>";
			}
			$success_message .= '</p>';

			echo json_encode(array(
					'success' => true ,
					'insert_primary_key' => $update_result,
					'success_message' => $success_message,
					'success_list_url'	=> $this->getListSuccessUrl($state_info->primary_key)
			));
		}
		$this->set_echo_and_die();
	}

	protected function get_integer_input($field_info,$value)
	{
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.numeric.min.js');
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.numeric.config.js');
		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'";
		$input = "<input id='field-{$field_info->name}' name='{$field_info->name}' type='text' value='$value' class='numeric form-control' $extra_attributes />";
		return $input;
	}

	protected function get_true_false_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/jquery_plugins/uniform/uniform.default.css');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.uniform.min.js');
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.uniform.config.js');

		$value_is_null = empty($value) && $value !== '0' && $value !== 0 ? true : false;

		$input = "<div class='pretty-radio-buttons'>";

		$true_string = is_array($field_info->extras) && array_key_exists(1,$field_info->extras) ? $field_info->extras[1] : $this->default_true_false_text[1];
		$checked = $value === '1' || ($value_is_null && $field_info->default === '1') ? "checked = 'checked'" : "";
		$input .= "<label><input id='field-{$field_info->name}-true' class='radio-uniform'  type='radio' name='{$field_info->name}' value='1' $checked /> ".$true_string."</label> ";

		$false_string =  is_array($field_info->extras) && array_key_exists(0,$field_info->extras) ? $field_info->extras[0] : $this->default_true_false_text[0];
		$checked = $value === '0' || ($value_is_null && $field_info->default === '0') ? "checked = 'checked'" : "";
		$input .= "<label><input id='field-{$field_info->name}-false' class='radio-uniform' type='radio' name='{$field_info->name}' value='0' $checked /> ".$false_string."</label>";

		$input .= "</div>";

		return $input;
	}

	protected function get_string_input($field_info,$value)
	{
		$value = !is_string($value) ? '' : str_replace('"',"&quot;",$value);

		$extra_attributes = '';
		if (!empty($field_info->db_max_length)) {

            if (in_array($field_info->type, array("decimal", "float"))) {
                $decimal_lentgh = explode(",", $field_info->db_max_length);
                $decimal_lentgh = ((int)$decimal_lentgh[0]) + 1;

                $extra_attributes .= "maxlength='" . $decimal_lentgh . "'";
            } else {
                $extra_attributes .= "maxlength='{$field_info->db_max_length}'";
            }

        }
		$input = "<input id='field-{$field_info->name}' class='form-control' name='{$field_info->name}' type='text' value=\"$value\" $extra_attributes />";
		return $input;
	}

	protected function get_text_input($field_info,$value)
	{
		if($field_info->extras == 'text_editor')
		{
			$editor = $this->config->default_text_editor;
			switch ($editor) {
				case 'ckeditor':
					$this->set_js_lib($this->default_texteditor_path.'/ckeditor/ckeditor.js');
					$this->set_js_lib($this->default_texteditor_path.'/ckeditor/adapters/jquery.js');
					$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.ckeditor.config.js');
				break;

				case 'tinymce':
					$this->set_js_lib($this->default_texteditor_path.'/tiny_mce/jquery.tinymce.js');
					$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.tine_mce.config.js');
				break;

				case 'markitup':
					$this->set_css($this->default_texteditor_path.'/markitup/skins/markitup/style.css');
					$this->set_css($this->default_texteditor_path.'/markitup/sets/default/style.css');

					$this->set_js_lib($this->default_texteditor_path.'/markitup/jquery.markitup.js');
					$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.markitup.config.js');
				break;
			}

			$class_name = $this->config->text_editor_type == 'minimal' ? 'mini-texteditor' : 'texteditor';

			$input = "<textarea id='field-{$field_info->name}' name='{$field_info->name}' class='$class_name' >$value</textarea>";
		}
		else
		{
			$input = "<textarea id='field-{$field_info->name}' name='{$field_info->name}'>$value</textarea>";
		}
		return $input;
	}

	protected function get_datetime_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
		$this->set_css($this->default_css_path.'/jquery_plugins/jquery.ui.datetime.css');
		$this->set_css($this->default_css_path.'/jquery_plugins/jquery-ui-timepicker-addon.css');
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery-ui-timepicker-addon.js');

		if($this->language !== 'english')
		{
			include($this->default_config_path.'/language_alias.php');
			if(array_key_exists($this->language, $language_alias))
			{
				$i18n_date_js_file = $this->default_javascript_path.'/jquery_plugins/ui/i18n/datepicker/jquery.ui.datepicker-'.$language_alias[$this->language].'.js';
				if(file_exists($i18n_date_js_file))
				{
					$this->set_js_lib($i18n_date_js_file);
				}

				$i18n_datetime_js_file = $this->default_javascript_path.'/jquery_plugins/ui/i18n/timepicker/jquery-ui-timepicker-'.$language_alias[$this->language].'.js';
				if(file_exists($i18n_datetime_js_file))
				{
					$this->set_js_lib($i18n_datetime_js_file);
				}
			}
		}

		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery-ui-timepicker-addon.config.js');

		if(!empty($value) && $value != '0000-00-00 00:00:00' && $value != '1970-01-01 00:00:00'){
			list($year,$month,$day) = explode('-',substr($value,0,10));
			$date = date($this->php_date_format, mktime(0,0,0,$month,$day,$year));
			$datetime = $date.substr($value,10);
		}
		else
		{
			$datetime = '';
		}
		$input = "<input id='field-{$field_info->name}' name='{$field_info->name}' type='text' value='$datetime' maxlength='19' class='datetime-input form-control' />
		<a class='datetime-input-clear' tabindex='-1'>".$this->l('form_button_clear')."</a>
		({$this->ui_date_format}) hh:mm:ss";
		return $input;
	}

	protected function get_hidden_input($field_info,$value)
	{
		if($field_info->extras !== null && $field_info->extras != false)
			$value = $field_info->extras;
		$input = "<input id='field-{$field_info->name}' type='hidden' name='{$field_info->name}' value='$value' />";
		return $input;
	}

	protected function get_password_input($field_info,$value)
	{
		$value = !is_string($value) ? '' : $value;

		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'";
		$input = "<input id='field-{$field_info->name}' class='form-control' name='{$field_info->name}' type='password' value='$value' $extra_attributes />";
		return $input;
	}

	protected function get_date_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
		$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);

		if($this->language !== 'english')
		{
			include($this->default_config_path.'/language_alias.php');
			if(array_key_exists($this->language, $language_alias))
			{
				$i18n_date_js_file = $this->default_javascript_path.'/jquery_plugins/ui/i18n/datepicker/jquery.ui.datepicker-'.$language_alias[$this->language].'.js';
				if(file_exists($i18n_date_js_file))
				{
					$this->set_js_lib($i18n_date_js_file);
				}
			}
		}

		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.datepicker.config.js');

		if(!empty($value) && $value != '0000-00-00' && $value != '1970-01-01')
		{
			list($year,$month,$day) = explode('-',substr($value,0,10));
			$date = date($this->php_date_format, mktime(0,0,0,$month,$day,$year));
		}
		else
		{
			$date = '';
		}

		$input = "<input id='field-{$field_info->name}' name='{$field_info->name}' type='text' value='$date' maxlength='10' class='datepicker-input form-control' />
		<a class='datepicker-input-clear' tabindex='-1'>".$this->l('form_button_clear')."</a> (".$this->ui_date_format.")";
		return $input;
	}

	protected function get_dropdown_input($field_info,$value)
	{
		$this->load_js_chosen();
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));

		$input = "<select id='field-{$field_info->name}' name='{$field_info->name}' class='chosen-select' data-placeholder='".$select_title."'>";
		$options = array('' => '') + $field_info->extras;
		foreach($options as $option_value => $option_label)
		{
			$selected = !empty($value) && $value == $option_value ? "selected='selected'" : '';
			$input .= "<option value='$option_value' $selected >$option_label</option>";
		}

		$input .= "</select>";
		return $input;
	}

	protected function get_enum_input($field_info,$value)
	{
		$this->load_js_chosen();
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));

		$input = "<select id='field-{$field_info->name}' name='{$field_info->name}' class='chosen-select' data-placeholder='".$select_title."'>";
		$options_array = $field_info->extras !== false && is_array($field_info->extras)? $field_info->extras : explode("','",substr($field_info->db_max_length,1,-1));
		$options_array = array('' => '') + $options_array;

		foreach($options_array as $option)
		{
			$selected = !empty($value) && $value == $option ? "selected='selected'" : '';
			$input .= "<option value='$option' $selected >$option</option>";
		}

		$input .= "</select>";
		return $input;
	}

	protected function get_readonly_input($field_info, $value)
	{
		$read_only_value = "&nbsp;";

	    if (!empty($value) && !is_array($value)) {
	    	$read_only_value = $value;
    	} elseif (is_array($value)) {
    		$all_values = array_values($value);
    		$read_only_value = implode(", ",$all_values);
    	}

        return '<div id="field-'.$field_info->name.'" class="readonly_label">'.$read_only_value.'</div>';
	}

	protected function get_set_input($field_info,$value)
	{
		$this->load_js_chosen();
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$options_array = $field_info->extras !== false && is_array($field_info->extras)? $field_info->extras : explode("','",substr($field_info->db_max_length,1,-1));
		$selected_values 	= !empty($value) ? explode(",",$value) : array();

		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));
		$input = "<select id='field-{$field_info->name}' name='{$field_info->name}[]' multiple='multiple' size='8' class='chosen-multiple-select' data-placeholder='$select_title' style='width:510px;' >";

		foreach($options_array as $option)
		{
			$selected = !empty($value) && in_array($option,$selected_values) ? "selected='selected'" : '';
			$input .= "<option value='$option' $selected >$option</option>";
		}

		$input .= "</select>";

		return $input;
	}

	protected function get_multiselect_input($field_info,$value)
	{
		$this->load_js_chosen();
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$options_array = $field_info->extras;
		$selected_values 	= !empty($value) ? explode(",",$value) : array();

		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));
		$input = "<select id='field-{$field_info->name}' name='{$field_info->name}[]' multiple='multiple' size='8' class='chosen-multiple-select' data-placeholder='$select_title' style='width:510px;' >";

		foreach($options_array as $option_value => $option_label)
		{
			$selected = !empty($value) && in_array($option_value,$selected_values) ? "selected='selected'" : '';
			$input .= "<option value='$option_value' $selected >$option_label</option>";
		}

		$input .= "</select>";

		return $input;
	}

	protected function get_relation_input($field_info,$value)
	{
		$this->load_js_chosen();
		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$ajax_limitation = 10000;
		$total_rows = $this->get_relation_total_rows($field_info->extras);


		//Check if we will use ajax for our queries or just clien-side javascript
		$using_ajax = $total_rows > $ajax_limitation ? true : false;

		//We will not use it for now. It is not ready yet. Probably we will have this functionality at version 1.4
		$using_ajax = false;

		//If total rows are more than the limitation, use the ajax plugin
		$ajax_or_not_class = $using_ajax ? 'chosen-select' : 'chosen-select';

		$this->_inline_js("var ajax_relation_url = '".$this->getAjaxRelationUrl()."';\n");

		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));
		$input = "<select id='field-{$field_info->name}'  name='{$field_info->name}' class='$ajax_or_not_class' data-placeholder='$select_title' style='width:300px'>";
		$input .= "<option value=''></option>";

		if(!$using_ajax)
		{
			$options_array = $this->get_relation_array($field_info->extras);
			foreach($options_array as $option_value => $option)
			{
				$selected = !empty($value) && $value == $option_value ? "selected='selected'" : '';
				$input .= "<option value='$option_value' $selected >$option</option>";
			}
		}
		elseif(!empty($value) || (is_numeric($value) && $value == '0') ) //If it's ajax then we only need the selected items and not all the items
		{
			$selected_options_array = $this->get_relation_array($field_info->extras, $value);
			foreach($selected_options_array as $option_value => $option)
			{
				$input .= "<option value='$option_value'selected='selected' >$option</option>";
			}
		}

		$input .= "</select>";
		return $input;
	}

	protected function get_relation_readonly_input($field_info,$value)
	{
		$options_array = $this->get_relation_array($field_info->extras);

		$value = isset($options_array[$value]) ? $options_array[$value] : '';

		return $this->get_readonly_input($field_info, $value);
	}

	protected function get_upload_file_readonly_input($field_info,$value)
	{
		$file = $file_url = base_url().$field_info->extras->upload_path.'/'.$value;

		$value = !empty($value) ? '<a href="'.$file.'" target="_blank">'.$value.'</a>' : '';

		return $this->get_readonly_input($field_info, $value);
	}

	protected function get_relation_n_n_input($field_info_type, $selected_values)
	{
		$has_priority_field = !empty($field_info_type->extras->priority_field_relation_table) ? true : false;
		$is_ie_7 = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false) ? true : false;

		if($has_priority_field || $is_ie_7)
		{
			$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
			$this->set_css($this->default_css_path.'/jquery_plugins/ui.multiselect.css');
			$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);
			$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/ui.multiselect.min.js');
			$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.multiselect.js');

			if($this->language !== 'english')
			{
				include($this->default_config_path.'/language_alias.php');
				if(array_key_exists($this->language, $language_alias))
				{
					$i18n_date_js_file = $this->default_javascript_path.'/jquery_plugins/ui/i18n/multiselect/ui-multiselect-'.$language_alias[$this->language].'.js';
					if(file_exists($i18n_date_js_file))
					{
						$this->set_js_lib($i18n_date_js_file);
					}
				}
			}
		}
		else
		{
			$this->set_css($this->default_css_path.'/jquery_plugins/chosen/chosen.css');
			$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.chosen.min.js');
			$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');
		}

		$this->_inline_js("var ajax_relation_url = '".$this->getAjaxRelationUrl()."';\n");

		$field_info 		= $this->relation_n_n[$field_info_type->name]; //As we use this function the relation_n_n exists, so don't need to check
		$unselected_values 	= $this->get_relation_n_n_unselected_array($field_info, $selected_values);

		if(empty($unselected_values) && empty($selected_values))
		{
			$input = "Please add {$field_info_type->display_as} first";
		}
		else
		{
			$css_class = $has_priority_field || $is_ie_7 ? 'multiselect': 'chosen-multiple-select';
			$width_style = $has_priority_field || $is_ie_7 ? '' : 'width:510px;';

			$select_title = str_replace('{field_display_as}',$field_info_type->display_as,$this->l('set_relation_title'));
			$input = "<select id='field-{$field_info_type->name}' name='{$field_info_type->name}[]' multiple='multiple' size='8' class='$css_class' data-placeholder='$select_title' style='$width_style' >";

			if(!empty($unselected_values))
				foreach($unselected_values as $id => $name)
				{
					$input .= "<option value='$id'>$name</option>";
				}

			if(!empty($selected_values))
				foreach($selected_values as $id => $name)
				{
					$input .= "<option value='$id' selected='selected'>$name</option>";
				}

			$input .= "</select>";
		}

		return $input;
	}

	protected function _convert_bytes_ui_to_bytes($bytes_ui)
	{
		$bytes_ui = str_replace(' ','',$bytes_ui);
		if(strstr($bytes_ui,'MB'))
			$bytes = (int)(str_replace('MB','',$bytes_ui))*1024*1024;
		elseif(strstr($bytes_ui,'KB'))
			$bytes = (int)(str_replace('KB','',$bytes_ui))*1024;
		elseif(strstr($bytes_ui,'B'))
			$bytes = (int)(str_replace('B','',$bytes_ui));
		else
			$bytes = (int)($bytes_ui);

		return $bytes;
	}

	protected function get_upload_file_input($field_info, $value)
	{
		$this->load_js_uploader();

		//Fancybox
		$this->load_js_fancybox();

		$this->set_js_config($this->default_javascript_path.'/jquery_plugins/config/jquery.fancybox.config.js');

		$unique = mt_rand();

		$allowed_files = $this->config->file_upload_allow_file_types;
		$allowed_files_ui = '.'.str_replace('|',',.',$allowed_files);
		$max_file_size_ui = $this->config->file_upload_max_file_size;
		$max_file_size_bytes = $this->_convert_bytes_ui_to_bytes($max_file_size_ui);

		$this->_inline_js('
			var upload_info_'.$unique.' = {
				accepted_file_types: /(\\.|\\/)('.$allowed_files.')$/i,
				accepted_file_types_ui : "'.$allowed_files_ui.'",
				max_file_size: '.$max_file_size_bytes.',
				max_file_size_ui: "'.$max_file_size_ui.'"
			};

			var string_upload_file 	= "'.$this->l('form_upload_a_file').'";
			var string_delete_file 	= "'.$this->l('string_delete_file').'";
			var string_progress 			= "'.$this->l('string_progress').'";
			var error_on_uploading 			= "'.$this->l('error_on_uploading').'";
			var message_prompt_delete_file 	= "'.$this->l('message_prompt_delete_file').'";

			var error_max_number_of_files 	= "'.$this->l('error_max_number_of_files').'";
			var error_accept_file_types 	= "'.$this->l('error_accept_file_types').'";
			var error_max_file_size 		= "'.str_replace("{max_file_size}",$max_file_size_ui,$this->l('error_max_file_size')).'";
			var error_min_file_size 		= "'.$this->l('error_min_file_size').'";

			var base_url = "'.base_url().'";
			var upload_a_file_string = "'.$this->l('form_upload_a_file').'";
		');

		$uploader_display_none 	= empty($value) ? "" : "display:none;";
		$file_display_none  	= empty($value) ?  "display:none;" : "";

		$is_image = !empty($value) &&
						( substr($value,-4) == '.jpg'
								|| substr($value,-4) == '.png'
								|| substr($value,-5) == '.jpeg'
								|| substr($value,-4) == '.gif'
								|| substr($value,-5) == '.tiff')
					? true : false;

		$image_class = $is_image ? 'image-thumbnail' : '';

		$input = '<span class="fileinput-button qq-upload-button" id="upload-button-'.$unique.'" style="'.$uploader_display_none.'">
			<span>'.$this->l('form_upload_a_file').'</span>
			<input type="file" name="'.$this->_unique_field_name($field_info->name).'" class="gc-file-upload" rel="'.$this->getUploadUrl($field_info->name).'" id="'.$unique.'">
			<input class="hidden-upload-input" type="hidden" name="'.$field_info->name.'" value="'.$value.'" rel="'.$this->_unique_field_name($field_info->name).'" />
		</span>';

		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/fileuploader.css');

		$file_url = base_url().$field_info->extras->upload_path.'/'.$value;

		$input .= "<div id='uploader_$unique' rel='$unique' class='grocery-crud-uploader' style='$uploader_display_none'></div>";
		$input .= "<div id='success_$unique' class='upload-success-url' style='$file_display_none padding-top:7px;'>";
		$input .= "<a href='".$file_url."' id='file_$unique' class='open-file";
		$input .= $is_image ? " $image_class'><img src='".$file_url."' height='50px'>" : "' target='_blank'>$value";
		$input .= "</a> ";
		$input .= "<a href='javascript:void(0)' id='delete_$unique' class='delete-anchor'>".$this->l('form_upload_delete')."</a> ";
		$input .= "</div><div style='clear:both'></div>";
		$input .= "<div id='loading-$unique' style='display:none'><span id='upload-state-message-$unique'></span> <span class='qq-upload-spinner'></span> <span id='progress-$unique'></span></div>";
		$input .= "<div style='display:none'><a href='".$this->getUploadUrl($field_info->name)."' id='url_$unique'></a></div>";
		$input .= "<div style='display:none'><a href='".$this->getFileDeleteUrl($field_info->name)."' id='delete_url_$unique' rel='$value' ></a></div>";

		return $input;
	}

	protected function get_add_hidden_fields()
	{
		return $this->add_hidden_fields;
	}

	protected function get_edit_hidden_fields()
	{
		return $this->edit_hidden_fields;
	}

	protected function get_add_input_fields($field_values = null)
	{
		$fields = $this->get_add_fields();
		$types 	= $this->get_field_types();

		$input_fields = array();

		foreach($fields as $field_num => $field)
		{
			$field_info = $types[$field->field_name];

			$field_value = !empty($field_values) && isset($field_values->{$field->field_name}) ? $field_values->{$field->field_name} : null;

			if(!isset($this->callback_add_field[$field->field_name]))
			{
				$field_input = $this->get_field_input($field_info, $field_value);
			}
			else
			{
				$field_input = $field_info;
				$field_input->input = call_user_func($this->callback_add_field[$field->field_name], $field_value, null, $field_info);
			}

			switch ($field_info->crud_type) {
				case 'invisible':
					unset($this->add_fields[$field_num]);
					unset($fields[$field_num]);
					continue;
				break;
				case 'hidden':
					$this->add_hidden_fields[] = $field_input;
					unset($this->add_fields[$field_num]);
					unset($fields[$field_num]);
					continue;
				break;
			}

			$input_fields[$field->field_name] = $field_input;
		}

		return $input_fields;
	}

	protected function get_edit_input_fields($field_values = null)
	{
		$fields = $this->get_edit_fields();
		$types 	= $this->get_field_types();

		$input_fields = array();

		foreach($fields as $field_num => $field)
		{
			$field_info = $types[$field->field_name];

			$field_value = !empty($field_values) && isset($field_values->{$field->field_name}) ? $field_values->{$field->field_name} : null;
			if(!isset($this->callback_edit_field[$field->field_name]))
			{
				$field_input = $this->get_field_input($field_info, $field_value);
			}
			else
			{
				$primary_key = $this->getStateInfo()->primary_key;
				$field_input = $field_info;
				$field_input->input = call_user_func($this->callback_edit_field[$field->field_name], $field_value, $primary_key, $field_info, $field_values);
			}

			switch ($field_info->crud_type) {
				case 'invisible':
					unset($this->edit_fields[$field_num]);
					unset($fields[$field_num]);
					continue;
				break;
				case 'hidden':
					$this->edit_hidden_fields[] = $field_input;
					unset($this->edit_fields[$field_num]);
					unset($fields[$field_num]);
					continue;
				break;
			}

			$input_fields[$field->field_name] = $field_input;
		}

		return $input_fields;
	}

	protected function get_read_input_fields($field_values = null)
	{
		$read_fields = $this->get_read_fields();

		$this->field_types = null;
		$this->required_fields = null;

		$read_inputs = array();
		foreach ($read_fields as $field) {
			if (!empty($this->change_field_type)
					&& isset($this->change_field_type[$field->field_name])
					&& $this->change_field_type[$field->field_name]->type == 'hidden') {
				continue;
			}
			$this->field_type($field->field_name, 'readonly');
		}

		$fields = $this->get_read_fields();
		$types 	= $this->get_field_types();

		$input_fields = array();

		foreach($fields as $field_num => $field)
		{
			$field_info = $types[$field->field_name];

			$field_value = !empty($field_values) && isset($field_values->{$field->field_name}) ? $field_values->{$field->field_name} : null;
			if(!isset($this->callback_read_field[$field->field_name]))
			{
				$field_input = $this->get_field_input($field_info, $field_value);
			}
			else
			{
				$primary_key = $this->getStateInfo()->primary_key;
				$field_input = $field_info;
				$field_input->input = call_user_func($this->callback_read_field[$field->field_name], $field_value, $primary_key, $field_info, $field_values);
			}

			switch ($field_info->crud_type) {
			    case 'invisible':
			    	unset($this->read_fields[$field_num]);
			    	unset($fields[$field_num]);
			    	continue;
			    	break;
			    case 'hidden':
			    	$this->read_hidden_fields[] = $field_input;
			    	unset($this->read_fields[$field_num]);
			    	unset($fields[$field_num]);
			    	continue;
			    	break;
			}

			$input_fields[$field->field_name] = $field_input;
		}

		return $input_fields;
	}

	protected function setThemeBasics()
	{
		$this->theme_path = $this->default_theme_path;
		if(substr($this->theme_path,-1) != '/')
			$this->theme_path = $this->theme_path.'/';

		include($this->theme_path.$this->theme.'/config.php');

		$this->theme_config = $config;
	}

	public function set_theme($theme = null)
	{
		$this->theme = $theme;

		return $this;
	}

	protected function _get_ajax_results()
	{
		//This is a $_POST request rather that $_GET request , because
		//Codeigniter doesn't like the $_GET requests so much!
		if ($this->_is_ajax()) {
			@ob_end_clean();
			$results= (object)array(
					'output' => $this->views_as_string,
					'js_files' => array_values($this->get_js_files()),
					'js_lib_files' => array_values($this->get_js_lib_files()),
					'js_config_files' => array_values($this->get_js_config_files()),
					'css_files' => array_values($this->get_css_files())
			);

			echo json_encode($results);
			die;
		}
		//else just continue
	}

	protected function _is_ajax()
	{
		return array_key_exists('is_ajax', $_POST) && $_POST['is_ajax'] == 'true' ? true: false;
	}

	protected function _theme_view($view, $vars = array(), $return = FALSE)
	{
		$vars = (is_object($vars)) ? get_object_vars($vars) : $vars;

		$file_exists = FALSE;

		$ext = pathinfo($view, PATHINFO_EXTENSION);
		$file = ($ext == '') ? $view.'.php' : $view;

		$view_file = $this->theme_path.$this->theme.'/views/';

		if (file_exists($view_file.$file))
		{
			$path = $view_file.$file;
			$file_exists = TRUE;
		}

		if ( ! $file_exists)
		{
			throw new Exception('Unable to load the requested file: '.$file, 16);
		}

		extract($vars);

		#region buffering...
		ob_start();

		include($path);

		$buffer = ob_get_contents();
		@ob_end_clean();
		#endregion

		if ($return === TRUE)
		{
			return $buffer;
		}

		$this->views_as_string .= $buffer;
	}

	protected function _inline_js($inline_js = '')
	{
		$this->views_as_string .= "<script type=\"text/javascript\">\n{$inline_js}\n</script>\n";
	}

	protected function _add_js_vars($js_vars = array())
	{
		$javascript_as_string = "<script type=\"text/javascript\">\n";
		foreach ($js_vars as $js_var => $js_value) {
			$javascript_as_string .= "\tvar $js_var = '$js_value';\n";
		}
		$javascript_as_string .= "\n</script>\n";
		$this->views_as_string .= $javascript_as_string;
	}

	protected function get_views_as_string()
	{
		if(!empty($this->views_as_string))
			return $this->views_as_string;
		else
			return null;
	}
}


/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * Grocery CRUD is released with dual licensing, using the GPL v3 (license-gpl3.txt) and the MIT license (license-mit.txt).
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */

// ------------------------------------------------------------------------

/**
 * PHP grocery States
 *
 * States of grocery CRUD
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @version    	1.5.4
 */
class grocery_CRUD_States extends grocery_CRUD_Layout
{
    const STATE_UNKNOWN = 0;
    const STATE_LIST = 1;
    const STATE_ADD = 2;
    const STATE_EDIT = 3;
    const STATE_DELETE = 4;
    const STATE_INSERT = 5;

    const STATE_READ = 18;
    const STATE_DELETE_MULTIPLE = '19';

	protected $states = array(
		0	=> 'unknown',
		1	=> 'list',
		2	=> 'add',
		3	=> 'edit',
		4	=> 'delete',
		5	=> 'insert',
		6	=> 'update',
		7	=> 'ajax_list',
		8   => 'ajax_list_info',
		9	=> 'insert_validation',
		10	=> 'update_validation',
		11	=> 'upload_file',
		12	=> 'delete_file',
		13	=> 'ajax_relation',
		14	=> 'ajax_relation_n_n',
		15	=> 'success',
		16  => 'export',
		17  => 'print',
		18  => 'read',
        19  => 'delete_multiple'
	);

    public function getStateInfo()
    {
        $state_code = $this->getStateCode();
        $segment_object = $this->get_state_info_from_url();

        $first_parameter = $segment_object->first_parameter;
        $second_parameter = $segment_object->second_parameter;

        $state_info = (object)array();

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
                if(!empty($_POST['per_page']))
                {
                    $state_info->per_page = is_numeric($_POST['per_page']) ? $_POST['per_page'] : null;
                }
                if(!empty($_POST['page']))
                {
                    $state_info->page = is_numeric($_POST['page']) ? $_POST['page'] : null;
                }
                //If we request an export or a print we don't care about what page we are
                if($state_code === 16 || $state_code === 17)
                {
                    $state_info->page = 1;
                    $state_info->per_page = 1000000; //a very big number!
                }
                if(!empty($_POST['order_by'][0]))
                {
                    $state_info->order_by = $_POST['order_by'];
                }
                if(!empty($_POST['search_text']))
                {
                    if(empty($_POST['search_field']))
                    {
                        $search_text = strip_tags($_POST['search_field']);
                        $state_info->search = (object)array('field' => null , 'text' => $_POST['search_text']);
                    }
                    else
                    {
                        if (is_array($_POST['search_field'])) {
                            $search_array = array();
                            foreach ($_POST['search_field'] as $search_key => $search_field_name) {
                                $search_array[$search_field_name] = !empty($_POST['search_text'][$search_key]) ? $_POST['search_text'][$search_key] : '';
                            }
                            $state_info->search	= $search_array;
                        } else {
                            $state_info->search	= (object)array(
                                'field' => strip_tags($_POST['search_field']) ,
                                'text' => $_POST['search_text'] );
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

        return $state_info;
    }

	protected function getStateCode()
	{
		$state_string = $this->get_state_info_from_url()->operation;

		if( $state_string != 'unknown' && in_array( $state_string, $this->states ) )
			$state_code =  array_search($state_string, $this->states);
		else
			$state_code = 0;

		return $state_code;
	}

	protected function state_url($url = '', $is_list_page = false)
	{
		//Easy scenario, we had set the crud_url_path
		if (!empty($this->crud_url_path)) {
			$state_url = !empty($this->list_url_path) && $is_list_page?
							$this->list_url_path :
							$this->crud_url_path.'/'.$url ;
		} else {
			//Complicated scenario. The crud_url_path is not specified so we are
			//trying to understand what is going on from the URL.
			$ci = &get_instance();

			$segment_object = $this->get_state_info_from_url();
			$method_name = $this->get_method_name();
			$segment_position = $segment_object->segment_position;

			$state_url_array = array();

		    if( sizeof($ci->uri->segments) > 0 ) {
		      foreach($ci->uri->segments as $num => $value)
		      {
		        $state_url_array[$num] = $value;
		        if($num == ($segment_position - 1))
		          break;
		      }

		      if( $method_name == 'index' && !in_array( 'index', $state_url_array ) ) //there is a scenario that you don't have the index to your url
		        $state_url_array[$num+1] = 'index';
		    }

			$state_url =  site_url(implode('/',$state_url_array).'/'.$url);
		}

		return $state_url;
	}

	protected function get_state_info_from_url()
	{
		$ci = &get_instance();

		$segment_position = count($ci->uri->segments) + 1;
		$operation = 'list';

		$segements = $ci->uri->segments;
		foreach($segements as $num => $value)
		{
			if($value != 'unknown' && in_array($value, $this->states))
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

	protected function get_method_hash()
	{
		$ci = &get_instance();

		$state_info = $this->get_state_info_from_url();
		$extra_values = $ci->uri->segment($state_info->segment_position - 1) != $this->get_method_name() ? $ci->uri->segment($state_info->segment_position - 1) : '';

		return $this->crud_url_path !== null
					? md5($this->crud_url_path)
					: md5($this->get_controller_name().$this->get_method_name().$extra_values);
	}

	protected function get_method_name()
	{
		$ci = &get_instance();
		return $ci->router->method;
	}

	protected function get_controller_name()
	{
		$ci = &get_instance();
		return $ci->router->class;
	}

	public function getState()
	{
		return $this->states[$this->getStateCode()];
	}

	protected function getListUrl()
	{
		return $this->state_url('',true);
	}

	protected function getAjaxListUrl()
	{
		return $this->state_url('ajax_list');
	}

	protected function getExportToExcelUrl()
	{
		return $this->state_url('export');
	}

	protected function getPrintUrl()
	{
		return $this->state_url('print');
	}

	protected function getAjaxListInfoUrl()
	{
		return $this->state_url('ajax_list_info');
	}

	protected function getAddUrl()
	{
		return $this->state_url('add');
	}

	protected function getInsertUrl()
	{
		return $this->state_url('insert');
	}

	protected function getValidationInsertUrl()
	{
		return $this->state_url('insert_validation');
	}

	protected function getValidationUpdateUrl($primary_key = null)
	{
		if($primary_key === null)
			return $this->state_url('update_validation');
		else
			return $this->state_url('update_validation/'.$primary_key);
	}

	protected function getEditUrl($primary_key = null)
	{
		if($primary_key === null)
			return $this->state_url('edit');
		else
			return $this->state_url('edit/'.$primary_key);
	}

	protected function getReadUrl($primary_key = null)
	{
		if($primary_key === null)
			return $this->state_url('read');
		else
			return $this->state_url('read/'.$primary_key);
	}

	protected function getUpdateUrl($state_info)
	{
		return $this->state_url('update/'.$state_info->primary_key);
	}

	protected function getDeleteUrl($state_info = null)
	{
		if (empty($state_info)) {
            return $this->state_url('delete');
        } else {
			return $this->state_url('delete/'.$state_info->primary_key);
        }
	}

    protected function getDeleteMultipleUrl()
    {
        return $this->state_url('delete_multiple');
    }

	protected function getListSuccessUrl($primary_key = null)
	{
		if(empty($primary_key))
			return $this->state_url('success',true);
		else
			return $this->state_url('success/'.$primary_key,true);
	}

	protected function getUploadUrl($field_name)
	{
		return $this->state_url('upload_file/'.$field_name);
	}

	protected function getFileDeleteUrl($field_name)
	{
		return $this->state_url('delete_file/'.$field_name);
	}

	protected function getAjaxRelationUrl()
	{
		return $this->state_url('ajax_relation');
	}

	protected function getAjaxRelationManytoManyUrl()
	{
		return $this->state_url('ajax_relation_n_n');
	}
}


/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * Grocery CRUD is released with dual licensing, using the GPL v3 (license-gpl3.txt) and the MIT license (license-mit.txt).
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @version    	1.5.4
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */

// ------------------------------------------------------------------------

/**
 * PHP grocery CRUD
 *
 * Creates a full functional CRUD with few lines of code.
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @license     https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @link		http://www.grocerycrud.com/documentation
 */
class Grocery_CRUD extends grocery_CRUD_States
{
	/**
	 * Grocery CRUD version
	 *
	 * @var	string
	 */
	const	VERSION = "1.5.4";

	const	JQUERY 			= "jquery-1.11.1.min.js";
	const	JQUERY_UI_JS 	= "jquery-ui-1.10.3.custom.min.js";
	const	JQUERY_UI_CSS 	= "jquery-ui-1.10.1.custom.min.css";

	protected $state_code 			= null;
	protected $state_info 			= null;
	protected $columns				= null;

	private $basic_db_table_checked = false;
	private $columns_checked		= false;
	private $add_fields_checked		= false;
	private $edit_fields_checked	= false;
	private $read_fields_checked	= false;

	protected $default_theme		= 'flexigrid';
	protected $language				= null;
	protected $lang_strings			= array();
	protected $php_date_format		= null;
	protected $js_date_format		= null;
	protected $ui_date_format		= null;
	protected $character_limiter    = null;
	protected $config    			= null;

	protected $add_fields			= null;
	protected $edit_fields			= null;
	protected $read_fields			= null;
	protected $add_hidden_fields 	= array();
	protected $edit_hidden_fields 	= array();
	protected $field_types 			= null;
	protected $basic_db_table 		= null;
	protected $theme_config 		= array();
	protected $subject 				= null;
	protected $subject_plural 		= null;
	protected $display_as 			= array();
	protected $order_by 			= null;
	protected $where 				= array();
	protected $like 				= array();
	protected $having 				= array();
	protected $or_having 			= array();
	protected $limit 				= null;
	protected $required_fields		= array();
	protected $_unique_fields 			= array();
	protected $validation_rules		= array();
	protected $relation				= array();
	protected $relation_n_n			= array();
	protected $upload_fields		= array();
	protected $actions				= array();

	protected $form_validation		= null;
	protected $change_field_type	= null;
	protected $primary_keys			= array();
	protected $crud_url_path		= null;
	protected $list_url_path		= null;

	/* The unsetters */
	protected $unset_texteditor		= array();
	protected $unset_add			= false;
	protected $unset_edit			= false;
	protected $unset_delete			= false;
	protected $unset_read			= false;
	protected $unset_jquery			= false;
	protected $unset_jquery_ui		= false;
	protected $unset_bootstrap 		= false;
	protected $unset_list			= false;
	protected $unset_export			= false;
	protected $unset_print			= false;
	protected $unset_back_to_list	= false;
	protected $unset_columns		= null;
	protected $unset_add_fields 	= null;
	protected $unset_edit_fields	= null;
	protected $unset_read_fields	= null;

	/* Callbacks */
	protected $callback_before_insert 	= null;
	protected $callback_after_insert 	= null;
	protected $callback_insert 			= null;
	protected $callback_before_update 	= null;
	protected $callback_after_update 	= null;
	protected $callback_update 			= null;
	protected $callback_before_delete 	= null;
	protected $callback_after_delete 	= null;
	protected $callback_delete 			= null;
	protected $callback_column			= array();
	protected $callback_add_field		= array();
	protected $callback_edit_field		= array();
	protected $callback_upload			= null;
	protected $callback_before_upload	= null;
	protected $callback_after_upload	= null;

	protected $default_javascript_path				= null; //autogenerate, please do not modify
	protected $default_css_path						= null; //autogenerate, please do not modify
	protected $default_texteditor_path 				= null; //autogenerate, please do not modify
	protected $default_theme_path					= null; //autogenerate, please do not modify
	protected $default_language_path				= 'assets/grocery_crud/languages';
	protected $default_config_path					= 'assets/grocery_crud/config';
	protected $default_assets_path					= 'assets/grocery_crud';

	/**
	 *
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct()
	{

	}

	/**
	 * The displayed columns that user see
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	void
	 */
	public function columns()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->columns = $args;

		return $this;
	}


	/**
	 * Set Validation Rules
	 *
	 * Important note: If the $field is an array then no automated crud fields will take apart
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	function set_rules($field, $label = '', $rules = '')
	{
		if(is_string($field))
		{
			$this->validation_rules[$field] = array('field' => $field, 'label' => $label, 'rules' => $rules);
		}elseif(is_array($field))
		{
			foreach($field as $num_field => $field_array)
			{
				$this->validation_rules[$field_array['field']] = $field_array;
			}
		}
		return $this;
	}

	/**
	 *
	 * Changes the default field type
	 * @param string $field
	 * @param string $type
	 * @param array|string $extras
	 */
	public function change_field_type($field , $type, $extras = null)
	{
		$field_type = (object)array('type' => $type);

		$field_type->extras = $extras;

		$this->change_field_type[$field] = $field_type;

		return $this;
	}

	/**
	 *
	 * Just an alias to the change_field_type method
	 * @param string $field
	 * @param string $type
	 * @param array|string $extras
	 */
	public function field_type($field , $type, $extras = null)
	{
		return $this->change_field_type($field , $type, $extras);
	}

	/**
	 * Change the default primary key for a specific table.
	 * If the $table_name is NULL then the primary key is for the default table name that we added at the set_table method
	 *
	 * @param string $primary_key_field
	 * @param string $table_name
	 */
	public function set_primary_key($primary_key_field, $table_name = null)
	{
		$this->primary_keys[] = array('field_name' => $primary_key_field, 'table_name' => $table_name);

		return $this;
	}

	/**
	 * Unsets the texteditor of the selected fields
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	void
	 */
	public function unset_texteditor()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		foreach($args as $arg)
		{
			$this->unset_texteditor[] = $arg;
		}

		return $this;
	}

	/**
	 * Unsets just the jquery library from the js. This function can be used if there is already a jquery included
	 * in the main template. This will avoid all jquery conflicts.
	 *
	 * @return	void
	 */
	public function unset_jquery()
	{
		$this->unset_jquery = true;

		return $this;
	}

	/**
	 * Unsets the jquery UI Javascript and CSS. This function is really useful
	 * when the jquery UI JavaScript and CSS are already included in the main template.
	 * This will avoid all jquery UI conflicts.
	 *
	 * @return	void
	 */
	public function unset_jquery_ui()
	{
		$this->unset_jquery_ui = true;

		return $this;
	}

	/**
	 * Unsets just the twitter bootstrap libraries from the js and css. This function can be used if there is already twitter bootstrap files included
	 * in the main template. If you are already using a bootstrap template then it's not necessary to load the files again.
	 *
	 * @return	void
	 */
	public function unset_bootstrap()
	{
		$this->unset_bootstrap = true;

		return $this;
	}

	/**
	 * Unsets the add operation from the list
	 *
	 * @return	void
	 */
	public function unset_add()
	{
		$this->unset_add = true;

		return $this;
	}

	/**
	 * Unsets the edit operation from the list
	 *
	 * @return	void
	 */
	public function unset_edit()
	{
		$this->unset_edit = true;

		return $this;
	}

	/**
	 * Unsets the delete operation from the list
	 *
	 * @return	void
	 */
	public function unset_delete()
	{
		$this->unset_delete = true;

		return $this;
	}

	/**
	 * Unsets the read operation from the list
	 *
	 * @return	void
	 */
	public function unset_read()
	{
		$this->unset_read = true;

		return $this;
	}

	/**
	 * Just an alias to unset_read
	 *
	 * @return	void
	 * */
	public function unset_view()
	{
		return unset_read();
	}

	/**
	 * Unsets the export button and functionality from the list
	 *
	 * @return	void
	 */
	public function unset_export()
	{
		$this->unset_export = true;

		return $this;
	}


	/**
	 * Unsets the print button and functionality from the list
	 *
	 * @return	void
	 */
	public function unset_print()
	{
		$this->unset_print = true;

		return $this;
	}

	/**
	 * Unsets all the operations from the list
	 *
	 * @return	void
	 */
	public function unset_operations()
	{
		$this->unset_add 	= true;
		$this->unset_edit 	= true;
		$this->unset_delete = true;
		$this->unset_read	= true;
		$this->unset_export = true;
		$this->unset_print  = true;

		return $this;
	}

	/**
	 * Unsets a column from the list
	 *
	 * @return	void.
	 */
	public function unset_columns()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->unset_columns = $args;

		return $this;
	}

	public function unset_list()
	{
		$this->unset_list = true;

		return $this;
	}

	public function unset_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->unset_add_fields = $args;
		$this->unset_edit_fields = $args;
		$this->unset_read_fields = $args;

		return $this;
	}

	public function unset_add_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->unset_add_fields = $args;

		return $this;
	}

	public function unset_edit_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->unset_edit_fields = $args;

		return $this;
	}

	public function unset_read_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->unset_read_fields = $args;

		return $this;
	}


	/**
	 * Unsets everything that has to do with buttons or links with go back to list message
	 * @access	public
	 * @return	void
	 */
	public function unset_back_to_list()
	{
		$this->unset_back_to_list = true;

		return $this;
	}

	/**
	 *
	 * The fields that user will see on add/edit
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	void
	 */
	public function fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->add_fields = $args;
		$this->edit_fields = $args;

		return $this;
	}

	/**
	 *
	 * The fields that user can see . It is only for the add form
	 */
	public function add_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->add_fields = $args;

		return $this;
	}

	/**
	 *
	 *  The fields that user can see . It is only for the edit form
	 */
	public function edit_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->edit_fields = $args;

		return $this;
	}

	public function set_read_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0])) {
			$args = $args[0];
		}

		$this->read_fields = $args;

		return $this;
	}

	/**
	 *
	 * Changes the displaying label of the field
	 * @param $field_name
	 * @param $display_as
	 * @return void
	 */
	public function display_as($field_name, $display_as = null)
	{
		if(is_array($field_name))
		{
			foreach($field_name as $field => $display_as)
			{
				$this->display_as[$field] = $display_as;
			}
		}
		elseif($display_as !== null)
		{
			$this->display_as[$field_name] = $display_as;
		}
		return $this;
	}

	/**
	 *
	 * Load the language strings array from the language file
	 */
	protected function _load_language()
	{
		if($this->language === null)
		{
			$this->language = strtolower($this->config->default_language);
		}
		include($this->default_language_path.'/'.$this->language.'.php');

		foreach($lang as $handle => $lang_string)
			if(!isset($this->lang_strings[$handle]))
				$this->lang_strings[$handle] = $lang_string;

		$this->default_true_false_text = array( $this->l('form_inactive') , $this->l('form_active'));
		$this->subject = $this->subject === null ? $this->l('list_record') : $this->subject;

	}

	protected function _load_date_format()
	{
		list($php_day, $php_month, $php_year) = array('d','m','Y');
		list($js_day, $js_month, $js_year) = array('dd','mm','yy');
		list($ui_day, $ui_month, $ui_year) = array($this->l('ui_day'), $this->l('ui_month'), $this->l('ui_year'));

		$date_format = $this->config->date_format;
		switch ($date_format) {
			case 'uk-date':
				$this->php_date_format 		= "$php_day/$php_month/$php_year";
				$this->js_date_format		= "$js_day/$js_month/$js_year";
				$this->ui_date_format		= "$ui_day/$ui_month/$ui_year";
			break;

			case 'us-date':
				$this->php_date_format 		= "$php_month/$php_day/$php_year";
				$this->js_date_format		= "$js_month/$js_day/$js_year";
				$this->ui_date_format		= "$ui_month/$ui_day/$ui_year";
			break;

			case 'sql-date':
			default:
				$this->php_date_format 		= "$php_year-$php_month-$php_day";
				$this->js_date_format		= "$js_year-$js_month-$js_day";
				$this->ui_date_format		= "$ui_year-$ui_month-$ui_day";
			break;
		}
	}

	/**
	 *
	 * Set a language string directly
	 * @param string $handle
	 * @param string $string
	 */
	public function set_lang_string($handle, $lang_string){
		$this->lang_strings[$handle] = $lang_string;

		return $this;
	}

	/**
	 *
	 * Just an alias to get_lang_string method
	 * @param string $handle
	 */
	public function l($handle)
	{
		return $this->get_lang_string($handle);
	}

	/**
	 *
	 * Get the language string of the inserted string handle
	 * @param string $handle
	 */
	public function get_lang_string($handle)
	{
		return $this->lang_strings[$handle];
	}

	/**
	 *
	 * Simply set the language
	 * @example english
	 * @param string $language
	 */
	public function set_language($language)
	{
		$this->language = $language;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	protected function get_columns()
	{
		if($this->columns_checked === false)
		{
			$field_types = $this->get_field_types();
			if(empty($this->columns))
			{
				$this->columns = array();
				foreach($field_types as $field)
				{
					if( !isset($field->db_extra) || $field->db_extra != 'auto_increment' )
						$this->columns[] = $field->name;
				}
			}

			foreach($this->columns as $col_num => $column)
			{

				if(isset($this->relation[$column]))
				{

					$new_column = $this->_unique_field_name($this->relation[$column][0]);
					$this->columns[$col_num] = $new_column;

					if(isset($this->display_as[$column]))
					{
						$display_as = $this->display_as[$column];
						unset($this->display_as[$column]);
						$this->display_as[$new_column] = $display_as;
					}
					else
					{
						$this->display_as[$new_column] = ucfirst(str_replace('_',' ',$column));
					}

					$column = $new_column;
					$this->columns[$col_num] = $new_column;
				}
				else
				{
					if(!empty($this->relation))
					{
						$table_name  = $this->get_table();
						foreach($this->relation as $relation)
						{
							if( $relation[2] == $column )
							{
								$new_column = $table_name.'.'.$column;
								if(isset($this->display_as[$column]))
								{
									$display_as = $this->display_as[$column];
									unset($this->display_as[$column]);
									$this->display_as[$new_column] = $display_as;
								}
								else
								{
									$this->display_as[$new_column] = ucfirst(str_replace('_',' ',$column));
								}

								$column = $new_column;
								$this->columns[$col_num] = $new_column;
							}
						}
					}

				}

				if(isset($this->display_as[$column]))
					$this->columns[$col_num] = (object)array('field_name' => $column, 'display_as' => $this->display_as[$column]);
				elseif(isset($field_types[$column]))
					$this->columns[$col_num] = (object)array('field_name' => $column, 'display_as' => $field_types[$column]->display_as);
				else
					$this->columns[$col_num] = (object)array('field_name' => $column, 'display_as' =>
						ucfirst(str_replace('_',' ',$column)));

				if(!empty($this->unset_columns) && in_array($column,$this->unset_columns))
				{
					unset($this->columns[$col_num]);
				}
			}

			$this->columns_checked = true;

		}

		return $this->columns;
	}

	/**
	 *
	 * Enter description here ...
	 */
	protected function get_add_fields()
	{
		if($this->add_fields_checked === false)
		{
			$field_types = $this->get_field_types();
			if(!empty($this->add_fields))
			{
				foreach($this->add_fields as $field_num => $field)
				{
					if(isset($this->display_as[$field]))
						$this->add_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $this->display_as[$field]);
					elseif(isset($field_types[$field]->display_as))
						$this->add_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $field_types[$field]->display_as);
					else
						$this->add_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => ucfirst(str_replace('_',' ',$field)));
				}
			}
			else
			{
				$this->add_fields = array();
				foreach($field_types as $field)
				{
					//Check if an unset_add_field is initialize for this field name
					if($this->unset_add_fields !== null && is_array($this->unset_add_fields) && in_array($field->name,$this->unset_add_fields))
						continue;

					if( (!isset($field->db_extra) || $field->db_extra != 'auto_increment') )
					{
						if(isset($this->display_as[$field->name]))
							$this->add_fields[] = (object)array('field_name' => $field->name, 'display_as' => $this->display_as[$field->name]);
						else
							$this->add_fields[] = (object)array('field_name' => $field->name, 'display_as' => $field->display_as);
					}
				}
			}

			$this->add_fields_checked = true;
		}
		return $this->add_fields;
	}

	/**
	 *
	 * Enter description here ...
	 */
	protected function get_edit_fields()
	{
		if($this->edit_fields_checked === false)
		{
			$field_types = $this->get_field_types();
			if(!empty($this->edit_fields))
			{
				foreach($this->edit_fields as $field_num => $field)
				{
					if(isset($this->display_as[$field]))
						$this->edit_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $this->display_as[$field]);
					else
						$this->edit_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $field_types[$field]->display_as);
				}
			}
			else
			{
				$this->edit_fields = array();
				foreach($field_types as $field)
				{
					//Check if an unset_edit_field is initialize for this field name
					if($this->unset_edit_fields !== null && is_array($this->unset_edit_fields) && in_array($field->name,$this->unset_edit_fields))
						continue;

					if(!isset($field->db_extra) || $field->db_extra != 'auto_increment')
					{
						if(isset($this->display_as[$field->name]))
							$this->edit_fields[] = (object)array('field_name' => $field->name, 'display_as' => $this->display_as[$field->name]);
						else
							$this->edit_fields[] = (object)array('field_name' => $field->name, 'display_as' => $field->display_as);
					}
				}
			}

			$this->edit_fields_checked = true;
		}
		return $this->edit_fields;
	}

	/**
	 *
	 * Enter description here ...
	 */
	protected function get_read_fields()
	{
		if($this->read_fields_checked === false)
		{
			$field_types = $this->get_field_types();
			if(!empty($this->read_fields))
			{
				foreach($this->read_fields as $field_num => $field)
				{
					if(isset($this->display_as[$field]))
						$this->read_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $this->display_as[$field]);
					else
						$this->read_fields[$field_num] = (object)array('field_name' => $field, 'display_as' => $field_types[$field]->display_as);
				}
			}
			else
			{
				$this->read_fields = array();
				foreach($field_types as $field)
				{
					//Check if an unset_read_field is initialize for this field name
					if($this->unset_read_fields !== null && is_array($this->unset_read_fields) && in_array($field->name,$this->unset_read_fields))
						continue;

					if(!isset($field->db_extra) || $field->db_extra != 'auto_increment')
					{
						if(isset($this->display_as[$field->name]))
							$this->read_fields[] = (object)array('field_name' => $field->name, 'display_as' => $this->display_as[$field->name]);
						else
							$this->read_fields[] = (object)array('field_name' => $field->name, 'display_as' => $field->display_as);
					}
				}
			}

			$this->read_fields_checked = true;
		}
		return $this->read_fields;
	}

	public function order_by($order_by, $direction = 'asc')
	{
		$this->order_by = array($order_by,$direction);

		return $this;
	}

	public function where($key, $value = NULL, $escape = TRUE)
	{
		$this->where[] = array($key,$value,$escape);

		return $this;
	}

	public function or_where($key, $value = NULL, $escape = TRUE)
	{
		$this->or_where[] = array($key,$value,$escape);

		return $this;
	}

	public function like($field, $match = '', $side = 'both')
	{
		$this->like[] = array($field, $match, $side);

		return $this;
	}

	protected function having($key, $value = '', $escape = TRUE)
	{
		$this->having[] = array($key, $value, $escape);

		return $this;
	}

	protected function or_having($key, $value = '', $escape = TRUE)
	{
		$this->or_having[] = array($key, $value, $escape);

		return $this;
	}

	public function or_like($field, $match = '', $side = 'both')
	{
		$this->or_like[] = array($field, $match, $side);

		return $this;
	}

	public function limit($limit, $offset = '')
	{
		$this->limit = array($limit,$offset);

		return $this;
	}

	protected function _initialize_helpers()
	{
		$ci = &get_instance();

		$ci->load->helper('url');
		$ci->load->helper('form');
	}

	protected function _initialize_variables()
	{
		$ci = &get_instance();
		$ci->load->config('grocery_crud');

		$this->config = (object)array();

		/** Initialize all the config variables into this object */
		$this->config->default_language 	= $ci->config->item('grocery_crud_default_language');
		$this->config->date_format 			= $ci->config->item('grocery_crud_date_format');
		$this->config->default_per_page		= $ci->config->item('grocery_crud_default_per_page');
		$this->config->file_upload_allow_file_types	= $ci->config->item('grocery_crud_file_upload_allow_file_types');
		$this->config->file_upload_max_file_size	= $ci->config->item('grocery_crud_file_upload_max_file_size');
		$this->config->default_text_editor	= $ci->config->item('grocery_crud_default_text_editor');
		$this->config->text_editor_type		= $ci->config->item('grocery_crud_text_editor_type');
		$this->config->character_limiter	= $ci->config->item('grocery_crud_character_limiter');
		$this->config->dialog_forms			= $ci->config->item('grocery_crud_dialog_forms');
		$this->config->paging_options		= $ci->config->item('grocery_crud_paging_options');
        $this->config->default_theme        = $ci->config->item('grocery_crud_default_theme');
        $this->config->environment          = $ci->config->item('grocery_crud_environment');

		/** Initialize default paths */
		$this->default_javascript_path				= $this->default_assets_path.'/js';
		$this->default_css_path						= $this->default_assets_path.'/css';
		$this->default_texteditor_path 				= $this->default_assets_path.'/texteditor';
		$this->default_theme_path					= $this->default_assets_path.'/themes';

		$this->character_limiter = $this->config->character_limiter;

		if ($this->character_limiter === 0 || $this->character_limiter === '0') {
			$this->character_limiter = 1000000; //a very big number
		} elseif($this->character_limiter === null || $this->character_limiter === false) {
			$this->character_limiter = 30; //is better to have the number 30 rather than the 0 value
		}

        if ($this->theme === null && !empty($this->config->default_theme)) {
            $this->set_theme($this->config->default_theme);
        }
	}

	protected function _set_primary_keys_to_model()
	{
		if(!empty($this->primary_keys))
		{
			foreach($this->primary_keys as $primary_key)
			{
				$this->basic_model->set_primary_key($primary_key['field_name'],$primary_key['table_name']);
			}
		}
	}

	/**
	 * Initialize all the required libraries and variables before rendering
	 */
	protected function pre_render()
	{
		$this->_initialize_variables();
		$this->_initialize_helpers();
		$this->_load_language();
		$this->state_code = $this->getStateCode();

		if($this->basic_model === null)
			$this->set_default_Model();

		$this->set_basic_db_table($this->get_table());

		$this->_load_date_format();

		$this->_set_primary_keys_to_model();
	}

	/**
	 *
	 * Or else ... make it work! The web application takes decision of what to do and show it to the final user.
	 * Without this function nothing works. Here is the core of grocery CRUD project.
	 *
	 * @access	public
	 */
	public function render()
	{
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

		}

		return $this->get_layout();
	}

	protected function get_common_data()
	{
		$data = (object)array();

		$data->subject 				= $this->subject;
		$data->subject_plural 		= $this->subject_plural;

		return $data;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_before_insert($callback = null)
	{
		$this->callback_before_insert = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_after_insert($callback = null)
	{
		$this->callback_after_insert = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_insert($callback = null)
	{
		$this->callback_insert = $callback;

		return $this;
	}


	/**
	 *
	 * Enter description here ...
	 */
	public function callback_before_update($callback = null)
	{
		$this->callback_before_update = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_after_update($callback = null)
	{
		$this->callback_after_update = $callback;

		return $this;
	}


	/**
	 *
	 * Enter description here ...
	 * @param mixed $callback
	 */
	public function callback_update($callback = null)
	{
		$this->callback_update = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_before_delete($callback = null)
	{
		$this->callback_before_delete = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_after_delete($callback = null)
	{
		$this->callback_after_delete = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function callback_delete($callback = null)
	{
		$this->callback_delete = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $column
	 * @param mixed $callback
	 */
	public function callback_column($column ,$callback = null)
	{
		$this->callback_column[$column] = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $field
	 * @param mixed $callback
	 */
	public function callback_field($field, $callback = null)
	{
		$this->callback_add_field[$field] = $callback;
		$this->callback_edit_field[$field] = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $field
	 * @param mixed $callback
	 */
	public function callback_add_field($field, $callback = null)
	{
		$this->callback_add_field[$field] = $callback;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $field
	 * @param mixed $callback
	 */
	public function callback_edit_field($field, $callback = null)
	{
		$this->callback_edit_field[$field] = $callback;

		return $this;
	}

	/**
	 *
	 * Callback that replace the default auto uploader
	 *
	 * @param mixed $callback
	 * @return grocery_CRUD
	 */
	public function callback_upload($callback = null)
	{
		$this->callback_upload = $callback;

		return $this;
	}

	/**
	 *
	 * A callback that triggered before the upload functionality. This callback is suggested for validation checks
	 * @param mixed $callback
	 * @return grocery_CRUD
	 */
	public function callback_before_upload($callback = null)
	{
		$this->callback_before_upload = $callback;

		return $this;
	}

	/**
	 *
	 * A callback that triggered after the upload functionality
	 * @param mixed $callback
	 * @return grocery_CRUD
	 */
	public function callback_after_upload($callback = null)
	{
		$this->callback_after_upload = $callback;

		return $this;

	}

	/**
	 *
	 * Gets the basic database table of our crud.
	 * @return string
	 */
	public function get_table()
	{
		if($this->basic_db_table_checked)
		{
			return $this->basic_db_table;
		}
		elseif( $this->basic_db_table !== null )
		{
			if(!$this->table_exists($this->basic_db_table))
			{
				throw new Exception('The table name does not exist. Please check you database and try again.',11);
				die();
			}
			$this->basic_db_table_checked = true;
			return $this->basic_db_table;
		}
		else
		{
			//Last try , try to find the table from your view / function name!!! Not suggested but it works .
			$last_chance_table_name = $this->get_method_name();
			if($this->table_exists($last_chance_table_name))
			{
				$this->set_table($last_chance_table_name);
			}
			$this->basic_db_table_checked = true;
			return $this->basic_db_table;

		}

		return false;
	}

	/**
	 *
	 * The field names of the required fields
	 */
	public function required_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->required_fields = $args;

		return $this;
	}

	/**
	 * Add the fields that they are as UNIQUE in the database structure
	 *
	 * @return grocery_CRUD
	 */
	public function unique_fields()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}

		$this->_unique_fields = $args;

		return $this;
	}

	/**
	 *
	 * Sets the basic database table that we will get our data.
	 * @param string $table_name
	 * @return grocery_CRUD
	 */
	public function set_table($table_name)
	{
		if(!empty($table_name) && $this->basic_db_table === null)
		{
			$this->basic_db_table = $table_name;
		}
		elseif(!empty($table_name))
		{
			throw new Exception('You have already insert a table name once...', 1);
		}
		else
		{
			throw new Exception('The table name cannot be empty.', 2);
			die();
		}

		return $this;
	}

	/**
	 * Set a full URL path to this method.
	 *
	 * This method is useful when the path is not specified correctly.
	 * Especially when we are using routes.
	 * For example:
	 * Let's say we have the path http://www.example.com/ however the original url path is
	 * http://www.example.com/example/index . We have to specify the url so we can have
	 * all the CRUD operations correctly.
	 * The url path has to be set from this method like this:
	 * <code>
	 * 		$crud->set_crud_url_path(site_url('example/index'));
	 * </code>
	 *
	 * @param string $crud_url_path
	 * @param string $list_url_path
	 * @return grocery_CRUD
	 */
	public function set_crud_url_path($crud_url_path, $list_url_path = null)
	{
		$this->crud_url_path = $crud_url_path;

		//If the list_url_path is empty so we are guessing that the list_url_path
		//will be the same with crud_url_path
		$this->list_url_path = !empty($list_url_path) ? $list_url_path : $crud_url_path;

		return $this;
	}

	/**
	 *
	 * Set a subject to understand what type of CRUD you use.
     * ----------------------------------------------------------------------------------------------
     * Subject_plural: Sets the subject to its plural form. For example the plural
     * of "Customer" is "Customers", "Product" is "Products"... e.t.c.
	 * @example In this CRUD we work with the table db_categories. The $subject will be the 'Category'
     * and the $subject_plural will be 'Categories'
	 * @param string $subject
	 * @param string $subject_plural
	 * @return grocery_CRUD
	 */
	public function set_subject($subject, $subject_plural = null)
	{
		$this->subject = $subject;
        $this->subject_plural 	= $subject_plural === null ? $subject : $subject_plural;

		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $title
	 * @param $image_url
	 * @param $url
	 * @param $css_class
	 * @param $url_callback
	 */
	public function add_action( $label, $image_url = '', $link_url = '', $css_class = '', $url_callback = null)
	{
		$unique_id = substr($label,0,1).substr(md5($label.$link_url),-8); //The unique id is used for class name so it must begin with a string

		$this->actions[$unique_id]  = (object)array(
			'label' 		=> $label,
			'image_url' 	=> $image_url,
			'link_url'		=> $link_url,
			'css_class' 	=> $css_class,
			'url_callback' 	=> $url_callback,
			'url_has_http'	=> substr($link_url,0,7) == 'http://' || substr($link_url,0,8) == 'https://' ? true : false
		);

		return $this;
	}

	/**
	 *
	 * Set a simple 1-n foreign key relation
	 * @param string $field_name
	 * @param string $related_table
	 * @param string $related_title_field
	 * @param mixed $where_clause
	 * @param string $order_by
     * @return Grocery_CRUD
	 */
	public function set_relation($field_name , $related_table, $related_title_field, $where_clause = null, $order_by = null)
	{
		$this->relation[$field_name] = array($field_name, $related_table,$related_title_field, $where_clause, $order_by);
		return $this;
	}

	/**
	 *
	 * Sets a relation with n-n relationship.
	 * @param string $field_name
	 * @param string $relation_table
	 * @param string $selection_table
	 * @param string $primary_key_alias_to_this_table
	 * @param string $primary_key_alias_to_selection_table
	 * @param string $title_field_selection_table
	 * @param string $priority_field_relation_table
	 * @param mixed $where_clause
     * @return Grocery_CRUD
	 */
	public function set_relation_n_n($field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table, $primary_key_alias_to_selection_table , $title_field_selection_table , $priority_field_relation_table = null, $where_clause = null)
	{
		$this->relation_n_n[$field_name] =
			(object)array(
				'field_name' => $field_name,
				'relation_table' => $relation_table,
				'selection_table' => $selection_table,
				'primary_key_alias_to_this_table' => $primary_key_alias_to_this_table,
				'primary_key_alias_to_selection_table' => $primary_key_alias_to_selection_table ,
				'title_field_selection_table' => $title_field_selection_table ,
				'priority_field_relation_table' => $priority_field_relation_table,
				'where_clause' => $where_clause
			);

		return $this;
	}

	/**
	 *
	 * Transform a field to an upload field
	 *
	 * @param string $field_name
	 * @param string $upload_path
     * @return Grocery_CRUD
	 */
	public function set_field_upload($field_name, $upload_dir = '', $allowed_file_types = '')
	{
		$upload_dir = !empty($upload_dir) && substr($upload_dir,-1,1) == '/'
						? substr($upload_dir,0,-1)
						: $upload_dir;
		$upload_dir = !empty($upload_dir) ? $upload_dir : 'assets/uploads/files';

		/** Check if the upload Url folder exists. If not then throw an exception **/
		if (!is_dir(FCPATH.$upload_dir)) {
			throw new Exception("It seems that the folder \"".FCPATH.$upload_dir."\" for the field name
					\"".$field_name."\" doesn't exists. Please create the folder and try again.");
		}

		$this->upload_fields[$field_name] = (object) array(
				'field_name' => $field_name,
				'upload_path' => $upload_dir,
				'allowed_file_types' => $allowed_file_types,
				'encrypted_field_name' => $this->_unique_field_name($field_name));
		return $this;
	}
}

if(defined('CI_VERSION'))
{
	$ci = &get_instance();
	$ci->load->library('Form_validation');

	class grocery_CRUD_Form_validation extends CI_Form_validation{

		public $CI;
		public $_field_data			= array();
		public $_config_rules		= array();
		public $_error_array		= array();
		public $_error_messages		= array();
		public $_error_prefix		= '<p>';
		public $_error_suffix		= '</p>';
		public $error_string		= '';
		public $_safe_form_data		= FALSE;
	}
}

/*
 * jQuery File Upload Plugin PHP Example 5.5
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class UploadHandler
{
    private $options;
    public $default_config_path = null;

    function __construct($options=null) {
        $this->options = array(
            'script_url' => $this->getFullUrl().'/'.basename(__FILE__),
            'upload_dir' => dirname(__FILE__).'/files/',
            'upload_url' => $this->getFullUrl().'/files/',
            'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            // Set the following option to false to enable non-multipart uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname(__FILE__).'/files/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200
                ),

                'thumbnail' => array(
                    'upload_dir' => dirname(__FILE__).'/thumbnails/',
                    'upload_url' => $this->getFullUrl().'/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80
                )
                */
            )
        );
        if ($options) {
            // Or else for PHP >= 5.3.0 use: $this->options = array_replace_recursive($this->options, $options);
            foreach($options as $option_name => $option)
            {
            	$this->options[$option_name] = $option;
            }
        }
    }

    function getFullUrl() {
      	return
        		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
        		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
        		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
        		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
        		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
        		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    private function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].rawurlencode($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url']
                        .rawurlencode($file->name);
                }
            }
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name);
            $file->delete_type = 'DELETE';
            return $file;
        }
        return null;
    }

    private function get_file_objects() {
        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            scandir($this->options['upload_dir'])
        )));
    }

    private function create_scaled_image($file_name, $options) {
        $file_path = $this->options['upload_dir'].$file_name;
        $new_file_path = $options['upload_dir'].$file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale > 1) {
            $scale = 1;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                break;
            default:
                $src_img = $image_method = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    private function has_error($uploaded_file, $file, $error) {
        if ($error) {
			switch($error) {
				case UPLOAD_ERR_INI_SIZE:
					return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
					break;
				case UPLOAD_ERR_PARTIAL:
					return 'The uploaded file was only partially uploaded.';
					break;
				case UPLOAD_ERR_NO_FILE:
					return 'No file was uploaded.';
					break;
				case UPLOAD_ERR_CANT_WRITE:
					return 'Failed to write file to disk.';
					break;
				case UPLOAD_ERR_EXTENSION:
					return 'File upload stopped by extension.';
					break;
				default:
					return $error;
					break;
			}
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }

        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }

    private function trim_file_name($name, $type) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }

        //Ensure that we don't have disallowed characters and add a unique id just to ensure that the file name will be unique
        $file_name = substr(uniqid(),-5).'-'.$this->_transliterate_characters($file_name);

        //all the characters has to be lowercase
        $file_name = strtolower($file_name);

        return $file_name;
    }

    private function _transliterate_characters($file_name)
	{
		include($this->default_config_path.'/translit_chars.php');
		if ( isset($translit_characters))
		{
			$file_name = preg_replace(array_keys($translit_characters), array_values($translit_characters), $file_name);
		}

		$file_name = preg_replace("/([^a-zA-Z0-9\.\-\_]+?){1}/i", '-', $file_name);
		$file_name = str_replace(" ", "-", $file_name);

		return preg_replace('/\-+/', '-', trim($file_name, '-'));
	}

    private function orient_image($file_path) {
      	$exif = exif_read_data($file_path);
      	$orientation = intval(@$exif['Orientation']);
      	if (!in_array($orientation, array(3, 6, 8))) {
      	    return false;
      	}
      	$image = @imagecreatefromjpeg($file_path);
      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}
      	$success = imagejpeg($image, $file_path);
      	// Free up memory (imagedestroy does not delete files):
      	@imagedestroy($image);
      	return $success;
    }

    private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type);
        $file->size = intval($size);
        $file->type = $type;
        $error = $this->has_error($uploaded_file, $file, $error);
        if (!$error && $file->name) {
            $file_path = $this->options['upload_dir'].$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] &&
                is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
            		if ($this->options['orient_image']) {
            		    $this->orient_image($file_path);
            		}
                $file->url = $this->options['upload_url'].rawurlencode($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
                    if ($this->create_scaled_image($file->name, $options)) {
                        $file->{$version.'_url'} = $options['upload_url']
                            .rawurlencode($file->name);
                    }
                }
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = "It seems that this user doesn't have permissions to upload to this folder";
            }
            $file->size = $file_size;
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name);
            $file->delete_type = 'DELETE';
        } else {
            $file->error = $error;
        }
        return $file;
    }

    public function get() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo json_encode($info);
    }

    public function post() {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                isset($upload['error']) ? $upload['error'] : null
            );
        }
        header('Vary: Accept');

        $redirect = isset($_REQUEST['redirect']) ?
            stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        return $info;
    }

    public function delete() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'].$file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Content-type: application/json');
        echo json_encode($success);
    }

}
