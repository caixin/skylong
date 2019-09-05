<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_rakeback_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'user_rakeback';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'user_group_id', 'label' => '昵称', 'rules' => "trim|required"],
            ['field' => 'type', 'label' => '返水類型', 'rules' => "trim|required"],
            ['field' => 'category', 'label' => '玩法类别', 'rules' => "trim|required"],
            ['field' => 'lottery_type_id', 'label' => '彩种大类', 'rules' => "trim|required"],
            ['field' => 'lottery_id', 'label' => '彩種ID', 'rules' => "trim|required"],
            ['field' => 'rakeback_per', 'label' => '返水比率', 'rules' => "trim|required|min_length[0]"],
            ['field' => 'rakeback_max', 'label' => '返水上限', 'rules' => "trim|required|min_length[0]"],
            ['field' => 'start_money', 'label' => '起算金额', 'rules' => "trim|required|min_length[0]"],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            if ($this->_where['operator_id'] != 0) {
                $this->db->where('t.operator_id', $this->_where['operator_id']);
            }
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        if (isset($this->_where['user_group_id'])) {
            $this->db->where('t.user_group_id', $this->_where['user_group_id']);
            unset($this->_where['user_group_id']);
        }
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['category'])) {
            $this->db->where('t.category', $this->_where['category']);
            unset($this->_where['category']);
        }
        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('t.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        return $this;
    }

    /**
     * 用戶返水計算
     *
     * @return void
     */
    public function rakeback()
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        //防止重複返水
        $now_date = date("Y-m-d");
        $sysconfig = $this->sysconfig_db->where([
            'operator_id' => $this->operator_id,
            'varname'     => 'last_rakeback',
        ])->result_one();
        if ($now_date <= $sysconfig['value']) {
            Monolog::writeLogs('Rakeback', 200, "本日已执行!");
            return;
        }

        $rakeback = $this->getRakeback();
        if ($rakeback === []) {
            Monolog::writeLogs('Rakeback', 200, "无返水设定!");
            return;
        }

        $starttime = date('Y-m-d', strtotime($now_date) - 86400) . ' 00:00:00';
        $endtime = date('Y-m-d', strtotime($now_date) - 86400) . ' 23:59:59';
        //取得當日注單
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'ettm_lottery t2', 't.lottery_id = t2.id', 'left'];
        $where = [
            'operator_id'      => $this->operator_id,
            't.status'         => 1,
            't.create_time >=' => $starttime,
            't.create_time <=' => $endtime,
        ];
        $classic = $this->ettm_classic_bet_record_db->select('t.uid,t1.user_group_id,1 category,t2.lottery_type_id,t.lottery_id,t.c_value,t.total_p_value,t.is_lose_win')
            ->where($where)->join($join)->get_compiled_select();
        $official = $this->ettm_official_bet_record_db->select('t.uid,t1.user_group_id,2 category,t2.lottery_type_id,t.lottery_id,t.c_value,t.total_p_value,t.is_lose_win')
            ->where($where)->join($join)->get_compiled_select();
        $where['t.money_type'] = 0;
        $special = $this->ettm_special_bet_record_db->select('t.uid,t1.user_group_id,3 category,t2.lottery_type_id,t.lottery_id,t.c_value,t.total_p_value,t.is_lose_win')
            ->where($where)->join($join)->get_compiled_select();
        $sql = "$classic UNION ALL $official UNION ALL $special ORDER BY category DESC,lottery_type_id DESC,lottery_id DESC";
        $bet = $this->query($sql)->result_array();
        $data1 = $data2 = [];
        foreach ($bet as $row) {
            $bet_eff = 0;
            switch ($row['category']) {
                case 1:
                    $bet_eff = $this->ettm_classic_bet_record_db->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win']);
                    break;
                case 2:
                    $bet_eff = $this->ettm_official_bet_record_db->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win']);
                    break;
                case 3:
                    $bet_eff = $this->ettm_special_bet_record_db->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win'], $row['p_value']);
                    break;
            }
            $profit = bcsub($row['c_value'], $row['total_p_value'], 2);

            $state = [];
            //經典采種、官方采種、特色棋牌
            $state[] = $row['user_group_id'] . '-' . $row['category'] . '-' . $row['lottery_type_id'] . '-' . $row['lottery_id'];
            $state[] = $row['user_group_id'] . '-' . $row['category'] . '-' . $row['lottery_type_id'] . '-0';
            $state[] = $row['user_group_id'] . '-' . $row['category'] . '-0-0';
            //全部
            $state[] = $row['user_group_id'] . '-0-0-0';

            //歸類用户的有效投注额
            foreach ($state as $val) {
                if (isset($rakeback[0][$val])) {
                    $data1[$val][$row['uid']] = isset($data1[$val][$row['uid']]) ? bcadd($data1[$val][$row['uid']], $bet_eff, 2) : $bet_eff;
                    break;
                }
            }

            //歸類用户的負盈利
            foreach ($state as $val) {
                if (isset($rakeback[1][$val])) {
                    $data2[$val][$row['uid']] = isset($data2[$val][$row['uid']]) ? bcadd($data2[$val][$row['uid']], $profit, 2) : $profit;
                    break;
                }
            }
        }

        $lottery_type = $this->ettm_lottery_type_db->getTypeList();
        $lottery = $this->ettm_lottery_db->getLotteryList();
        $lottery_type[0] = '全部';
        $lottery[0] = '全部';

        //返水事務
        $this->trans_start();
        if ($data1 !== []) {
            foreach ($data1 as $state => $uid_array) {
                foreach ($uid_array as $uid => $amount) {
                    if ($amount >= min(array_keys($rakeback[0][$state]))) {
                        foreach ($rakeback[0][$state] as $k => $arr) {
                            if ($amount >= $k) {
                                $money = bcmul($amount, bcdiv($arr['rakeback_per'], 100, 4), 2);
                                $money = $money > $arr['rakeback_max'] ? $arr['rakeback_max'] : $money;
                                //帳變明細
                                $description = '';
                                switch ($arr['category']) {
                                    case 0:
                                        $description = '全部';
                                        break;
                                    case 1:
                                        $description = '经典-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                    case 2:
                                        $description = '官方-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                    case 3:
                                        $description = '特色-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                }
                                $this->user_db->addMoney($uid, create_order_sn('TF'), 4, $money, "$description-投注返水");
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($data2 !== []) {
            foreach ($data2 as $state => $uid_array) {
                foreach ($uid_array as $uid => $amount) {
                    if ($amount < 0 && abs($amount) >= min(array_keys($rakeback[1][$state]))) {
                        foreach ($rakeback[1][$state] as $k => $arr) {
                            if (abs($amount) >= $k) {
                                $money = bcmul(abs($amount), bcdiv($arr['rakeback_per'], 100, 4), 2);
                                $money = $money > $arr['rakeback_max'] ? $arr['rakeback_max'] : $money;
                                //帳變明細
                                $description = '';
                                switch ($arr['category']) {
                                    case 0:
                                        $description = '全部';
                                        break;
                                    case 1:
                                        $description = '经典-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                    case 2:
                                        $description = '官方-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                    case 3:
                                        $description = '特色-' . $lottery_type[$arr['lottery_type_id']] . '-' . $lottery[$arr['lottery_id']];
                                        break;
                                }
                                $this->user_db->addMoney($uid, create_order_sn('TF'), 4, $money, "$description-负盈利返水");
                                break;
                            }
                        }
                    }
                }
            }
        }
        $this->sysconfig_db->update([
            'id'    => $sysconfig['id'],
            'value' => $now_date,
        ]);
        $this->trans_complete();
    }

    /**
     * 取得返水設置
     *
     * @return array 返水設置
     */
    public function getRakeback()
    {
        if ($this->operator_id > 0) {
            $this->where(['operator_id'=>$this->operator_id]);
        }
        $result = $this->order([
            'user_group_id'   => 'asc',
            'type'            => 'asc',
            'category'        => 'desc',
            'lottery_type_id' => 'desc',
            'lottery_id'      => 'desc',
            'start_money'     => 'desc',
        ])->result();

        $rakeback = [];
        foreach ($result as $row) {
            $rakeback[$row['type']]["$row[user_group_id]-$row[category]-$row[lottery_type_id]-$row[lottery_id]"][(int)$row['start_money']] = $row;
        }
        return $rakeback;
    }

    public static $typeList = [
        0 => '投注返水',
        1 => '负盈利返水',
    ];

    public static $categoryList = [
        0 => '全部',
        1 => '经典彩',
        2 => '官方彩',
        3 => '特色棋牌',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'user_group_id'   => '会员分层',
        'type'            => '返水類型',
        'category'        => '玩法类别',
        'lottery_type_id' => '彩种大类',
        'lottery_id'      => '彩种ID',
        'rakeback_per'    => '返水比例',
        'rakeback_max'    => '返水上限',
        'start_money'     => '起算金额',
    ];
}
