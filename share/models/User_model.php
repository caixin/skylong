<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'user';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名', 'rules' => "trim|required|min_length[4]|max_length[11]|is_unique[$this->_table_name.user_name]"],
            ['field' => 'user_pwd', 'label' => '用户密码', 'rules' => "trim|required|min_length[6]|max_length[12]"],
            ['field' => 'security_pwd', 'label' => '取款密码', 'rules' => "trim|required|min_length[6]|max_length[12]"],
            ['field' => 'real_name', 'label' => '真实姓名', 'rules' => "trim|required"],
            ['field' => 'mobile', 'label' => '手机号码', 'rules' => "trim|required|min_length[11]|max_length[11]|is_unique[$this->_table_name.mobile]"],
            ['field' => 'agent_code', 'label' => '代理邀请码', 'rules' => "trim|callback_agent_code_check"],
        ];
    }

    public function register_rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名', 'rules' => "trim|required|min_length[4]|max_length[11]|is_unique[$this->_table_name.user_name]"],
            ['field' => 'user_pwd', 'label' => '用户密码', 'rules' => "trim|required|min_length[6]|max_length[12]"],
            ['field' => 'security_pwd', 'label' => '取款密码', 'rules' => "trim|required|min_length[6]|max_length[12]"],
            ['field' => 'real_name', 'label' => '真实姓名', 'rules' => "trim|required"],
            ['field' => 'mobile', 'label' => '手机号码', 'rules' => "trim|required|min_length[11]|max_length[11]|is_unique[$this->_table_name.mobile]"],
        ];
    }

    public function login_rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名', 'rules' => "trim|required|min_length[4]|max_length[11]"],
            ['field' => 'user_pwd', 'label' => '用户密码', 'rules' => "trim|required|min_length[6]|max_length[12]"],
        ];
    }

    public function edit_rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名', 'rules' => "trim|required|min_length[4]|max_length[11]"],
            ['field' => 'real_name', 'label' => '真实姓名', 'rules' => "trim|required"],
            ['field' => 'mobile', 'label' => '手机号码', 'rules' => "trim|required|min_length[11]|max_length[11]"],
            ['field' => 'agent_code', 'label' => '代理邀请码', 'rules' => "trim|callback_agent_code_check"],
        ];
    }

    public function edit_money_rules()
    {
        return [
            ['field' => 'add_money', 'label' => '操作金额', 'rules' => "trim|required"],
            ['field' => 'multiple', 'label' => '打码量倍数', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        $row['referrer_code'] = $this->getInvitationCode();
        if (isset($row['user_pwd'])) {
            if ($row['user_pwd'] != '') {
                $row['user_pwd'] = userPwdEncode($row['user_pwd']);
            } else {
                unset($row['user_pwd']);
            }
        }
        if (isset($row['security_pwd'])) {
            if ($row['security_pwd'] != '') {
                $row['security_pwd'] = userPwdEncode($row['security_pwd']);
            } else {
                unset($row['security_pwd']);
            }
        }
        $this->load->model('ip2location_model', 'ip2location_db');
        $ip = $this->input->ip_address();
        $ip_info = $this->ip2location_db->getIpData($ip);
        $ip_info = $ip_info === null ? [] : $ip_info;
        $row['create_ip'] = $ip;
        $row['create_ip_info'] = json_encode($ip_info);
        $row['create_ua'] = $this->input->user_agent();
        $row['create_domain'] = $this->input->server('SERVER_NAME');
        $row['session'] = $this->getSession(0, $this->input->ip_address());

        if (!isset($row['operator_id'])) {
            $row['operator_id'] = isset($this->operator_id) ? $this->operator_id : 1;
        }

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        $data = $this->row($row['id']);
        if (isset($row['user_pwd'])) {
            if ($row['user_pwd'] != '') {
                $row['user_pwd'] = userPwdEncode($row['user_pwd']);
            } else {
                unset($row['user_pwd']);
            }
        }
        if (isset($row['security_pwd'])) {
            if ($row['security_pwd'] != '') {
                $row['security_pwd'] = userPwdEncode($row['security_pwd']);
            } else {
                unset($row['security_pwd']);
            }
        }

        $num = parent::update($row, $return_string);
        //如果邀請碼變更而更換代理 則下級也跟著變更代理ID
        if (isset($row['agent_id']) && $data['agent_id'] != $row['agent_id']) {
            $uids = $this->getAgentAllSubUID($row['id']);
            $this->where(['ids'=>$uids])->update_where(['agent_id'=>$row['agent_id']]);
        }

        return $num;
    }

    //查詢
    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where_in('t.operator_id', [0,$this->_where['operator_id']]);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t.agent_id', $this->session->userdata('id'));
        }

        if (isset($this->_where['ids'])) {
            $this->db->where_in('t.id', $this->_where['ids']);
            unset($this->_where['ids']);
        }

        if (isset($this->_where['user_name'])) {
            $this->db->like('t.user_name', $this->_where['user_name'], 'both');
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['mobile'])) {
            $this->db->where('t.mobile', $this->_where['mobile']);
            unset($this->_where['mobile']);
        }

        if (isset($this->_where['referrer_code'])) {
            $this->db->where('t.referrer_code', $this->_where['referrer_code']);
            unset($this->_where['referrer_code']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }

        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }

        if (isset($this->_where['mode'])) {
            $this->db->where('t.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }

        if (isset($this->_where['user_group_id'])) {
            $this->db->where('t.user_group_id', $this->_where['user_group_id']);
            unset($this->_where['user_group_id']);
        }

        if (isset($this->_where['last_active_time1'])) {
            $this->db->where('t.last_active_time >=', $this->_where['last_active_time1']);
            unset($this->_where['last_active_time1']);
        }

        if (isset($this->_where['last_active_time2'])) {
            $this->db->where('t.last_active_time <=', $this->_where['last_active_time2']);
            unset($this->_where['last_active_time2']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }

        if (isset($this->_where['vip_info_ios'])) {
            $this->db->group_start();
            if ($this->_where['vip_info_ios'] == '1') {
                $this->db->where('t.vip_info_ios !=', '');
                $this->db->where('t.vip_info_ios !=', null);
            } else {
                $this->db->where('t.vip_info_ios =', '');
                $this->db->or_where('t.vip_info_ios =', null);
            }
            $this->db->group_end();
            unset($this->_where['vip_info_ios']);
        }

        return $this;
    }

    /**
     * 變動餘額+LOG
     * @param int $uid 用戶ID
     * @param string $order_sn 訂單號
     * @param int $type 帳變類別
     * @param float $add_money 帳變金額
     * @param string $description 描述
     * @param int $category 分類 1:經典 2:官方
     * @param int $lottery_id 彩種ID
     * @param int $related_id 關聯ID
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     * @return void
     */
    public function addMoney($uid, $order_sn, $type, $add_money, $description, $category = 0, $lottery_id = 0, $related_id = 0, $money_type = 0)
    {
        $user = $this->row($uid);
        //找不到帳號則跳出
        if ($user === null) {
            return;
        }
        //帳變金額為0則不動作
        if ((float) $add_money == 0) {
            return;
        }
        //機器人不寫入
        if ($user['type'] < 0) {
            return;
        }
        $money_column = 'money';
        if ($money_type != 0) {
            $money_column = 'money' . $money_type;
        }

        $update['id'] = $uid;
        $update[$money_column] = $user[$money_column] + $add_money;
        //提現-寫入凍結金幣 審核後扣除
        if ($type == 1 && $money_type == 0) {
            $update['money_frozen'] = $user['money_frozen'] - $add_money;
        }
        //計算輸贏
        if (in_array($type, [0,1,2,3])) {
            $update['profit'] = $user['profit'] + $add_money;
        }

        $this->trans_start();
        $this->update($update);

        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->user_money_log_db->insert([
            'uid'          => $uid,
            'type'         => $type,
            'money_type'   => $money_type,
            'order_sn'     => $order_sn,
            'category'     => $category,
            'lottery_id'   => $lottery_id,
            'related_id'   => $related_id,
            'money_before' => $user[$money_column],
            'money_add'    => $add_money,
            'money_after'  => $update[$money_column],
            'description'  => $description,
        ]);
        $this->trans_complete();
    }

    /**
     * 用戶登入
     */
    public function userLogin($params)
    {
        $now = date('Y-m-d H:i:s');
        $ip = $this->input->ip_address();
        //驗證帳密
        $row = $this->where([
            't.user_name' => $params['user_name'],
            't.user_pwd'  => userPwdEncode($params['user_pwd']),
        ])->result_one();

        if ($row === null) {
            return [
                'status'  => 0,
                'code'    => 400,
                'message' => '用户名或密码错误！'
            ];
        }

        if ($row['status'] == 1) {
            return [
                'status'  => 0,
                'code'    => 451,
                'message' => '账号已封号，请联系客服！'
            ];
        }

        //在線飛踢
        if ($row['unlock_time'] > date('Y-m-d H:i:s')) {
            return [
                'status'  => 0,
                'code'    => 400,
                'message' => '登录请求失败, 请您稍后再试',
            ];
        }

        $this->trans_start();
        //還原非常規退款標記後無作退款之清除標記
        if ($row['status'] == 3) {
            $this->update([
                'id'     => $row['id'],
                'status' => 0,
            ]);
        }
        $session = $this->getSession($row['id'], $ip);
        //更新使用者資訊
        $this->update([
            'id'               => $row['id'],
            'session'          => $session,
            'last_login_ip'    => $ip,
            'last_login_time'  => $now,
            'last_active_time' => $now,
        ]);

        //寫入Login Log
        $this->load->model('user_login_log_model', 'user_login_log_db');
        $this->user_login_log_db->insert([
            'uid'        => $row['id'],
            'source_url' => base_url($_SERVER['REQUEST_URI']),
            'domain_url' => base_url(),
        ]);
        $this->trans_complete();

        $this->input->set_cookie('cookie', $session, 86400 * 3);
        return [
            'status'  => 1,
            'code'    => 200,
            'message' => '登录成功',
            'data'    => $this->getProfile($row['id']),
        ];
    }

    /**
     * 取得用戶資訊
     */
    public function getProfile($id)
    {
        $this->load->model('user_bank_model', 'user_bank_db');
        $this->load->model('user_group_model', 'user_group_db');
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');

        $join[] = [$this->table_ . 'agent_code t1', 't.agent_code = t1.code', 'left'];
        $row = $this->select('t.id uid,user_name,real_name,mobile,money,money1,t.type,user_group_id,t.agent_id,t.agent_code,t.status,t.mode,t1.type code_type,t1.level')
            ->join($join)->where(['t.id' => $id])->result_one();
        $row['money']         = sprintf("%.2f", $row['money']);
        $row['money1']        = sprintf("%.2f", $row['money1']);
        $row['type']          = (int)$row['type'];
        $row['status']        = (int)$row['status'];
        $row['user_group_id'] = (int)$row['user_group_id'];
        $row['agent_id']      = (int)$row['agent_id'];
        $row['code_type']     = (int)$row['code_type'];
        $row['level']         = (int)$row['level'];
        $row['show_bet_hot']  = ($row['mode'] & 8) == 0;
        //綁定銀行卡
        $row['bank_account'] = '';
        $row['bank_name']    = '';
        $row['is_bind_bank'] = 0;
        $bank = $this->user_bank_db->where(['uid'=>$id])->result_one();
        if ($bank !== null) {
            $row['bank_account'] = $bank['bank_account'];
            $row['bank_name']    = $bank['bank_name'];
            $row['is_bind_bank'] = 1;
        }
        //最小提現金額
        $group = $this->user_group_db->row($row['user_group_id']);
        $row['min_extract_money'] = (int)$group['min_extract_money'];
        $row['max_extract_money'] = (int)$group['max_extract_money'];
        //額外資訊
        $row['uid'] = auth_code($row['uid'], 'ENCODE');
        $row['money_get'] = $this->code_amount_db->getNeedByUid($id); //所需打碼量
        $row['allow_agent'] = $row['agent_id'] != 0 && ($row['agent_code'] == '' || $row['code_type'] == 1) ? 1 : 0; //是否有代理權限
        //今日輸贏
        $today = date('Y-m-d');
        $classic = $this->ettm_classic_bet_record_db->getProfit($id, $today, $today);
        $official = $this->ettm_official_bet_record_db->getProfit($id, $today, $today);
        $special = $this->ettm_special_bet_record_db->getProfit($id, $today, $today);
        $row['dayIncome'] = bcadd(bcadd($classic, $official, 2), $special, 2);
        //救濟金
        if (array_key_exists(1, $this->module)) {
            $this->load->model('prediction_relief_model', 'prediction_relief_db');
            $result = $this->prediction_relief_db->where([
                'uid'      => $id,
                'status <' => 3,
            ])->result();
            $row['relief'] = $row['activation_relief'] = 0;
            $row['is_relief'] = false;
            foreach ($result as $arr) {
                if (in_array($arr['status'], [0,1])) {
                    $row['relief'] += $arr['relief'];
                }
                if ($arr['status'] == 1) {
                    $row['activation_relief'] += $arr['relief'];
                }
                $row['is_relief'] = true;
            }
        }
        return $row;
    }

    /**
     * 取得Session
     */
    private function getSession($uid, $ip)
    {
        while (true) {
            $session = md5($uid . $ip . GetRandStr(6));
            $count = $this->where(['session' => $session])->count();
            if ($count == 0) {
                break;
            }
        }
        return $session;
    }

    /**
     * 生成邀請碼
     */
    public function getInvitationCode()
    {
        while (true) {
            $code = GetRandStr(6);
            $count = $this->where(['referrer_code' => $code])->count();
            if ($count == 0) {
                break;
            }
        }
        return $code;
    }

    /**
     * 取得下級所有UID
     */
    public function getAgentAllSubUID($uid, $uids = [], $starttime = '', $endtime = '')
    {
        $uids[] = $uid;
        $where['type'] = 0;
        $where['agent_pid'] = $uid;
        if ($starttime != '') {
            $where['create_time1'] = $starttime;
        }
        if ($endtime != '') {
            $where['create_time2'] = $endtime;
        }
        $result = $this->select('id')->where($where)->result();
        foreach ($result as $row) {
            $uids = $this->getAgentAllSubUID($row['id'], $uids, $starttime, $endtime);
        }
        return $uids;
    }

    /**
     * 依邀請碼取得所有下級
     * @param string $code 邀請碼
     */
    public function getAgentCodeAllSubUID($code)
    {
        $result = $this->where(['agent_code' => $code])->result();
        $uids = [0];
        foreach ($result as $row) {
            $uids = $this->getAgentAllSubUID($row['id'], $uids);
        }
        return $uids;
    }

    /**
     * 計算留存率
     * @param int $type 類型
     * @param int $operator_id 營運商ID
     */
    public function retention($type, $operator_id)
    {
        $where['type'] = 0; //剔除白名單
        $where['operator_id'] = $operator_id;
        switch ($type) {
            case 1:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400);
                break;
            case 2:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400 * 3);
                break;
            case 3:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400 * 7);
                break;
            case 4:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400 * 15);
                break;
            case 5:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400 * 30);
                break;
            case 6:
                $where['last_login_time <'] = date('Y-m-d', time() - 86400 * 30);
                break;
        }
        return $this->escape(false)->select('COUNT(id) day_count, ROUND(AVG(money)) avg_money')->where($where)->result_one();
    }

    /**
     * 留存率區間
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param int $type 類型
     */
    public function retention_analysis($starttime, $endtime, $type)
    {
        $where['type'] = 0;
        $where['create_time1'] = $starttime;
        $where['create_time2'] = $endtime;
        switch ($type) {
            case 1:
                $where['last_login_time >= create_time + INTERVAL 0 DAY'] = null;
                break;
            case 2:
                $where['last_login_time >= create_time + INTERVAL 1 DAY'] = null;
                break;
            case 3:
                $where['last_login_time >= create_time + INTERVAL 3 DAY'] = null;
                break;
            case 4:
                $where['last_login_time >= create_time + INTERVAL 7 DAY'] = null;
                break;
            case 5:
                $where['last_login_time >= create_time + INTERVAL 15 DAY'] = null;
                break;
            case 6:
                $where['last_login_time >= create_time + INTERVAL 30 DAY'] = null;
                break;
            case 7:
                $where['last_login_time >= create_time + INTERVAL 60 DAY'] = null;
                break;
            case 8:
                $where['last_login_time >= create_time + INTERVAL 90 DAY'] = null;
                break;
            case 9:
                $where['last_login_time >='] = date('Y-m-d', time() - 86400 * 7);
                break;
        }
        return $this->escape(false)->select('IFNULL(COUNT(id),0) count, IFNULL(ROUND(AVG(money)),0) avg_money')->where($where)->result_one();
    }

    /**
     * 新帳號留存率
     * @param int $type 類型
     * @param string $date 日期
     * @param int $operator_id 營運商ID
     */
    public function retention_daily($type, $date, $operator_id)
    {
        switch ($type) {
            case 1:
                $createdate = date('Y-m-d', strtotime($date) - 86400 * 1);
                break;
            case 2:
                $createdate = date('Y-m-d', strtotime($date) - 86400 * 3);
                break;
            case 3:
                $createdate = date('Y-m-d', strtotime($date) - 86400 * 7);
                break;
            case 4:
                $createdate = date('Y-m-d', strtotime($date) - 86400 * 14);
                break;
            case 5:
                $createdate = date('Y-m-d', strtotime($date) - 86400 * 28);
                break;
        }

        $array['all_count'] = $this->where([
            'type'         => 0,
            'operator_id'  => $operator_id,
            'create_time1' => $createdate,
            'create_time2' => $createdate,
        ])->count();

        $join[] = [$this->table_ . 'user_login_log t1', 't.id = t1.uid', 'right'];
        $row = $this->select('COUNT(DISTINCT t.id) total')->where([
            't.type' => 0,
            'operator_id'  => $operator_id,
            'create_time1' => $createdate,
            'create_time2' => $createdate,
            't1.create_time >=' => "$date 00:00:00",
            't1.create_time <=' => "$date 23:59:59",
        ])->join($join)->result_one();

        $array['day_count'] = isset($row['total']) ? $row['total'] : 0;

        return $array;
    }

    //帳戶類型
    public static $moneyTypeList = [
        0 => '主帐户',
        1 => '牛牛帐户'
    ];

    public static $typeList = [
        0  => '会员用户',
        1  => '白名单用户',
        -1 => '牛牛Robot',
        -2 => '抢庄牛牛Robot',
    ];

    public static $statusList = [
        0 => '正常',
        1 => '封号',
        2 => '冻结',
        3 => '标记',
    ];

    public static $statusColor = [
        0 => '#000',
        1 => 'red',
        2 => 'blue',
        3 => 'goldenrod',
    ];

    public static $whetherList = [
        1 => '是',
        0 => '否',
    ];

    public static $actionTypeList = [
        0 => '人工加款',
        1 => '人工减款',
    ];

    public static $modeList = [
        1 => '是否首充',
        2 => '是否二充',
        4 => '是否提现',
        8 => '是否首次看投注熱度',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'           => '编号',
        'user_pwd'     => '用户密码',
        'security_pwd' => '提现密码',
        'user_name'    => '用户名称',
        'mobile'       => '手机号码',
        'type'         => '用户类型',
        'status'       => '状态',
        'money'        => '余额',
        'vip_info_ios' => 'ios的vip包資訊',
    ];
}
