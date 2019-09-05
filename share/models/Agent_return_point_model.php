<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent_return_point_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'agent_return_point';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'uid', 'label' => '用户ID', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where_in('t1.operator_id', [0,$this->_where['operator_id']]);
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
        
        if (isset($this->_where['uid'])) {
            $this->db->where('t.uid', $this->_where['uid']);
            unset($this->_where['uid']);
        }

        if (isset($this->_where['from_uid'])) {
            $this->db->where('t.from_uid', $this->_where['from_uid']);
            unset($this->_where['from_uid']);
        }

        if (isset($this->_where['category'])) {
            $this->db->where('t.category', $this->_where['category']);
            unset($this->_where['category']);
        }

        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }

        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
        return $this;
    }

    /**
     *  結算返點
     * @param int $category 玩法類別
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     */
    public function settlement($category, $lottery_id, $qishu)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('user_model', 'user_db');
        $this->load->model('user_rakeback_model', 'user_rakeback_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        switch ($category) {
            case 1:
                $bet_db = 'ettm_classic_bet_record_db';
                break;
            case 2:
                $bet_db = 'ettm_official_bet_record_db';
                break;
        }
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        $return_point_dir = $lottery['key_word'] . 'ReturnPoint';
        //判斷是否已執行過
        $count = $this->where([
            'category'   => $category,
            'lottery_id' => $lottery_id,
            'qishu'      => $qishu,
        ])->count();
        if ($count > 0) {
            Monolog::writeLogs($return_point_dir, 200, '---- 本期已执行过 ----');
            return -1;
        }

        //取得玩家返水資料
        $rakeback = $this->user_rakeback_db->getRakeback();

        //取出該期注單
        $where = [
            't.lottery_id'  => $lottery_id,
            't.qishu'       => $qishu,
        ];
        if ($category == 1) {
            $where['t.is_lose_win <'] = 2;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $bet = $this->$bet_db->escape(false)->select('t.uid,SUM(t.total_p_value) total_p_value,t1.user_group_id')
            ->where($where)->join($join)->group('t.uid')->result();
        if ($bet === []) {
            Monolog::writeLogs($return_point_dir, 200, '---- 无下注资料 ----');
            return -2;
        }

        $insert = $user = [];
        $date = date('Y-m-d H:i:s');
        foreach ($bet as $row) {
            $state = [];
            $state[] = $row['user_group_id'] . '-' . $category . '-' . $lottery['lottery_type_id'] . '-' . $lottery_id;
            $state[] = $row['user_group_id'] . '-' . $category . '-' . $lottery['lottery_type_id'] . '-0';
            $state[] = $row['user_group_id'] . '-' . $category . '-0-0';
            $state[] = $row['user_group_id'] . '-0-0-0';

            $user_rakeback = 0;
            //加入投注返水%數
            foreach ($state as $state_val) {
                if (isset($rakeback[0][$state_val])) {
                    foreach ($rakeback[0][$state_val] as $start_money => $val) {
                        if ($row['total_p_value'] > $start_money) {
                            $user_rakeback = bcadd($user_rakeback, $val['rakeback_per'], 2);
                            break;
                        }
                    }
                    break;
                }
            }

            $up_data = $this->agent_code_db->getUpReturnPoint($row['uid'], $lottery_id);
            $i = 1;
            foreach ($up_data as $uid => $return_point) {
                if ($i == count($up_data)) {
                    $return_point = bcsub($return_point, $user_rakeback, 2);
                    $return_point = $return_point < 0 ? 0 : $return_point;
                }
                $amount = bcdiv(bcmul($row['total_p_value'], $return_point, 4), 100, 2);
                $insert[] = [
                    'uid'         => $uid,
                    'from_uid'    => $row['uid'],
                    'category'    => $category,
                    'lottery_id'  => $lottery_id,
                    'qishu'       => $qishu,
                    'amount'      => $amount,
                    'create_time' => $date,
                    'create_by'   => 'OpenAction',
                ];
                $user[$uid] = isset($user[$uid]) ? bcadd($user[$uid], $amount, 2) : $amount;
                $i++;
            }
        }

        Monolog::writeLogs($return_point_dir, 200, $user);
        if ($insert != []) {
            $this->trans_start();
            $this->insert_batch($insert);
            foreach ($user as $uid => $amount) {
                $description = self::$categoryList[$category] . "-$lottery[name]-代理返點";
                $this->user_db->addMoney($uid, $qishu, 19, $amount, $description, $category, $lottery_id);
            }
            $this->trans_complete();
        }
        return 1;
    }

    /**
     * 查詢返點明細
     * @param int $uid 用戶ID
     */
    public function getReturnPointDetail($uid)
    {
        $join[] = [$this->table_ . 'user t1', 't.from_uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'ettm_lottery t2', 't.lottery_id = t2.id', 'left'];
        $result = $this->escape(false)->select('t1.user_name,t.from_uid uid,t2.name lottery_name,SUM(t.amount) amount')
            ->where(['t.uid' => $uid])->join($join)->group('from_uid,lottery_id')->result();

        return $result;
    }

    /**
     * 取得時間內返點總額
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getReturnPointUser($starttime, $endtime)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t.uid,SUM(t.amount) amount')->where([
            't1.type'      => 0,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
        ])->join($join)->group('t.uid')->result();

        return array_column($result, 'amount', 'uid');
    }

    /**
     * 下級返點統計
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     */
    public function getReturnPointStats($starttime, $endtime, $uid, $from_uids)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select("IFNULL(SUM(t.amount),0) amount")->where([
            't1.type'      => 0,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
            't.uid'        => $uid,
            't.from_uid'   => $from_uids,
        ])->join($join)->result_one();

        return $result['amount'];
    }

    public static $categoryList = [
        1 => '经典',
        2 => '官方',
    ];

    public static $typeList = [
        1 => '代理',
        2 => '玩家',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'uid'        => '用户ID',
        'from_uid'   => '来源用户ID',
        'category'   => '类别',
        'lottery_id' => '彩种ID',
        'qishu'      => '期数',
    ];
}
