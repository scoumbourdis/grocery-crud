<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * PHP grocery CRUD
 *
 * A Codeigniter library that creates a CRUD automatically with just few lines of code.
 *
 * Copyright (C) 2010 - 2012  John Skoumbourdis. 
 *
 * LICENSE
 *
 * Grocery CRUD is released with dual licensing, using the GPL v3 (license-gpl3.txt) and the MIT license (license-mit.txt).
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2012, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @version    	1.2
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
		if($this->field_types !== null)
			return $this->field_types;
		
		$types	= array();
		foreach($this->basic_model->get_field_types_basic_table() as $field_info)
		{
			$field_info->required = !empty($this->required_fields) && in_array($field_info->name,$this->required_fields) ? true : false;
			 
			$field_info->display_as = 
				isset($this->display_as[$field_info->name]) ? 
					$this->display_as[$field_info->name] : 
					ucfirst(str_replace("_"," ",$field_info->name));
					
			if($this->change_field_type != null && isset($this->change_field_type[$field_info->name]))
			{
				$field_info->crud_type 	= $this->change_field_type[$field_info->name]->type;
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
					$field_info->extras 	= $this->relation[$field_info->name];
				break;			
				
				case 'upload_file':
					$field_info->extras 	= $this->upload_fields[$field_info->name];
				break;
				
				case 'hidden':
					if(isset($this->change_field_type[$field_info->name]->value))
						$field_info->extras = $this->change_field_type[$field_info->name]->value;
					else
						$field_info->extras = false;
				break;
				
				default:
					$field_info->extras = false;
				break;
			}
			
			$types[$field_info->name] = $field_info;
		}
		
		if(!empty($this->relation_n_n))
		{
			foreach($this->relation_n_n as $field_name => $field_extras)
			{
				$field_info = (object)array();
				$field_info->name		= $field_name;
				$field_info->crud_type 	= 'relation_n_n';
				$field_info->extras 	= $field_extras;
				$field_info->required	= false; //Temporary false
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
				
				if(!isset($types[$field_name]))
				{
					$field_info = (object)array(
						'name' => $field_name, 
						'crud_type' => 'string',
						'display_as' => isset($this->display_as[$field_name]) ? 
												$this->display_as[$field_name] : 
												ucfirst(str_replace("_"," ",$field_name)),
						'required'	=> in_array($field_name,$this->required_fields) ? true : false
					);
					
					$types[$field_name] = $field_info;
				}
			}
		
		if(!empty($this->edit_fields))
			foreach($this->edit_fields as $field_object)
			{
				$field_name = isset($field_object->field_name) ? $field_object->field_name : $field_object;
				
				if(!isset($types[$field_name]))
				{
					$field_info = (object)array(
						'name' => $field_name, 
						'crud_type' => 'string',
						'display_as' => isset($this->display_as[$field_name]) ? 
												$this->display_as[$field_name] : 
												ucfirst(str_replace("_"," ",$field_name)),
						'required'	=> in_array($field_name,$this->required_fields) ? true : false
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
	
	protected function get_field_input($field_info, $value = null)
	{
			$real_type = $field_info->crud_type;
			switch ($real_type) {
				case 'integer':
					$field_info->input = $this->get_integer_input($field_info,$value);
				break;
				case 'true_false':
					$field_info->input = $this->get_true_false_input($field_info,$value);
				break;
				case 'string':
					$field_info->input = $this->get_string_input($field_info,$value);
				break;
				case 'text':
					$field_info->input = $this->get_text_input($field_info,$value);
				break;
				case 'date':
					$field_info->input = $this->get_date_input($field_info,$value);
				break;
				case 'datetime':
					$field_info->input = $this->get_datetime_input($field_info,$value);
				break;			
				case 'enum':
					$field_info->input = $this->get_enum_input($field_info,$value);
				break;
				case 'set':
					$field_info->input = $this->get_set_input($field_info,$value);
				break;
				case 'relation':
					$field_info->input = $this->get_relation_input($field_info,$value);
				break;
				case 'relation_n_n':
					$field_info->input = $this->get_relation_n_n_input($field_info,$value);
				break;								
				case 'upload_file':
					$field_info->input = $this->get_upload_file_input($field_info,$value);
				break;
				case 'hidden':
					$field_info->input = $this->get_hidden_input($field_info,$value);
				break;
				case 'password':
					$field_info->input = $this->get_password_input($field_info,$value);
				break;															
				case 'readonly':
					$field_info->input = $this->get_readonly_input($field_info,$value);;
				break;				
				
				default:
					$field_info->input = $this->get_string_input($field_info,$value);
				break;
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
				if(isset($this->default_true_false_text[$value]))
					$value = $this->default_true_false_text[$value];
			break;
			case 'string':
				$value = $this->character_limiter($value,30,"...");
			break;
			case 'text':
				$value = $this->character_limiter(strip_tags($value),30,"...");
			break;
			case 'date':
				if(!empty($value) && $value != '0000-00-00' && $value != '1970-01-01')
				{
					list($year,$month,$day) = explode("-",$value);
					$value = date ("d M Y",mktime (0,0,0,(int)$month , (int)$day , (int)$year));
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
					$value = date ("d M Y - H:i", mktime ( (int)$hours , (int)$minutes ,0, (int)$month , (int)$day ,(int)$year));
				}
				else 
				{
					$value = '';
				}
			break;
			case 'enum':
				$value = $this->character_limiter($value,20,"...");
			break;	
			case 'relation_n_n':
				$value = $this->character_limiter($value,30,"...");
			break;						
			
			case 'password':
				$value = '******';
			break;
			
			case 'upload_file':
				$value = !empty($value) ? 
							"<a href='".base_url().$field_info->extras->upload_path."/$value' target='_blank'>".
								$this->character_limiter($value,20,"...",true).
							"</a>":
							"";
			break;
			
			default:
				$value = $this->character_limiter($value,30,"...");
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
	function character_limiter($str, $n = 500, $end_char = '&#8230;', $force = false)
	{
		if (strlen($str) < $n)
		{
			return $str;
		}

		if($force === true)
		{
			return substr($str,0,$n).$end_char;
		}
		
		$str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

		if (strlen($str) <= $n)
		{
			return $str;
		}

		$out = "";
		foreach (explode(' ', trim($str)) as $val)
		{
			$out .= $val.' ';

			if (strlen($out) >= $n)
			{
				$out = trim($out);
				return (strlen($out) == strlen($str)) ? $out : $out.$end_char;
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
 * @version    	1.2  
 * @link		http://www.grocerycrud.com/documentation
 */
class grocery_CRUD_Model_Driver extends grocery_CRUD_Field_Types
{
	/**
	 * @var grocery_CRUD_Model
	 */
	public $basic_model = null;
	
	protected function set_default_Model()
	{
		$ci = &get_instance();
		$ci->load->model('grocery_CRUD_Model');
		
		$this->basic_model = $ci->grocery_CRUD_Model;
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
		$ci->load->model('grocery_CRUD_Model');	
		
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
			if(!empty($this->relation))
				foreach($this->relation as $relation_name => $relation_values)
					$temp_relation[$this->_unique_field_name($relation_name)] = $this->_get_field_names_to_search($relation_values);
			
			if($state_info->search->field != null)
			{			
				if(isset($temp_relation[$state_info->search->field]))
				{
					if(is_array($temp_relation[$state_info->search->field]))
						foreach($temp_relation[$state_info->search->field] as $search_field)
							$this->or_like($search_field , $state_info->search->text);
					else
						$this->like($temp_relation[$state_info->search->field] , $state_info->search->text);
				}
				elseif(isset($this->relation_n_n[$state_info->search->field]))
				{
					$escaped_text = $this->basic_model->escape_str($state_info->search->text);
					$this->having($state_info->search->field." LIKE '%".$escaped_text."%'");
				} 
				else
				{
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

			if($this->callback_insert == null)
			{
				if($this->callback_before_insert != null)
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
					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
					{
						if(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && $post_data[$field->field_name] === '')
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
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'set')
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
				
				if($this->callback_after_insert != null)
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
			
			if($this->callback_update == null)
			{
				if($this->callback_before_update != null)
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
					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
					{
						if(isset($types[$field->field_name]->db_null) && $types[$field->field_name]->db_null && $post_data[$field->field_name] === '')
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
						elseif(isset($types[$field->field_name]->crud_type) && $types[$field->field_name]->crud_type == 'set')
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
				$this->basic_model->db_update($update_data, $primary_key);
				
				if(!empty($this->relation_n_n))
				{
					foreach($this->relation_n_n as $field_name => $field_info)
					{
						$relation_data = isset( $post_data[$field_name] ) ? $post_data[$field_name] : array() ; 
						$this->db_relation_n_n_update($field_info, $relation_data ,$primary_key);
					}
				}				
				
				if($this->callback_after_update != null)
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
    
	protected function db_delete($state_info)
	{
		$primary_key 	= $state_info->primary_key;
		
		if($this->callback_delete == null)
		{
			if($this->callback_before_delete != null)
			{
				$callback_return = call_user_func($this->callback_before_delete, $primary_key);
				
				if($callback_return === false) 
				{
					return false;
				}
				
			}
			
			if(!empty($this->relation_n_n))
			{
				foreach($this->relation_n_n as $field_name => $field_info)
				{
					$this->db_relation_n_n_delete( $field_info, $primary_key );
				}
			}					
			
			$delete_result = $this->basic_model->db_delete($primary_key);
			
			if($delete_result === false)
			{
				return false;
			}
			
			if($this->callback_after_delete != null)
			{
				$callback_return = call_user_func($this->callback_after_delete, $primary_key);
				
				if($callback_return === false) 
				{
					return false;
				}
				
			}				
		}
		else
		{
			$callback_return = call_user_func($this->callback_delete, $primary_key);
				
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
		
		if($this->config['crud_paging'] === true)
		{
			if($this->limit == null)
			{
				$ci = &get_instance();
				$ci->load->config('grocery_crud');
				
				$default_per_page = $ci->config->item('grocery_crud_default_per_page');
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
				
				$ci = &get_instance();
				$ci->config->load('grocery_crud');		
				
				$allowed_files = $ci->config->item('grocery_crud_file_upload_allow_file_types');
				$reg_exp = '/(\\.|\\/)('.$allowed_files.')$/i';		

				$max_file_size_ui = $ci->config->item('grocery_crud_file_upload_max_file_size');
				$max_file_size_bytes = $this->_convert_bytes_ui_to_bytes($max_file_size_ui);
			
				$options = array(
					'upload_dir' 		=> $upload_info->upload_path.'/',
					'param_name'		=> $this->_unique_field_name($state_info->field_name),
					'upload_url'		=> base_url().$upload_info->upload_path.'/',
					'accept_file_types' => $reg_exp,
					'max_file_size'		=> $max_file_size_bytes
				);
				$upload_handler = new UploadHandler($options);
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
 * @copyright  	Copyright (c) 2010 through 2012, John Skoumbourdis
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
 * @version    	1.2
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
		$data->ajax_list_url		= $this->getAjaxListUrl();
		$data->ajax_list_info_url	= $this->getAjaxListInfoUrl();
		$data->actions				= $this->actions;
		$data->unique_hash			= $this->get_method_hash();
		
		$data->unset_add			= $this->unset_add;
		$data->unset_edit			= $this->unset_edit;
		$data->unset_delete			= $this->unset_delete;
		
		$ci = &get_instance();
		$ci->load->config('grocery_crud');
		
		$default_per_page = $ci->config->item('grocery_crud_default_per_page');
		$data->paging_options = array('10','25','50','100');
		$data->default_per_page		= is_numeric($default_per_page) && $default_per_page >1 && in_array($default_per_page,$data->paging_options)? $default_per_page : 25; 
		
		if($data->list === false)
		{
			throw new Exception('It is impossible to get data. Please check your model and try again.', 13);
			$data->list = array();
		}
		
		foreach($data->list as $num_row => $row)
		{
			$data->list[$num_row]->edit_url = $data->edit_url.'/'.$row->{$data->primary_key};
			$data->list[$num_row]->delete_url = $data->delete_url.'/'.$row->{$data->primary_key};
		}
		
		if(!$ajax)
		{
			$data->list_view = $this->_theme_view('list.php',$data,true);
			$this->_theme_view('list_template.php',$data);	
		}
		else
		{
			$this->set_echo_and_die();
			$this->_theme_view('list.php',$data);
		}
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
		$this->set_js($this->default_javascript_path.'/jquery-1.7.1.min.js');
		
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
		
		$data->list_url 		= $this->getListUrl();
		$data->insert_url		= $this->getInsertUrl();
		$data->validation_url	= $this->getValidationInsertUrl();
		$data->input_fields 	= $this->get_add_input_fields();
		
		$data->fields 			= $this->get_add_fields();
		$data->hidden_fields	= $this->get_add_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
		
		$this->_theme_view('add.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
	}
	
	protected function showEditForm($state_info)
	{
		$this->set_js($this->default_javascript_path.'/jquery-1.7.1.min.js');
		
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
		
		$data->field_values = $this->get_edit_values($state_info->primary_key);
		
		$data->add_url		= $this->getAddUrl();
		
		$data->list_url 	= $this->getListUrl();
		$data->update_url	= $this->getUpdateUrl($state_info);
		$data->delete_url	= $this->getDeleteUrl($state_info);
		$data->input_fields = $this->get_edit_input_fields($data->field_values);

		$data->fields 		= $this->get_edit_fields();
		$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
		
		$data->validation_url	= $this->getValidationUpdateUrl($state_info->primary_key); 
		
		$this->_theme_view('edit.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
	}
	
	protected function delete_layout($delete_result = true)
	{
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
				return $this->l('insert_success_message')." <a href='".$this->getEditUrl($field_info->primary_key)."'>".$this->l('form_edit')." {$this->subject}</a> ";
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
		if($insert_result === false)
		{
			echo json_encode(array('success' => false));	
		}
		else 
		{
			$success_message = '<p>'.$this->l('insert_success_message');
			
			if(!$this->unset_back_to_list && !empty($insert_result) && !$this->unset_edit)
			{
				$success_message .= " <a href='".$this->getEditUrl($insert_result)."'>".$this->l('form_edit')." {$this->subject}</a> ".$this->l('form_or');
			}
			
			if(!$this->unset_back_to_list)
			{
				$success_message .= " <a href='".$this->getListUrl()."'>".$this->l('form_go_back_to_list')."</a>";
			}
			
			$success_message .= '</p>';
			
			echo "<textarea>".json_encode(array(
					'success' => true , 
					'insert_primary_key' => $insert_result, 
					'success_message' => $success_message,
					'success_list_url'	=> $this->getListSuccessUrl($insert_result)
			))."</textarea>";
		}
		$this->set_echo_and_die();
	}

	protected function validation_layout($validation_result)
	{
		echo "<textarea>".json_encode($validation_result)."</textarea>";
		$this->set_echo_and_die();
	}

	protected function upload_layout($upload_result, $field_name)
	{
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

	public function get_css_files()
	{
		return $this->css_files;
	}

	public function get_js_files()
	{
		return $this->js_files;
	}	
	
	protected function get_layout()
	{		
		$js_files = $this->get_js_files();
		$css_files =  $this->get_css_files();

		if($this->unset_jquery)
			unset($js_files[sha1($this->default_javascript_path.'/jquery-1.7.1.min.js')]);
		
		if($this->echo_and_die === false)
		{
			return (object)array('output' => $this->views_as_string, 'js_files' => $js_files, 'css_files' => $css_files);
		}
		elseif($this->echo_and_die === true)
		{
			echo $this->views_as_string;
			die();
		}	
	}
	
	protected function update_layout($update_result = false, $state_info = null)
	{
		if($update_result === false)
		{
			echo json_encode(array('success' => $update_result));	
		}
		else 
		{
			$success_message = '<p>'.$this->l('update_success_message');
			if(!$this->unset_back_to_list)
			{
				$success_message .= " <a href='".$this->getListUrl()."'>".$this->l('form_go_back_to_list')."</a>";
			}
			$success_message .= '</p>';
			
			/* The textarea is only because of a BUG of the jquery form plugin with the combination of multipart forms */
			echo "<textarea>".json_encode(array(
					'success' => true , 
					'insert_primary_key' => $update_result, 
					'success_message' => $success_message,
					'success_list_url'	=> $this->getListSuccessUrl($state_info->primary_key)
			))."</textarea>";
		}
		$this->set_echo_and_die();
	}
	
	protected function get_integer_input($field_info,$value)
	{
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.numeric.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.numeric.config.js');
		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'"; 
		$input = "<input name='{$field_info->name}' type='text' value='$value' class='numeric' $extra_attributes />";
		return $input;
	}

	protected function get_true_false_input($field_info,$value)
	{
		$input = "<input name='{$field_info->name}' type='text' value='$value' class='numeric' />";
		
		$checked = $value == 1 ? "checked = 'checked'" : "";
		$input = "<label><input type='radio' name='{$field_info->name}' value='1' $checked /> ".$this->default_true_false_text[1]."</label> ";
		$checked = $value === '0' ? "checked = 'checked'" : ""; 
		$input .= "<label><input type='radio' name='{$field_info->name}' value='0' $checked /> ".$this->default_true_false_text[0]."</label>";
		
		return $input;
	}	
	
	protected function get_string_input($field_info,$value)
	{
		$value = !is_string($value) ? '' : str_replace('"',"&quot;",$value); 
		
		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'"; 
		$input = "<input name='{$field_info->name}' type='text' value=\"$value\" $extra_attributes />";
		return $input;
	}

	protected function get_text_input($field_info,$value)
	{   
		if($field_info->extras == 'text_editor')
		{
			$this->set_js($this->default_texteditor_path.'/jquery.tinymce.js');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.tine_mce.config.js');
			$input = "<textarea name='{$field_info->name}' class='texteditor' >$value</textarea>";
		}
		else
		{
			$input = "<textarea name='{$field_info->name}'>$value</textarea>";
		}
		return $input;
	}
	
	protected function get_datetime_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/ui/simple/jquery-ui-1.8.10.custom.css');
		$this->set_css($this->default_css_path.'/jquery_plugins/jquery.ui.datetime.css');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery-ui-1.8.10.custom.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.ui.datetime.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.datetime.config.js');
		
		if(!empty($value) && $value != '0000-00-00 00:00:00' && $value != '1970-01-01 00:00:00'){
			list($year,$month,$day) = explode('-',substr($value,0,10));
			$date = date($this->php_date_format, mktime(0,0,0,$month,$day,$year));
			$datetime = $date.substr($value,10);	
		}
		else 
		{
			$datetime = '';
		}
		$input = "<input name='{$field_info->name}' type='text' value='$datetime' maxlength='19' class='datetime-input' /> 
		<a class='datetime-input-clear' tabindex='-1'>".$this->l('form_button_clear')."</a>
		({$this->ui_date_format}) hh:mm:ss";
		return $input;
	}
	
	protected function get_hidden_input($field_info,$value)
	{
		if($field_info->extras != null && $field_info->extras != false)
			$value = $field_info->extras;
		$input = "<input type='hidden' name='{$field_info->name}' value='$value' />";
		return $input;		
	}
	
	protected function get_password_input($field_info,$value)
	{
		$value = !is_string($value) ? '' : $value; 
		
		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'"; 
		$input = "<input name='{$field_info->name}' type='password' value='$value' $extra_attributes />";
		return $input;
	}
	
	protected function get_date_input($field_info,$value)
	{	
		$this->set_css($this->default_css_path.'/ui/simple/jquery-ui-1.8.10.custom.css');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery-ui-1.8.10.custom.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.datepicker.config.js');
		
		if(!empty($value) && $value != '0000-00-00' && $value != '1970-01-01')
		{
			list($year,$month,$day) = explode('-',substr($value,0,10));
			$date = date($this->php_date_format, mktime(0,0,0,$month,$day,$year));
		}
		else
		{
			$date = '';
		}
		
		$input = "<input name='{$field_info->name}' type='text' value='$date' maxlength='10' class='datepicker-input' /> 
		<a class='datepicker-input-clear' tabindex='-1'>".$this->l('form_button_clear')."</a> (".$this->ui_date_format.")";
		return $input;
	}	

	protected function get_enum_input($field_info,$value)
	{		
		$input = "<select name='{$field_info->name}'>";
			
		$options_array = explode("','",substr($field_info->db_max_length,1,-1));
		foreach($options_array as $option)
		{
			$selected = !empty($value) && $value == $option ? "selected='selected'" : ''; 
			$input .= "<option value='$option' $selected >$option</option>";	
		}
		
		$input .= "</select>";
		return $input;
	}
	
	protected function get_readonly_input($field_info,$value)
	{
		return '<div class="readonly_label">'.$value.'</div>';
	}
	
	protected function get_set_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/jquery_plugins/chosen/chosen.css');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.chosen.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/ajax-chosen.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');
		
		$options_array = explode("','",substr($field_info->db_max_length,1,-1));
		$selected_values 	= !empty($value) ? explode(",",$value) : array();
		
		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));
		$input = "<select name='{$field_info->name}[]' multiple='multiple' size='8' class='chosen-multiple-select' data-placeholder='$select_title' style='width:510px;' >";
		
		foreach($options_array as $option)
		{
			$selected = !empty($value) && in_array($option,$selected_values) ? "selected='selected'" : ''; 
			$input .= "<option value='$option' $selected >$option</option>";	
		}
			
		$input .= "</select>";
		
		return $input;
	}	
	
	protected function get_relation_input($field_info,$value)
	{
		$this->set_css($this->default_css_path.'/jquery_plugins/chosen/chosen.css');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.chosen.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/ajax-chosen.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');

		$ci = &get_instance();
		$ci->load->config('grocery_crud');
		
		$ajax_limitation = $ci->config->item('grocery_crud_set_relation_max_data_without_ajax');
		$total_rows = $this->get_relation_total_rows($field_info->extras);

		
		//Check if we will use ajax for our queries or just clien-side javascript
		$using_ajax = $total_rows > $ajax_limitation ? true : false;		
		
		//We will not use it for now. It is not ready yet. Probably we will have this functionality at version 1.2.2
		$using_ajax = false;
		
		//If total rows are more than the limitation, use the ajax plugin
		$ajax_or_not_class = $using_ajax ? 'chosen-select' : 'chosen-select';
		
		$this->_inline_js("var ajax_relation_url = '".$this->getAjaxRelationUrl()."';\n");
		
		$select_title = str_replace('{field_display_as}',$field_info->display_as,$this->l('set_relation_title'));
		$input = "<select name='{$field_info->name}' id='' class='$ajax_or_not_class' data-placeholder='$select_title' style='width:300px'>";
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
	
	protected function get_relation_n_n_input($field_info_type, $selected_values)
	{	
		$has_priority_field = !empty($field_info_type->extras->priority_field_relation_table) ? true : false;
		
		if($has_priority_field)
		{
			$this->set_css($this->default_css_path.'/ui/simple/jquery-ui-1.8.10.custom.css');	
			$this->set_css($this->default_css_path.'/jquery_plugins/ui.multiselect.css');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery-ui-1.8.10.custom.min.js');	
			$this->set_js($this->default_javascript_path.'/jquery_plugins/ui.multiselect.js');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.multiselect.js');
		}
		else 
		{
			$this->set_css($this->default_css_path.'/jquery_plugins/chosen/chosen.css');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.chosen.min.js');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/ajax-chosen.js');
			$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.chosen.config.js');
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
			$css_class = $has_priority_field ? 'multiselect': 'chosen-multiple-select';
			$width_style = $has_priority_field ? '' : 'width:510px;';

			$select_title = str_replace('{field_display_as}',$field_info_type->display_as,$this->l('set_relation_title'));
			$input = "<select name='{$field_info_type->name}[]' multiple='multiple' size='8' class='$css_class' data-placeholder='$select_title' style='$width_style' >";
			
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
		$this->set_css($this->default_css_path.'/ui/simple/jquery-ui-1.8.10.custom.css');
		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/file-uploader.css');
		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/jquery.fileupload-ui.css');

		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery-ui-1.8.10.custom.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/tmpl.min.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/load-image.min.js');

		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.iframe-transport.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/jquery.fileupload.js');
		$this->set_js($this->default_javascript_path.'/jquery_plugins/config/jquery.fileupload.config.js');
		
		$unique = uniqid();
		
		$ci = &get_instance();
		$ci->config->load('grocery_crud');		
		
		$allowed_files = $ci->config->item('grocery_crud_file_upload_allow_file_types');
		$allowed_files_ui = '.'.str_replace('|',',.',$allowed_files);
		$max_file_size_ui = $ci->config->item('grocery_crud_file_upload_max_file_size');
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
			var message_promt_delete_file 	= "'.$this->l('message_promt_delete_file').'";
			
			var error_max_number_of_files 	= "'.$this->l('error_max_number_of_files').'";
			var error_accept_file_types 	= "'.$this->l('error_accept_file_types').'";
			var error_max_file_size 		= "'.str_replace("{max_file_size}",$max_file_size_ui,$this->l('error_max_file_size')).'";
			var error_min_file_size 		= "'.$this->l('error_min_file_size').'";				
		');		
		
		
		
		$uploader_display_none 	= empty($value) ? "" : "display:none;";
		$file_display_none  	= empty($value) ?  "display:none;" : "";
		
		$input = '<span class="fileinput-button qq-upload-button" id="upload-button-'.$unique.'" style="'.$uploader_display_none.'">
			<span>'.$this->l('form_upload_a_file').'</span>
			<input type="file" name="'.$this->_unique_field_name($field_info->name).'" class="gc-file-upload" rel="'.$this->getUploadUrl($field_info->name).'" id="'.$unique.'">
			<input class="hidden-upload-input" type="hidden" name="'.$field_info->name.'" value="'.$value.'" rel="'.$this->_unique_field_name($field_info->name).'" />
		</span>';
		
		$this->set_css($this->default_css_path.'/jquery_plugins/file_upload/fileuploader.css');
		
		$input .= "<div id='uploader_$unique' rel='$unique' class='grocery-crud-uploader' style='$uploader_display_none'></div>";
		$input .= "<div id='success_$unique' class='upload-success-url' style='$file_display_none padding-top:7px;'>";
		$input .= "		<a href='".base_url().$field_info->extras->upload_path.'/'.$value."' class='open-file' target='_blank' id='file_$unique'>$value</a> ";
		$input .= "		<a href='javascript:void(0)' id='delete_$unique' class='delete-anchor'>".$this->l('form_upload_delete')."</a> ";
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
	
	protected function setThemeBasics()
	{
		$this->theme_path = $this->default_theme_path;
		if(substr($this->theme_path,-1) != '/')
			$this->theme_path = $this->theme_path.'/';
			
		include($this->theme_path.$this->theme.'/config.php');
		
		$this->config = $config;
	}
	
	public function set_theme($theme = null)
	{
		$this->theme = $theme;
	}
	
	private function _theme_view($view, $vars = array(), $return = FALSE)
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
 * @copyright  	Copyright (c) 2010 through 2012, John Skoumbourdis
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
 * @version    	1.2
 */
class grocery_CRUD_States extends grocery_CRUD_Layout
{
	private $states = array(
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
		15	=> 'success'
	);
	
	protected function getStateCode()
	{
		$state_string = $this->get_state_info_from_url()->operation;
		
		if( $state_string != 'unknown' && in_array( $state_string, $this->states ) )
			$state_code =  array_search($state_string, $this->states);
		else
			$state_code = 0;
		
		return $state_code;
	}
	
	private function state_url($url = '')
	{
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
		
		$state_url = implode('/',$state_url_array).'/'.$url;
		
		return site_url($state_url);
	}
	
	private function get_state_info_from_url()
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
		
		$first_parameter = !empty($segements[$segment_position+1]) || (!empty($segements[$segment_position+1]) && $segements[$segment_position+1] == 0) ? $segements[$segment_position+1] : false;
		$second_parameter = !empty($segements[$segment_position+2]) || (!empty($segements[$segment_position+2]) && $segements[$segment_position+2] == 0) ? $segements[$segment_position+2] : false;		
		
		return (object)array('segment_position' => $segment_position, 'operation' => $operation, 'first_parameter' => $first_parameter, 'second_parameter' => $second_parameter);
	}
	
	protected function get_method_hash()
	{
		return md5($this->get_controller_name().$this->get_method_name());
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
		return $this->state_url('');
	}

	protected function getAjaxListUrl()
	{
		return $this->state_url('ajax_list');
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
	
	protected function getUpdateUrl($state_info)
	{		
		return $this->state_url('update/'.$state_info->primary_key);
	}	
	
	protected function getDeleteUrl($state_info = null)
	{
		if(empty($state_info))
			return $this->state_url('delete');
		else
			return $this->state_url('delete/'.$state_info->primary_key);
	}

	protected function getListSuccessUrl($primary_key = null)
	{
		if(empty($primary_key))
			return $this->state_url('success');
		else
			return $this->state_url('success/'.$primary_key);
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
	
	public function getStateInfo()
	{
		$state_code = $this->getStateCode();
		$segment_object = $this->get_state_info_from_url();
		
		$first_parameter = $segment_object->first_parameter;
		$second_parameter = $segment_object->second_parameter;
		
		$state_info = (object)array();
		
		switch ($state_code) {
			case 1:
			case 2:
				
			break;		
			
			case 3:
				if($first_parameter != null)
				{
					$state_info = (object)array('primary_key' => $first_parameter);
				}	
				else
				{
					throw new Exception('On the state "edit" the Primary key cannot be null', 6);
					die();
				}
			break;
			
			case 4:
				if($first_parameter != null)
				{
					$state_info = (object)array('primary_key' => $first_parameter);
				}	
				else
				{
					throw new Exception('On the state "delete" the Primary key cannot be null',7);
					die();
				}
			break;
			
			case 5:
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
				if(!empty($_POST) && $first_parameter != null)
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
				$state_info = (object)array();
				if(!empty($_POST['per_page']))
				{
					$state_info->per_page = is_numeric($_POST['per_page']) ? $_POST['per_page'] : null;
				}
				if(!empty($_POST['page']))
				{
					$state_info->page = is_numeric($_POST['page']) ? $_POST['page'] : null;
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
						
						$state_info->search = (object)array( 'field' => null , 'text' => $_POST['search_text'] );
						
					}
					else 
					{
						$state_info->search	= (object)array( 'field' => strip_tags($_POST['search_field']) , 'text' => $_POST['search_text'] );
					}
				}
			break;
			
			case 9:
			case 10:
				
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
 * @copyright  	Copyright (c) 2010 through 2012, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @version    	1.2
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com> 
 */

// ------------------------------------------------------------------------

/**
 * PHP grocery CRUD
 *
 * Creates a full functional CRUD
 *
 * @package    	grocery CRUD 
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @license     https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @link		http://www.grocerycrud.com/documentation
 */
class grocery_CRUD extends grocery_CRUD_States
{
	private $state_code 			= null;
	private $state_info 			= null;
	private $basic_db_table_checked = false;
	private $columns				= null;
	private $columns_checked		= false;
	private $add_fields_checked		= false;
	private $edit_fields_checked	= false;	
	
	protected $default_theme		= 'flexigrid';
	protected $language				= null;
	protected $lang_strings			= array();
	protected $php_date_format		= null;
	protected $js_date_format		= null;
	protected $ui_date_format		= null;
	
	protected $add_fields			= null;
	protected $edit_fields			= null;
	protected $add_hidden_fields 	= array();
	protected $edit_hidden_fields 	= array();
	protected $field_types 			= null;	
	protected $basic_db_table 		= null;
	protected $config 				= array();
	protected $subject 				= null;
	protected $subject_plural 		= null;
	protected $display_as 			= array();
	protected $order_by 			= null;
	protected $where 				= array();
	protected $like 				= array();
	protected $having 				= array();
	protected $limit 				= null;
	protected $required_fields		= array();
	protected $validation_rules		= array();
	protected $relation				= array();
	protected $relation_n_n			= array();
	protected $upload_fields		= array();
	protected $actions				= array();
	
	protected $form_validation		= null;
	protected $change_field_type	= null;
	
	/* The unsetters */
	protected $unset_texteditor		= array();
	protected $unset_add			= false;
	protected $unset_edit			= false;
	protected $unset_delete			= false;
	protected $unset_jquery			= false;
	protected $unset_list			= false;
	protected $unset_back_to_list	= false;
	protected $unset_columns		= null;
	protected $unset_add_fields 	= null;
	protected $unset_edit_fields	= null;
	
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
	
	protected $default_javascript_path				= 'assets/grocery_crud/js';
	protected $default_css_path						= 'assets/grocery_crud/css';
	protected $default_texteditor_path 				= 'assets/grocery_crud/texteditor';
	protected $default_theme_path					= 'assets/grocery_crud/themes';
	protected $default_language_path				= 'assets/grocery_crud/languages';
	
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
	 * @param string $value
	 */
	public function change_field_type($field , $type, $value = null)
	{
		$field_type = (object)array('type' => $type);
		if($value != null)
			$field_type->value = $value;
		
		$this->change_field_type[$field] = $field_type;
		
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
	 * Unsets all the operations from the list
	 * 
	 * @return	void
	 */
	public function unset_operations()
	{
		$this->unset_add 	= true;
		$this->unset_edit 	= true;
		$this->unset_delete = true;
		
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
		
		$this->unset_back_to_list();
	
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
	private function _load_language()
	{
		$ci = &get_instance();
		$ci->config->load('grocery_crud');
		if($this->language === null)
		{
			$this->language = strtolower($ci->config->item('grocery_crud_default_language'));
		}
		include($this->default_language_path.'/'.$this->language.'.php');
		
		foreach($lang as $handle => $lang_string)
			if(!isset($this->lang_strings[$handle]))
				$this->lang_strings[$handle] = $lang_string;
		
		$this->default_true_false_text = array( $this->l('form_inactive') , $this->l('form_active'));
		$this->subject = $this->subject === null ? $this->l('list_record') : $this->subject;		
		
	}

	private function _load_date_format()
	{
		$ci = &get_instance();
		
		list($php_day, $php_month, $php_year) = array('d','m','Y');
		list($js_day, $js_month, $js_year) = array('dd','mm','yy');
		list($ui_day, $ui_month, $ui_year) = array('dd','mm','yyyy');
//@todo ui_day, ui_month, ui_year has to be lang strings
		
		$date_format = $ci->config->item('grocery_crud_date_format');
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
	
	public function order_by($order_by, $direction = '')
	{
		if($direction == '')
			$direction = 'asc';
		$this->order_by = array($order_by,$direction);
	}
	
	public function where($key, $value = NULL, $escape = TRUE)
	{
		$this->where[] = array($key,$value,$escape);
	}
	
	public function or_where($key, $value = NULL, $escape = TRUE)
	{
		$this->or_where[] = array($key,$value,$escape);
	}	
	
	public function like($field, $match = '', $side = 'both')
	{
		$this->like[] = array($field, $match, $side);
	}

	protected function having($key, $value = '', $escape = TRUE)
	{
		$this->having[] = array($key, $value, $escape);
	}
	
	public function or_like($field, $match = '', $side = 'both')
	{
		$this->or_like[] = array($field, $match, $side);
	}	

	public function limit($limit, $offset = '')
	{
		$this->limit = array($limit,$offset);
	}
	
	/**
	 * 
	 * Or else ... make it work! The web application takes decision of what to do and show it to the final user.
	 * Without this function nothing works. Here is the core of grocery CRUD project.
	 * 
	 * @return void
	 * @access	public
	 */
	public function render()
	{
		$this->_load_language();
		$this->state_code = $this->getStateCode();
		
		if( $this->state_code != 0 )
		{
			$this->state_info = $this->getStateInfo();
		}
		else
		{
			throw new Exception('The state is unknown , I don\'t know what I will do with your data!', 4);
			die();
		}		
		
		if($this->basic_model === null)
			$this->set_default_Model();
		
		$this->set_basic_db_table($this->get_table());		
		
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
				
				$this->_load_date_format();
				
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
				
				$this->_load_date_format();
				
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
				$this->_load_date_format();
				
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
				
				$this->_load_date_format();
				
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
		elseif( $this->basic_db_table != null )
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
	 * 
	 * Set a subject to understand what type of CRUD you use.
	 * @example In this CRUD we work with the table db_categories. The $subject will be the 'Category'
	 * @param string $subject
	 * @param bool $has_plural
	 * @return grocery_CRUD
	 */
	public function set_subject( $subject )
	{		
		$this->subject 			= $subject;
		$this->subject_plural 	= $subject;
			
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
			'url_has_http'	=> substr($link_url,0,7) == 'http://' ? true : false
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
	 */
	public function set_relation_n_n($field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table, $primary_key_alias_to_selection_table , $title_field_selection_table , $priority_field_relation_table = null)
	{
		$this->relation_n_n[$field_name] = 
			(object)array( 
				'field_name' => $field_name, 
				'relation_table' => $relation_table, 
				'selection_table' => $selection_table, 
				'primary_key_alias_to_this_table' => $primary_key_alias_to_this_table, 
				'primary_key_alias_to_selection_table' => $primary_key_alias_to_selection_table , 
				'title_field_selection_table' => $title_field_selection_table , 
				'priority_field_relation_table' => $priority_field_relation_table
			);
			
		return $this;
	}
	
	/**
	 * 
	 * Transform a field to an upload field
	 * 
	 * @param string $field_name
	 * @param string $upload_path
	 */
	public function set_field_upload($field_name, $upload_dir = null)
	{
		$upload_dir = substr($upload_dir,-1,1) == '/' ? substr($upload_dir,0,-1) : $upload_dir;
		$this->upload_fields[$field_name] = (object)array( 'field_name' => $field_name , 'upload_path' => $upload_dir, 'encrypted_field_name' =>  $this->_unique_field_name($field_name));		
	}
}

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
            return $error;
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
        $file_name = substr(uniqid(),-5).'-'.preg_replace("/([^a-zA-Z0-9\.\-\_]+?){1}/i", '-', $file_name);

        return $file_name;
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
                $file->error = 'abort';
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
