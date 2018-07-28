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
	}

	public function create_post()
	{
		$json_data 	=	$this->request->body;
		$scene_id = '';
		$webRespAr=array();
		$socRespAr=array();
		$scene_flag = 0;

		if(isset($json_data['scene_name']) && isset($json_data['trigger_type']) && isset($json_data['execution_mode']) && isset($json_data['execution_scene_id']))
		{
			$scene_data = array(
							'scene_name'		 => $json_data['scene_name'],
							'trigger_type'		 => $json_data['trigger_type'],
							'execution_mode'	 => $json_data['execution_mode'],
							'execution_scene_id' => $json_data['execution_scene_id']
						);
						$this->db->set('scene_id', 'UUID()', FALSE);

			$this->db->insert('Scene', $scene_data);
			//echo $this->db->last_query();
			$this->db->select('scene_id')->from('Scene')->order_by('id','DESC')->limit(1);
			$query = $this->db->get();
			//echo $this->db->last_query();
			foreach ($query->result() as $row)
			{
				$scene_id = $row->scene_id;
			}

			switch($json_data['trigger_type'])
			{
				case '1':
				{
					//input trigger
					foreach($json_data['input_parameters'] as $value1)
					{
						if(isset($json_data['execution_mode']) && isset($value1['cuid']) && isset($value1['device_id']) && isset($value1['end_point']) && isset($value1['cmd_cls']) && isset($value1['cmd']) && isset($value1['cmd_val']))
						{
							$input_data = array(
												'scene_id'			=> $scene_id,
												'execution_mode'	=> $json_data['execution_mode'],
												'cuid'				=> $value1['cuid'],
												'device_id'         => $value1['device_id'],
												'end_point'         => $value1['end_point'],
												'cmd_cls'         	=> $value1['cmd_cls'],
												'cmd'      			=> $value1['cmd'],
												'cmd_val'    		=> $value1['cmd_val']
											);
							$this->db->insert('Scene_Trigger', $input_data);
							$scene_flag = 1;
						}
					}
				}
				break;

				case '2':
				{
					//on time
					foreach($json_data['input_parameters'] as $value1)
					{
						if(isset($json_data['trigger_type']) && isset($value1['execution_time']) && isset($value1['repeat']) && isset($value1['week_days']) && isset($value1['month']))
						{
							$wk_cron ='';$weekdays='';$day_crn='';$month_crn='';
							$wk_ds_arr = explode(',',$value1['week_days']);
							if($value1['repeat'] == "1")
							{
								foreach($wk_ds_arr as $index => $value12)
								{
									if($weekdays != '')
									{
										$weekdays = $weekdays.','.$value12;
										if($value12 == '1')
										{
											if($wk_cron != '')
												$wk_cron = $wk_cron.','.$index;
											else
												$wk_cron = $index;
										}
									}
									else
									{
										$weekdays = $value12;
										if($value12 == '1')
											$wk_cron = $index;
									}
								}
							}
							else
							{
								$day_crn=date('d');
								$month_crn=date('m');
							}
							//echo $weekdays.'<br>'.$month;

							$input_data = array(
											'scene_id'			=> $scene_id,
											'trigger_type'		=> $json_data['trigger_type'],
											'execution_time'    => $value1['execution_time'],
											'repeat'	        => $value1['repeat'],
											'week_days'         => $value1['week_days'],
											'month'         	=> $value1['month']
										);
							$this->db->insert('Scene_Schedule', $input_data);

							$hr  = substr($value1['execution_time'],0,2);
							$min = substr($value1['execution_time'],-2,2);

							//curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://localhost:8281/api/scene/execute
							$url = 'curl -H "Content-Type: application/json" -X POST -d \'{"scene_id":"'.$scene_id.'"}\' http://localhost:8281/api/scene/execute';
							if($value1['repeat'] == "1")
								$cron_cmd = $min.' '.$hr.' * * '.$wk_cron.' '.$url."\n";
							else
								$cron_cmd = $min.' '.$hr.' '.$day_crn.' '.$month_crn.' * '.$url."\n";
							echo exec('sudo crontab -u gems -l > cronJobs.txt')."<br><br>";
							//file_put_contents("cronJobs.txt", $emp_file, FILE_APPEND);

							//echo "cron:".$emp_file."<br><br>";
							//echo "cron cmd:".$cron_cmd."<br><br>";
							//get current cron jobs
							//echo system('crontab -u gems -l > cronJobs.txt')."<br>";

							//echo file_get_contents("cronJobs.txt");
							//add cron cmd

							//$cron_cmd = ($emp_file == '') ? $cron_cmd : $emp_file."\n".$cron_cmd;
							//echo "cron cmd:".$cron_cmd."<br><br>";
							file_put_contents("cronJobs.txt", $cron_cmd, FILE_APPEND);
							//echo "file_content:".file_get_contents("cronJobs.txt");
							exec("sudo crontab -u gems cronJobs.txt");
							exec("sudo service cron reload");
							$scene_flag = 1;
						}
					}
				}
				break;

				case '3':
				{
					//between time
					foreach($json_data['input_parameters'] as $value1)
					{
						if(isset($json_data['trigger_type']) && isset($value1['start_time']) && isset($value1['end_time']) && isset($value1['repeat']) && isset($value1['week_days']) && isset($value1['month']))
						{
							$wk_cron ='';$weekdays='';$day_crn='';$month_crn='';
							$wk_ds_arr = explode(',',$value1['week_days']);
							if($value1['repeat'] == "1")
							{
								foreach($wk_ds_arr as $index => $value12)
								{
									if($weekdays != '')
									{
										$weekdays = $weekdays.','.$value12;
										if($value12 == '1')
										{
											if($wk_cron != '')
												$wk_cron = $wk_cron.','.$index;
											else
												$wk_cron = $index;
										}
									}
									else
									{
										$weekdays = $value12;
										if($value12 == '1')
											$wk_cron = $index;
									}
								}
							}
							else
							{
								$day_crn=date('d');
								$month_crn=date('m');
							}
							//echo $weekdays.'<br>'.$month;

							$input_data = array(
											'scene_id'			=> $scene_id,
											'trigger_type'		=> $json_data['trigger_type'],
											'start_time'    	=> $value1['start_time'],
											'end_time'	    	=> $value1['end_time'],
											'repeat'	        => $value1['repeat'],
											'week_days'         => $value1['week_days'],
											'month'         	=> $value1['month']
										);
							 $this->db->insert('Scene_Schedule', $input_data);
							 $scene_flag = 1;
						}
					}
				}
				break;

				case '4':
				{
					//sunrise time
					foreach($json_data['input_parameters'] as $value1)
					{
						if(isset($json_data['trigger_type']) && isset($value1['sunrise_time']) && isset($value1['sunrise_delay']) && isset($value1['repeat']) && isset($value1['week_days']) && isset($value1['month']))
						{
							$wk_cron ='';$weekdays='';$day_crn='';$month_crn='';
							$wk_ds_arr = explode(',',$value1['week_days']);
							if($value1['repeat'] == "1")
							{
								foreach($wk_ds_arr as $index => $value12)
								{
									if($weekdays != '')
									{
										$weekdays = $weekdays.','.$value12;
										if($value12 == '1')
										{
											if($wk_cron != '')
												$wk_cron = $wk_cron.','.$index;
											else
												$wk_cron = $index;
										}
									}
									else
									{
										$weekdays = $value12;
										if($value12 == '1')
											$wk_cron = $index;
									}
								}
							}
							else
							{
								$day_crn=date('d');
								$month_crn=date('m');
							}
							//echo $weekdays.'<br>'.$month;

							$input_data = array(
											'scene_id'			=> $scene_id,
											'trigger_type'		=> $json_data['trigger_type'],
											'sunrise_time'    	=> $value1['sunrise_time'],
											'sunrise_delay'    	=> $value1['sunrise_delay'],
											'repeat'	        => $value1['repeat'],
											'week_days'         => $value1['week_days'],
											'month'         	=> $value1['month']
										);
							$this->db->insert('Scene_Schedule', $input_data);
							$this->db->select('sun_rise')->from('Stargate_Manager');
							$query = $this->db->get();$sun_rise='';

							foreach ($query->result() as $row)
							{
								$sun_rise = $row->sun_rise;
							}

							$hr  = substr($value1['sunrise_delay'],0,2);
							$min = substr($value1['sunrise_delay'],-2,2);

							$sun_rise = date('H:i',strtotime('+'.$hr.' hour +'.$min.' minutes',strtotime($sun_rise)));

							$hr  = substr($sun_rise,0,2);
							$min = substr($sun_rise,-2,2);

							//curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://localhost:8281/api/scene/execute
							$url = 'curl -H "Content-Type: application/json" -X POST -d \'{"scene_id":"'.$scene_id.'"}\' http://localhost:8281/api/scene/execute';
							if($value1['repeat'] == "1")
								$cron_cmd = $min.' '.$hr.' * * '.$wk_cron.' '.$url."\n";
							else
								$cron_cmd = $min.' '.$hr.' '.$day_crn.' '.$month_crn.' * '.$url."\n";
							echo exec('sudo crontab -u gems -l > cronJobs.txt')."<br><br>";
							file_put_contents("cronJobs.txt", $cron_cmd, FILE_APPEND);
							exec("sudo crontab -u gems cronJobs.txt");
							exec("sudo service cron reload");

							$scene_flag = 1;
						}
					}
				}
				break;

				case '5':
				{
					//on sunset time
					foreach($json_data['input_parameters'] as $value1)
					{
						if(isset($json_data['trigger_type']) && isset($value1['sunset_time']) && isset($value1['sunset_delay']) && isset($value1['repeat']) && isset($value1['week_days']) && isset($value1['month']))
						{
							$wk_cron ='';$weekdays='';$day_crn='';$month_crn='';
							$wk_ds_arr = explode(',',$value1['week_days']);
							if($value1['repeat'] == "1")
							{
								foreach($wk_ds_arr as $index => $value12)
								{
									if($weekdays != '')
									{
										$weekdays = $weekdays.','.$value12;
										if($value12 == '1')
										{
											if($wk_cron != '')
												$wk_cron = $wk_cron.','.$index;
											else
												$wk_cron = $index;
										}
									}
									else
									{
										$weekdays = $value12;
										if($value12 == '1')
											$wk_cron = $index;
									}
								}
							}
							else
							{
								$day_crn=date('d');
								$month_crn=date('m');
							}
							//echo $weekdays.'<br>'.$month;

							$input_data = array(
											'scene_id'			=> $scene_id,
											'trigger_type'		=> $json_data['trigger_type'],
											'sunset_time'    	=> $value1['sunset_time'],
											'sunset_delay'    	=> $value1['sunset_delay'],
											'repeat'	        => $value1['repeat'],
											'week_days'         => $value1['week_days'],
											'month'         	=> $value1['month']
										);
							$this->db->insert('Scene_Schedule', $input_data);
							//echo $this->db->last_query();

							$this->db->select('sun_set')->from('Stargate_Manager');
							$query = $this->db->get();$sun_set='';

							foreach ($query->result() as $row)
							{
								$sun_set = $row->sun_set;
							}

							$hr  = substr($value1['sunset_delay'],0,2);
							$min = substr($value1['sunset_delay'],-2,2);

							$sun_set = date('H:i',strtotime('+'.$hr.' hour +'.$min.' minutes',strtotime($sun_set)));

							$hr  = substr($sun_set,0,2);
							$min = substr($sun_set,-2,2);

							//curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://localhost:8281/api/scene/execute
							$url = 'curl -H "Content-Type: application/json" -X POST -d \'{"scene_id":"'.$scene_id.'"}\' http://localhost:8281/api/scene/execute';
							if($value1['repeat'] == "1")
								$cron_cmd = $min.' '.$hr.' * * '.$wk_cron.' '.$url."\n";
							else
								$cron_cmd = $min.' '.$hr.' '.$day_crn.' '.$month_crn.' * '.$url."\n";
							echo exec('sudo crontab -u gems -l > cronJobs.txt')."<br><br>";
							file_put_contents("cronJobs.txt", $cron_cmd, FILE_APPEND);
							exec("sudo crontab -u gems cronJobs.txt");
							exec("sudo service cron reload");
							$scene_flag = 1;
						}
					}
				}
				break;
			}

			foreach($json_data['output_parameters'] as $value2)
			{
				if(isset($value2['cuid']) && isset($value2['device_id']) && isset($value2['end_point']) && isset($value2['cmd_cls']) && isset($value2['cmd']) && isset($value2['cmd_val']) && isset($value2['execution_order']) && isset($value2['execution_delay']))
				{
					$output_data = array(
									'scene_id'			=> $scene_id,
									'cuid'				=> $value2['cuid'],
									'device_id'         => $value2['device_id'],
									'end_point'         => $value2['end_point'],
									'cmd_cls'         	=> $value2['cmd_cls'],
									'cmd'      			=> $value2['cmd'],
									'cmd_val'    		=> $value2['cmd_val'],
									'execution_order'   => $value2['execution_order'],
									'execution_delay'   => $value2['execution_delay']
								);
					$this->db->insert('Scene_Activity', $output_data);
				}
			}

			foreach($json_data['notification_parameters'] as $value3)
			{
				if(isset($value3['user_id']) && isset($value3['sms']) && isset($value3['email']) && isset($value3['push']) && isset($value3['message']))
				{
					$notify_data = array(
									'scene_id'			=> $scene_id,
									'user_id'         	=> $value3['user_id'],
									'sms'		        => $value3['sms'],
									'email'         	=> $value3['email'],
									'push'     			=> $value3['push'],
									'message'     		=> $value3['message']
								);
					$this->db->insert('Scene_Notification', $notify_data);
				}
			}

			if($scene_flag == 1)
			{
				$webResp =	array(
								'scene_id'      => 	$scene_id,
								'scene_name'    =>  $json_data['scene_name'],
								'response_text' => 'scene created'
								);
				$socResp =	array(
								'model'			=>	'scenes',
								'method'		=>	'created',
								'scene_id'      => 	$scene_id,
								'scene_name'	=>	$json_data['scene_name'],
								'trigger_type'  =>  $json_data['trigger_type']
								);
			}
		}
		else
        {
			/*$socResp =	array(
							'model'			=>	'scenes',
							'method'		=>	'create failed',
							);*/
            $webResp =	array("response_text" => "scene creation failed");
        }
		/*{
			"model": "scenes",
  			"method": "created",
			"scene_id": "45b147f5-1b8f-11e8-b03e-b827eb40cc51",
			"scene_name": "All manual Scene",
			"id": 10
		}*/

		$socResp = json_encode($socResp, JSON_PRETTY_PRINT)."\r\n";
		//$webResp = json_encode($webResp, JSON_PRETTY_PRINT);
		$this->sent2socket(8286, $socResp);
		$this->response($webResp, REST_Controller::HTTP_OK);
	}

	//function to get scene list
	public function list_post()
	{
		//scene device control
		$json_data 	= $this->request->body;
		$scene_flag = 0;$resp = array();
		$this->db->select('scene_name,scene_id,trigger_type,execution_mode,execution_scene_id');
		$this->db->from('Scene');
		if(isset($json_data['scene_id']))
			$this->db->where('scene_id' , $json_data['scene_id']);
		$query1 = $this->db->get();
		//echo $this->db->last_query();

		$scene_flag=0;
		foreach ($query1->result() as $row1)
		{
			switch($row1->trigger_type)
			{
				case '1':
				{
					$this->db->select('scene_id,cuid,device_id,end_point,cmd_cls,cmd,cmd_val');
					$this->db->from('Scene_Trigger');
					$this->db->where('scene_id' , $row1->scene_id);
					$query2 = $this->db->get();

					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();

					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					//$resp = array_merge($resp,$temp_resp);
					$scene_flag=1;
				}
				break;

				case '2':
				{
					$this->db->select('execution_time,repeat,week_days,month');
					$this->db->from('Scene_Schedule');
					$this->db->where('scene_id' , $row1->scene_id);
					$query2 = $this->db->get();

					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();

					$input = array();

					/*foreach($query2->result() as $row2)
					{
						$input[] = array(
										'execution_time' 	=> $row2->execution_time,
										'repeat'			=>	$row2->repeat,
										'week_days' 		=> array($row2->week_days),
										'month'				=> array($row2->month)
										);

					}*/
					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					$scene_flag=1;
				}
				break;

				case '3':
				{
					$this->db->select('start_time,end_time,repeat,week_days,month');
					$this->db->from('Scene_Schedule');
					$this->db->where('scene_id' , $row1->scene_id);
					$query2 = $this->db->get();

					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();

					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					$scene_flag=1;
				}
				break;

				case '4':
				{
					$this->db->select('sunrise_time,sunrise_delay,repeat,week_days,month');
					$this->db->from('Scene_Schedule');
					$this->db->where('scene_id' , $row1->scene_id);
					$query2 = $this->db->get();

					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();

					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					$scene_flag=1;
				}
				break;

				case '5':
				{
					$this->db->select('sunset_time,sunset_delay,repeat,week_days,month');
					$this->db->from('Scene_Schedule');
					$this->db->where('scene_id' , $row1->scene_id);
					$query2 = $this->db->get();

					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();
					//echo $this->db->last_query();
					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'input_parameters'			=>	$query2->result(),
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					$scene_flag=1;
				}
				break;

				case '6':
				{
					$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_order,execution_delay');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id', $row1->scene_id);
					$this->db->order_by('execution_order', 'asc');
					$query3 = $this->db->get();

					$this->db->select('user_id,sms,email,push,message');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $row1->scene_id);
					$query4 = $this->db->get();

					if (empty($resp))
					{
						$resp[]  = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'execution_scene_id'		=>	$row1->execution_scene_id,
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					else
					{
						$resp[] = array(
											'scene_name'				=>	$row1->scene_name,
											'scene_id'					=>	$row1->scene_id,
											'trigger_type'				=>	$row1->trigger_type,
											'execution_mode'			=>	$row1->execution_mode,
											'output_parameters'			=>	$query3->result(),
											'notification_parameters'	=>	$query4->result());
					}
					$scene_flag=1;
				}
				break;
			}

		}


		//if($scene_flag == 0)
		//	$resp =	array("response_text"=>"scene or trigger get failed");
		$resp = array("result" => $resp);
		//$resp = json_encode($resp, JSON_PRETTY_PRINT);
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function delete_post()
	{
		//scene device control
		$json_data = $this->request->body;
		$scene_flag = 0;$webRespAr = array();$socRespAr = array();
		$this->db->select('id,trigger_type,scene_name');
		$this->db->from('Scene');
		$this->db->where('scene_id' , $json_data['scene_id']);
		$query1 = $this->db->get();$scene_name='';$trigger_type='';

		$scene_flag=0;
		foreach ($query1->result() as $row1)
		{
			$scene_name = $row1->scene_name;
			$trigger_type = $row1->trigger_type;
			$this->db->delete('Scene', array('id' => $row1->id));
			switch($row1->trigger_type)
			{
				case 1:
				{
					$this->db->select('id');
					$this->db->from('Scene_Trigger');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query2 = $this->db->get();
					foreach ($query2->result() as $row2)
					{
						$this->db->delete('Scene_Trigger', array('id' => $row2->id));
					}

					$this->db->select('id');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query3 = $this->db->get();
					foreach ($query3->result() as $row3)
					{
						$this->db->delete('Scene_Activity', array('id' => $row3->id));
					}

					$this->db->select('id');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query4 = $this->db->get();
					foreach ($query4->result() as $row4)
					{
						$this->db->delete('Scene_Notification', array('id' => $row4->id));
					}

					$scene_flag=1;
				}
				break;

				case 2://on time
				case 4://sun rise time
				case 5://sun set time
					{
						exec('sudo crontab -u gems -l > cronJobs.txt');
						$lines = file('cronJobs.txt');
						//echo $json_data['scene_id']."<br><br>";
						// Loop through our array, show HTML source as HTML source; and line numbers too.
						foreach ($lines as $line_num => $line)
						{
							if(strpos($line, $json_data['scene_id']))
								unset($lines[$line_num]);
							//else
								//echo $lines[$line_num]."<br><br>";
						}
						$line_str = implode( $lines);
						file_put_contents("cronJobs.txt", $line_str);
						exec("sudo crontab -u gems cronJobs.txt");
						exec("sudo service cron reload");
					}
				case 3:
				{
					$this->db->select('id');
					$this->db->from('Scene_Schedule');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query2 = $this->db->get();
					foreach ($query2->result() as $row2)
					{
						$this->db->delete('Scene_Trigger', array('id' => $row2->id));
					}

					$this->db->select('id');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query3 = $this->db->get();
					foreach ($query3->result() as $row3)
					{
						$this->db->delete('Scene_Activity', array('id' => $row3->id));
					}

					$this->db->select('id');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query4 = $this->db->get();
					foreach ($query4->result() as $row4)
					{
						$this->db->delete('Scene_Notification', array('id' => $row4->id));
					}

					$scene_flag=1;
				}
				break;

				case 6:
				{
					$this->db->select('id');
					$this->db->from('Scene_Activity');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query2 = $this->db->get();
					foreach ($query2->result() as $row2)
					{
						$this->db->delete('Scene_Activity', array('id' => $row2->id));
					}

					$this->db->select('id');
					$this->db->from('Scene_Notification');
					$this->db->where('scene_id' , $json_data['scene_id']);
					$query3 = $this->db->get();
					foreach ($query3->result() as $row3)
					{
						$this->db->delete('Scene_Notification', array('id' => $row3->id));
					}
					$scene_flag=1;
				}
				break;
			}
		}
		if($scene_flag == 1)
		{
			$webRespAr =	array(
							'scene_id'		=>	$json_data['scene_id'],
							'scene_name'	=>	$scene_name,
							'response_text'	=>	'scene deleted');

			$socRespAr =	array(
							'model'			=>	'scenes',
							'method'		=>	'deleted',
							'scene_id'		=>	$json_data['scene_id'],
							'scene_name'	=>	$scene_name,
							'trigger_type'	=>	$trigger_type
							);
		}
		else
		{
			$webResp =	array(
							'scene_id'		=>	$json_data['scene_id'],
							'scene_name'	=>	$scene_name,
							'response_text'=>'scene delete failed');

			/*$socResp =	array(
							'model'			=>	'scenes',
							'method'		=>	'delete failed',
							'scene_id'		=>	$json_data['scene_id'],
							'scene_name'	=>	$scene_name
							);*/
		}
		$socResp = json_encode($socRespAr, JSON_PRETTY_PRINT)."\r\n";
		//$webResp = json_encode($webResp, JSON_PRETTY_PRINT);

		$this->sent2socket(8286, $socResp);
		$this->response($webRespAr, REST_Controller::HTTP_OK);
	}

	public function execute_post()
	{
		$json_data = $this->request->body;
		$resp =  array();
		$scene_flag = 0;$scene_id='';$scene_name='';
		$webRespAr=array();
		$socRespAr=array();

		if(isset($json_data['scene_id']))
		{
			$scene_id = $json_data['scene_id'];
			$this->db->select('scene_name');
			$this->db->from('Scene');
			$this->db->where('scene_id' , $scene_id);
			$query = $this->db->get();
			//echo $this->db->last_query();
			foreach ($query->result() as $row)
			{
				$scene_name = $row->scene_name;
			}

			//output parameters
			$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_delay');
			$this->db->from('Scene_Activity');
			$this->db->where('scene_id', $json_data['scene_id']);
			$this->db->order_by('execution_order', 'asc');
			$query1 = $this->db->get();
			//echo $this->db->last_query();

			foreach ($query1->result() as $row1)
			{
				$this->db->select('class_version');
				$this->db->from('ZW_Device_Control_Commands');
				$this->db->where('node_id', $row1->device_id);
				$query2 = $this->db->get();$post_data='';
				$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
				foreach($query2->result() as $row2)
				{
					$post_data = 	array(
										'nodeid'	=> intval($row1->device_id),
										'instance'	=> intval($row1->end_point),
										'cmdclass'	=> $row1->cmd_cls,
										'version'	=> $row2->class_version,
										'cmd'		=> $row1->cmd,
										'value'		=> intval($row1->cmd_val));
					$myJSON = json_encode($post_data, JSON_PRETTY_PRINT);
					//echo $myJSON;
					$options = array(
						'http' 		=> array(
						'method'  	=> 'POST',
						'content' 	=> $myJSON ,
						'header'	=>  "Content-Type: application/json\r\n" .
										"Accept: application/json\r\n"
						)
					);
					$context  = stream_context_create( $options );
					$result = file_get_contents( $url, false, $context );
					//echo $url,'<br>',$myJSON,'<br>';
					//echo $result.'<br><br>';
				}
			}

			//notification parameters
			//{"model": "Scene_notification","user_id": "hfakchiycbz","scene_name": "sn set to On Devices","message": "text message"}
			$this->db->select('user_id,message');
			$this->db->from('Scene_Notification');
			$this->db->where(array('scene_id' => $scene_id, 'push' => '1'));
			$query3 = $this->db->get();
			//echo $this->db->last_query();
			foreach ($query3->result() as $row3)
			{
				$notify_data = 	array(
										'model'			=> 'Scene_notification',
										'user_id'		=> $row3->user_id,
										'scene_name'	=> $scene_name,
										'message'		=> $row3->message);

				$notify = json_encode($notify_data, JSON_PRETTY_PRINT);

				//echo $notify.'<br>';

				$this->sent2socket(8286, $notify);
			}
			$scene_flag = 1;
		}
		else if(isset($json_data['device_id']) && isset($json_data['end_point']) && isset($json_data['cmd_cls']) && isset($json_data['cmd']) && isset($json_data['cmd_val']))
		{
			//output parameters
			$this->db->select('scene_id');
			$this->db->from('Scene_Trigger');
			$this->db->where(array(
									'cuid' 		=> $json_data['cuid'],
									'device_id'	=> $json_data['device_id'],
									'end_point'	=> $json_data['end_point'],
									'cmd_cls'	=> $json_data['cmd_cls'],
									'cmd' 		=> $json_data['cmd'],
									'cmd_val' 	=> $json_data['cmd_val']));
			$query1 = $this->db->get();
			//echo $this->db->last_query();

			foreach ($query1->result() as $row1)
			{
				$scene_id = $row1->scene_id;
				$this->db->select('scene_name');
				$this->db->from('Scene');
				$this->db->where('scene_id' , $scene_id);
				$query2 = $this->db->get();
				//echo $this->db->last_query();
				foreach ($query2->result() as $row2)
				{
					$scene_name = $row2->scene_name;
				}

				//output parameters
				$this->db->select('cuid,device_id,end_point,cmd_cls,cmd,cmd_val,execution_delay');
				$this->db->from('Scene_Activity');
				$this->db->where('scene_id', $scene_id);
				$this->db->order_by('execution_order', 'asc');
				$query3 = $this->db->get();
				//echo $this->db->last_query();

				foreach ($query3->result() as $row3)
				{
					$this->db->select('class_version');
					$this->db->from('ZW_Device_Control_Commands');
					$this->db->where('node_id', $row3->device_id);
					$query4 = $this->db->get();$post_data='';
					$url = 'http://127.0.1.1:8281/stargate/index.php/query/sendZwaveData';
					foreach($query4->result() as $row4)
					{
						$post_data = 	array(
											'nodeid'	=> intval($row3->device_id),
											'instance'	=> intval($row3->end_point),
											'cmdclass'	=> $row3->cmd_cls,
											'version'	=> $row4->class_version,
											'cmd'		=> $row3->cmd,
											'value'		=> intval($row3->cmd_val));
						$myJSON = json_encode($post_data, JSON_PRETTY_PRINT);
						//echo $myJSON;
						$options = array(
							'http' 		=> array(
							'method'  	=> 'POST',
							'content' 	=> $myJSON ,
							'header'	=>  "Content-Type: application/json\r\n" .
											"Accept: application/json\r\n"
							)
						);
						$context  = stream_context_create( $options );
						$result = file_get_contents( $url, false, $context );
						//echo $url,'<br>',$myJSON,'<br>';
						//echo $result.'<br><br>';
					}
				}

				//notification parameters
				//{"model": "Scene_notification","user_id": "hfakchiycbz","scene_name": "sn set to On Devices","message": "text message"}
				$this->db->select('user_id,message');
				$this->db->from('Scene_Notification');
				$this->db->where(array('scene_id' => $scene_id, 'push' => '1'));
				$query5 = $this->db->get();
				//echo $this->db->last_query();
				foreach ($query5->result() as $row5)
				{
					$notify_data = 	array(
											'model'			=> 'Scene_notification',
											'user_id'		=> $row5->user_id,
											'scene_name'	=> $scene_name,
											'message'		=> $row5->message);

					$notify = json_encode($notify_data, JSON_PRETTY_PRINT);

					//echo $notify.'<br>';

					$this->sent2socket(8286, $notify);
				}
				$scene_flag = 1;
			}
		}
		if($scene_flag == 1)
		{
			$webRespAr =	array(
							'scene_id'		=>	$scene_id,
							'response_text'	=>	'scene executed');

			$socRespAr = array(
								'model'			=>	'scenes',
								'method'		=>	'executed',
								'scene_id'      => 	$scene_id,
								'scene_name'	=>	$scene_name,
								);
		}
		else
		{
			$webRespAr =	array(
							'scene_id'		=>	$scene_id,
							'response_text'	=>	'scene execution failed');

			/*$socRespAr = array(
								'model'			=>	'scenes',
								'method'		=>	'execute failed',
								'scene_id'      => 	$scene_id,
								'scene_name'	=>	$scene_name,
								);*/
		}
		$socResp = json_encode($socRespAr, JSON_PRETTY_PRINT)."\r\n";
		//$resp = json_encode($resp, JSON_PRETTY_PRINT);

		$this->sent2socket(8286, $socResp);
		$this->response($webRespAr, REST_Controller::HTTP_OK);
	}

	/*public function update_post()
	{
		$json_data 	=	$this->request->body;
		$scene_id = '';$resp='';
		$scene_flag = 0;

		if(isset($json_data['scene_name']) && isset($json_data['trigger_type']) && isset($json_data['execution_mode']) && isset($json_data['execution_scene_id']))
		{
			$scene_data = array(
							'scene_name'			=> $json_data['scene_name'],
							'trigger_type'			=> $json_data['trigger_type'],
							'execution_mode'	 	=> $json_data['execution_mode'],
							'execution_scene_id' 	=> $json_data['execution_scene_id']
						);
			$this->db->set($scene_data);
			$this->db->where('scene_id', $json_data['scene_id']);
			$this->db->update('Scene', $scene_data);

			switch($json_data['trigger_type'])
			{
				case '1':
				{
					foreach($json_data['input_parameters'] as $value1)
					{
						$this->db->select('id');
						$this->db->from('Scene_Trigger');
						$this->db->where(array(
												'scene_id' 	=> $json_data['scene_id'],
												'cuid' 		=> $value1['cuid'],
												'device_id'	=> $value1['device_id'],
												'end_point'	=> $value1['end_point'],
												'cmd_cls' 	=> $value1['cmd_cls'],
												'cmd'		=> $value1['cmd'],
												'cmd_val' 	=> $value1['cmd_val']));
						$query1 = $this->db->get();
						foreach($query1->result() as $row1)
						{
							$input_data = array(
												'scene_id'			=> $json_data['scene_id'],
												'execution_mode'	=> $json_data['execution_mode'],
												'cuid'				=> $value1['cuid'],
												'device_id'         => $value1['device_id'],
			                                    'end_point'         => $value1['end_point'],
			                                    'cmd_cls'         	=> $value1['cmd_cls'],
			                                    'cmd'      			=> $value1['cmd'],
			                                    'cmd_val'    		=> $value1['cmd_val']
			                                );
							$this->db->set($input_data);
							$this->db->where('id', $row1->id);
							$this->db->update('Scene_Trigger', $input_data);
						}
					}

					foreach($json_data['output_parameters'] as $value2)
					{
						$this->db->select('id');
						$this->db->from('Scene_Activity');
						$this->db->where(array(
												'scene_id' 	=> $json_data['scene_id'],
												'cuid' 		=> $value2['cuid'],
												'device_id'	=> $value2['device_id'],
												'end_point'	=> $value2['end_point'],
												'cmd_cls' 	=> $value2['cmd_cls'],
												'cmd'		=> $value2['cmd'],
												'cmd_val' 	=> $value2['cmd_val']));
						$query2 = $this->db->get();
						foreach($query2->result() as $row2)
						{
							$output_data = array(
											'scene_id'			=> $json_data['scene_id'],
											'cuid'				=> $value2['cuid'],
											'device_id'         => $value2['device_id'],
		                                    'end_point'         => $value2['end_point'],
		                                    'cmd_cls'         	=> $value2['cmd_cls'],
		                                    'cmd'      			=> $value2['cmd'],
		                                    'cmd_val'    		=> $value2['cmd_val'],
											'execution_order'   => $value2['execution_order'],
											'execution_delay'   => $value2['execution_delay']
		                                );
							$this->db->set($output_data);
							$this->db->where('id', $row2->id);
							$this->db->update('Scene_Activity', $output_data);
						}
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$this->db->select('id');
						$this->db->from('Scene_Notification');
						$this->db->where(array(
											'scene_id'			=> $json_data['scene_id'],
											'user_id'         	=> $value3['user_id'],
		                                    'sms'		        => $value3['sms'],
		                                    'email'         	=> $value3['email'],
		                                    'push'     			=> $value3['push']
	                                	));
						$query4 = $this->db->get();
						foreach($query4->result() as $row4)
						{
							$this->db->insert('Scene_Notification', $notify_data);
						}
					}
					$scene_flag = 1;
				}
				break;

				/*case '2':
				{
					foreach($json_data['input_parameters'] as $value1)
					{
						$weekdays='';$month='';
						foreach($value1['week_days'] as $value12)
						{
							if($weekdays != '')
								$weekdays = $weekdays.','.$value12;
							else
								$weekdays = $value12;
						}
						foreach($value1['month'] as $value13)
						{
							if($month != '')
								$month = $month.','.$value13;
							else
								$month = $value13;
						}
						//echo $weekdays.'<br>'.$month;

						$input_data = array(
										'scene_id'			=> $scene_id,
										'trigger_type'		=> $json_data['trigger_type'],
										'execution_time'    => $value1['execution_time'],
	                                    'repeat'	        => $value1['repeat'],
										'week_days'         => $weekdays,
										'month'         	=> $month
	                                );
					}
					$this->db->insert('Scene_Schedule', $input_data);

					foreach($json_data['output_parameters'] as $value2)
					{
						$output_data = array(
										'scene_id'			=> $scene_id,
										'cuid'				=> $value2['cuid'],
										'device_id'         => $value2['device_id'],
	                                    'end_point'         => $value2['end_point'],
	                                    'cmd_cls'         	=> $value2['cmd_cls'],
	                                    'cmd'      			=> $value2['cmd'],
	                                    'cmd_val'    		=> $value2['cmd_val'],
										'execution_order'   => $value2['execution_order'],
										'execution_delay'   => $value2['execution_delay']
	                                );
						$this->db->insert('Scene_Activity', $output_data);
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$notify_data = array(
										'scene_id'			=> $scene_id,
										'user_id'         	=> $value3['user_id'],
	                                    'sms'		        => $value3['sms'],
	                                    'email'         	=> $value3['email'],
	                                    'push'     			=> $value3['push']
	                                );
						$this->db->insert('Scene_Notification', $notify_data);
					}
					$scene_flag = 1;
				}
				break;

				case '3':
				{
					foreach($json_data['input_parameters'] as $value1)
					{
						$weekdays='';$month='';
						foreach($value1['week_days'] as $value12)
						{
							if($weekdays != '')
								$weekdays = $weekdays.','.$value12;
							else
								$weekdays = $value12;
						}
						foreach($value1['month'] as $value13)
						{
							if($month != '')
								$month = $month.','.$value13;
							else
								$month = $value13;
						}
						//echo $weekdays.'<br>'.$month;

						$input_data = array(
										'scene_id'			=> $scene_id,
										'trigger_type'		=> $json_data['trigger_type'],
										'start_time'    	=> $value1['start_time'],
										'end_time'	    	=> $value1['end_time'],
	                                    'repeat'	        => $value1['repeat'],
										'week_days'         => $weekdays,
										'month'         	=> $month
	                                );
					}
					$this->db->insert('Scene_Schedule', $input_data);

					foreach($json_data['output_parameters'] as $value2)
					{
						$output_data = array(
										'scene_id'			=> $scene_id,
										'cuid'				=> $value2['cuid'],
										'device_id'         => $value2['device_id'],
	                                    'end_point'         => $value2['end_point'],
	                                    'cmd_cls'         	=> $value2['cmd_cls'],
	                                    'cmd'      			=> $value2['cmd'],
	                                    'cmd_val'    		=> $value2['cmd_val'],
										'execution_order'   => $value2['execution_order'],
										'execution_delay'   => $value2['execution_delay']
	                                );
						$this->db->insert('Scene_Activity', $output_data);
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$notify_data = array(
										'scene_id'			=> $scene_id,
										'user_id'         	=> $value3['user_id'],
	                                    'sms'		        => $value3['sms'],
	                                    'email'         	=> $value3['email'],
	                                    'push'     			=> $value3['push']
	                                );
						$this->db->insert('Scene_Notification', $notify_data);
					}
					$scene_flag = 1;
				}
				break;

				case '4':
				{
					foreach($json_data['input_parameters'] as $value1)
					{
						$weekdays='';$month='';
						foreach($value1['week_days'] as $value12)
						{
							if($weekdays != '')
								$weekdays = $weekdays.','.$value12;
							else
								$weekdays = $value12;
						}
						foreach($value1['month'] as $value13)
						{
							if($month != '')
								$month = $month.','.$value13;
							else
								$month = $value13;
						}
						//echo $weekdays.'<br>'.$month;

						$input_data = array(
										'scene_id'			=> $scene_id,
										'trigger_type'		=> $json_data['trigger_type'],
													{
										'sunrise_time'    	=> $value1['sunrise_time'],
										'sunrise_delay'    	=> $value1['sunrise_delay'],
	                                    'repeat'	        => $value1['repeat'],
										'week_days'         => $weekdays,
										'month'         	=> $month
	                                );
					}
					$this->db->insert('Scene_Schedule', $input_data);

					foreach($json_data['output_parameters'] as $value2)
					{
						$output_data = array(
													{
										'scene_id'			=> $scene_id,
										'cuid'				=> $value2['cuid'],
										'device_id'         => $value2['device_id'],
	                                    'end_point'         => $value2['end_point'],
	                                    'cmd_cls'         	=> $value2['cmd_cls'],
	                                    'cmd'      			=> $value2['cmd'],
	                                    'cmd_val'    		=> $value2['cmd_val'],
										'execution_order'   => $value2['execution_order'],
										'execution_delay'   => $value2['execution_delay']
	                                );
						$this->db->insert('Scene_Activity', $output_data);
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$notify_data = array(
										'scene_id'			=> $scene_id,
										'user_id'         	=> $value3['user_id'],
	                                    'sms'		        => $value3['sms'],
	                                    'email'         	=> $value3['email'],
	                                    'push'     			=> $value3['push']
	                                );
						$this->db->insert('Scene_Notification', $notify_data);
					}
					$scene_flag = 1;
				}
				break;

				case '5':
				{
					foreach($json_data['input_parameters'] as $value1)
					{
						$weekdays='';$month='';
						foreach($value1['week_days'] as $value12)
						{
							if($weekdays != '')
								$weekdays = $weekdays.','.$value12;
							else
								$weekdays = $value12;
						}
						foreach($value1['month'] as $value13)
						{
							if($month != '')
								$month = $month.','.$value13;
							else
								$month = $value13;
						}
						//echo $weekdays.'<br>'.$month;

						$input_data = array(
										'scene_id'			=> $scene_id,
										'trigger_type'		=> $json_data['trigger_type'],
										'sunset_time'    	=> $value1['sunset_time'],
										'sunset_delay'    	=> $value1['sunset_delay'],
	                                    'repeat'	        => $value1['repeat'],
										'week_days'         => $weekdays,
										'month'         	=> $month
	                                );
					}
					$this->db->insert('Scene_Schedule', $input_data);

					foreach($json_data['output_parameters'] as $value2)
					{
						$output_data = array(
										'scene_id'			=> $json_data['scene_id'],
										'cuid'				=> $value2['cuid'],
										'device_id'         => $value2['device_id'],
	                                    'end_point'         => $value2['end_point'],
	                                    'cmd_cls'         	=> $value2['cmd_cls'],
	                                    'cmd'      			=> $value2['cmd'],
	                                    'cmd_val'    		=> $value2['cmd_val'],
										'execution_order'   => $value2['execution_order'],
										'execution_delay'   => $value2['execution_delay']
	                                );
						$this->db->insert('Scene_Activity', $output_data);
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$notify_data = array(
										'scene_id'			=> $json_data['scene_id'],
										'user_id'         	=> $value3['user_id'],
	                                    'sms'		        => $value3['sms'],
	                                    'email'         	=> $value3['email'],
	                                    'push'     			=> $value3['push']
	                                );
						$this->db->insert('Scene_Notification', $notify_data);
					}
					$scene_flag = 1;
				}
				break;

				case '6':
				{
					foreach($json_data['output_parameters'] as $value2)
					{
						$output_data = array(
										'scene_id'			=> $scene_id,
										'cuid'				=> $value2['cuid'],
										'device_id'         => $value2['device_id'],
	                                    'end_point'         => $value2['end_point'],
	                                    'cmd_cls'         	=> $value2['cmd_cls'],
	                                    'cmd'      			=> $value2['cmd'],
	                                    'cmd_val'    		=> $value2['cmd_val'],
										'execution_order'   => $value2['execution_order'],
										'execution_delay'   => $value2['execution_delay']
	                                );
						$this->db->insert('Scene_Activity', $output_data);
					}

					foreach($json_data['notification_parameters'] as $value3)
					{
						$notify_data = array(
										'scene_id'			=> $scene_id,
										'user_id'         	=> $value3['user_id'],
	                                    'sms'		        => $value3['sms'],
	                                    'email'         	=> $value3['email'],
	                                    'push'     			=> $value3['push']
	                                );
						$this->db->insert('Scene_Notification', $notify_data);
					}
					$scene_flag = 1;
				}
				break;*/
		/*	}
			if($scene_flag == 1)
			{
				$this->db->select('scene_id')->from('Scene')->order_by('id','DESC')->limit(1);
				$query = $this->db->get();
				foreach ($query->result() as $row)
				{
					$resp =	array(
									'scene_id'      => $row->scene_id,
									'scene_name'    =>  $json_data['scene_name'],
									'response_text' => 'scene created'
								);
				}
			}
		}
		else
        {
            $resp =	array("response_text" => "scene update failed");
        }
		$myJSON = json_encode($resp, JSON_PRETTY_PRINT)."\r\n";
		$this->sent2socket(8286, $myJSON."\r\n");
		$this->response($resp, REST_Controller::HTTP_OK);
	}*/
}
