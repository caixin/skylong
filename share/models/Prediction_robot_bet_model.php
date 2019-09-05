<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_robot_bet_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'prediction_robot_bet';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'bet_money', 'label' => '下注金额', 'rules' => 'trim|required'],
            ['field' => 'bet_money_max', 'label' => '投注总额', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }

        if (isset($this->_where['prediction_id'])) {
            $this->db->where('t.prediction_id', $this->_where['prediction_id']);
            unset($this->_where['prediction_id']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t1.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }
        return $this;
    }

    /**
     * 虛擬下注
     *
     * @return void
     */
    public function robotBet()
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_sort_model', 'ettm_lottery_sort_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('prediction_model', 'prediction_db');
        $this->load->model('prediction_robot_setting_model', 'prediction_robot_setting_db');
        $this->load->model('qishu_model');

        $hour = date('H');
        $time = time();
        $nowtime = date('Y-m-d H:i:s');
        //整理公式
        $result = $this->prediction_robot_setting_db->result();
        $data = [];
        foreach ($result as $row) {
            $data[$row['lottery_id']][$row['operator_id']] = $row;
        }
        $lottery_data = [];
        foreach ($data as $lottery_id => $operator_arr) {
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            //找不到彩種則跳出
            if ($lottery === null) {
                continue;
            }
            $lottery_sort = $this->ettm_lottery_sort_db->row_change($lottery_id);
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);

            $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;
            $count_down = $qishu_arr['count_down'] - $time;
            if ($lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                $status = 2;
            }
            //彩種非正常投注狀態則跳出
            if ($status != 1) {
                continue;
            }
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery_id,
                'qishu'      => $qishu_arr['next_qishu'],
                'status'     => 0,
            ])->result_one();
            //非未開獎則跳出(預防提前開獎)
            if ($record === null) {
                continue;
            }

            foreach ($operator_arr as $operator_id => $row) {
                $lottery_data[$lottery_id][$operator_id] = [
                    'bet_time' => bcdiv($count_down * 100, $qishu_arr['interval'], 2),
                    'qishu'    => $qishu_arr['next_qishu'],
                    'setting'  => $row,
                ];
            }
        }
        
        $result = $this->prediction_db->order(['lottery_id','asc'])->result();
        $insert = $update = [];
        foreach ($result as $row) {
            //無公式則跳出
            if (!isset($lottery_data[$row['lottery_id']])) {
                continue;
            }
            $operator_arr = $lottery_data[$row['lottery_id']];
            //各運營商獨立計算虛擬下注
            foreach ($operator_arr as $operator_id => $setting_arr) {
                $bet_time = $setting_arr['bet_time'];
                $qishu    = $setting_arr['qishu'];
                $setting  = $setting_arr['setting'];
                $total_formula = $bet_formula = [];
                //取得投注總額公式
                $formula = (array)json_decode($setting['total_formula'], true);
                foreach ($formula as $arr) {
                    if ($hour >= $arr['hour_start'] && $hour <= $arr['hour_end']) {
                        $total_formula = $arr;
                        break;
                    }
                }
                //取得下注公式
                $formula = (array)json_decode($setting['bet_formula'], true);
                $tmp = 0;
                foreach ($formula as $arr) {
                    $tmp += $arr['bet_time'];
                    if ($bet_time <= $tmp) {
                        $bet_formula = $arr;
                        break;
                    }
                }
                //查詢不到公式則跳出
                if ($total_formula == [] || $bet_formula == []) {
                    continue;
                }
            
                $bets = $this->where([
                    'operator_id'   => $operator_id,
                    'prediction_id' => $row['id'],
                    'qishu'         => $qishu,
                ])->order(['values','asc'])->result();
                $bets = array_column($bets, null, 'values');
                $values = $this->prediction_db->getValues($row['id']);
                $over = 0;
                foreach ($values as $key => $value) {
                    $value = is_numeric($value) ? (int)$value: $key;
                    //判斷是否第一次執行
                    if (!isset($bets[$value])) {
                        $bet_money = $bet_money_max = 0;
                        if ($over >= $total_formula['over_number']) {
                            $bet_money_max = rand($total_formula['total_min'], $total_formula['total_middle']);
                        } else {
                            $bet_money_max = rand($total_formula['total_min'], $total_formula['total_max']);
                            if ($bet_money_max > $total_formula['total_middle']) {
                                $over++;
                            }
                        }
                        //下注
                        if (rand(1, 10000) <= $bet_formula['bet_action'] * 100) {
                            $percent = rand($bet_formula['bet_min'], $bet_formula['bet_max']);
                            $bet_money = bcmul($bet_money_max, bcdiv($percent, 100, 2));
                        }
                        $insert[] = [
                            'operator_id'   => $operator_id,
                            'prediction_id' => $row['id'],
                            'values'        => $value,
                            'qishu'         => $qishu,
                            'bet_money'     => $bet_money,
                            'bet_money_max' => $bet_money_max,
                            'create_time'   => $nowtime,
                            'update_time'   => $nowtime,
                        ];
                    } else {
                        $bet = $bets[$value];
                        $bet_money = $bet['bet_money'];
                        //下注
                        if (rand(1, 10000) <= $bet_formula['bet_action'] * 100) {
                            $percent = rand($bet_formula['bet_min'], $bet_formula['bet_max']);
                            $bet_money += bcmul($bet['bet_money_max'], bcdiv($percent, 100, 2));
                            $bet_money = $bet_money > $bet['bet_money_max'] ? $bet['bet_money_max']:$bet_money;
                        }
                        if ($bet_money != $bet['bet_money']) {
                            $update[] = [
                                'id'          => $bet['id'],
                                'bet_money'   => $bet_money,
                                'update_time' => $nowtime,
                            ];
                        }
                    }
                }
            }
        }
        //開始事務 寫入資料
        $this->trans_start();
        if ($insert !== []) {
            $this->insert_batch($insert);
        }
        if ($update !== []) {
            $this->update_batch($update, 'id');
        }
        $this->trans_complete();
    }

    /**
     * 取得該位置預測號下注金額
     *
     * @param integer $prediction_id 預測ID
     * @param integer $qishu 期數
     * @return array
     */
    public function getBetTotal($prediction_id, $qishu)
    {
        $this->load->model('prediction_model', 'prediction_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $prediction = $this->prediction_db->row($prediction_id);
        $wanfa_detail_ids = $this->prediction_db->getValuesWanfaDetailID($prediction_id);

        $join[] = [$this->table_.'user t1','t.uid = t1.id', 'left'];
        $bets = $this->ettm_classic_bet_record_db->escape(false)
            ->select('wanfa_detail_id,SUM(total_p_value) total_p_value')
            ->where([
                'operator_id' => $this->operator_id,
                'lottery_id'  => $prediction['lottery_id'],
                'qishu'       => $qishu,
            ])->join($join)->group('wanfa_detail_id')->result();
        $bet_total = array_column($bets, 'total_p_value', 'wanfa_detail_id');
        
        $values = $this->prediction_db->getValues($prediction_id);
        $data = [];
        foreach ($values as $value) {
            $value = is_numeric($value) ? (int)$value: $value;
            $total_p_value = 0;
            foreach ($bet_total as $id => $bet) {
                if (in_array($id, $wanfa_detail_ids[(string)$value])) {
                    $total_p_value += $bet;
                }
            }
            $data[$value] = $total_p_value;
        }
        return $data;
    }

    /**
     * 取得投注熱度排行資料
     *
     * @param integer $prediction_id 預測ID
     * @param integer $qishu 期數
     * @return array
     */
    public function getRankData($prediction_id, $qishu)
    {
        $prediction = $this->prediction_db->row($prediction_id);
        $lottery = $this->ettm_lottery_db->row($prediction['lottery_id']);
        //各預測號虛擬下注
        $robot_bet = $this->where([
            'operator_id'   => $this->operator_id,
            'prediction_id' => $prediction_id,
            'qishu'         => $qishu,
        ])->result();
        $robot_bet = array_column($robot_bet, 'bet_money', 'values');
        //各預測號實際下注
        $bet = $this->getBetTotal($prediction_id, $qishu);
        $values = $this->prediction_db->getValues($prediction_id);
        $array = [];
        //相加
        foreach ($values as $key => $value) {
            $value = is_numeric($value) ? (int)$value: $key;
            $bet_total = 0;
            if (isset($robot_bet[$value])) {
                $bet_total += $robot_bet[$value];
            }
            if (isset($bet[$value])) {
                $bet_total += $bet[$value];
            }
            $array[$value] = $bet_total;
        }
        arsort($array);
        $list = [];
        $i = 1;
        //排行
        foreach ($array as $key => $val) {
            $list[$key] = [
                'sort'  => $i++,
                'name'  => $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $key),
                'value' => $val,
            ];
        }
        ksort($list);

        return array_values($list);
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'            => '编号',
        'operator_id'   => '运营商ID',
        'prediction_id' => '预测ID',
        'qishu'         => '期数',
        'bet_money'     => '下注金额',
        'bet_money_max' => '投注总额',
    ];
}
