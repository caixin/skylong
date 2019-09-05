<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_official_bet_record_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'ettm_official_bet_record';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required'],
            ['field' => 'qishu', 'label' => '期数', 'rules' => 'trim|required'],
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
                if (strpos($arr[0], $this->table_.'user ') !== false) {
                    $table = trim(str_replace($this->table_.'user ', '', $arr[0]));
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
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
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
        $result = $this->select('t.id,t.uid,t.c_value,t.return_money,t.status')->join($join)->where($where)->result();
        $user = $update = [];
        foreach ($result as $row) {
            //如果有賠付金額要扣回來
            if ($row['c_value'] > 0) {
                $user[$row['uid']] = isset($user[$row['uid']]) ? bcadd($user[$row['uid']], $row['c_value'], 2) : $row['c_value'];
            }
            //如果有返利金額要扣回來
            if ($row['return_money'] > 0) {
                $user[$row['uid']] = isset($user[$row['uid']]) ? bcadd($user[$row['uid']], $row['return_money'], 2) : $row['return_money'];
            }
            $update[] = [
                'id'           => $row['id'],
                'c_value'      => 0,
                'return_money' => 0,
                'is_lose_win'  => 0,
                'status'       => $row['status'] == 3 ? -1 : 0,   //非常規退款還原成處理中
            ];
            //還原打碼量
            if ($row['status'] == 1) {
                $this->code_amount_db->restoreBetEffect(2, $row['id']);
            }
        }

        if ($update !== []) {
            foreach ($user as $uid => $money) {
                $this->user_db->addMoney($uid, $qishu, 14, $money * -1, "还原注单扣款", 2, $lottery_id);
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

        $join = [];
        $where = [
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu,
            't.status <='  => 1,
        ];
        if ($uid > 0) {
            $where['t.uid'] = $uid;
        }
        if ($this->operator_id > 0) { //for各運營
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $where['operator_id'] = $this->operator_id;
        }
        $result = $this->select('t.id,t.uid,t.total_p_value,t.c_value,t.return_money,t.status')
                    ->join($join)->where($where)->result();
        $update = $uids = [];
        foreach ($result as $row) {
            //退款
            $money = (float)bcsub($row['total_p_value'], bcadd($row['c_value'], $row['return_money'], 2), 2);
            $uids[$row['uid']] = isset($uids[$row['uid']]) ? bcadd($uids[$row['uid']], $money, 2) : $money;
            $update[] = [
                'id'           => $row['id'],
                'c_value'      => $row['total_p_value'],
                'return_money' => 0,
                'status'       => $row['status'] == -1 ? 3 : 2,   //非常規退款狀態為3
            ];
            //還原打碼量
            if ($row['status'] == 1) {
                $this->code_amount_db->restoreBetEffect(2, $row['id']);
            }
        }

        if ($update !== []) {
            foreach ($uids as $uid => $money) {
                $this->user_db->addMoney($uid, $qishu, 9, $money, "$lottery[name]官方下注退款", 2, $lottery_id);
            }
            $this->update_batch($update, 'id');
            //將標記用戶還原
            $this->user_db->where([
                'ids'    => array_keys($uids),
                'status' => 3
            ])->update_where([
                'status'      => 0,
                'unlock_time' => date('Y-m-d H:i:s'),
            ]);
        }
        return array_column($update, 'id');
    }

    /**
     * 取得輸贏
     */
    public function getProfit($uid = 0, $starttime = '', $endtime = '')
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

        $row = $this->escape(false)->select('SUM(c_value + return_money - total_p_value) win')->where($where)->result_one();

        return $row['win'] === null ? 0 : $row['win'];
    }

    /**
     * 計算有效投注額
     */
    public function getBetEffect($total_p_value, $c_value, $is_lose_win)
    {
        //return $is_lose_win == 0 ? $total_p_value : ($c_value - $total_p_value > $total_p_value ? $total_p_value : $c_value - $total_p_value);
        //官方彩有效投注修改為投注金額
        return $total_p_value;
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
