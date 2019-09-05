<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_lottery_bet
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('prediction_model', 'prediction_db');
        $this->CI->load->model('prediction_buy_model', 'prediction_buy_db');
        $this->CI->load->model('prediction_relief_model', 'prediction_relief_db');
        $this->CI->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->CI->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
        $this->CI->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
    }

    public function getBetInfo($data)
    {
        $category = $this->CI->input->post('category');
        $lottery_id = $this->CI->input->post('lottery_id');
        $qishu = $this->CI->input->post('qishu');
        //經典才有熱門預測
        if ($category == 1) {
            $wanfa_detail = $this->CI->prediction_buy_db->getBuyWanfaDetail($lottery_id, $qishu, $this->CI->uid);
            $wanfa_detail_ids = array_column($wanfa_detail, 'wanfa_detail_id');
            if ($wanfa_detail_ids !== []) {
                foreach ($data['bet_detail_list'] as $key => $row) {
                    if (in_array($row['wanfa_detail_id'], $wanfa_detail_ids)) {
                        $data['bet_detail_list'][$key]['is_buy'] = 1;
                    } else {
                        $data['bet_detail_list'][$key]['is_buy'] = 0;
                    }
                }
            }
        }
        return $data;
    }

    public function getBetList($data)
    {
        $wanfa = $this->CI->ettm_classic_wanfa_db->result();
        $wanfa = array_column($wanfa, 'pid', 'id');
        $join[] = [$this->CI->table_ . 'prediction t1', 't.prediction_id = t1.id', 'left'];
        foreach ($data['list'] as $key => $row) {
            if ($row['category'] == 1) {
                $row['is_buy'] = false;
                $row['bet_message'] = '';
                //判斷是否有購買預測
                $buys = $this->CI->prediction_buy_db->select('t.*,t1.wanfa_id')->where([
                    't.uid'         => $this->CI->uid,
                    't1.lottery_id' => $row['lottery_id'],
                    't.qishu'       => $row['qishu'],
                ])->join($join)->result();
                foreach ($buys as $arr) {
                    $pids = [];
                    foreach (explode(',', $arr['wanfa_id']) as $wanfa_id) {
                        $pids[] = $wanfa[$wanfa_id];
                    }
                    if (in_array($row['wanfa_pid'], $pids)) {
                        $row['is_buy'] = true;
                        break;
                    }
                }
                //判斷是否有救濟金
                $relief = $this->CI->prediction_relief_db->select('t.*,t1.wanfa_id')->where([
                    't.uid'         => $this->CI->uid,
                    't1.lottery_id' => $row['lottery_id'],
                    't.qishu'       => $row['qishu'],
                ])->join($join)->result_one();
                if ($relief !== null) {
                    $pids = [];
                    foreach (explode(',', $relief['wanfa_id']) as $wanfa_id) {
                        $pids[] = $wanfa[$wanfa_id];
                    }
                    if (in_array($row['wanfa_pid'], $pids)) {
                        $row['bet_message'] = "救济金已分发至个人账户";
                    }
                }
                $data['list'][$key] = $row;
            }
        }
        return $data;
    }

    public function checkBet($data)
    {
        $lottery_id = $this->CI->input->post('lottery_id');
        $qishu = $this->CI->input->post("qishu");
        $wanfa_detail = $this->CI->prediction_buy_db->getBuyWanfaDetail($lottery_id, $qishu, $this->CI->uid);
        $wanfa_detail_ids = array_column($wanfa_detail, 'wanfa_detail_id');
        $module = $this->CI->module[1];
        $data['alms'] = isset($module['param']['alms']) ? $module['param']['alms']:30;
        
        foreach ($data['value_list'] as $key => $row) {
            $row['is_buy'] = false;
            if (in_array($row['id'], $wanfa_detail_ids)) {
                $row['is_buy'] = true;
            }
            $data['value_list'][$key] = $row;
        }

        return $data;
    }
}
