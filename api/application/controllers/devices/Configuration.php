<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '.../../libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Configuration extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
	}
	
	public function rooms_get(){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("Device_Rooms");
						
			$res	=	$qry->get()->result();	

			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	
	public function room_locations_get(){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("Room_Locations");
			
		
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	public function menu_info_get(){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("App_Menu_Information");
			
		
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	
		
	public function categories_get(){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("Global_Device_Categories");
			
		
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	
		
	
	

}
