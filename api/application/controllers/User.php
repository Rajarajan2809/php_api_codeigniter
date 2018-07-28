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
class User extends REST_Controller 
{
	
	function __construct(){
		
			parent::__construct();
			$this->load->database();
			$this->load->library('encrypt');
	}
	
	public function info_get(){

			$headers = $this->input->request_headers();

			//print_r($headers);

			$api_key 	=	$this->input->get_request_header('x-api-key', TRUE);
			$user_id	=	$this->encrypt->decode($api_key);

			$cols		=	array("*");
		
			$condition	=	array("user_id"=>$user_id);

			//$encrypted_string = $this->encrypt->encode($msg);

			$qry		=	$this->db->select($cols)->from("Stargate_Users_Profile")->where($condition);
			
			$res		=	$qry->get()->row();	

			

			unset($res->user_id);
			unset($res->image);
			unset($res->password);

			
			
			
			$this->response($res, REST_Controller::HTTP_OK);
	}




	public function login_post(){

			/*	
				$res 	=	$this->request->body;
				print_r($res);
			*/
			


		echo	$email		=	$this->input->post("email");
			$password	=	md5($this->input->post("password"));
			
			$condition	=	array("email"=>$email,"password"=>$password);

			//$encrypted_string = $this->encrypt->encode($msg);

			$cols		=	array("*");
			$qry		=	$this->db->select($cols)->from("Stargate_Users_Profile")->where($condition);
			
			$res		=	$qry->get()->row();	
			
		if(isset($res->user_id)){
				$api_key	=	$this->encrypt->encode($res->user_id);

				unset($res->user_id);
				unset($res->image);
				unset($res->password);

				$res->api_key 	=	$api_key;

				$this->response($res, REST_Controller::HTTP_OK);
		}
		else{
			$res = array("status"=>"unauthorized access");

			$this->response($res, REST_Controller::HTTP_UNAUTHORIZED);
		}



		
			
	}
	
	
}
