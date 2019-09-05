<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_special_bet_record_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'ettm_special_bet_record';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'special_id', 'label' => '特色棋牌ID', 'rules' => 'trim|required'],
            ['field' => 'qishu', 'label' => '期数', 'rules' => 'trim|required'],
            ['field' => 'value_list', 'label' => '注单', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('t.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        } else {
            $this->db->where('t.is_delete', 0);
        }

        if (isset($this->_where['operator_id'])) {
            $this->db->where('t1.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator') && !isset($this->_where['t.uid'])) {
            //篩選運營商
            foreach ($this->_join as $arr) {
                if (strpos($arr[0], $this->table_ . 'user ') !== false) {
                    $table = trim(str_replace($this->table_ . 'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }
        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t1.agent_id', $this->session->userdata('id'));
        }
        if (isset($this->_where['user_name'])) {
            $this->db->like('t1.user_name', $this->_where['user_name'], 'both');
            unset($this->_where['user_name']);
        }
        if (isset($this->_where['agent_code'])) {
            $this->db->where('t1.agent_code', $this->_where['agent_code']);
            unset($this->_where['agent_code']);
        }
        if (isset($this->_where['agent_name'])) {
            $this->db->like('t2.name', $this->_where['agent_name'], 'both');
            unset($this->_where['agent_name']);
        }
        if (isset($this->_where['money_type'])) {
            $this->db->where('t.money_type', $this->_where['money_type']);
            unset($this->_where['money_type']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['special_id'])) {
            $this->db->where('t.special_id', $this->_where['special_id']);
            unset($this->_where['special_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
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
     * 還原結算注單
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     * @param int $uid 用戶ID(可選)
     * @return array 影響的注單ID
     */
    public function restoreBet($lottery_id, $qishu, $uid = 0)
    {
        $this->load->model('code_amount_model', 'code_amount_db');
        $join = [];
        $where = [
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu,
            't.status >'   => 0,
        ];
        if ($uid > 0) {
            $where['t.uid'] = $uid;
        }
        if ($this->operator_id > 0) { //for各運營
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select('t.id,t.uid,t.money_type,t.c_value,t.status')->join($join)->where($where)->result();
        $user = $update = [];
        foreach ($result as $row) {
            //如果有賠付金額要扣回來
            if ($row['c_value'] > 0) {
                $user[$row['uid']][$row['money_type']] = isset($user[$row['uid']][$row['money_type']]) ? bcadd($user[$row['uid']][$row['money_type']], $row['c_value'], 2) : $row['c_value'];
            }
            $update[] = [
                'id'          => $row['id'],
                'c_value'     => 0,
                'is_lose_win' => 0,
                'status'      => $row['status'] == 3 ? -1 : 0, //非常規退款還原成處理中
            ];
            //還原打碼量
            if ($row['status'] == 1) {
                $this->code_amount_db->restoreBetEffect(3, $row['id']);
            }
        }

        if ($update !== []) {
            foreach ($user as $uid => $money_type_arr) {
                foreach ($money_type_arr as $money_type => $money) {
                    $this->user_db->addMoney($uid, $qishu, 14, $money * -1, "还原注单扣款", 3, $lottery_id, 0, $money_type);
                }
            }
            $this->update_batch($update, 'id');
        }
        return array_column($update, 'id');
    }

    /**
     * 注單退款
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     * @param int $uid 用戶ID(可選)
     * @return array 影響的注單ID
     */
    public function refundBet($lottery_id, $qishu, $uid = 0)
    {
        $this->load->model('code_amount_model', 'code_amount_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $where = [
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu,
            't.status <='   => 1,
        ];
        if ($uid > 0) {
            $where['t.uid'] = $uid;
        }
        if ($this->operator_id > 0) { //for各運營
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select('t.id,t.uid,t.money_type,t.total_p_value,t.c_value,t.status')->join($join)->where($where)->result();
        $update = $uids = [];
        foreach ($result as $row) {
            //退款
            $money = (float)bcsub($row['total_p_value'], $row['c_value'], 2);
            $uids[$row['uid']][$row['money_type']] = isset($uids[$row['uid']][$row['money_type']]) ? bcadd($uids[$row['uid']][$row['money_type']], $money, 2) : $money;
            $update[] = [
                'id'           => $row['id'],
                'c_value'      => $row['total_p_value'],
                'status'       => $row['status'] == -1 ? 3 : 2,   //非常規退款狀態為3
            ];
            //還原打碼量
            if ($row['status'] == 1) {
                $this->code_amount_db->restoreBetEffect(3, $row['id']);
            }
        }

        if ($update !== []) {
            foreach ($uids as $uid => $money_type_arr) {
                foreach ($money_type_arr as $money_type => $money) {
                    $this->user_db->addMoney($uid, $qishu, 9, $money, "$lottery[name]特色棋牌下注退款", 3, $lottery_id, 0, $money_type);
                }
            }
            $this->update_batch($update, 'id');
        }
        return array_column($update, 'id');
    }

    /**
     * 牛牛注單紀錄
     * @param int $uid 用戶ID
     * @param int $qishu 期數
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     * @return array 投注詳情
     */
    public function getNiuBetDetail($special_id, $qishu, $uid, $money_type = 0)
    {
        $this->load->model('ettm_special_model', 'ettm_special_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $special = $this->ettm_special_db->row($special_id);
        $lottery = $this->ettm_lottery_db->row($special['lottery_id']);
        //預設值
        $result = [
            'name'        => $lottery['name'],
            'qishu'       => (int)$qishu,
            'logo'        => $lottery['pic_icon'],
            'numbers'     => [],
            'points'      => [],
            'create_time' => '--',
            'bet'         => 0,
            'win'         => 0,
            'type'        => ettm_special_model::$typeList[$special['type']],
            'status'      => 0,
            'message'     => '',
            'pk'          => [
                'banker' => [
                    'name'              => '庄家',
                    'point'             => '--',
                    'bet'               => 0,
                    'limit'             => (int)$special['banker_limit'],
                    'proportion'        => 0,
                    'profit'            => 0,
                    'proportion_profit' => 0,
                    'win'               => 0,
                ],
            ],
        ];
        for ($i = 1; $i <= 5; $i++) {
            $result['pk']['player'][$i] = [
                'name'        => "闲$i",
                'point'       => '--',
                'is_win'      => 0,
                'bet'         => ['--', '--'],
                'repayment'   => ['--', '--'],
                'win'         => ['--', '--'],
            ];
        }
        //開獎號碼
        $record = $this->ettm_lottery_record_db->where([
            'lottery_id' => $lottery['id'],
            'qishu'      => $qishu,
        ])->result_one();
        if ($record !== null && $record['status'] == 1) {
            $numbers = explode(',', $record['numbers']);
            $niu = $this->ettm_special_db->getNiuCard($numbers);
            $result['numbers'] = $numbers;
            $result['points'] = array_column($niu, 'point');
            foreach ($niu as $key => $arr) {
                if ($key == 0) {
                    $result['pk']['banker']['point'] = $arr['point'];
                } else {
                    $result['pk']['player'][$key]['point'] = $arr['point'];
                    $result['pk']['player'][$key]['is_win'] = $arr['is_win'];
                }
            }
        }
        //下注
        $bet = $this->escape(false)->select('bet_values,bet_multiple,SUM(p_value) p_value,SUM(c_value) c_value,SUM(total_p_value) total_p_value,SUM(IF(is_lose_win=1, total_p_value, c_value)) repayment,status,create_time')->where([
            'special_id' => $special_id,
            'qishu'      => $qishu,
            'uid'        => $uid,
            'money_type' => $money_type,
        ])->group('bet_values,bet_multiple')->order([
            'bet_values'   => 'ASC',
            'bet_multiple' => 'ASC',
        ])->result();
        $status = 0;
        foreach ($bet as $row) {
            $double = $row['bet_multiple'] > 1 ? 0 : 1;
            //下注時間
            $result['create_time'] = $row['create_time'];
            //總投注金額
            $result['bet'] = (float)bcadd($result['bet'], $row['total_p_value'], 2);
            $result['win'] = (float)bcadd($result['win'], $row['c_value'], 2);
            //寫入資料
            if ($row['bet_values'] == 0) {
                $result['pk']['banker']['bet'] = (float)$row['total_p_value'];
                $result['pk']['banker']['proportion'] = (float)bcdiv($row['total_p_value'], $special['banker_limit'], 2);
                //莊家
                if ($row['status'] == 1) {
                    $result['pk']['banker']['proportion_profit'] = (float)bcsub($row['c_value'], $row['total_p_value'], 2);
                    $result['pk']['banker']['win'] = (float)$row['c_value'];
                }
            } else {
                $result['pk']['player'][$row['bet_values']]['bet'][$double] = $row['p_value'];
                if ($row['status'] == 1) {
                    //還款
                    $result['pk']['player'][$row['bet_values']]['repayment'][$double] = $row['repayment'];
                    //賠付
                    $result['pk']['player'][$row['bet_values']]['win'][$double] = $row['c_value'];
                }
            }
            $status = $row['status'] > $status ? $row['status'] : $status;
        }

        //計算莊家總盈利
        $banker = $this->escape(false)->select('SUM(total_p_value - c_value) profit')->where([
            'special_id'   => $special_id,
            'qishu'        => $qishu,
            'money_type'   => $money_type,
            'bet_values >' => 0,
            'status'       => 1,
        ])->result_one();
        $result['pk']['banker']['profit'] = $banker['profit'] == 0 ? '--' : (float)$banker['profit'];

        $result['status'] = $status;
        $result['message'] = self::$statusList[$status];
        if ($status == 3) {
            $result['message'] = "该订单未接收成功，已退还本金";
            $result['numbers'] = [];
            $result['points'] = [];
            $result['win'] = 0;
            $result['pk']['banker']['point'] = '--';
            $result['pk']['banker']['proportion'] = '--';
            $result['pk']['banker']['profit'] = '--';
            $result['pk']['banker']['proportion_profit'] = '--';
            $result['pk']['banker']['win'] = '--';
            for ($i = 1; $i <= 5; $i++) {
                $result['pk']['player'][$i]['point'] = '--';
                $result['pk']['player'][$i]['is_win'] = 0;
                $result['pk']['player'][$i]['repayment'] = [0 => '--', 1 => '--',];
                $result['pk']['player'][$i]['win'] = [0 => '--', 1 => '--',];
            }
        }

        return $result;
    }

    /**
     * 取得輸贏
     */
    public function getProfit($uid = 0, $starttime = '', $endtime = '', $money_type = 0)
    {
        $where['status'] = 1;
        if ($uid != 0) {
            $where['uid'] = $uid;
        }
        if ($starttime != '') {
            $where['create_time1'] = $starttime;
        }
        if ($endtime != '') {
            $where['create_time2'] = $endtime;
        }
        $where['money_type'] = $money_type;

        $row = $this->escape(false)->select('SUM(c_value - total_p_value) win')->where($where)->result_one();

        return $row['win'] === null ? 0 : $row['win'];
    }

    /**
     * 計算有效投注額
     */
    public function getBetEffect($total_p_value, $c_value, $is_lose_win, $p_value)
    {
        return $is_lose_win == 0 ? $p_value : ($c_value - $total_p_value > $p_value ? $p_value : $c_value - $total_p_value);
    }

    public static $is_lose_winList = [
        0 => '输',
        1 => '贏',
        2 => '和',
    ];

    public static $is_code_amountList = [
        1 => '是',
        0 => '否',
    ];

    public static $statusList = [
        -1 => '处理中',
        0  => '待开奖',
        1  => '已开奖',
        2  => '已退款',
        3  => '下注失败',
    ];

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'        => '编号',
        'status'    => '状态',
        'is_delete' => '是否删除',
    ];
}
