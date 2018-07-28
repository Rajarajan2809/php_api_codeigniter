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
class Zwave extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
	}
	
	public function list_get($menu_id='#'){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("ZW_Device_Configuration");
			
			if($menu_id != '#' )
				$qry=	$qry->where(["menu_id"=>$menu_id]);
			
			
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	public function commands_configuration_get($menu_id='#'){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("ZW_Command_Configuration");
			
			$res	=	$qry->get()->result();	
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	public function control_commands_get($menu_id='#'){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("ZW_Device_Control_Commands");
			
			$res	=	$qry->get()->result();	
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	public function devices_get(){
			$cols	=	array("*");
			
			$modes	=	$this->db->select($cols)->from("ZW_Device_Configuration")->get()->result();
			
			$this->response($modes, REST_Controller::HTTP_OK);
	}
	
	
	public function security_modes_get($mode_id='all',$node_id='all'){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("security_mode_configuration");
			
			if($mode_id != 'all')
				$qry=	$qry->where(["mode_id"=>$mode_id]);
			
			if($node_id != 'all' )
				$qry=	$qry->where(["node_id"=>$node_id]);
			
			
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	
	
	public function status_get($node_id='all'){
			$cols	=	array("*");
		
			$qry	=	$this->db->select($cols)->from("ZW_Wireless_Status");
			
			if($node_id != 'all' )
				$qry=	$qry->where(["node_id"=>$node_id]);
			
			
			$res	=	$qry->get()->result();	
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}
	

}
