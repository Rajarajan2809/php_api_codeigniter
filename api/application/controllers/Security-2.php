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
class Security extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
	}
	
	public function modes_get(){
			$cols	=	array("mode_id", "mode_name", "active_status");
			
			$modes	=	$this->db->select($cols)->from("Security_Modes")->get()->result();
			
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
	
	
	
	# /security/mode_activate
	public function activate_put($mode_id){
			
			#deactivate other mode 
			$deactivate_data	=	array("active_status"=>"0");
			$this->db->where("mode_id != 1")->update("Security_Modes",$deactivate_data);

			#activate current mode
			$condition	=	array("mode_id"=>$mode_id);
			$data		=	array("active_status"=>"1");
			
			$this->db->where($condition)->update("Security_Modes",$data);
			
			$nos		=	$this->db->affected_rows();
			
			$active_modes	=	$this->activeModes();
			
			$msg	=	"Not Activated";
			if($nos > 0){
				$msg	=	"Activated";
			}
			else{
				$records	=	$this->db->from("Security_Modes")->where($condition)->where($data)->get()->result();
				if(count($records) > 0){
					$msg	=	"Already Activated";
				}
			}
			$resp		=	array("response_text"=>$msg,"active_modes"=>$active_modes);
			
			$sock_data	=	array("model"=>"security", "method"=>"response", "menu_id"=>"4014", "active_modes"=>$active_modes, "id"=>1);
			$this->sent2socket(8286, json_encode($sock_data));
			
			$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	# /security/deactivate
	public function deactivate_put(){
			
			$data		=	array("active_status"=>"0");
			
			$this->db->where("mode_id != 1")->update("Security_Modes",$data);
			
			$nos		=	$this->db->affected_rows();
			$active_modes	=	$this->activeModes();
			$msg	=	"Not Deactivated";
			if($nos > 0){
				$msg	=	"Deactivated";
			}
			else{
				$records	=	$this->db->from("Security_Modes")->where($data)->get()->result();
				if(count($records) > 3){
					$msg	=	"Already Deactivated";
				}
			}
			$resp		=	array("response_text"=>$msg,"active_modes"=>$active_modes);


			$sock_data	=	array("model"=>"security", "method"=>"response", "menu_id"=>"4014", "active_modes"=>$active_modes, "id"=>1);
			$this->sent2socket(8286, json_encode($sock_data));
			
			
			$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	
	
	# /security/status
	public function status_get(){
			$mode_id = $this->put('security_mode');
			
			$resp	=	array("mode_id"=>$mode_id);
			$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	
	
	# /security/arm
	public function arm_put($mode_id,$node_id="all"){
			$condition	=	array("mode_id"=>$mode_id,"node_id"=>$node_id);

         # when node is not defined or all
         if($node_id == "all"){
             $condition	=	array("mode_id"=>$mode_id);
         }

			$data		=	array("enable_status"=>"1");
			
			$this->db->where($condition)->update("Sensor_Security_Modes",$data);
			
			$nos		=	$this->db->affected_rows();
			
			$msg	=	"Not Armed";
			if($nos > 0){
				$msg	=	"Armed";
			}
			else{
				$data		=	array("enable_status"=>"1");
				$records	=	$this->db->from("Sensor_Security_Modes")->where($condition)->where($data)->get()->result();
				if(count($records) > 0){
					$msg	=	"Already Armed";
				}
			}
			$resp		=	array("response_text"=>$msg);
			
           # when node is not defined or all
         if($node_id == "all"){
             $sock_data	=	$this->modeSecurityInfo($mode_id);
         }
         else{
			$sock_data	=	$this->nodeSecurityInfo($node_id,$mode_id);
         }
			$this->sent2socket(8286, json_encode($sock_data));
			
			
			$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	
	# /security/deactivate
	public function bypass_put($mode_id,$node_id="all"){
					$condition	=	array("mode_id"=>$mode_id,"node_id"=>$node_id);

         # when node is not defined or all
         if($node_id == "all"){
             $condition	=	array("mode_id"=>$mode_id);
         }

			$this->db->where($condition)->update("Sensor_Security_Modes",$data);
			
			$nos		=	$this->db->affected_rows();
			
			$msg	=	"Not Bypassed";
			if($nos > 0){
				$msg	=	"Bypassed";
			}
			else{
				$records	=	$this->db->from("Sensor_Security_Modes")->where($data)->where($condition)->get()->result();
				if(count($records) > 0)
					$msg	=	"Already Bypassed";
			}
			$resp		=	array("response_text"=>$msg);
			
			
			  # when node is not defined or all
         if($node_id == "all"){
             $sock_data	=	$this->modeSecurityInfo($mode_id);
         }
         else{
			$sock_data	=	$this->nodeSecurityInfo($node_id,$mode_id);
         }

			$this->sent2socket(8286, json_encode($sock_data));
			
			
			$this->response($resp, REST_Controller::HTTP_OK);
	
	}	
	
	function activeModes(){
		$data		=	array("active_status"=>"1");
		$records	=	$this->db->select("mode_id")->from("Security_Modes")->where($data)->get()->result();
		
		$modes 		=	array();
		foreach($records as $rec)
			$modes[]	=	$rec->mode_id;
			
		return $modes;
	}

function nodeSecurityInfo($node_id,$mode_id){
		$data		=	array("mode_id"=>$mode_id,"node_id"=>$node_id);
		
		#security Info
		$records	=	$this->db->select("node_security_status, cuid, menu_id, room_id, device_name")->from("security_mode_configuration")->where($data)->get()->result();
		
		
		
		$arm 		=	0;
		$cuid		=	0;
		$menu_id	=	0;
		$room_id	=	"NA";
		$device_name=	"NA";
		$report_type=	"info";
		
		foreach($records as $rec){
			$arm		=	$rec->node_security_status;
			$cuid		=	$rec->cuid;
			$menu_id	=	$rec->menu_id;
			$room_id	=	$rec->room_id;
			$device_name=	$rec->device_name;
		}	
		
		$arm_status	=	($arm == 1)? "arm" : "bypass";
			
			
			
		#armed modes	
		$condition	=	array("node_security_status"=>"1","node_id"=>$node_id);
		$armed_modes=	$this->db->select("mode_id")->from("security_mode_configuration")->where($condition)->get()->result();
		
		$armedin	=	array();
		foreach($armed_modes as $mode){
			$armedin[]	=	$mode->mode_id;
		}
		
		
		
		
		$node_info	=	array(
								"model"=>"security",
								"method"=>"configuration",
								"node_id"=>$node_id,
								"end_point"=>1,
								"device_name"=>$device_name,
								"mode_id"=>$mode_id,
								"status_name"=>$arm_status, 
								"status"=>$arm, 
								"menu_id"=>$menu_id, 
								"cuid"=>$cuid, 
								"room_id"=>$room_id, 
								"armed_modes"=>$armedin,
								"report_type"=>$report_type, 
								"id"=>10
							);
		return $node_info;
	}




function modeSecurityInfo($mode_id){
       
		$arm 		=	0;
		$cuid		=	0;
		$menu_id	=	4014;
		$room_id	=	"NA";
		$mode_name=	"NA";
		$report_type=	"info";
      


      # mode info
       $data		=	array("mode_id"=>$mode_id);
		$records	=	$this->db->select("mode_id, active_status, mode_name")->from("Security_Modes")->where($data)->get()->result();
		
		$modes 		  =	array();
		foreach($records as $rec){
		
         $arm        =  $rec->active_status;
         $mode_name        =  $rec->mode_name;
         
		}
		
		
		
		
		$arm_status	=	($arm == 1)? "active" : "inactive";
			
			
			
		#armed nodes	
		$condition	=	array("node_security_status"=>"1","mode_id"=>$mode_id);
		$armed_nodes=	$this->db->select("node_id")->from("security_mode_configuration")->where($condition)->get()->result();
		
		$armednodes	=	array();
		foreach($armed_nodes as $node){
			$armednodes[]	=	$node->node_id;
		}
		
		
		
		
		$mode_info	=	array(
								"model"=>"security",
								"method"=>"mode_configuration",
								"mode_name"=>$mode_name,
								"mode_id"=>$mode_id,
								"status"=>$arm, 
								"menu_id"=>$menu_id, 
								"cuid"=>$cuid, 
								"room_id"=>$room_id, 
								"armed_nodes"=>$armednodes,
								"report_type"=>"info", 
								"id"=>10
							);
		return $mode_info;
	}


}
