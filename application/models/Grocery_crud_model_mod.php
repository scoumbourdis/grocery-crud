<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Modification of grocery_CRUD_model to allow adding join and where clauses

class grocery_CRUD_model_mod extends grocery_CRUD_model {

    function __construct() {
        parent::__construct();
    }
	
	public function get_dependent_dropdown_data($query, $query_binding=array())
	{
		$data = $this->db->query($query, $query_binding)->result_array();
		
		return $data;
	}
}