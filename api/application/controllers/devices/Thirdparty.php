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
class Thirdparty extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
	}
	
	public function global_devices_get(){
			$cols	=	array("mode_id", "mode_name", "active_status");
			
			$modes	=	$this->db->select("*")->from("Global_Device_Configuration")->get()->result();
			
			$this->response($modes, REST_Controller::HTTP_OK);
	}
	
	public function video_cameras_get(){
			$cols	=	array("*");
			
			$modes	=	$this->db->select("*")->from("Video_Cameras")->get()->result();
			
			$this->response($modes, REST_Controller::HTTP_OK);
	}
	
	
	public function entertainment_get(){
			$cols	=	array("*");
			
			$modes	=	$this->db->select($cols)->from("Entertainment_Devices")->get()->result();
			
			$this->response($modes, REST_Controller::HTTP_OK);
	}
	
	
	
	public function hvac_get(){
			$cols	=	array("*");
			
			$modes	=	$this->db->select("*")->from("HVAC_Controllers")->get()->result();
			
			$this->response($modes, REST_Controller::HTTP_OK);
	}
	
	
	
	

}
