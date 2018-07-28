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
		//echo $this->query('hagway');
		$this->db->select('version, hagway_id, device_name, wan_ip, wan_ports')->from('Stargate_Manager');//->where('hagway_id', $this->query('hagway'));
		$query = $this->db->get();
		if($query->result())//if($query->result() && $this->query('hagway'))
		{
			foreach ($query->result() as $row)
			{
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
				$wanPorts = preg_split("/[,]+/", $row->wan_ports);
				if(isset($wanPorts[0]) && isset($wanPorts[5]))
				{
					$jsonInfo[] =	array(
										"application" => "hagway_api",
										"version" => $row->version,
										"hagway_id" => $row->hagway_id,
										"device_name" => $row->device_name,
										"wan_ip" => $row->wan_ip,
										"wan_control_port" => $wanPorts[0],
										"wan_feedback_port" => $wanPorts[5],
										"status" => "hagway_found"
									);
					$jsonInfo = array("result" => $jsonInfo);
					$this->response($jsonInfo, REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		$resp =	array("response"=>"Invalid hagway id");
		$this->response($resp, REST_Controller::HTTP_OK);
	}
}
