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
class Ir extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function execute_post()
	{
		$json_data 	= $this->request->body;
		if(isset($json_data['model']) && ($json_data['model'] == 'ir') && isset($json_data['cuid']) && ($json_data['cuid'] == '4017') && isset($json_data['button_id']) && isset($json_data['product_id']))
		{
			$button_id = $json_data['product_id'];
			$this->db->select('ID.device_ip,ID.device_port');
			$this->db->from('Ir_Product IP');
			$this->db->join('Ir_Device ID','ID.device_id = IP.device_id','left');
			$this->db->where('product_id', $json_data['product_id']);
			$query1 = $this->db->get();
			$row1 = $query1->row();
			//echo $this->db->last_query();
			//echo $row1->device_ip."<br>";
			//echo $row1->device_port."<br>";
			
			$this->db->select('ir_code');
			$this->db->from('Ir_Button');
			$this->db->where(array(	'button_id' => $json_data['button_id'], 
									'product_id' => $json_data['product_id']));
			$query2 = $this->db->get();
			$row2 = $query2->row();
			//echo $this->db->last_query();
			//echo $row2->ir_code."<br>";
			
			if(!empty($row2) && !empty($row1))
			{
				tcp_socket($row1->device_ip,$row1->device_port,$row2->ir_code.'\r\n');
				$resp =	array(	"model" =>	$json_data['model'],
								"cuid"	=>	$json_data['cuid'],
								"response_text"=>"IR command sent");
				$this->response($resp, REST_Controller::HTTP_OK);
			}
		}
		$resp =	array(	"model" =>	'ir',
						"cuid"	=>	'4017',
						"response_text"=>"IR command not sent");
		$this->response($resp, REST_Controller::HTTP_OK);
	}
	
	public function list_post()
	{
		$json_data 	= $this->request->body;$resp=array();
		if(empty($json_data['product_id']))
		{
			$this->db->select('IP.product_id,IP.product_name');
			$this->db->from('Ir_Device ID');
			$this->db->join('Ir_Product IP','ID.device_id = IP.device_id','left');
			$query1 = $this->db->get();
			foreach($query1->result() as $row1)
			{
				$this->db->select('button_id,button_name,type,repeat');
				$this->db->from('Ir_Button');
				$this->db->where('product_id',$row1->product_id);
				$query2 = $this->db->get();
				//echo $this->db->last_query()."<br>";
				$resp[] = array(
								'model'			=>	'ir',
								'cuid'			=>	'4017',
								'product_id'	=>	$row1->product_id,
								'product_name'	=>	$row1->product_name,
								'button'		=>	$query2->result()
								);
			}
			$this->response($resp, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->db->select('product_name');
			$this->db->from('Ir_Product');
			$this->db->where('product_id',$json_data['product_id']);
			$query1 = $this->db->get();
			$row1 = $query1->row();
			
			$this->db->select('button_id,button_name,type,repeat');
			$this->db->from('Ir_Button');
			$this->db->where('product_id',$json_data['product_id']);
			$query2 = $this->db->get();
			//echo $this->db->last_query()."<br>";
			$resp[] = array(
							'model'			=>	'ir',
							'cuid'			=>	'4017',
							'product_id'	=>	$json_data['product_id'],
							'product_name'	=>	$row1->product_name,
							'button'		=>	$query2->result()
							);
			$this->response($resp, REST_Controller::HTTP_OK);
		}
	}
}

function tcp_socket($ip,$port,$data)
{
	//echo "ip:".$ip."<br>";
	//echo "port:".$port."<br>";
	//echo "data:".$data."<br>";
	$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Listening");
	// connect to server
	$result = socket_connect($socket, $ip, $port) or die("Listening.");  
	socket_write($socket,$data."\r\n");
	//sleep(1);
	//$line = trim(socket_read($socket, 1048));
	//echo "socket data:".$line;
	//	socket_write($socket,"------------------------"."\n");
	socket_close($socket);
}
?>
