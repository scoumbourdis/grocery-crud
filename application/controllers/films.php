<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Films extends CI_Controller {
	/**
	 * @param array $_rules
	 * Regras para validação do formulário
	 */
	private $_rules = array(
        array(
            'field' => 'title'
            ,'label' => 'Title'
            ,'rules' => 'trim|required|xss_clean'
        ),
        array(
            'field' => 'release_year'
            ,'label' => 'Release Year'
            ,'rules' => 'trim|is_numeric|required|xss_clean'
        ),
        array(
            'field' => 'rental_duration'
            ,'label' => 'Rental duration'
            ,'rules' => 'trim|required|xss_clean'
        ),
        array(
            'field' => 'rental_rate'
            ,'label' => 'Rental rate'
            ,'rules' => 'trim|required|xss_clean'
        ),
        array(
            'field' => 'length'
            ,'label' => 'Length'
            ,'rules' => 'trim|required|xss_clean'
        ),
        array(
            'field' => 'replacement_cost'
            ,'label' => 'Replacement cost'
            ,'rules' => 'trim|required|xss_clean'
        ),
        array(
            'field' => 'rating'
            ,'label' => 'Rating'
            ,'rules' => 'trim|required|xss_clean'
        ),
	);

	/**
	 * Construtor da classe
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('grocery_CRUD');
		$this->grocery_crud->set_theme('twitter-bootstrap');
	}

	/**
	 * Renderizando o CRUD para a tabela "film" no Banco de dados
	 *
	 * @return void
	 */
	public function index()
	{
		try{
			$this->grocery_crud->set_table('film');
			$this->grocery_crud->set_relation_n_n('actors', 'film_actor', 'actor', 'film_id', 'actor_id', 'fullname','priority');
			$this->grocery_crud->set_relation_n_n('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
			$this->grocery_crud->unset_columns('special_features','description','actors');

			$this->grocery_crud->fields('title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features');


			if( in_array($this->grocery_crud->getState(), array('insert', 'insert_validation', 'update', 'update_validation')) ) {
			    $this->grocery_crud->set_rules($this->_rules);
			}

			$output = $this->grocery_crud->render();
			$this->load->view('example', $output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

}