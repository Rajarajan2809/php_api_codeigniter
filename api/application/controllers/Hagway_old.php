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
class Hagway extends REST_Controller 
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function info_get()
	{
		//echo "Hagway id == ",$this->query('hagway');
		/*$this->db->select('version, device_name, wan_ip, wan_ports')->from('Stargate_Manager')->where('hagway_id', $this->query('hagway'));
		$query = $this->db->get();
		if($query->result() && )
		{
			foreach ($query->result() as $row)
			{
				$jsonInfo =	array(	
									"application" => "hagway_api",
									"version" => $row->version,
									"hagway_id" => $this->query('hagway'),
									"device_name" => $row->device_name,
									"wan_ip" => $row->wan_ip,
									"wan_control_port" => $row->wan_ip,
									"wan_feedback_port" => $room_data['description'],
									"status" => "hagway_found"
								);
		/*Example json:{
			"result": [
				{
					"application": "hagway_api",
					"version": "1.0",
					"hagway_id": "GCHAG00000002",
					"device_name": "hagway",
					"wan_ip": "183.82.249.181",
					"wan_control_port": "7281",
					"wan_feedback_port": "7286",
					"status": "hagway_found"
				}
			]
		}*/
		$cols	=	array("mode_id", "mode_name", "active_status");
		$info 	=	file_get_contents(__DIR__."/info/info.json");
		$info_arr	=	json_decode($info);
		$this->response($info_arr, REST_Controller::HTTP_OK);
	}
}
