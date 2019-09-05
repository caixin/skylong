<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('mqtt_publish'))
{
    function mqtt_publish($topic,$content,$qos=0,$retain=0)
    {
        $CI =& get_instance();
		$CI->load->library('phpMQTT');
		
		$CI->phpmqtt->broker($CI->config->item('mqtt_server'),$CI->config->item('mqtt_port'),$CI->config->item('mqtt_clientid'));
		
		if ($CI->phpmqtt->connect(true, NULL, $CI->config->item('mqtt_username'), $CI->config->item('mqtt_password')))
		{
			$CI->phpmqtt->publish($topic,$content,$qos,$retain); 
			$CI->phpmqtt->close();
		}
    }
}