<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

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
class Rooms extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
	}
	
	public function list_get()
	{
		$cols =	array("DR.*,COUNT(DC.room_id) AS configured_nos");
		$qry = $this->db->select($cols)->from("Device_Rooms AS DR")
							->join("ZW_Device_Configuration AS DC","DR.room_id=DC.room_id","left")
							->group_by("DR.room_id");
		
		$res = $qry->get()->result();	
		
		
		$this->response($res, REST_Controller::HTTP_OK);
	}
	
	public function create_post()
	{
		$room_data 	=	$this->request->body;
			
		if(isset($room_data['added_time']))
			unset($room_data['added_time']);
				
							
		$this->db->set('room_id', 'UUID()', FALSE);
		$res = $this->db->insert('Device_Rooms', $room_data);
		
		if($res)
		{
			$resp =	array("response_text"=>"created");
			$this->response($resp, REST_Controller::HTTP_OK);
		}
		else
		{
			$resp =	array("response_text"=>"could not create");
			$this->response($res, REST_Controller::HTTP_OK);
		}
	}
	
	
	
	public function update_post($room_id){
		$room_data 	=	$this->request->body;
		
		if(isset($room_data['room_id']))
				unset($room_data['room_id']);
		
		if(isset($room_data['added_time']))
				unset($room_data['added_time']);
				
		
				$this->db->where('room_id', $room_id);
		$res = $this->db->update('Device_Rooms', $room_data);
		
		if($res){
			$resp		=	array("response_text"=>"updated");
			$this->response($resp, REST_Controller::HTTP_OK);
		}	
		else{
			$resp		=	array("response_text"=>"could not update");
			$this->response($res, REST_Controller::HTTP_OK);
		}	
	}
	
	
	
	function delete_delete($room_id=0){
		$res	=	$this->db->delete('Device_Rooms', array('room_id' => $room_id)); 
			
		if($res){
			$resp		=	array("response_text"=>"deleted");
			$this->response($resp, REST_Controller::HTTP_OK);
		}	
		else{
			$resp		=	array("response_text"=>"could not delete");
			$this->response($resp, REST_Controller::HTTP_OK);
		}
	}
	
}
