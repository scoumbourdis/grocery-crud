<?php
class grocery_Model  extends CI_Model  {

	protected $primary_key = null;
	protected $table_name = null;
	protected $relation = array();

	function __construct()
    {
        parent::__construct();
    }

    function db_table_exists($table_name = null)
    {
    	return $this->db->table_exists($table_name);
    }

    function get_list()
    {
    	if($this->table_name === null)
    		return false;

    	$select = "{$this->table_name}.*";

    	if(!empty($this->relation))
    		foreach($this->relation as $relation)
    		{
    			list($field_name , $related_table , $related_field_title) = $relation;
    			$unique_join_name = $this->_unique_join_name($field_name);
    			$unique_field_name = $this->_unique_field_name($field_name);

				if(strstr($related_field_title,'{')) {
					$CI =& get_instance();
    				$select .= ", CONCAT('".str_replace(array('{','}'),array("',",",'"),$CI->db->escape_str($related_field_title))."') as $unique_field_name";
				} else
    				$select .= ", $unique_join_name.$related_field_title as $unique_field_name";

    			if($this->field_exists($related_field_title))
    				$select .= ", {$this->table_name}.$related_field_title as '{$this->table_name}.$related_field_title'";
    		}

    	$this->db->select($select, false);

    	$results = $this->db->get($this->table_name)->result();

    	return $results;
    }

    function order_by($order_by , $direction)
    {
    	$this->db->order_by( $order_by , $direction );
    }

    function where($key, $value = NULL, $escape = TRUE)
    {
    	$this->db->where( $key, $value, $escape);
    }

    function or_where($key, $value = NULL, $escape = TRUE)
    {
    	$this->db->or_where( $key, $value, $escape);
    }

    function like($field, $match = '', $side = 'both')
    {
    	$this->db->like($field, $match, $side);
    }

    function or_like($field, $match = '', $side = 'both')
    {
    	$this->db->or_like($field, $match, $side);
    }

    function limit($value, $offset = '')
    {
    	$this->db->limit( $value , $offset );
    }

    function get_total_results()
    {
    	$this->db->from($this->table_name);
		return $this->db->count_all_results();
    }

    function set_basic_table($table_name = null)
    {
    	if( !($this->db->table_exists($table_name)) )
    		return false;

    	$this->table_name = $table_name;

    	return true;
    }

    function get_edit_values($primary_key_value)
    {
    	$primary_key_field = $this->get_primary_key();
    	$this->db->where($primary_key_field,$primary_key_value);
    	$result = $this->db->get($this->table_name)->row();
    	return $result;
    }

    function join_relation($field_name , $related_table , $related_field_title)
    {
    	if($this->db_table_exists($related_table))
    	{
			$related_primary_key = $this->get_primary_key($related_table);

			if($related_primary_key !== false)
			{
				$unique_name = $this->_unique_join_name($field_name);
				$this->db->join( $related_table.' as '.$unique_name , "$unique_name.$related_primary_key = {$this->table_name}.$field_name",'left');

				$this->relation[$field_name] = array($field_name , $related_table , $related_field_title);

				return true;
			}
    	}

    	return false;
    }

    protected function _unique_join_name($field_name)
    {
    	return 'j'.substr(md5($field_name),0,8); //This j is because is better for a string to begin with a letter and not a number
    }

    protected function _unique_field_name($field_name)
    {
    	return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not a number
    }

    function get_relation_array($field_name , $related_table , $related_field_title)
    {
    	$relation_array = array();
    	$field_name_hash = $this->_unique_field_name($field_name); //Just to make sure that the string begins with a character and not with a number

    	$related_primary_key = $this->get_primary_key($related_table);

    	$select = "$related_table.$related_primary_key, ";

    	if(strstr($related_field_title,'{')) {
    		$CI =& get_instance();
    		$select .= "CONCAT('".str_replace(array('{','}'),array("',",",'"),$CI->db->escape_str($related_field_title))."') as $field_name_hash";
    	} else
	    	$select .= "$related_table.$related_field_title as $field_name_hash";

    	$this->db->select($select,false);
    	$this->db->order_by($field_name_hash);
    	$results = $this->db->get($related_table)->result();

    	foreach($results as $row)
    	{
    		$relation_array[$row->$related_primary_key] = $row->$field_name_hash;
    	}

    	return $relation_array;
    }

    function get_relation_n_n_selection_array($primary_key_value, $field_info)
    {
    	$selection_primary_key = $this->get_primary_key($field_info->selection_table);

    	if(empty($field_info->priority_field_relation_table))
    	{
    		$this->db->order_by("{$field_info->selection_table}.{$field_info->title_field_selection_table}");
    	}
    	else
    	{
    		$this->db->order_by("{$field_info->relation_table}.{$field_info->priority_field_relation_table}");
    	}
    	$this->db->where($field_info->primary_key_alias_to_this_table, $primary_key_value);
    	$this->db->join(
    		$field_info->selection_table,
    		"{$field_info->relation_table}.{$field_info->primary_key_alias_to_selection_table} = {$field_info->selection_table}.{$selection_primary_key}"
    	);
    	$results = $this->db->get($field_info->relation_table)->result();

    	$results_array = array();
    	foreach($results as $row)
    	{
    		$results_array[$row->{$field_info->primary_key_alias_to_selection_table}] = $row->{$field_info->title_field_selection_table};
    	}

    	return $results_array;
    }

