<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_money_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'user_money_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'uid', 'label' => 'UID', 'rules' => "trim|required"],
        ];
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

        if (isset($this->_where['money_type'])) {
            $this->db->where('t.money_type', $this->_where['money_type']);
            unset($this->_where['money_type']);
        }

        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['order_sn'])) {
            $this->db->where('t.order_sn', $this->_where['order_sn']);
            unset($this->_where['order_sn']);
        }

        if (isset($this->_where['user_type'])) {
            $this->db->where('t1.type', $this->_where['user_type']);
            unset($this->_where['user_type']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }

        if (isset($this->_where['type_in'])) {
            $this->db->where_in('t.type', $this->_where['type_in']);
            unset($this->_where['type_in']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }
        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
        if (isset($this->_where['agent_name'])) {
            $this->db->where('t2.username', $this->_where['agent_name']);
            unset($this->_where['agent_name']);
        }
        return $this;
    }

    /**
     * 會員帳變資訊
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param array $type 帳變類型
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function getMoneyLogUser($starttime, $endtime, $type, $money_type = 0)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t.uid,SUM(t.money_add) amount')->where([
            't1.type'      => 0,
            't1.status'    => 0,
            'money_type'   => $money_type,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
            't.type'       => $type,
        ])->join($join)->group('t.uid')->result();

        return array_column($result, 'amount', 'uid');
    }

    /**
     * 代理會員帳變資訊-Group By AgentCode
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param array $type 帳變類型
     * @param int $category 玩法類別
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function getAgentMoneyByAgentCode($starttime, $endtime, $type, $category = 0, $money_type = 0)
    {
        $where = [
            't1.type'      => 0,
            't1.status'    => 0,
            'money_type'   => $money_type,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
            't.type'       => $type,
        ];
        if ($category > 0) {
            $where['t.category'] = $category;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t1.agent_code,SUM(t.money_add) money')
            ->where($where)->join($join)->group('t1.agent_code')->result();

        return array_column($result, 'money', 'agent_code');
    }

    /**
     * 取得各營運商會員帳變總額
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param array $type 帳變類型
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function getOperatorMoneyLog($starttime, $endtime, $type, $money_type = 0)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t1.operator_id,SUM(t.money_add) amount')->where([
            't1.type'      => 0,
            't1.status'    => 0,
            'money_type'   => $money_type,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
            't.type'       => $type,
        ])->join($join)->group('t1.operator_id')->result();

        return array_column($result, 'amount', 'operator_id');
    }

    public static $typeList = [
        0 => '充值',
        1 => '提现',
        2 => '人工入款',
        3 => '人工减款',
        4 => '反水',
        5 => '投注',
        6 => '经典派奖',
        7 => '充值彩金',
        8 => '人工彩金',
        9 => '下注退款',
        //10 => '牌九下注',
        //11 => '牌九派奖',
        12 => '官彩派奖',
        13 => '反点',
        14 => '还原注单',
        15 => '修改注單',
        16 => '特色棋牌赔付',
        //17 => '特色棋牌-牛牛返还预扣金',
        18 => '特色棋牌下注',
        19 => '代理返点',
        20 => '预测购买',
        21 => '预测退款',
    ];
}
