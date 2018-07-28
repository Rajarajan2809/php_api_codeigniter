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
		
		/*foreach($room_data as $rmInfo) 
		{
			echo $rmInfo ,'<br>';
		}*/
		
		/*echo "room_name:",$room_data['room_name'] ,'<br>';
		echo "floor_id:",$room_data['floor_id'],'<br>';
		echo "description:",$room_data['description'] ,'<br>';
		echo "image_id:",$room_data['image_id'] ,'<br>';*/
		
		/*POST DATA:
		{
			"room_name":"test",
			"floor_id":"1011",
			"description":"hisgsagjopsgf",
			"image_id":"1"
		}*/
		
		if(isset($room_data['added_time']))
			unset($room_data['added_time']);
								
		$res = $this->db->set('room_id', 'UUID()', FALSE)->insert('Device_Rooms', $room_data);
		//$this->db->set('room_id', 'UUID()', FALSE);
		//$res = $this->db->insert('Device_Rooms', $room_data);
		
		if($res)
		{
			//select * from Device_Rooms order by room_id desc limit 1;
			$this->db->select('room_id, room_name, floor_id, description, image_id')->from('Device_Rooms')->order_by('id','DESC')->limit(1);
			$query = $this->db->get();
			foreach ($query->result() as $row)
			{
				/*
				CREATE JSON:
					{
						"model": "manage_rooms",
						"method": "configuration",
						"operation": "room_add",
						"status": "1",
						"room_id": "110d5357-836d-11e7-b993-b827eb40cc51",
						"room_name":"test",
						"floor_id":"1011",
						"description":"hisgsagjopsgf",
						"image_id":"1",
						"op_status": "0/1", 0-> failure, 1 -> success.
						"report_type": "info",
						"id": 10
					}
				*/
			
				$jsonRoom =	array(	
									"model"	=> "manage_rooms",
									"method" => "configuration",
									"operation" => "room_add",
									"status" => "1",
									"room_id" => $row->room_id,
									"room_name" => $room_data['room_name'],
									"floor_id" => $room_data['floor_id'],
									"description" => $room_data['description'],
									"image_id" => $room_data['image_id'],
									"op_status" => "1", //0-> failure
									"report_type" => "info",
									"id" => 10
								);
				$myJSON = json_encode($jsonRoom, JSON_PRETTY_PRINT)."\r\n";
				$this->sent2socket(8286, $myJSON."\r\n");
				$resp =	array("response_text"=>"created","room_id" => $row->room_id);
				$this->response($resp, REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$resp =	array("response_text"=>"could not create");
			$this->response($res, REST_Controller::HTTP_OK);
		}
	}
	
	
	
	public function update_post($room_id)
	{
		$room_data 	=	$this->request->body;
		/*foreach($room_data as $rmInfo) 
		{
			echo $rmInfo ,'<br>';
		}
		
		echo "room_name:",$room_data['room_name'] ,'<br>';
		echo "floor_id:",$room_data['floor_id'],'<br>';
		echo "description:",$room_data['description'] ,'<br>';
		echo "image_id:",$room_data['image_id'] ,'<br>';*/
		
		/*POST UPDATE DATA:
			{
				"room_name":"testingReady",
				"floor_id":"1011",
				"description":"hisgsagjopsgfkyky",
				"image_id":"2"
			}
		 */ 
		/*if(isset($room_id))
			unset($room_id);*/
			
		if(isset($room_data['room_id']))
			unset($room_data['room_id']);	
		
		if(isset($room_data['added_time']))
			unset($room_data['added_time']);
			
		$this->db->where('room_id', $room_id);
		$res = $this->db->update('Device_Rooms', $room_data);
		
		if($res)
		{
			$jsonRoom =	array(	
									"model"	=> "manage_rooms",
									"method" => "configuration",
									"operation" => "room_update",
									"status" => "1",
									"room_id" => $room_id,
									"room_name" => $room_data['room_name'],
									"floor_id" => $room_data['floor_id'],
									"description" => $room_data['description'],
									"image_id" => $room_data['image_id'],
									"op_status" => "1", //0-> failure
									"report_type" => "info",
									"id" => 10
								);
				$myJSON = json_encode($jsonRoom, JSON_PRETTY_PRINT)."\r\n";
				$this->sent2socket(8286, $myJSON."\r\n");
				$resp =	array("response_text"=>"updated","room_id" => $room_id);
				$this->response($resp, REST_Controller::HTTP_OK);
		}
		else
		{
			$resp		=	array("response_text"=>"could not update");
			$this->response($res, REST_Controller::HTTP_OK);
		}	
	}
	
	
	function delete_delete($room_id=0)
	{
		//echo "room_id:".$room_id."\n";
		if($room_id)
		{
			//echo "room_id is not empty.\n";
			$this->db->select('room_id, room_name, floor_id, description, image_id')->from('Device_Rooms')->where('room_id', $room_id);
			$query = $this->db->get();
			$res = $this->db->delete('Device_Rooms', array('room_id' => $room_id)); 
			if($query->result())
			{
				foreach ($query->result() as $row)
				{
					$jsonRoom =	array(	
											"model"	=> "manage_rooms",
											"method" => "configuration",
											"operation" => "room_delete",
											"status" => "1",
											"room_id" => $row->room_id,
											"room_name" => $row->room_name,
											"floor_id" => $row->floor_id,
											"description" => $row->description,
											"image_id" => $row->image_id,
											"op_status" => "1", //0-> failure
											"report_type" => "info",
											"id" => 10
										);
					//UPDATE `ZW_Device_Configuration` SET `room_id`='NA' WHERE room_id='0abd1157-ae50-11e7-bf9b-b827eb40cc51';
					//$query = $this->db->query('SELECT * FROM my_table WHERE room_id=''');
					$this->db->select('*')->from('ZW_Device_Configuration')->where('room_id', $room_id);
					$query = $this->db->get();
					if($query->num_rows() > 0)
					{
						$this->db->set('room_id', 'NA');
						$this->db->where('room_id', $row->room_id);
						$this->db->update('ZW_Device_Configuration'); // gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2
					}
					$myJSON = json_encode($jsonRoom, JSON_PRETTY_PRINT)."\r\n";
					$this->sent2socket(8286, $myJSON."\r\n");
					$resp =	array("response_text"=>"deleted","room_id" => $room_id);
					$this->response($resp, REST_Controller::HTTP_OK);	
				}
			}
			else
			{
				//echo "query is empty";
				$resp =	array("response_text"=>"could not delete");
				$this->response($resp, REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$jsonRoom =	array(	
				"model"	=> "manage_rooms",
				"method" => "configuration",
				"operation" => "room_delete_all",
				"status" => "1",
				"room_id" => "",
				"room_name" => "",
				"floor_id" => "",
				"description" => "",
				"image_id" => "",
				"op_status" => "1", //0-> failure
				"report_type" => "info",
				"id" => 10
			);

			//echo "room_id is empty.\n";
			$this->db->truncate('Device_Rooms');
			$myJSON = json_encode($jsonRoom, JSON_PRETTY_PRINT)."\r\n";
			$this->sent2socket(8286, $myJSON."\r\n");
		}
	}
}
