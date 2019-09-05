<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_lottery
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('prediction_model', 'prediction_db');
    }

    public function getCurrentPeriod($data)
    {
        $lottery_id = $this->CI->input->post('lottery_id');
        $prediction_lottery = $this->CI->prediction_db->getPredictionLottery();
        $data['is_pred'] = in_array($lottery_id, $prediction_lottery);
        return $data;
    }
}