    function get_relation_n_n_unselected_array($field_info, $selected_values)
    {
        $selection_primary_key = $this->get_primary_key($field_info->selection_table);

        $this->db->order_by("{$field_info->selection_table}.{$field_info->title_field_selection_table}");
        $results = $this->db->get($field_info->selection_table)->result();

        $results_array = array();
        foreach($results as $row)
        {
            if(!isset($selected_values[$row->$selection_primary_key]))
                $results_array[$row->$selection_primary_key] = $row->{$field_info->title_field_selection_table};
        }

        return $results_array;
    }

    function db_relation_n_n_update($field_info, $post_data ,$main_primary_key)
    {
    	$this->db->where($field_info->primary_key_alias_to_this_table, $main_primary_key);
    	if(!empty($post_data))
    		$this->db->where_not_in($field_info->primary_key_alias_to_selection_table , $post_data);
    	$this->db->delete($field_info->relation_table);

    	$counter = 0;
    	if(!empty($post_data))
    	{
    		foreach($post_data as $primary_key_value)
	    	{
				$where_array = array(
	    			$field_info->primary_key_alias_to_this_table => $main_primary_key,
	    			$field_info->primary_key_alias_to_selection_table => $primary_key_value,
	    		);

	    		$this->db->where($where_array);
				$count = $this->db->from($field_info->relation_table)->count_all_results();

				if($count == 0)
				{
					if(!empty($field_info->priority_field_relation_table))
						$where_array[$field_info->priority_field_relation_table] = $counter;

					$this->db->insert($field_info->relation_table, $where_array);

				}elseif($count >= 1 && !empty($field_info->priority_field_relation_table))
				{
					$this->db->update( $field_info->relation_table, array($field_info->priority_field_relation_table => $counter) , $where_array);
				}

				$counter++;
	    	}
    	}
    }

    function db_relation_n_n_delete($field_info, $main_primary_key)
    {
    	$this->db->where($field_info->primary_key_alias_to_this_table, $main_primary_key);
    	$this->db->delete($field_info->relation_table);
    }

    function get_field_types_basic_table()
    {
    	$db_field_types = array();
    	foreach($this->db->query("SHOW COLUMNS FROM {$this->table_name}")->result() as $db_field_type)
    	{
    		$type = explode("(",$db_field_type->Type);
    		$db_type = $type[0];

    		if(isset($type[1]))
    		{
    			$length = substr($type[1],0,-1);
    		}
    		else
    		{
    			$length = '';
    		}
    		$db_field_types[$db_field_type->Field]['db_max_length'] = $length;
    		$db_field_types[$db_field_type->Field]['db_type'] = $db_type;
    		$db_field_types[$db_field_type->Field]['db_extra'] = $db_field_type->Extra;
    	}

    	$results = $this->db->field_data($this->table_name);
    	foreach($results as $num => $row)
    	{
    		$row = (array)$row;
    		$results[$num] = (object)( array_merge($row, $db_field_types[$row['name']])  );
    	}

    	return $results;
    }

    function get_field_types($table_name)
    {
    	$results = $this->db->field_data($table_name);

    	return $results;
    }

    function db_update($post_array, $primary_key_value)
    {
    	$primary_key_field = $this->get_primary_key();
    	return $this->db->update($this->table_name,$post_array, array( $primary_key_field => $primary_key_value));
    }

    function db_insert($post_array)
    {
    	$insert = $this->db->insert($this->table_name,$post_array);
    	if($insert)
    	{
    		return $this->db->insert_id();
    	}
    	return false;
    }

    function db_delete($primary_key_value)
    {
    	$primary_key_field = $this->get_primary_key();
    	$this->db->delete($this->table_name,array( $primary_key_field => $primary_key_value));
    	if( $this->db->affected_rows() != 1)
    		return false;
    	else
    		return true;
    }

    function db_file_delete($field_name, $filename)
    {
    	if( $this->db->update($this->table_name,array($field_name => ''),array($field_name => $filename)) )
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }

    function field_exists($field,$table_name = null)
    {
    	if(empty($table_name))
    	{
    		$table_name = $this->table_name;
    	}
    	return $this->db->field_exists($field,$table_name);
    }

    function get_primary_key($table_name = null)
    {
    	if($table_name == null)
    	{
	    	if(empty($this->primary_key))
	    	{
		    	$fields = $this->get_field_types_basic_table();

		    	foreach($fields as $field)
		    	{
		    		if($field->primary_key == 1)
		    		{
		    			return $field->name;
		    		}
		    	}

		    	return false;
	    	}
	    	else
	    	{
	    		return $this->primary_key;
	    	}
    	}
    	else
    	{
	    	$fields = $this->get_field_types($table_name);

	    	foreach($fields as $field)
	    	{
	    		if($field->primary_key == 1)
	    		{
	    			return $field->name;
	    		}
	    	}

	    	return false;
    	}

    }

}