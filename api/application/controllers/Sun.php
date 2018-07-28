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
class Sun extends REST_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function time_get()
	{
		$resp=array();
		$loc_json = json_decode(post_curl('https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyCQlWl3ge3V8U_idqJT8iiKSU_w4NfanbQ',array()));
		//var_dump($loc_arr);
		if($loc_json !== NULL)
		{
			$loc_array = $loc_json->{'location'};
			//echo $loc_array->{'lat'}."<br><br>";
			//echo $loc_array->{'lng'}."<br><br>";
			$time_json = json_decode(post_curl('https://api.sunrise-sunset.org/json?lat='.$loc_array->{'lat'}.'&lng='.$loc_array->{'lng'},array()));
			$time_array = $time_json->{'results'};
			$sun_r = $time_array->{'sunrise'};
			$sun_s = $time_array->{'sunset'};

			//UTC to IST conversion
			$sun_r = date('h:i A',strtotime('+5 hour +30 minutes',strtotime($sun_r)));
			$sun_s = date('h:i A',strtotime('+5 hour +30 minutes',strtotime($sun_s)));

			//echo "Sun rise:".$sun_r."<br><br>";
			//echo "Sun set:".$sun_s."<br><br>";

			$this->db->select('id');
			$this->db->from('Stargate_Manager');
			$query1 = $this->db->get();

			foreach ($query1->result() as $row1)
			{
				$data = array(
								'latitude' 	=> $loc_array->{'lat'},
								'longitude' => $loc_array->{'lng'},
								'sun_rise' 	=> $sun_r,
								'sun_set'  	=> $sun_s);
				$this->db->set($data);
				$this->db->where('id', $row1->id);
				$this->db->update('Stargate_Manager', $data);
			}
			$resp =	array('response_text'	=>	'device data updated');
		}
		else
 		{
			$resp =	array('response_text'	=>	'device data update failed');
		}
		$this->response($resp, REST_Controller::HTTP_OK);
	}
}

function post_curl($url,$post)
{
	$defaults = array(
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $url,
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 4,
					CURLOPT_POSTFIELDS => http_build_query($post)
				);
	$ch = curl_init();
	curl_setopt_array($ch, ($defaults));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
