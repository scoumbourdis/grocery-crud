<?php
/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * This source file is subject to the GPL license that is bundled
 * with this package in the file licence.txt.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2011, John Skoumbourdis
 * @license    	http://www.gnu.org/licenses/gpl.html GNU GPL v3
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
 * @license     http://www.gnu.org/licenses   GNU License 
 * @version    	1.1.3   
 * @link		http://www.grocerycrud.com/crud/view/documentation
 */
class grocery_Field_Types
{	
	/**	 
	 * Gets the field types of the main table.
	 * @return array
	 */
	public function get_field_types()
	{
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
				$field_info->required	= false;//Temporary false
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
		
		return $types;
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
				$value = $this->default_true_false_text[$value];
			break;
			case 'string':
				$value = $this->character_limiter($value,30," [...]");
			break;
			case 'text':
				$value = $this->character_limiter(strip_tags($value),30," [...]");
			break;
			case 'date':
				if(!empty($value) && $value != '0000-00-00')
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
				$value = $this->character_limiter($value,20," [...]");
			break;	
			case 'relation_n_n':
				$value = implode(', ' ,$this->get_relation_n_n_selection_array( $value, $this->relation_n_n[$field_info->name] ));
				$value = $this->character_limiter($value,30," [...]");
			break;						
			
			case 'upload_file':
				$value = !empty($value) ? 
							"<a href='".base_url().$field_info->extras->upload_path."/$value' target='_blank'>".
								$this->character_limiter($value,20," [...]",true).
							"</a>":
							"";
			break;
			
			default:
				$value = $this->character_limiter($value,30," [...]");
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
					if( $db_type->db_type == 'tinyint' && $db_type->db_max_length ==  1)
						$type = 'true_false';
					else
						$type = 'integer';
				break;
				case '254':
				case 'string':
					if($db_type->db_type != 'enum')
						$type = 'string';
					else
						$type = 'enum';
				break;
				case '252':
				case 'blob':
					$type = 'text';
				break;
				case '10':
				case 'date':
					$type = 'date';
				break;
				case '12':
				case 'datetime':
					$type = 'datetime';
				break;
			}
		}
		return $type;
	}
}


/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * This source file is subject to the GPL license that is bundled
 * with this package in the file licence.txt.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2011, John Skoumbourdis
 * @license    	http://www.gnu.org/licenses/gpl.html GNU GPL v3
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */

// ------------------------------------------------------------------------

/**
 * Grocery Model Driver
 *
 * Drives the model - Like car drive :-)
 *
 * @package    	grocery CRUD
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 * @version    	1.1.3  
 * @link		http://www.grocerycrud.com/crud/view/documentation
 */
class grocery_Model_Driver extends grocery_Field_Types
{
	/**
	 * @var grocery_Model
	 */
	public $basic_model = null;
	
	protected function set_default_Model()
	{
		$ci = &get_instance();
		$ci->load->model('grocery_Model');
		
		$this->basic_model = $ci->grocery_Model;
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
				
		if(!empty($this->relation))
			foreach($this->relation as $relation)
				$this->basic_model->join_relation($relation[0],$relation[1],$relation[2]);				
				
		return $this->basic_model->get_total_results();
	}
	
