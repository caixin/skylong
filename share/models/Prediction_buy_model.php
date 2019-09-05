<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_buy_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'prediction_buy';
        $this->_key = 'id';
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t1.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            //篩選運營商
            foreach ($this->_join as $arr) {
                if (strpos($arr[0], $this->table_ . 'user ') !== false) {
                    $table = trim(str_replace($this->table_ . 'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }

        if (isset($this->_where['prediction_id'])) {
            $this->db->where('t.prediction_id', $this->_where['prediction_id']);
            unset($this->_where['prediction_id']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t2.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }
        if (isset($this->_where['user_name'])) {
            $this->db->like('t1.user_name', $this->_where['user_name'], 'both');
            unset($this->_where['user_name']);
        }
        if (isset($this->_where['digits'])) {
            $this->db->where('t.digits', $this->_where['digits']);
            unset($this->_where['digits']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }
        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
    }

    /**
     * 熱門預測計算救濟金
     *
     * @param integer $lottery_id 彩種ID
     * @param integer $qishu 期數
     * @param array $numbers 開獎號碼
     * @param string $lottery_time 開獎時間
     * @return string
     */
    public function settlement($lottery_id, $qishu, $numbers, $lottery_time)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('prediction_relief_model', 'prediction_relief_db');

        $lottery = $this->ettm_lottery_db->row($lottery_id);
        $values_str = $this->ettm_lottery_record_db->getValue($lottery['lottery_type_id'], implode(',', $numbers), $lottery_time);
        $prediction_dir = $lottery['key_word'] . 'Prediction';
        //下注資料
        $join = [];
        $where = [
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu,
            't.status'     => 1,
        ];
        if ($this->operator_id > 0) {
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->ettm_classic_bet_record_db->where($where)->join($join)->result();
        //用UID分類
        $bet_arr = [];
        foreach ($result as $row) {
            $bet_arr[$row['uid']][] = $row;
        }
        //購買的預測號
        $join = [];
        $join[] = [$this->table_.'prediction t2', 't.prediction_id = t2.id', 'left'];
        $where = [
            't2.lottery_id' => $lottery_id,
            't.qishu'       => $qishu,
            't.status'      => 0,
        ];
        if ($this->operator_id > 0) {
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select('t.*,t2.wanfa_id,t2.ball')
                    ->where($where)->join($join)->result();
        if ($result === []) {
            Monolog::writeLogs($prediction_dir, 200, '---- 无尚未执行的预测 ----');
            return;
        }
        $update_buy = $reliefs = [];
        foreach ($result as $row) {
            $wanfa_ids = explode(',', $row['wanfa_id']);
            $buy_numbers = explode(',', $row['numbers']);
            //判斷預測號是否中獎
            $winning = false;
            if ($lottery['lottery_type_id'] == 8 && $row['ball'] < 0) {
                switch ($row['ball']) {
                    case -1: //特肖
                        $number = isset($values_str[6]) ? $values_str[6]:'';
                        $winning = in_array($number, $buy_numbers);
                        break;
                    case -2: //一肖
                        for ($i=0;$i<6;$i++) {
                            $number = isset($values_str[$i]) ? $values_str[$i]:'';
                            if (in_array($number, $buy_numbers)) {
                                $winning = true;
                                break;
                            }
                        }
                        break;
                }
            } else {
                if (!isset($numbers[$row['ball']-1])) {
                    continue;
                }
                $winning = in_array($numbers[$row['ball']-1], $buy_numbers);
            }
            if ($winning) {
                //預測中獎-不發放救濟金
                $update_buy[] = [
                    'id'          => $row['id'],
                    'expire_time' => '2099-12-31',
                    'status'      => 1,
                    'update_time' => date('Y-m-d H:i:s'),
                    'update_by'   => $this->session->userdata('username'),
                ];
            } else {
                //預測失敗-判斷有無下注
                if (isset($bet_arr[$row['uid']])) {
                    //有下注
                    $values = $this->prediction_db->getValues($row['prediction_id']);
                    $bet_total_arr = [];
                    //判斷是否有中獎
                    $bet_win = false;
                    foreach ($bet_arr[$row['uid']] as $bet) {
                        if (is_numeric($bet['bet_values'])) {
                            $bet['bet_values'] = $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $bet['bet_values']);
                        }
                        if (in_array($bet['wanfa_id'], $wanfa_ids) && in_array($bet['bet_values'], $values)) {
                            if ($bet['is_lose_win'] == 1) {
                                $bet_win = true;
                            }
                            if (in_array($bet['bet_values'], $buy_numbers)) {
                                $bet_total_arr[$bet['id']] = $bet['total_p_value'];
                            }
                        }
                    }
                    if ($bet_win || $bet_total_arr == []) {
                        //該位置有中獎或沒有下注預測號則不發放救濟金
                        $update_buy[] = [
                            'id'          => $row['id'],
                            'expire_time' => '2099-12-31',
                            'status'      => 1,
                            'update_time' => date('Y-m-d H:i:s'),
                            'update_by'   => $this->session->userdata('username'),
                        ];
                    } else {
                        //該位置都輸-寫入救濟金
                        $user = $this->user_db->row($row['uid']);
                        $module = $this->module_operator_db->getEnable($user['operator_id']);
                        $expire_days = isset($module[1]['param']['expire_days']) ? $module[1]['param']['expire_days']:7;
                        $alms = isset($module[1]['param']['alms']) ? $module[1]['param']['alms']:30;
                        $expire_time = date('Y-m-d H:i:s', time()+86400*$expire_days);
                        $update_buy[] = [
                            'id'          => $row['id'],
                            'expire_time' => $expire_time,
                            'status'      => 2,
                            'update_time' => date('Y-m-d H:i:s'),
                            'update_by'   => $this->session->userdata('username'),
                        ];
                        $reliefs[$row['uid']][$row['prediction_id']]['expire_time'] = $expire_time;
                        $reliefs[$row['uid']][$row['prediction_id']]['alms'] = $alms;
                        $reliefs[$row['uid']][$row['prediction_id']]['list'][] = [
                            'buy_id'        => $row['id'],
                            'bet_total_arr' => $bet_total_arr,
                            'digits'        => $row['digits'],
                            'price'         => $row['price'],
                        ];
                    }
                } else {
                    //無下注
                    $update_buy[] = [
                        'id'          => $row['id'],
                        'expire_time' => '2099-12-31',
                        'status'      => 1,
                        'update_time' => date('Y-m-d H:i:s'),
                        'update_by'   => $this->session->userdata('username'),
                    ];
                }
            }
        }
        //計算救濟金
        $insert = [];
        foreach ($reliefs as $uid => $prediction) {
            foreach ($prediction as $prediction_id => $data) {
                $buy_ids = $bet_total_arr = $digits = [];
                $bet_money = $relief = 0;
                foreach ($data['list'] as $row) {
                    $buy_ids[] = (int)$row['buy_id'];
                    $digits[$row['digits']] = (float)$row['price'];
                    $relief += $row['price'];
                    $bet_total_arr += $row['bet_total_arr'];
                }

                $bet_ids = [];
                foreach ($bet_total_arr as $bet_id => $total_p_value) {
                    $bet_ids[] = $bet_id;
                    $relief = bcadd($relief, bcmul($total_p_value, bcdiv($data['alms'], 100, 3), 2), 2);
                    $bet_money = bcadd($bet_money, $total_p_value, 2);
                }

                $payload = json_encode([
                    'buy_ids' => $buy_ids,
                    'bet_ids' => $bet_ids,
                    'digits'  => $digits,
                    'alms'    => (float)$data['alms'],
                ]);

                $insert[] = [
                    'uid'           => $uid,
                    'prediction_id' => $prediction_id,
                    'qishu'         => $qishu,
                    'payload'       => $payload,
                    'relief'        => $relief,
                    'bet_money'     => $bet_money,
                    'expire_time'   => $data['expire_time'],
                    'status'        => 0,
                    'create_time'   => date('Y-m-d H:i:s'),
                    'create_by'     => $this->session->userdata('username'),
                    'update_time'   => date('Y-m-d H:i:s'),
                    'update_by'     => $this->session->userdata('username'),
                ];
            }
        }
        //寫入資料
        $this->trans_start();
        if ($update_buy !== []) {
            $this->update_batch($update_buy, 'id');
        }
        if ($insert !== []) {
            $this->prediction_relief_db->insert_batch($insert);
        }
        $this->trans_complete();
        return 'done';
    }

    /**
     * 預測號退款
     */
    public function refund($lottery_id, $qishu, $uid = 0)
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('prediction_relief_model', 'prediction_relief_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        $join[] = [$this->table_.'prediction t2', 't.prediction_id = t2.id', 'left'];
        $where = [
            't2.lottery_id' => $lottery_id,
            't.qishu'       => $qishu
        ];
        if ($uid > 0) {
            $where['t.uid'] = $uid;
        }
        if ($this->operator_id > 0) { //for各運營
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select("t.id,t.uid,t.price")->join($join)->where($where+['t.status >='=>0])->result();
        $update = [];
        foreach ($result as $row) {
            $this->user_db->addMoney($row['uid'], $qishu, 21, $row['price'], "$lottery[name]预测看号退款", 1, $lottery_id, $row['id']);
            //預測購買註記退款
            $update[] = [
                'id'     => $row['id'],
                'status' => -1
            ];
        }

        $result = $this->prediction_relief_db->join($join)->where($where)->result();
        $relief = [];
        foreach ($result as $row) {
            //救濟金註記失效
            $relief[] = [
                'id'     => $row['id'],
                'status' => 3
            ];
        }

        $this->trans_start();
        if ($update !== []) {
            $this->update_batch($update, 'id');
        }
        if ($relief !== []) {
            $this->prediction_relief_db->update_batch($relief, 'id');
        }
        $this->trans_complete();
    }

    /**
     * 將購買預測狀態(status)改為 0待處理 (重新派獎用)
     *
     * @param integer $lottery_id 彩種ID
     * @param integer $qishu 期數
     * @return void
     */
    public function reaward($lottery_id, $qishu)
    {
        $this->load->model('prediction_relief_model', 'prediction_relief_db');

        $join[] = [$this->table_.'prediction t2', 't.prediction_id = t2.id', 'left'];
        $where = [
            't2.lottery_id' => $lottery_id,
            't.qishu'       => $qishu,
            't.status >='   => 0
        ];
        if ($this->operator_id > 0) { //for各運營
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select("t.id")->join($join)->where($where)->result();
        $update = [];
        foreach ($result as $row) {
            $update[] = [
                'id'     => $row['id'],
                'status' => 0,
            ];
        }
        
        $this->trans_start();
        if ($update !== []) {
            $this->update_batch($update, 'id');
        }
        $result = $this->prediction_relief_db->select("t.id")->join($join)->where($where)->result();
        foreach ($result as $row) {
            $this->prediction_relief_db->delete($row['id']);
        }
        $this->trans_complete();
    }

    /**
     * 取得購買預測的玩法詳情
     *
     * @param integer $lottery_id 彩種ID
     * @param integer $qishu 期數
     * @param integer $uid 用戶ID
     * @return array 玩法詳情
     */
    public function getBuyWanfaDetail($lottery_id, $qishu, $uid)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        //彩種資訊
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        //玩法整理
        $wanfa = $this->ettm_classic_wanfa_detail_db->where([
            't.lottery_type_id' => $lottery['lottery_type_id'],
        ])->result();
        $wanfa_arr = [];
        foreach ($wanfa as $row) {
            $wanfa_arr[$row['wanfa_id']][$row['id']] = $row['values'];
        }
        //購買預測
        $join[] = [$this->table_.'prediction t1', 't.prediction_id = t1.id', 'left'];
        $result = $this->select('t.*,t1.wanfa_id')->where([
            't.uid'         => $uid,
            't1.lottery_id' => $lottery_id,
            't.qishu'       => $qishu,
            't.status >='   => 0,
        ])->join($join)->result();

        $data = [];
        foreach ($result as $row) {
            $wanfa_ids = explode(',', $row['wanfa_id']);
            $numbers = explode(',', $row['numbers']);
            
            foreach ($wanfa_ids as $key => $wanfa_id) {
                if (!isset($wanfa_arr[$wanfa_id])) {
                    continue;
                }
                foreach ($wanfa_arr[$wanfa_id] as $wanfa_detail_id => $values) {
                    if (is_numeric($values)) {
                        $values = $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $values);
                    }
                    if (in_array($values, $numbers)) {
                        $data[] = [
                            'wanfa_detail_id' => $wanfa_detail_id,
                            'prediction_id'   => $row['prediction_id'],
                            'digits'          => $row['digits'],
                            'index'           => $key,
                        ];
                    }
                }
            }
        }
        return $data;
    }

    //預測幾碼
    public static $digitsList = [
        3 => '三码',
        5 => '五码',
    ];

    public static $statusList = [
        -1 => '已退款',
        0  => '待处理',
        1  => '已结算(无救济金)',
        2  => '已结算(有救济金)',
    ];
}
