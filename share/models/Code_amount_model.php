<?php defined('BASEPATH') || exit('No direct script access allowed');

class Code_amount_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'code_amount';
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

        if (isset($this->_where['moeny_type'])) {
            $this->db->where('t.moeny_type', $this->_where['moeny_type']);
            unset($this->_where['moeny_type']);
        }

        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
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
        return $this;
    }

    /**
     * 加減碼打碼量
     * @param int $uid 用戶ID
     * @param int $amount 加減碼
     * @param int $code_amount_log_id 打碼量LogID
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function setAmount($uid, $amount, $code_amount_log_id, $money_type = 0)
    {
        $this->load->model('code_amount_assign_model', 'code_amount_assign_db');

        $result = $this->where([
            'uid'        => $uid,
            'money_type' => $money_type,
            'status'     => 0,
        ])->order(['create_time', 'asc'])->result();

        foreach ($result as $row) {
            if ($amount == 0) {
                break;
            }

            $use = 0;
            if ($amount < 0) {
                //減碼
                $use = min($row['code_amount'], abs($amount)) * -1;
            } else {
                //加碼
                $need = bcsub($row['code_amount_need'], $row['code_amount'], 2);
                $use = min($need, $amount);
            }
            if ($use != 0) {
                $row['code_amount'] = bcadd($row['code_amount'], $use, 2);
                $amount = bcsub($amount, $use, 2);

                $this->code_amount_assign_db->insert([
                    'code_amount_log_id' => $code_amount_log_id,
                    'code_amount_id'     => $row['id'],
                    'code_amount_use'    => $use,
                ]);
                $this->update([
                    'id'          => $row['id'],
                    'code_amount' => $row['code_amount'],
                    'status'      => $row['code_amount_need'] == $row['code_amount'] ? 1 : 0,
                ]);
            }
        }
    }

    /**
     * 取得需求打碼量
     * @param int $uid 用戶ID
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     * @return int 需求打碼量
     */
    public function getNeedByUid($uid, $money_type = 0)
    {
        $result = $this->where([
            'uid'        => $uid,
            'money_type' => $money_type,
            'status'     => 0,
        ])->result();
        $need = 0;
        foreach ($result as $row) {
            $need += $row['code_amount_need'] - $row['code_amount'];
        }

        return $need;
    }

    /**
     * 有效注單打碼量計算
     * @param integer $category 分類 1:經典 2:官方 3:特色
     * @param integer $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function setBetEffect($category, $money_type = 0)
    {
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('code_amount_log_model', 'code_amount_log_db');

        Monolog::writeLogs('CodeAmount', 200, code_amount_log_model::$categoryList[$category] . "开始执行!");
        $result = [];
        $bet_model = '';
        switch ($category) {
            case 1:
                $bet_model = 'ettm_classic_bet_record_db';
                break;
            case 2:
                $bet_model = 'ettm_official_bet_record_db';
                break;
            case 3:
                $bet_model = 'ettm_special_bet_record_db';
                break;
        }
        $bet_where = [
            't.status'         => 1,
            't.is_lose_win <>' => 2,
            't.is_code_amount' => 0,
        ];
        if ($category == 3) {
            $bet_where = array_merge($bet_where, ['t.money_type' => $money_type]);
        }
        $result = $this->$bet_model->where($bet_where)->result();
        if ($result === []) {
            Monolog::writeLogs('CodeAmount', 200, code_amount_log_model::$categoryList[$category] . '无未打码注单!');
            return;
        }
        //打碼量事務
        $this->trans_start();
        foreach ($result as $row) {
            //有效投注額
            $bet_eff = $this->$bet_model->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win'], $row['p_value']);
            //寫入log
            $log_id = $this->code_amount_log_db->insert([
                'uid'           => $row['uid'],
                'money_type'    => $money_type,
                'type'          => 0,
                'category'      => $category,
                'bet_record_id' => $row['id'],
                'code_amount'   => $bet_eff,
            ]);
            //寫入打碼量
            $this->setAmount($row['uid'], $bet_eff, $log_id, $money_type);
            //注單標記已打碼
            $this->$bet_model->update([
                'id' => $row['id'],
                'is_code_amount' => 1,
            ]);
        }
        $this->trans_complete();
        if ($this->trans_status() !== false) {
            Monolog::writeLogs('updateCodeAmount', 200, code_amount_log_model::$categoryList[$category] . '执行完成!');
        } else {
            Monolog::writeLogs('updateCodeAmount', 200, code_amount_log_model::$categoryList[$category] . '执行失败!');
        }
    }

    /**
     * 還原注單打碼量
     * @param integer $category 分類 1:經典 2:官方 3:特色
     * @param integer $bet_id 注單ID
     */
    public function restoreBetEffect($category, $bet_id)
    {
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('code_amount_log_model', 'code_amount_log_db');
        $this->load->model('code_amount_assign_model', 'code_amount_assign_db');

        $bet_model = '';
        switch ($category) {
            case 1:
                $bet_model = 'ettm_classic_bet_record_db';
                break;
            case 2:
                $bet_model = 'ettm_official_bet_record_db';
                break;
            case 3:
                $bet_model = 'ettm_special_bet_record_db';
                break;
        }

        $bet = $this->$bet_model->row($bet_id);
        if ($bet['is_code_amount'] == 0) {
            return;
        }

        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $log = $this->code_amount_log_db->select('t.*')->where([
            'category'      => $category,
            'bet_record_id' => $bet_id,
        ])->join($join)->order(['create_time', 'desc'])->result_one();
        //沒資料或是最新一筆是退款則跳過
        if (!isset($log) || $log['type'] == 3) {
            return;
        }
        //取得該LOG分配紀錄
        $assign = $this->code_amount_assign_db->where([
            'code_amount_log_id' => $log['id'],
        ])->result();

        //還原打碼量事務
        $this->trans_start();
        //寫入一筆退款
        $log_id = $this->code_amount_log_db->insert([
            'uid'           => $bet['uid'],
            'money_type'    => isset($bet['money_type']) ? $bet['money_type'] : 0,
            'type'          => 3,
            'category'      => $category,
            'bet_record_id' => $bet_id,
            'code_amount'   => bcmul($log['code_amount'], -1, 2),
        ]);
        foreach ($assign as $row) {
            $use = bcmul($row['code_amount_use'], -1, 2);
            //寫入assign
            $this->code_amount_assign_db->insert([
                'code_amount_log_id' => $log_id,
                'code_amount_id'     => $row['code_amount_id'],
                'code_amount_use'    => $use,
            ]);
            //更新打碼量
            $arr = $this->row($row['code_amount_id']);
            $this->update([
                'id'          => $arr['id'],
                'code_amount' => bcadd($arr['code_amount'], $use, 2),
                'status'      => 0,
            ]);
        }
        //將注單更新為未打碼
        $this->$bet_model->update([
            'id' => $bet_id,
            'is_code_amount' => 0,
        ]);
        $this->trans_complete();
    }

    public static $typeList = [
        1 => '充值',
        2 => '赠送彩金',
        3 => '人工入款',
        4 => '人工彩金',
    ];

    public static $statusList = [
        1 => '通过',
        0 => '不通过',
    ];

    public static $statusColorList = [
        1 => 'green',
        0 => 'red',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'               => '编号',
        'uid'              => '用户ID',
        'type'             => '類型',
        'money'            => '金额',
        'description'      => '描述',
        'multiple'         => '打码量倍数',
        'code_amount_need' => '需求打码量',
        'code_amount'      => '有效打码量',
        'status'           => '稽核',
    ];
}
