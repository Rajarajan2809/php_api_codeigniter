<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';
//require APPPATH . '/libraries/Curl.php';
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
class Scene extends REST_Controller
{

	function __construct()
	{

		parent::__construct();
		$this->load->database();
		//$this->load->library('curl');

	}

	public function list_post()
	{
		//SELECT distinct scene_id,scene_name FROM Scene_Activity ORDER BY id;
		$this->db->distinct();
		$this->db->select('scene_id,scene_name');
		$this->db->order_by('id', 'asc');
		//$res = $this->db->get('Scene_Activity');

		//echo $res;
		$res = $this->db->get('Scene_Activity')->result();
		$this->response($res, REST_Controller::HTTP_OK);
	}

	public function activate_post()
	{
		$room_data 	=	$this->request->body;

		/*foreach($room_data as $rmInfo)
		{
			echo $rmInfo ,'<br>';
		}*/

		/*if(is_array($room_data))
		{
			echo "scene_id:",$room_data['scene_id'] ,'<br>';
			echo "user_id:",$room_data['user_id'],'<br>';
			echo "node_id:",$room_data['node_id'] ,'<br>';
			echo "end_pt:",$room_data['end_pt'] ,'<br>';
			echo "cmd_cls:",$room_data['cmd_cls'] ,'<br>';
			echo "cmd:",$room_data['cmd'] ,'<br>';
			echo "cmd_val:",$room_data['cmd_val'] ,'<br>';
		}*/

		/*POST DATA:
		{
			"scene_id":"1234",
			"user_id":"1011",
			"node_id":"12",
			"end_pt":"1",
			"cmd_cls":"1",
			"cmd":"20",
			"cmd_val":"255"
		}*/

		//SELECT SA.node_id,SA.end_point,SA.scene_value,SA.timed,SA.delay,SA.start_time,SA.end_time,COALESCE(SH.action_type, '') AS prev_act,ZDCC.class_key,ZDCC.class_version,ZDCC.command_key FROM Scene_Activity AS SA LEFT JOIN Scene_History AS SH ON SH.scene_id = SA.scene_id LEFT JOIN ZW_Device_Configuration AS ZDC ON ZDC.node_id = SA.node_id AND ZDC.end_point = SA.end_point LEFT JOIN ZW_Device_Control_Commands AS ZDCC ON ZDCC.node_id = ZDC.node_id WHERE SA.scene_id = '"+sceneId+"' AND ZDCC.class_key != '0x20';
		if(isset($room_data['scene_id']) && isset($room_data['user_id']))
		{
			//scene
			$this->db->select('SA.node_id,SA.end_point,SA.scene_value,SA.timed,SA.delay,SA.start_time,SA.end_time,COALESCE(SH.action_type, \'\') AS prev_act,ZDCC.class_key,ZDCC.class_version,ZDCC.command_key');
			$this->db->from('Scene_Activity SA');
			$this->db->join('Scene_History SH','SH.scene_id = SA.scene_id','left');
			$this->db->join('ZW_Device_Configuration ZDC','ZDC.node_id = SA.node_id AND ZDC.end_point = SA.end_point','left');
			$this->db->join('ZW_Device_Control_Commands ZDCC','ZDCC.node_id = ZDC.node_id','left');
			$this->db->where(array('SA.scene_id' => $room_data['scene_id'], 'ZDCC.class_key !=' => '0x20'));
			$query = $this->db->get();
			//echo '<pre>';                  // to preserve formatting
			//die($this->db->last_query());  // halt execution and print last ran query.
			if($query)
			{
				foreach ($query->result() as $row)
				{
					/*CREATE JSON:
						{
							"nodeid" : "1001",//nodeId
							"instance" : "1",//instance
							"cmdclass" : "0x21",//cmdClass
							"version" : "0x01",//version
							"cmd" : "0x03",//cmd
							"value" : "255"//value
						}
					*/

					/*$jsonRoom =	array(
										"nodeid"	=> $row->node_id,
										"instance"	=> $row->end_point,
										"cmdclass"	=> $row->class_key,
										"version"	=> $row->class_version,
										"cmd"		=> $row->command_key,
										"value"		=> $row->scene_value
									);*/
					//$myJSON = json_encode($jsonRoom, JSON_PRETTY_PRINT)."\r\n";
					//echo "JSON:",$myJSON;
					/*$this->sent2socket(8286, $myJSON."\r\n");*/
					$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
					$post_data = 	array(
										'nodeid'	=> $row->node_id,
										'instance'	=> $row->end_point,
										'cmdclass'	=> $row->class_key,
										'version'	=> $row->class_version,
										'cmd'		=> $row->command_key,
										'value'		=> $row->scene_value
									);
					$myJSON = json_encode($post_data, JSON_PRETTY_PRINT)."\r\n";
					$options = array(
						'http' => array(
						'method'  => 'POST',
						'content' => $myJSON ,
						'header'=>  "Content-Type: application/json\r\n" .
									"Accept: application/json\r\n"
						)
					);

					$context  = stream_context_create( $options );
					$result = file_get_contents( $url, false, $context );
					//echo $result;


					//echo $url,'<br>',$myJSON,'<br>','<br>';
					//echo $output;

				}
				$resp =	array("scene_id" => $room_data['scene_id'],"response_text"=>"scene executed");
				$this->response($resp, REST_Controller::HTTP_OK);
			}
		}
		else if(isset($room_data['node_id']) && isset($room_data['end_pt']) && isset($room_data['cmd_cls'])  && isset($room_data['cmd'])  && isset($room_data['cmd_val']))
		{
			//trigger
		}
		else
		{
			$resp =	array("response_text"=>"Invalid");
			$this->response($resp, REST_Controller::HTTP_OK);
		}
	}
}
