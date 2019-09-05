<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_withdraw_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'user_withdraw';
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
        //累計提現金額
        $data = $this->escape(false)->where([
            't.uid' => $row['uid'],
            'status' => 1,
        ])->select('SUM(t.money) money')->result_one();
        $row['grand_total'] = $data['money'] === null ? 0 : $data['money'];
        //當日提現金額
        $data = $this->escape(false)->where([
            't.uid' => $row['uid'],
            'status' => 1,
            'create_time1' => $date,
            'create_time2' => $date,
        ])->select('SUM(t.money) money')->result_one();
        $row['today_total'] = $data['money'] === null ? 0 : $data['money'];

        return parent::insert($row, $return_string);
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
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
        return $this;
    }

    /**
     * 會員提現
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getWithdrawUser($starttime, $endtime)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t.uid,SUM(t.money) amount')->where([
            'check_time1' => $starttime,
            'check_time2' => $endtime,
            't.status'        => 1,
            't1.type'         => 0,
            't1.status'       => 0,
        ])->join($join)->group('t.uid')->result();

        return array_column($result, 'amount', 'uid');
    }

    /**
     * 代理會員提現
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getAgentWithdrawUser($starttime, $endtime)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t.uid,t1.agent_id,t1.agent_code,SUM(t.money) money')->where([
            't1.type'          => 0,
            "t1.agent_code <>" => '',
            'create_time1'     => $starttime,
            'create_time2'     => $endtime,
            't.status'         => 1,
        ])->join($join)->group('t.uid,t1.agent_id,t1.agent_code')->result();

        return $result;
    }

    /**
     * 取得各營運商提現總額
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getOperatorWithdraw($starttime, $endtime)
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

    public static $statusList = [
        0 => '待审核',
        1 => '提现成功',
        2 => '提现失败',
    ];

    public static $statusColorList = [
        0 => '#00bfff',
        1 => 'green',
        2 => 'red',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'            => '编号',
        'status'        => '状态',
        'check_remarks' => '审核备注',
        'check_time'    => '审核时间',
        'check_by'      => '审核者',
    ];
}
