<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge_order_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'recharge_order';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'status', 'label' => '状态', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        $date = date('Y-m-d');
        //累計充值金額
        if (!isset($row['grand_total'])) {
            $data = $this->escape(false)->where([
                't.uid'  => $row['uid'],
                'status' => 1,
            ])->select('SUM(t.money) money')->result_one();
            $row['grand_total'] = $data['money'] === null ? 0 : $data['money'];
        }
        //當日充值金額
        if (!isset($row['today_total'])) {
            $data = $this->escape(false)->where([
                't.uid'        => $row['uid'],
                'status'       => 1,
                'create_time1' => $date,
                'create_time2' => $date,
            ])->select('SUM(t.money) money')->result_one();
            $row['today_total'] = $data['money'] === null ? 0 : $data['money'];
        }

        return parent::insert($row, $return_string);
    }

    public function _do_where()
    {
        if (isset($this->_where['sidebar'])) {
            unset($this->_where['sidebar']);
        }
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
        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t1.agent_id', $this->session->userdata('id'));
        }
        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['mobile'])) {
            $this->db->where('t1.mobile', $this->_where['mobile']);
            unset($this->_where['mobile']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }

        if (isset($this->_where['order_sn'])) {
            $this->db->where('t.order_sn', $this->_where['order_sn']);
            unset($this->_where['order_sn']);
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

        if (isset($this->_where['check_time1'])) {
            $this->db->where('t.check_time >=', $this->_where['check_time1'] . ' 00:00:00');
            unset($this->_where['check_time1']);
        }
        if (isset($this->_where['check_time2'])) {
            $this->db->where('t.check_time <=', $this->_where['check_time2'] . ' 23:59:59');
            unset($this->_where['check_time2']);
        }
        if (isset($this->_where['channel'])) {
            $this->db->where('t.offline_channel ', $this->_where['channel']);
            unset($this->_where['channel']);
        }
        return $this;
    }

    /**
     * 訂單完成-進行充值
     * @param int $id 訂單ID
     * @param int $type 充值類型 1:線上 2:線下
     */
    public function orderSuccess($id, $type = 1)
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->load->model('prediction_relief_model', 'prediction_relief_db');
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        if ($type == 1) {
            $join[] = [$this->table_ . 'recharge_online t2', 't.line_id = t2.id', 'left'];
        } else {
            $join[] = [$this->table_ . 'recharge_offline t2', 't.line_id = t2.id', 'left'];
        }
        $data = $this->select('t.*,t2.handsel_percent,t2.handsel_max,t2.multiple')
            ->join($join)->where(['t.id' => $id])->result_one();
        //充值用戶Money
        $this->user_db->addMoney($data['uid'], $data['order_sn'], 0, $data['money'], '充值');
        //充值打碼
        $this->code_amount_db->insert([
            'uid'              => $data['uid'],
            'money_type'       => 0,
            'type'             => 1,
            'related_id'       => $id,
            'money'            => $data['money'],
            'multiple'         => $data['multiple'],
            'code_amount_need' => bcmul($data['money'], $data['multiple'], 2),
            'description'      => '充值打码',
        ]);
        //充值救濟金
        $this->prediction_relief_db->recharge($data['uid'], $id, $data['money']);
        //充值贈送彩金
        $handsel = bcmul($data['money'], bcdiv($data['handsel_percent'], 100, 4), 2);
        $handsel = $handsel > $data['handsel_max'] ? $data['handsel_max'] : $handsel;
        if ($handsel > 0) {
            $this->user_db->addMoney($data['uid'], $data['order_sn'], 7, $handsel, '充值赠送彩金');
            //充值打碼
            $this->code_amount_db->insert([
                'uid'              => $data['uid'],
                'money_type'       => 0,
                'type'             => 2,
                'related_id'       => $id,
                'money'            => $handsel,
                'multiple'         => $data['multiple'],
                'code_amount_need' => bcmul($handsel, $data['multiple'], 2),
                'description'      => '充值赠送彩金打码',
            ]);
        }
        //標記充值用戶
        $user = $this->user_db->row($data['uid']);
        $update['id'] = $user['id'];
        if (($user['mode'] & 1) == 0) { //首充
            $update['mode'] = $user['mode'] + 1;
            $update['first_recharge'] = date('Y-m-d H:i:s');
            $update['first_money'] = $data['money'];
        } elseif (($user['mode'] & 2) == 0) { //二充
            $update['mode'] = $user['mode'] + 2;
            $update['second_recharge'] = date('Y-m-d H:i:s');
        }
        $this->user_db->update($update);
    }

    /**
     * 會員充值
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param int $type 類型 1:線上 2:線下
     */
    public function getRechargeUser($starttime, $endtime, $type = 0)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $where = [
            't1.type'      => 0,
            't1.status'    => 0,
            'check_time1' => $starttime,
            'check_time2' => $endtime,
            't.status'     => 1,
        ];
        if ($type > 0) {
            $where['t.type'] = $type;
        }
        $result = $this->escape(false)->select('t.uid,SUM(t.money) amount')
            ->where($where)->join($join)->group('t.uid')->result();

        return array_column($result, 'amount', 'uid');
    }

    /**
     * 代理會員充值
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getAgentRechargeUser($starttime, $endtime)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t.uid,t1.agent_id,t1.agent_code,SUM(t.money) money')->where([
            't1.type'          => 0,
            't1.status'        => 0,
            "t1.agent_code <>" => '',
            'check_time1'      => $starttime,
            'check_time2'      => $endtime,
            't.status'         => 1,
        ])->join($join)->group('t.uid,t1.agent_id,t1.agent_code')->result();

        return $result;
    }

    /**
     * 取得各營運商充值總額
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getOperatorRecharge($starttime, $endtime)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t1.operator_id,SUM(t.money) money')->where([
            't1.type'     => 0,
            't1.status'   => 0,
            'check_time1' => $starttime,
            'check_time2' => $endtime,
            't.status'    => 1,
        ])->join($join)->group('t1.operator_id')->result();

        return array_column($result, 'money', 'operator_id');
    }

    public static $typeList = [
        1 => '线上充值',
        2 => '线下充值',
    ];

    public static $offline_pay_typeList = [
        1 => '网银转帐',
        2 => 'ATM自动柜员机',
        3 => '银行柜台',
        4 => '手机银行',
        5 => '其他',
    ];

    public static $statusList = [
        0 => '待审核',
        1 => '充值成功',
        2 => '充值失败',
        3 => '待付款',
    ];

    public static $statusColorList = [
        0 => '#00bfff',
        1 => 'green',
        2 => 'red',
        3 => '#00bfff',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'       => '编号',
        'status'   => '状态',
    ];
}