	public function set_model($model_name)
	{
		$ci = &get_instance();
		$ci->load->model('grocery_Model');	
		
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
			if($state_info->search->field != null)
			{
				$this->like($state_info->search->field , $state_info->search->text);
			}
			else 
			{
				$columns = $this->get_columns();
				$search_text = $state_info->search->text;
				
				foreach($columns as $column)
				{
					$this->or_like($column->field_name, $search_text);
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
	
	protected function get_relation_array($relation_info)
	{
		list($field_name , $related_table , $related_field_title)  = $relation_info;

		$relation_array = $this->basic_model->get_relation_array($field_name , $related_table , $related_field_title);
		
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
				$validation_result->error_message = validation_errors();
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
			$ci = &get_instance();
			$ci->load->library('form_validation');

			return $ci->form_validation;
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
				$validation_result->error_message = validation_errors();
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

			if($this->callback_escape_insert == null)
			{
				if($this->callback_before_insert != null)
				{
					$callback_return = call_user_func($this->callback_before_insert, $post_data);
					
					if(!empty($callback_return) && is_array($callback_return))
					{
						$post_data = $callback_return;
					}
					elseif($callback_return === false) 
					{
						return false;
					}
					
				}
				
				$insert_data = array();
				foreach($add_fields as $num_row => $field)
				{
					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
						$insert_data[$field->field_name] = $post_data[$field->field_name];
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
					$callback_return = call_user_func($this->callback_escape_insert, $post_data);
					
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
			
			if($this->callback_escape_update == null)
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
				foreach($edit_fields as $num_row => $field)
				{
					if(isset($post_data[$field->field_name]) && !isset($this->relation_n_n[$field->field_name]))
						$update_data[$field->field_name] = $post_data[$field->field_name];
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
				$callback_return = call_user_func($this->callback_escape_update, $post_data, $primary_key);
					
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
	
    protected function _unique_join_name($field_name)
    {
    	return 'j'.substr(md5($field_name),0,6); //This j is because is better for a string to begin with a letter and not a number
    }	
	
	protected function db_delete($state_info)
	{
		$primary_key 	= $state_info->primary_key;
		
		if($this->callback_escape_delete == null)
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
			$callback_return = call_user_func($this->callback_escape_delete, $primary_key);
				
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
			
		if(!empty($this->relation))
			foreach($this->relation as $relation)
				$this->basic_model->join_relation($relation[0],$relation[1],$relation[2]);
				
		if($this->config['crud_paging'] === true)
		{
			if($this->limit == null)
			{
				$this->basic_model->limit(25);	
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
		if(isset($state_info->field_name) && isset($this->upload_fields[$state_info->field_name]))
		{
			$upload_info = $this->upload_fields[$state_info->field_name];
			
			$input = fopen("php://input", "r");
	        $temp = tmpfile();
	        $realSize = stream_copy_to_stream($input, $temp);
	        fclose($input);
	        
	        $target = fopen("{$upload_info->upload_path}/{$state_info->file_name}", "w");
	        fseek($temp, 0, SEEK_SET);
	        stream_copy_to_stream($temp, $target);
	        fclose($target);
	        
	        return (object)array('file_name' => $state_info->file_name);
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
}


/**
 * PHP grocery CRUD
 *
 * LICENSE
 *
 * This source file is subject to the GPL license that is bundled
 * with this package in the file licence.txt.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2011, John Skoumbourdis
 * @license    	http://www.gnu.org/licenses/gpl.html GNU GPL v3
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
 * @version    	1.1.3
 */
class grocery_Layout extends grocery_Model_Driver
{
	private $theme_path 				= null;
	private $views_as_string			= '';
	private $echo_and_die				= false;
	protected $theme 					= null;
	protected $default_true_false_text 	= array('inactive' , 'active');
	
	protected static $css_files					= array();
	protected static $js_files					= array();
	
	protected function set_basic_Layout()
	{			
		if(!file_exists($this->theme_path.$this->theme.'/views/list_template.php'))
		{
			throw new Exception('The template does not exist. Please check your files and try again.', 12);
			die();
		}
	}
	
	protected function showList($ajax = false)
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
				elseif(isset($types[$field_name]) && $types[$field_name]->crud_type != 'relation_n_n')
					$list[$num_row]->$field_name = $this->change_list_value($types[$field_name] , $field_value);
				elseif(isset($types[$field_name]) && $types[$field_name]->crud_type == 'relation_n_n')
				
					$list[$num_row]->$field_name = $this->change_list_value($types[$field_name] , $row->$primary_key);				
				else
					$list[$num_row]->$field_name = $field_value;
			}
		}
		
		return $list;
	}
	
	protected function showAddForm()
	{
		$this->set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
		
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
		
		$data->list_url 		= $this->getListUrl();
		$data->insert_url		= $this->getInsertUrl();
		$data->validation_url	= $this->getValidationInsertUrl();
		$data->input_fields 	= $this->get_add_input_fields();
		
		$data->fields 			= $this->get_add_fields();
		$data->hidden_fields	= $this->get_add_hidden_fields();
		
		$this->_theme_view('add.php',$data);
	}
	
	protected function showEditForm($state_info)
	{
		$this->set_js('assets/grocery_crud/themes/datatables/js/jquery-1.6.2.min.js');
		
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
		
		$data->validation_url	= $this->getValidationUpdateUrl(); 
		
		$this->_theme_view('edit.php',$data);
	}
	
	protected function delete_layout($delete_result = true)
	{
		if($delete_result === false)
		{
			$error_message = '<p>Your data was not deleted successfully from the database.</p>';
			
			echo json_encode(array('success' => $delete_result ,'error_message' => $error_message));	
		}
		else 
		{
			$success_message = '<p>Your data has been successfully deleted from the database.</p>';
			
			echo json_encode(array('success' => true , 'success_message' => $success_message));
		}
		$this->set_echo_and_die();
	}
	
	protected function insert_layout($insert_result = false)
	{
		if($insert_result === false)
		{
			echo json_encode(array('success' => $insert_result));	
		}
		else 
		{
			$success_message = '<p>Your data has been successfully stored into the database.';
			if($insert_result !== true)
			{
				$success_message .= " <a href='".$this->getEditUrl($insert_result)."'>Edit {$this->subject}</a> or";
			}
			$success_message .= " <a href='".$this->getListUrl()."'>Go back to list</a>";
			$success_message .= '</p>';
			
			echo "<textarea>".json_encode(array('success' => true , 'insert_primary_key' => $insert_result, 'success_message' => $success_message))."</textarea>";
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
		if($upload_result !== false)
		{
			echo json_encode(
				(object)array(
					'success' => true, 
					'file_name' => $upload_result->file_name,
					'full_url' => base_url().$this->upload_fields[$field_name]->upload_path.'/'.$upload_result->file_name
				)
			);
			$this->set_echo_and_die();	
		}
		else
		{
			echo json_encode((object)array('success' => false));
			$this->set_echo_and_die();	
		}
	}	
	
	protected function delete_file_layout($upload_result)
	{
		if($upload_result !== false)
		{
			echo json_encode( (object)array( 'success' => true ) );
			$this->set_echo_and_die();	
		}
		else
		{
			echo json_encode((object)array('success' => false));
			$this->set_echo_and_die();	
		}
	}	
	
	public static function set_css($css_file)
	{
		grocery_CRUD::$css_files[sha1($css_file)] = base_url().$css_file;
	}

	public static function set_js($js_file)
	{
		grocery_CRUD::$js_files[sha1($js_file)] = base_url().$js_file;
	}

	public function get_css_files()
	{
		return grocery_CRUD::$css_files;
	}

	public function get_js_files()
	{
		return grocery_CRUD::$js_files;
	}	
	
	protected function get_layout()
	{		
		$js_files = $this->get_js_files();
		$css_files =  $this->get_css_files();
		
		if($this->unset_jquery)
			unset($js_files['763b4d272e158bdb8ed5a12a1824c94f494954bd']);
		
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
	
	protected function update_layout($update_result = false)
	{
		if($update_result === false)
		{
			echo json_encode(array('success' => $update_result));	
		}
		else 
		{
			$success_message = '<p>Your data has been successfully updated';
			$success_message .= ". <a href='".$this->getListUrl()."'>Go back to list</a>";
			$success_message .= '</p>';
			
			/* The textarea is only because of a BUG of the jquery plugin jquery form */
			echo "<textarea>".json_encode(array('success' => true , 'insert_primary_key' => $update_result, 'success_message' => $success_message))."</textarea>";
		}
		$this->set_echo_and_die();
	}
	
	protected function get_integer_input($field_info,$value)
	{
		$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery.numeric.js');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/config/jquery.numeric.config.js');
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
		$value = !is_string($value) ? '' : $value; 
		
		$extra_attributes = '';
		if(!empty($field_info->db_max_length))
			$extra_attributes .= "maxlength='{$field_info->db_max_length}'"; 
		$input = "<input name='{$field_info->name}' type='text' value='$value' $extra_attributes />";
		return $input;
	}

	protected function get_text_input($field_info,$value)
	{   
		if($field_info->extras == 'text_editor')
		{
			$this->set_js('assets/grocery_crud/texteditor/jquery.tinymce.js');
			$this->set_js('assets/grocery_crud/js/jquery_plugins/config/jquery.tine_mce.config.js');
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
		$this->set_css('assets/grocery_crud/css/ui/simple/jquery-ui-1.8.10.custom.css');
		$this->set_css('assets/grocery_crud/css/jquery_plugins/jquery.ui.datetime.css');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery-ui-1.8.10.custom.min.js');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery.ui.datetime.js');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/config/jquery.datetime.config.js');
		$input = "<input name='{$field_info->name}' type='text' value='$value' maxlength='19' class='datetime-input' /> 
		<button class='datetime-input-clear'>Clear</button>
		(yyyy-mm-dd) hh:mm:ss";
		return $input;
	}
	
	protected function get_hidden_input($field_info,$value)
	{
		if($field_info->extras != null && $field_info->extras != false)
			$value = $field_info->extras;
		$input = "<input type='hidden' name='{$field_info->name}' value='$value' />";
		return $input;		
	}
	
	protected function get_date_input($field_info,$value)
	{
		$this->set_css('assets/grocery_crud/css/ui/simple/jquery-ui-1.8.10.custom.css');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery-ui-1.8.10.custom.min.js');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/config/jquery.datepicker.config.js');
		$input = "<input name='{$field_info->name}' type='text' value='$value' maxlength='10' class='datepicker-input' /> 
		<button class='datepicker-input-clear'>Clear</button> (yyyy-mm-dd)";
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
	
	protected function get_relation_input($field_info,$value)
	{
		$input = "<select name='{$field_info->name}'>";
		
		$options_array = $this->get_relation_array($field_info->extras);
		foreach($options_array as $option_value => $option)
		{
			$selected = !empty($value) && $value == $option_value ? "selected='selected'" : ''; 
			$input .= "<option value='$option_value' $selected >$option</option>";	
		}
		
		$input .= "</select>";
		return $input;
	}
	
	protected function get_relation_n_n_input($field_info_type, $selected_values)
	{	
		$this->set_css('assets/grocery_crud/css/ui/simple/jquery-ui-1.8.10.custom.css');		
		$this->set_css('assets/grocery_crud/css/jquery_plugins/ui.multiselect.css');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/jquery-ui-1.8.10.custom.min.js');	
		$this->set_js('assets/grocery_crud/js/jquery_plugins/ui.multiselect.js');
		$this->set_js('assets/grocery_crud/js/jquery_plugins/config/jquery.multiselect.js');
		
		$field_info 		= $this->relation_n_n[$field_info_type->name]; //As its inside here the relation_n_n exists
		$unselected_values 	= $this->get_relation_n_n_unselected_array($field_info, $selected_values);
		
		if(empty($unselected_values) && empty($selected_values))
		{
			$input = "Please add {$field_info_type->display_as} first";
		}
		else
		{
		
			$input = "<select name='{$field_info_type->name}[]' multiple='multiple' size='8' class='multiselect'>";
			
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

	protected function get_upload_file_input($field_info, $value)
	{
		$this->set_css('assets/grocery_crud/css/other/fileuploader/fileuploader.css');
		$this->set_js('assets/grocery_crud/js/other/fileuploader.js');
		$this->set_js('assets/grocery_crud/js/other/fileuploader.config.js');
		
		$unique = uniqid();
		
		$uploader_display_none 	= empty($value) ? "" : "display:none;";
		$file_display_none  	= empty($value) ?  "display:none;" : "";
		
		$input 	= "<div id='uploader_$unique' rel='$unique' class='grocery-crud-uploader' style='$uploader_display_none'></div>";
		$input .= "<div id='success_$unique' style='$file_display_none'>";
		$input .= "<a href='".base_url().$field_info->extras->upload_path.'/'.$value."' class='open-file' target='_blank' id='file_$unique'>$value</a> ";
		$input .= "<a href='javascript:void(0)' id='delete_$unique' class='delete-anchor'>delete</a> ";
		$input .= "<input type='hidden' name='{$field_info->name}' value='$value' id='hidden_$unique'/>";
		$input .= "</div><div style='clear:both'></div>";
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
				$field_input->input = call_user_func($this->callback_add_field[$field->field_name]);
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
				$field_input->input = call_user_func($this->callback_edit_field[$field->field_name], $field_value, $primary_key);
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
 * This source file is subject to the GPL license that is bundled
 * with this package in the file licence.txt.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2011, John Skoumbourdis
 * @license    	http://www.gnu.org/licenses/gpl.html GNU GPL v3
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
 * @version    	1.1.3
 */
class grocery_States extends grocery_Layout
{
	private $states = array(
		0	=>	'unknown',
		1	=>	'list',
		2	=>	'add',
		3	=>	'edit',
		4	=>	'delete',
		5	=>	'insert',
		6	=>	'update',
		7	=>  'ajax_list',
		8   =>  'ajax_list_info',
		9	=>  'insert_validation',
		10	=>	'update_validation',
		11	=>	'upload_file',
		12	=>	'delete_file'
	);
	
	protected function getStateCode()
	{
		$real_segment = $this->get_segment_genius()->segment;
		
		#region scenarios

		if($real_segment == 'list')
		{
			$state_code = 1;
			
		}elseif($real_segment == 'ajax_list')
		{
			$state_code = 7;
		}elseif($real_segment == 'ajax_list_info')
		{
			$state_code = 8;
		}elseif($real_segment == 'edit')
		{
			if(empty($_POST))
			{
				$state_code = 3;	
			}
			else
			{
				$state_code = 6;		
			}
		}elseif($real_segment == 'add')
		{
			if(empty($_POST))
			{
				$state_code = 2;	
			}
			else
			{
				$state_code = 5;		
			}
		}elseif($real_segment == 'delete')
		{
			$state_code = 4;
		}
		elseif($real_segment == 'insert_validation')
		{
			$state_code = 9;
		}
		elseif($real_segment == 'update_validation')
		{
			$state_code = 10;
		}
		elseif($real_segment == 'upload_file')
		{
			$state_code = 11;
		}
		elseif($real_segment == 'delete_file')
		{
			$state_code = 12;
		}							
		else 
		{
			$state_code = 0;
		}
		
		#endregion
		
		return $state_code;
	}
	
	private function state_url($url = '')
	{
		$ci = &get_instance();
		
		$segment_object = $this->get_segment_genius();
		$method_name = $this->get_method_name();
		$segment_position = $segment_object->segment_position;
		
		$state_url_array = array();
		foreach($ci->uri->segments as $num => $value)
		{
			$state_url_array[$num] = $value;
			if($num == ($segment_position - 1))
				break;
		}
		
		#region there is a scenario that you forgot the index
			if($method_name == 'index' && !in_array('index',$state_url_array)) //This means that you just forgot to add the index to your url
			{
				$state_url_array[$num+1] = 'index';
			}
		#endregion
		
		$state_url = implode('/',$state_url_array).'/'.$url;
		
		return site_url($state_url);
	}
	
	private function get_segment_genius()
	{
		$ci = &get_instance();
		
		$segment_position = count($ci->uri->segments) + 1;
		$segment = 'list';
		
		$segements = $ci->uri->segments;
		foreach($segements as $num => $value)
		{
			if(in_array($value,array('ajax_list','ajax_list_info','add','edit','delete','insert_validation','update_validation','upload_file','delete_file')))
			{
				$segment_position = (int)$num;
				$segment = $value;
				//I don't have a "break" here because I want to ensure that is the LAST segment with name that is in the array.
			}
		}
		
		$function_name = $this->get_method_name();
		
		if($function_name == 'index' && !in_array('index',$ci->uri->segments))
			$segment_position++;
		
		$second_segment = !empty($segements[$segment_position+1]) || (!empty($segements[$segment_position+1]) && $segements[$segment_position+1] == 0) ? $segements[$segment_position+1] : false;
		$third_segment = !empty($segements[$segment_position+2]) || (!empty($segements[$segment_position+2]) && $segements[$segment_position+2] == 0) ? $segements[$segment_position+2] : false;		
		
		return (object)array('segment_position' => $segment_position, 'segment' => $segment, 'second_segment' => $second_segment, 'third_segment' => $third_segment);
	}
	
	protected function get_method_hash()
	{
		return md5($this->get_method_name());
	}
	
	protected function get_method_name()
	{
		$ci = &get_instance();		
		return $ci->router->method;
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
		return $this->state_url('add');
	}
	
	protected function getValidationInsertUrl()
	{
		return $this->state_url('insert_validation');
	}
	
	protected function getValidationUpdateUrl()
	{
		return $this->state_url('update_validation');
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
		return $this->state_url('edit/'.$state_info->primary_key);
	}	
	
	protected function getDeleteUrl($state_info = null)
	{
		if(empty($state_info))
			return $this->state_url('delete');
		else
			return $this->state_url('delete/'.$state_info->primary_key);
	}

	protected function getUploadUrl($field_name)
	{		
		return $this->state_url('upload_file/'.$field_name);
	}	

	protected function getFileDeleteUrl($field_name)
	{
		return $this->state_url('delete_file/'.$field_name);
	}	
	
	public function getStateInfo()
	{
		$state_code = $this->getStateCode();
		
		$segment_object = $this->get_segment_genius();
		
		$method_name = $this->get_method_name();
		$real_segment = $segment_object->segment;
		$second_segment = $segment_object->second_segment;
		$third_segment = $segment_object->third_segment;
		
		$state_info = (object)array();
		
		switch ($state_code) {
			case 1:
				
			break;
			
			case 2:
				
			break;		
			
			case 3:
				if($second_segment != null)
				{
					$state_info = (object)array('primary_key' => $second_segment);
				}	
				else
				{
					throw new Exception('On the state "edit" the Primary key cannot be null', 6);
					die();
				}
			break;
			
			case 4:
				if($second_segment != null)
				{
					$state_info = (object)array('primary_key' => $second_segment);
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
				if(!empty($_POST) && $second_segment != null)
				{
					$state_info = (object)array('primary_key' => $second_segment,'unwrapped_data' => $_POST);
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
			
			case 8:
			case 7:
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
				
			break;
			
			case 10:
				
			break;

			case 11:
				$state_info->field_name = $second_segment;
				$state_info->file_name = substr(uniqid(),-5).'-'.preg_replace('/[^A-Za-z0-9_\.]+/', '-', trim(urldecode($third_segment)));
			break;

			case 12:
				$state_info->field_name = $second_segment;
				$state_info->file_name = $third_segment;
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
 * This source file is subject to the GPL license that is bundled
 * with this package in the file licence.txt.
 *
 * @package    	grocery CRUD
 * @copyright  	Copyright (c) 2010 through 2011, John Skoumbourdis
 * @license    	http://www.gnu.org/licenses/gpl.html GNU GPL v3
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
 * @version    	1.1.3  
 * @license     http://www.gnu.org/licenses/   GNU License
 * @link		http://www.grocerycrud.com/crud/view/documentation
 */
class grocery_CRUD extends grocery_States
{
	private $state_code 			= null;
	private $state_info 			= null;
	private $basic_db_table_checked = false;
	private $columns				= null;
	private $columns_checked		= false;
	private $add_fields_checked		= false;
	private $edit_fields_checked	= false;	
	
	protected $default_theme		= 'flexigrid';
	protected $default_theme_path		= 'assets/grocery_crud/themes';
	
	protected $add_fields			= null;
	protected $edit_fields			= null;
	protected $add_hidden_fields 	= array();
	protected $edit_hidden_fields 	= array();
	protected $basic_db_table 		= null;
	protected $config 				= array();
	protected $subject 				= 'Record';
	protected $subject_plural 		= 'Records';
	protected $display_as 			= array();
	protected $order_by 			= null;
	protected $where 				= array();
	protected $like 				= array();
	protected $limit 				= null;
	protected $required_fields		= array();
	protected $unset_columns		= null;
	protected $validation_rules		= array();
	protected $relation				= array();
	protected $relation_n_n			= array();
	protected $upload_fields		= array();
	protected $actions				= array();
	
	protected $change_field_type	= null;
	
	/* The unsetters */
	protected $unset_texteditor	= array();
	protected $unset_add		= false;
	protected $unset_edit		= false;
	protected $unset_delete		= false;
	protected $unset_jquery		= false;
	
	/* Callbacks */
	protected $callback_before_insert 	= null;
	protected $callback_after_insert 	= null;
	protected $callback_escape_insert 	= null;
	protected $callback_before_update 	= null;
	protected $callback_after_update 	= null;
	protected $callback_escape_update 	= null;	
	protected $callback_before_delete 	= null;
	protected $callback_after_delete 	= null;
	protected $callback_escape_delete 	= null;		
	protected $callback_column			= array();
	protected $callback_add_field		= array();
	protected $callback_edit_field		= array();
	
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
					$new_column = $this->_unique_join_name($this->relation[$column][0]).'.'.$this->relation[$column][2];
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
					if(!isset($field->db_extra) || $field->db_extra != 'auto_increment')
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
		
		switch ($this->state_code) {			
			case 1://list
				$this->set_basic_db_table($this->get_table());
				
				if($this->theme === null)
					$this->set_theme($this->default_theme);				
				$this->setThemeBasics();
					
				$this->set_basic_Layout();
					
				$this->showList();

			break;
			
			case 2://add
				if($this->unset_add)
				{
					throw new Exception('This user is not allowed to do this operation', 14);
					die();
				}
				
				$this->set_basic_db_table($this->get_table());
				if($this->theme === null)
					$this->set_theme($this->default_theme);				
				$this->setThemeBasics();
				
				$this->set_basic_Layout();
				
				$this->showAddForm();
				
			break;
			
			case 3://edit
				if($this->unset_edit)
				{
					throw new Exception('This user is not allowed to do this operation', 14);
					die();
				}
				
				$this->set_basic_db_table($this->get_table());
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
					
				$this->set_basic_db_table($this->get_table());
				
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
				$this->set_basic_db_table($this->get_table());
				
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
				
				$this->set_basic_db_table($this->get_table());
				
				$state_info = $this->getStateInfo();
				$update_result = $this->db_update($state_info);
				
				$this->update_layout( $update_result );
			break;	

			case 7://ajax_list
				$this->set_basic_db_table($this->get_table());
				
				if($this->theme === null)
					$this->set_theme($this->default_theme);				
				$this->setThemeBasics();
				
				$this->set_basic_Layout();
				
				$state_info = $this->getStateInfo();
				$this->set_ajax_list_queries($state_info);				
					
				$this->showList(true);
				
			break;

			case 8://ajax_list_info
				$this->set_basic_db_table($this->get_table());
				
				if($this->theme === null)
					$this->set_theme($this->default_theme);				
				$this->setThemeBasics();
				
				$this->set_basic_Layout();
				
				$state_info = $this->getStateInfo();
				$this->set_ajax_list_queries($state_info);				
					
				$this->showListInfo();
			break;
			
			case 9://insert_validation
				$this->set_basic_db_table($this->get_table());
				
				$validation_result = $this->db_insert_validation();
				
				$this->validation_layout($validation_result);
			break;
			
			case 10://update_validation
				$this->set_basic_db_table($this->get_table());
				
				$validation_result = $this->db_update_validation();
				
				$this->validation_layout($validation_result);
			break;

			case 11://upload_file
				$this->set_basic_db_table($this->get_table());
				$state_info = $this->getStateInfo();
				
				$upload_result = $this->upload_file($state_info);

				$this->upload_layout($upload_result, $state_info->field_name);
			break;

			case 12://delete_file
				$this->set_basic_db_table($this->get_table());
				$state_info = $this->getStateInfo();
				
				$delete_file_result = $this->delete_file($state_info);
				
				$this->delete_file_layout($delete_file_result);
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
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_after_insert($callback = null)
	{
		$this->callback_after_insert = $callback;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_escape_insert($callback = null)
	{
		$this->callback_escape_insert = $callback;
	}

	
	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_before_update($callback = null)
	{
		$this->callback_before_update = $callback;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_after_update($callback = null)
	{
		$this->callback_after_update = $callback;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_escape_update($callback = null)
	{
		$this->callback_escape_update = $callback;
	}	
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_before_delete($callback = null)
	{
		$this->callback_before_delete = $callback;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_after_delete($callback = null)
	{
		$this->callback_after_delete = $callback;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function callback_escape_delete($callback = null)
	{
		$this->callback_escape_delete = $callback;
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
	public function set_subject( $subject , $has_plural = true)
	{

		if(!is_bool($has_plural))
		{
			$has_plural = true;
			throw new Exception('This variable must be boolean.', 5);
		}
			
		$subject = strip_tags(trim($subject));
		$subject_plural = $subject;
		
		if($has_plural)
		{		
			$end = substr($subject_plural, -1);
	
			if ($end == 'y')
			{
				$vowels = array('a', 'e', 'i', 'o', 'u');
				$subject_plural = in_array(substr($subject_plural, -2, 1), $vowels) ? $subject_plural.'s' : substr($subject_plural, 0, -1).'ies';
			}
			elseif ($end == 'h')
			{
				if (substr($subject_plural, -2) == 'ch' OR substr($subject_plural, -2) == 'sh')
				{
					$subject_plural .= 'es';
				}
				else
				{
					$subject_plural .= 's';
				}
			}
			elseif ($end == 's')
			{
				if ($has_plural)
				{
					$subject_plural .= 'es';
				}
			}
			else
			{
				$subject_plural .= 's';
			}	
		}
		
		$this->subject 			= $subject;
		$this->subject_plural 	= $subject_plural;
			
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
	 * Enter description here ...
	 * @param string $field_name
	 * @param string $related_table
	 * @param string $related_title_field
	 */
	public function set_relation($field_name , $related_table, $related_title_field)
	{
		$this->relation[$field_name] = array($field_name, $related_table,$related_title_field);
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
	 * Enter description here ...
	 * @param string $field_name
	 * @param string $upload_path
	 */
	public function set_field_upload($field_name, $upload_path)
	{
		$upload_path = substr($upload_path,-1,1) == '/' ? substr($upload_path,0,-1) : $upload_path;
		$this->upload_fields[$field_name] = (object)array( 'field_name' => $field_name , 'upload_path' => $upload_path);		
	}
}