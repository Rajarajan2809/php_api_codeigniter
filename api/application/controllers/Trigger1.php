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
class Trigger extends REST_Controller 
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	//function to get scene list
	public function list_post()
	{
		$json_data = $this->request->body;
		$resp = array();
		
		if(isset($json_data['type']))
		{
			if($json_data['type'] == 'scene')
			{
				//scene device control
				$this->db->select('scene_id,action_id');
				$this->db->from('Scene');
				if(isset($json_data['scene_id']))
					$this->db->where('scene_id' , $json_data['scene_id']);
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				$scene_flag=0;
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->select('AD.device_id,AD.end_point,AD.class_key,AD.command_key, ZDCC.class_version,AD.device_status,AD.execution_delay');
						$this->db->from('Action_Detail AD');
						$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
						$this->db->where(array('AD.action_id' => $row1->action_id,'ZDCC.class_version !=' => NULL));//,'AD.device_id IS NOT NULL','AD.end_point IS NOT NULL','AD.class_key IS NOT NULL','AD.command_key IS NOT NULL', 'ZDCC.class_version IS NOT NULL','AD.device_status IS NOT NULL','AD.execution_delay IS NOT NULL'));
						$this->db->order_by('AD.execution_order', 'asc');
						$query2 = $this->db->get();
						//echo $this->db->last_query();
						//$arrData = array();

						$this->db->select('AN.notification_type,AN.recipient');
						$this->db->from('Action_Notifications AN');
						$this->db->where('AN.action_id' , $row1->action_id);//,'AD.device_id IS NOT NULL','AD.end_point IS NOT NULL','AD.class_key IS NOT NULL','AD.command_key IS NOT NULL', 'ZDCC.class_version IS NOT NULL','AD.device_status IS NOT NULL','AD.execution_delay IS NOT NULL'));
						$query3 = $this->db->get();
						$resp[]  = array('scene_id' => $row1->scene_id,'device_parameters' => $query2->result(),'notification_parameters' => $query3->result());
					}
					
					$scene_flag=1;
				}
			}
			else if($json_data['type'] == 'trigger')
			{
				//scene device control
				$this->db->select('trigger_name,trigger_id,action_id,trigger_type_id,device_id,end_point,class_key,command_key,device_status,execution_time,start_time,end_time,sunrise_time,sunset_time');
				$this->db->from('Triggers');
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				$trigger_flag=0;
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->select('AD.device_id,AD.end_point,AD.class_key,AD.command_key, ZDCC.class_version,AD.device_status,AD.execution_delay');
						$this->db->from('Action_Detail AD');
						$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
						$this->db->where(array('AD.action_id' => $row1->action_id,'ZDCC.class_version !=' => NULL));//,'AD.device_id IS NOT NULL','AD.end_point IS NOT NULL','AD.class_key IS NOT NULL','AD.command_key IS NOT NULL', 'ZDCC.class_version IS NOT NULL','AD.device_status IS NOT NULL','AD.execution_delay IS NOT NULL'));
						$this->db->order_by('AD.execution_order', 'asc');
						$query2 = $this->db->get();
						//echo $this->db->last_query();
						//$arrData = array();

						$this->db->select('AN.notification_type,AN.recipient');
						$this->db->from('Action_Notifications AN');
						$this->db->where('AN.action_id' , $row1->action_id);//,'AD.device_id IS NOT NULL','AD.end_point IS NOT NULL','AD.class_key IS NOT NULL','AD.command_key IS NOT NULL', 'ZDCC.class_version IS NOT NULL','AD.device_status IS NOT NULL','AD.execution_delay IS NOT NULL'));
						$query3 = $this->db->get();
						$resp[]  = array(
										'trigger_name' 		=> $row1->trigger_name,
										'trigger_type' 		=> $row1->trigger_id,
										'execution_time'	=> $row1->execution_time,
										'start_time'		=> $row1->start_time,
										'end_time'			=> $row1->end_time,
										'sunrise_time'		=> $row1->sunrise_time,
										'sunset_time' 		=> $row1->sunset_time,
										'trigger_input'		=> array(
																'device_id' => $row1->device_id,
																'end_point' => $row1->end_point,
																'cmd_cls' 	=> $row1->class_key,
																'cmd' 		=> $row1->command_key,
																'cmd_val' 	=> $row1->device_status),
										'device_parameters' 		=> $query2->result(),
										'notification_parameters' 	=> $query3->result());
					}
					$trigger_flag=1;
				}
			}
		}
		else
		{
			$resp =	array("response_text"=>"Invalid");
		}

		//echo $res;
		//$resp = $this->db->get('Scene')->result();
		if(($trigger_flag == 0) && ($scene_flag == 0))
			$resp =	array("response_text"=>"scene or trigger get failed");
		$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	public function activate_post()
	{
		$q_param = $this->request->body;
		$resp = '';
		
		if(isset($q_param['scene_id']))
		{
			//scene device control
			$this->db->select('S.action_id,AD.device_id,AD.end_point,AD.class_key,AD.command_key, ZDCC.class_version,AD.device_status,AD.execution_delay');
			$this->db->from('Scene S');
			$this->db->join('Action_Detail AD','S.action_id = AD.action_id','left');
			$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
			$this->db->where(array('S.scene_id' => $q_param['scene_id'],'ZDCC.class_version !=' => NULL));//,'AD.device_id IS NOT NULL','AD.end_point IS NOT NULL','AD.class_key IS NOT NULL','AD.command_key IS NOT NULL', 'ZDCC.class_version IS NOT NULL','AD.device_status IS NOT NULL','AD.execution_delay IS NOT NULL'));
			$this->db->order_by('AD.execution_order', 'asc');
			$query = $this->db->get();
			//echo $this->db->last_query();
			
			$scene_flag=0;
			if($query->result())
			{
				foreach ($query->result() as $row)
				{
					$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
					$post_data = 	array(
										'nodeid'	=> $row->device_id,
										'instance'	=> $row->end_point,
										'cmdclass'	=> $row->class_key,
										'version'	=> $row->class_version,
										'cmd'		=> $row->command_key,
										'value'		=> $row->device_status
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
					$scene_flag=1;
					$context  = stream_context_create( $options );
					$result = file_get_contents( $url, false, $context );
					//echo $url,'<br>',$myJSON,'<br>';
					//echo $result.'<br><br>';
				}
			}
			
			//scene device notification
			$this->db->select('S.action_id,AN.notification_type,AN.recipient');
			$this->db->from('Scene S');
			$this->db->join('Action_Notifications AN','S.action_id = AN.action_id','left');
			$this->db->where(array('S.scene_id' => $q_param['scene_id']));
			$query = $this->db->get();
			
			if($query->result())
			{
				foreach ($query->result() as $row)
				{
					//echo 'notification','<br>';
					//$scene_flag=1;
				}
			}
			
			if($scene_flag == 1)
			{
				$resp =	array("scene_id" => $q_param['scene_id'],"response_text"=>"scene executed");
			}
			else
			{
				$resp =	array("scene_id" => $q_param['scene_id'],"response_text"=>"scene execution failed.");
			}
		}
		else if(isset($q_param['device_id']) && isset($q_param['end_point']) && isset($q_param['cmd_cls'])  && isset($q_param['cmd'])  && isset($q_param['cmd_val']))
		{
			//triggerdevice
			$this->db->select('T.trigger_id,T.action_id,AD.device_id,AD.end_point,AD.class_key,AD.command_key,ZDCC.class_version,AD.device_status,AD.execution_delay');
			$this->db->from('Triggers T');
			$this->db->join('Action_Detail AD','T.action_id = AD.action_id','left');
			$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
			$this->db->where(array('T.device_id' => $q_param['device_id'],'T.end_point' => $q_param['end_point'],'T.class_key' => $q_param['cmd_cls'],'T.command_key' => $q_param['cmd'],'T.device_status' => $q_param['cmd_val'],'ZDCC.class_version !=' => NULL));
			$this->db->order_by('T.action_id asc,AD.execution_order asc');
			$query = $this->db->get();
			//echo $this->db->last_query().'<br>';
			$trigger_flag=0;
			$trigger_id = '';

			if($query->result())
			{
				foreach ($query->result() as $row)
				{
					$trigger_id = $row->trigger_id;
					$trigger_flag = 1;
					$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
					$post_data = 	array(
										'nodeid'	=> $row->device_id,
										'instance'	=> $row->end_point,
										'cmdclass'	=> $row->class_key,
										'version'	=> $row->class_version,
										'cmd'		=> $row->command_key,
										'value'		=> $row->device_status
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
				}

				$this->db->select('T.action_id,AN.notification_type,AN.recipient');
				$this->db->from('Triggers T');
				$this->db->join('Action_Notifications AN','T.action_id = AN.action_id','left');
				$this->db->where(array('T.device_id' => $q_param['device_id'],'T.end_point' => $q_param['end_point'],'T.class_key' => $q_param['cmd_cls'],'T.command_key' => $q_param['cmd'],'T.device_status' => $q_param['cmd_val']));
				$query = $this->db->get();
				
				if($query->result())
				{
					foreach ($query->result() as $row)
					{
						//echo 'notification<br>';
						$trigger_flag = 1;
					}
				}
			}
			if($trigger_flag == 1)
				$resp =	array("trigger_id" => $trigger_id,"response_text"=>"trigger executed");
			else
				$resp =	array("trigger_id" => $trigger_id,"response_text"=>"trigger execution failed.");
		}
		else if(isset($q_param['action_id']))
		{
			//trigger/scene testing
			$query1 = $this->db->query('SELECT trigger_id FROM Triggers WHERE action_id = '.$q_param['action_id']);
			$query2 = $this->db->query('SELECT scene_id FROM Scene WHERE action_id = '.$q_param['action_id']);
			if($query1->num_rows() > 0)
			{
				$this->db->select('T.trigger_id,AD.device_id,AD.end_point,AD.class_key,AD.command_key,ZDCC.class_version,AD.device_status,AD.execution_delay');
				$this->db->from('Triggers T');
				$this->db->join('Action_Detail AD','T.action_id = AD.action_id','left');
				$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
				$this->db->where(array('T.action_id' => $q_param['action_id'],'ZDCC.class_version !=' => NULL));
				$this->db->order_by('AD.execution_order', 'asc');
				$query = $this->db->get();
				//echo $this->db->last_query().'<br>';
				$trigger_flag=0;
				$trigger_id = '';

				if($query->result())
				{
					foreach ($query->result() as $row)
					{
						$trigger_id = $row->trigger_id;
						$trigger_flag = 1;
						$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
						$post_data = 	array(
											'nodeid'	=> $row->device_id,
											'instance'	=> $row->end_point,
											'cmdclass'	=> $row->class_key,
											'version'	=> $row->class_version,
											'cmd'		=> $row->command_key,
											'value'		=> $row->device_status
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
					}
				}	

				$this->db->select('AN.notification_type,AN.recipient');
				$this->db->from('Triggers T');
				$this->db->join('Action_Notifications AN','T.action_id = AN.action_id','left');
				$this->db->where(array('T.action_id' => $q_param['action_id']));//,'T.end_point' => $q_param['end_point'],'T.class_key' => $q_param['cmd_cls'],'T.command_key' => $q_param['cmd'],'T.device_status' => $q_param['cmd_val']));
				$query = $this->db->get();
				
				if($query->result())
				{
					foreach ($query->result() as $row)
					{
						//echo 'notification<br>';
						$trigger_flag = 1;
					}
				}

				if($trigger_flag == 1)
					$resp =	array("trigger_id" => $trigger_id,"response_text"=>"trigger executed");
				else
					$resp =	array("trigger_id" => $trigger_id,"response_text"=>"trigger execution failed.");
			}
			else if($query2->num_rows() > 0)
			{
				$this->db->select('S.scene_id,AD.device_id,AD.end_point,AD.class_key,AD.command_key,ZDCC.class_version,AD.device_status,AD.execution_delay');
				$this->db->from('Scene S');
				$this->db->join('Action_Detail AD','S.action_id = AD.action_id','left');
				$this->db->join('ZW_Device_Control_Commands ZDCC','AD.device_id = ZDCC.node_id AND AD.end_point = ZDCC.end_points','left');
				$this->db->where(array('S.action_id' => $q_param['action_id'],'ZDCC.class_version !=' => NULL));
				$this->db->order_by('AD.execution_order', 'asc');
				$query = $this->db->get();
				//echo $this->db->last_query().'<br>';
				$scene_flag=0;
				$scene_id = '';

				if($query->result())
				{
					foreach ($query->result() as $row)
					{
						$scene_id = $row->scene_id;
						$scene_flag = 1;
						$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
						$post_data = 	array(
											'nodeid'	=> $row->device_id,
											'instance'	=> $row->end_point,
											'cmdclass'	=> $row->class_key,
											'version'	=> $row->class_version,
											'cmd'		=> $row->command_key,
											'value'		=> $row->device_status
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
					}

					$this->db->select('AN.notification_type,AN.recipient');
					$this->db->from('Scene S');
					$this->db->join('Action_Notifications AN','S.action_id = AN.action_id','left');
					$this->db->where(array('S.action_id' => $q_param['action_id']));//,'T.end_point' => $q_param['end_point'],'T.class_key' => $q_param['cmd_cls'],'T.command_key' => $q_param['cmd'],'T.device_status' => $q_param['cmd_val']));
					$query = $this->db->get();
					
					if($query->result())
					{
						foreach ($query->result() as $row)
						{
							//echo 'notification<br>';
							$scene_flag = 1;
						}
					}
				}

				if($scene_flag == 1)
					$resp =	array("scene_id" => $scene_id,"response_text"=>"scene executed");
				else
					$resp =	array("scene_id" => $scene_id,"response_text"=>"scene execution failed.");
			}
			else
			{
				$resp =	array("response_text"=>"Invalid");
			}
		}
		else
		{
			$resp =	array("response_text"=>"Invalid");
		}
		$myJSON = json_encode($resp, JSON_PRETTY_PRINT)."\r\n";
		$this->sent2socket(8286, $myJSON."\r\n");
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function create_post()
	{
		$json_data 	=	$this->request->body;
		//echo "trigger_name:",$room_data['trigger_name'] ,'<br>';
		//echo "input_trigger:",$room_data['input_trigger'],'<br>';
		//echo "parameters:",$room_data['parameters'] ,'<br>';
		$action_id = '';$scene_flag = 0; $trigger_flag = 0;$resp='';
		foreach($json_data as $key => $value)
		{
			//echo "$key :";
			if(!is_array($json_data[$key]))
			{
				//echo $json_data[$key].'<br>';
				if($key == "name")//	echo "trigger input<br>";
				{
					//echo "trigger input<br>";
					$data = array(
						'scene_name' => $json_data['name'],
						//$value1 = 
					);
					$this->db->insert('Scene', $data);
					
					//get trigger id
					$this->db->select('scene_id,action_id')->from('Scene')->order_by('id','DESC')->limit(1);
					$query = $this->db->get();
					foreach ($query->result() as $row)
					{
						$action_id = $row->action_id;
						//$scene_id = $row->scene_id;
					}
					$scene_flag = 2;
					$resp =	array("scene_id" => $row->scene_id,"scene_name" =>  $json_data['scene_name'],"response_text" => "scene created");
					//echo "<br>action_id : ".$action_id."<br>";
				}
			}
			else
			{
				//$arr = $json_data[$key];
				foreach($json_data[$key] as $value1)
				{
					//$arr2 = $value1;
					if($key == "trigger_input")//	echo "trigger input<br>";
					{
						//echo "trigger input<br>";
						$data = array(
										'trigger_name' => $json_data['trigger_name'],
										'trigger_type_id' => $json_data['trigger_type'],
										'device_id' => $value1['device_id'],
										'end_point' => $value1['end_point'],
										'class_key' => $value1['cmd_cls'],
										'command_key' => $value1['cmd'],
										'device_status' => $value1['cmd_val']
										//$value1 = 
									);
						switch($json_data['trigger_type'])
						{
							case '2':
								$data = $data + array('execution_time' => $json_data['execution_time']);
								break;

							case '3':
								$data = $data + array('start_time' => $json_data['start_time']) + array('end_time' => $json_data['end_time']);
								break;

							case '4':
								$data = $data + array('sunrise_time' => $json_data['sunrise_time']) + array('sunset_time' => $json_data['sunset_time']);
								break;
						}
						$this->db->insert('Triggers', $data);
						
						//get action id
						$this->db->select('trigger_id,action_id')->from('Triggers')->order_by('id','DESC')->limit(1);
						$query = $this->db->get();
						//$trigger_id='';
						foreach ($query->result() as $row)
						{
							$action_id = $row->action_id;
							//$trigger_id = $row->trigger_id;
						}
						//echo "<br>action_id : ".$action_id."<br>";
						$trigger_flag = 2;
						$resp =	array('trigger_id' => $row->trigger_id,'trigger_name' =>  $json_data['trigger_name'],'action_id' => $action_id,'response_text' => 'trigger created');
					}
					else if(($key == "parameters") && ($action_id != ''))
					{
						//echo "trigger output<br>";
						if(isset($value1['notification_type']) && isset($value1['recipient']))
						{
							$trigger_data = array(
								'action_id' => $action_id,
								'notification_type' => $value1['notification_type'],
								'recipient' => $value1['recipient']
							);
							$this->db->insert('Action_Notifications', $trigger_data);
						}
						else
						{
							$trigger_data = array(
								'action_id' 		=> $action_id,
								'device_id' 		=> $value1['device_id'],
								'end_point' 		=> $value1['end_point'],
								'class_key' 		=> $value1['cmd_cls'],
								'command_key' 		=> $value1['cmd'],
								'device_status' 	=> $value1['cmd_val'],
								'execution_order' 	=> $value1['execution_order'],
								'execution_delay' 	=> $value1['execution_delay']
								//$value1 = 
							);
							$this->db->insert('Action_Detail', $trigger_data);
						}
					}
					else
					{
						$scene_flag =1;$trigger_flag=1;
						$resp =	array("response_text" => "scene/trigger creation failed");
					}
					//echo '<br>';
				}
			}
		}
		$myJSON = json_encode($resp, JSON_PRETTY_PRINT)."\r\n";
		$this->sent2socket(8286, $myJSON."\r\n");
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function delete_post()
	{
		$json_data = $this->request->body;
		$resp = array();
		$trigger_flag = 0;$scene_flag = 0;

		if(isset($json_data['type']))
		{
			if($json_data['type'] == 'scene')
			{
				//scene device control
				$this->db->select('scene_id,action_id');
				$this->db->from('Scene');
				if(isset($json_data['scene_id']))
					$this->db->where('scene_id' , $json_data['scene_id']);
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->delete('Action_Detail', array('action_id' => $row1->action_id));
						//echo $this->db->last_query();
						
						$this->db->delete('Action_Notifications', array('action_id' => $row1->action_id));
						
						if(isset($json_data['scene_id']))
						{
							$this->db->delete('Scene', array('scene_id' => $json_data['scene_id']));
							$resp[]  = array('scene_id' => $row1->scene_id,'response_text' => 'scene deleted');
						}
						else
						{
							$this->db->truncate('Scene');
							$resp[]  = array('response_text' => 'all scenes deleted');
							break;
						}
					}
					$scene_flag=1;
				}
			}
			else if($json_data['type'] == 'trigger')
			{
				//trigger device control
				$this->db->select('trigger_id,action_id');
				$this->db->from('Triggers');
				if(isset($json_data['trigger_id']))
					$this->db->where('trigger_id' , $json_data['trigger_id']);
				else if(isset($json_data['device_id']) && isset($json_data['end_point']) && isset($json_data['cmd_cls']) && isset($json_data['cmd_key']) && isset($json_data['device_status']))
						$this->db->where(array(
											'device_id' 	=> $json_data['device_id'],
											'end_point' 	=> $json_data['end_point'],
											'cmd_cls' 		=> $json_data['cmd_cls'],
											'cmd_key'		=> $json_data['cmd_key'],
											'device_status' => $json_data['device_status']
											));
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->delete('Action_Detail', array('action_id' => $row1->action_id));
						//echo $this->db->last_query();
						
						$this->db->delete('Action_Notifications', array('action_id' => $row1->action_id));
						
						if(isset($json_data['trigger_id']))
						{
							$this->db->delete('Triggers', array('trigger_id' => $json_data['trigger_id']));
							$resp[]  = array('trigger_id' => $row1->trigger_id,'response_text' => 'trigger deleted');
						}
						else if(isset($json_data['device_id']) && isset($json_data['end_point']) && isset($json_data['cmd_cls']) && isset($json_data['cmd_key']) && isset($json_data['device_status']))
						{
							$this->db->delete('Triggers', array(
																'device_id' 	=> $json_data['device_id'],
																'end_point' 	=> $json_data['end_point'],
																'cmd_cls' 		=> $json_data['cmd_cls'],
																'cmd_key'		=> $json_data['cmd_key'],
																'device_status' => $json_data['device_status']
																));
							$resp[]  = array(
												'device_id' 	=> $json_data['device_id'],
												'end_point' 	=> $json_data['end_point'],
												'cmd_cls' 		=> $json_data['cmd_cls'],
												'cmd_key'		=> $json_data['cmd_key'],
												'device_status' => $json_data['device_status'],
												'response_text' => 'trigger deleted'
											);
						}
						else
						{
							$this->db->truncate('Triggers');
							$resp[]  = array('response_text' => 'all triggers deleted');
							break;
						}
					}
					$trigger_flag = 1;
				}
			}
		}
		else
		{
			$resp =	array('response_text' => 'Invalid');
		}

		//echo $res;
		//$resp = $this->db->get('Scene')->result();
		if(($trigger_flag == 0) && ($scene_flag == 0))
			$resp =	array('response_text'=>'scene or trigger delete failed');
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	/*public function update_post()
	{
		$json_data = $this->request->body;
		$resp = array();
		
		if(isset($json_data['type']))
		{
			if($json_data['type'] == 'scene')
			{
				//scene device control
				$this->db->select('scene_id,action_id');
				$this->db->from('Scene');
				if(isset($json_data['scene_id']))
					$this->db->where('scene_id' , $json_data['scene_id']);
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				$scene_flag=0;
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->delete('Action_Detail', array('action_id' => $row1['action_id']));
						//echo $this->db->last_query();
						
						$this->db->delete('Action_Notifications', array('action_id' => $row1['action_id']));
						
						if(isset($json_data['scene_id']))
						{
							$this->db->delete('Scene', array('scene_id' => $json_data['scene_id']));
							$resp[]  = array('scene_id' => $row1->scene_id,'response_text' => 'scene deleted');
						}
						else
						{
							$this->db->truncate('Scene');
							$resp[]  = array('response_text' => 'all scenes deleted');
							break;
						}
					}
					$scene_flag=1;
				}
			}
			else if($json_data['type'] == 'trigger')
			{
				//trigger device control
				$this->db->select('trigger_id,action_id');
				$this->db->from('Triggers');
				if(isset($json_data['trigger_id']))
					$this->db->where('trigger_id' , $json_data['trigger_id']);
				else if(isset($json_data['device_id']) && isset($json_data['end_point']) && isset($json_data['cmd_cls']) && isset($json_data['cmd_key']) && isset($json_data['device_status']))
						$this->db->where(array(
											'device_id' 	=> $json_data['device_id'],
											'end_point' 	=> $json_data['end_point'],
											'cmd_cls' 		=> $json_data['cmd_cls'],
											'cmd_key'		=> $json_data['cmd_key'],
											'device_status' => $json_data['device_status']
											));
				$query1 = $this->db->get();
				//echo $this->db->last_query();
				
				$trigger_flag = 0;
				if($query1->result())
				{
					foreach ($query1->result() as $row1)
					{
						$this->db->delete('Action_Detail', array('action_id' => $row1['action_id']));
						//echo $this->db->last_query();
						
						$this->db->delete('Action_Notifications', array('action_id' => $row1['action_id']));
						
						if(isset($json_data['trigger_id']))
						{
							$this->db->delete('Triggers', array('trigger_id' => $json_data['trigger_id']));
							$resp[]  = array('trigger_id' => $row1->trigger_id,'response_text' => 'trigger deleted');
						}
						else if(isset($json_data['device_id']) && isset($json_data['end_point']) && isset($json_data['cmd_cls']) && isset($json_data['cmd_key']) && isset($json_data['device_status']))
						{
							$this->db->delete('Triggers', array(
																'device_id' 	=> $json_data['device_id'],
																'end_point' 	=> $json_data['end_point'],
																'cmd_cls' 		=> $json_data['cmd_cls'],
																'cmd_key'		=> $json_data['cmd_key'],
																'device_status' => $json_data['device_status']
																));
							$resp[]  = array(
												'device_id' 	=> $json_data['device_id'],
												'end_point' 	=> $json_data['end_point'],
												'cmd_cls' 		=> $json_data['cmd_cls'],
												'cmd_key'		=> $json_data['cmd_key'],
												'device_status' => $json_data['device_status'],
												'response_text' => 'trigger deleted'
											);
						}
						else
						{
							$this->db->truncate('Triggers');
							$resp[]  = array('response_text' => 'all triggers deleted');
							break;
						}
					}
					$trigger_flag = 1;
				}
			}
		}
		else
		{
			$resp =	array('response_text' => 'Invalid');
		}

		//echo $res;
		//$resp = $this->db->get('Scene')->result();
		if(($trigger_flag == 0) && ($scene_flag == 0))
			$resp =	array("response_text"=>"scene/trigger delete failed");
		$this->response($resp, REST_Controller::HTTP_OK);
	}*/
}
