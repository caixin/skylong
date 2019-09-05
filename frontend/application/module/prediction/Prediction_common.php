<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_common
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function webParam($data)
    {
        $module = $this->CI->module[1];
        $data['alms'] = isset($module['param']['alms']) ? $module['param']['alms']:30;
        return $data;
    }
}
