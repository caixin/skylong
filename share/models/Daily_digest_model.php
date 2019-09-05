<?php defined('BASEPATH') || exit('No direct script access allowed');

class Daily_digest_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'daily_digest';
        $this->_key = 'id';
    }

    public function _do_where()
    {
        if (isset($this->_where['sidebar'])) {
            unset($this->_where['sidebar']);
        }
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        if (isset($this->_where['day_time1'])) {
            $this->db->where('t.day_time >=', $this->_where['day_time1']);
            unset($this->_where['day_time1']);
        }
        if (isset($this->_where['day_time2'])) {
            $this->db->where('t.day_time <=', $this->_where['day_time2']);
            unset($this->_where['day_time2']);
        }
        return $this;
    }

    public function statistics($start_date, $end_date)
    {
        $this->load->model('user_login_log_model', 'user_login_log_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('agent_return_point_model', 'agent_return_point_db');

        $start_time = $start_date . ' 00:00:00';
        $end_time   = $end_date . ' 23:59:59';
        $now = date('Y-m-d H:i:s');
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'inner'];

        //取得資金匯總報表資料
        $result = $this->where([
            't.day_time >=' => $start_date,
            't.day_time <=' => $end_date
        ])->order([
            't.day_time' => 'asc',
            't.operator_id' => 'asc'
        ])->result();
        $report = [];
        foreach ($result as $row) {
            $report["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得註冊人數
        $result = $this->user_db->escape(false)->select("
            COUNT(t.id) AS register_people,
            DATE_FORMAT(t.create_time, '%Y-%m-%d') AS day_time,
            t.operator_id
        ")->where([
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time,
            't.status'         => 0,
            't.type'           => 0
        ])->group('day_time,operator_id')->result();
        $register = [];
        foreach ($result as $row) {
            $register["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得登錄人數
        $result = $this->user_login_log_db->escape(false)->select("
            COUNT(DISTINCT t.uid) AS login_people,
            DATE_FORMAT(t.create_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ")->join($join)->where([
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time,
            't1.status'        => 0,
            't1.type'          => 0
        ])->group('day_time,operator_id')->result();
        $login = [];
        foreach ($result as $row) {
            $login["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得首充人數、首充金額
        $result = $this->user_db->escape(false)->select("
            COUNT(DISTINCT t.id) AS first_recharge_people,
            SUM(t.first_money) AS first_recharge_money,
            DATE_FORMAT(t.first_recharge, '%Y-%m-%d') AS day_time,
            t.operator_id
        ")->where([
            't.first_recharge >=' => $start_time,
            't.first_recharge <=' => $end_time,
            't.status'            => 0,
            't.type'              => 0
        ])->group('day_time,operator_id')->result();
        $first = [];
        foreach ($result as $row) {
            $first["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得充值人數、充值金額
        $result = $this->recharge_order_db->escape(false)->select("
            COUNT(DISTINCT t.uid) AS recharge_people,
            SUM(t.money) AS recharge_money,
            DATE_FORMAT(t.check_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ")->join($join)->where([
            't.check_time >=' => $start_time,
            't.check_time <=' => $end_time,
            't.status'        => 1,
            't1.status'       => 0,
            't1.type'         => 0
        ])->group('day_time,operator_id')->result();
        $recharge = [];
        foreach ($result as $row) {
            $recharge["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得提現人數、提現金額
        $result = $this->user_withdraw_db->escape(false)->select("
            COUNT(DISTINCT t.uid) AS withdraw_people,
            SUM(t.money) AS withdraw_money,
            DATE_FORMAT(t.check_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ")->join($join)->where([
            't.check_time >=' => $start_time,
            't.check_time <=' => $end_time,
            't.status'        => 1,
            't1.status'       => 0,
            't1.type'         => 0
        ])->group('day_time,operator_id')->result();
        $withdraw = [];
        foreach ($result as $row) {
            $withdraw["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得人工存款(計算資金匯總)
        $result = $this->user_money_log_db->escape(false)->select("
            SUM(t.money_add) AS artificial_recharge_money,
            DATE_FORMAT(t.create_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ")->join($join)->where([
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time,
            't.type'           => 2,
            't.money_type'     => 0,
            't1.status'        => 0,
            't1.type'          => 0
        ])->group('day_time,operator_id')->result();
        $artificial = [];
        foreach ($result as $row) {
            $artificial["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得投注人數、投注注數、投注金額、中獎金額
        $select = "
            t.uid,
            t.bet_number,
            t.total_p_value AS p_value,
            t.c_value,
            DATE_FORMAT(t.create_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ";
        $where = [
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time,
            't.status'         => 1,
            't.money_type'     => 0,
            't1.status'        => 0,
            't1.type'          => 0
        ];
        //生成經典注單語法
        $classic_sql = $this->ettm_classic_bet_record_db->escape(false)
            ->select($select)
            ->join($join)
            ->where($where)
            ->get_compiled_select();
        //生成官方注單語法
        $official_sql = $this->ettm_official_bet_record_db->escape(false)
            ->select($select)
            ->join($join)
            ->where($where)
            ->get_compiled_select();
        //生成特色注單語法
        $special_sql = $this->ettm_special_bet_record_db->escape(false)
            ->select($select)
            ->join($join)
            ->where($where)
            ->get_compiled_select();
        $result = $this->base_model->query("SELECT
                COUNT(DISTINCT uid) AS bet_people,
                SUM(bet_number) AS bet_number,
                SUM(p_value) AS p_value,
                SUM(c_value) AS c_value,
                day_time,
                operator_id
            FROM ($classic_sql UNION ALL $official_sql UNION ALL $special_sql) AS a
            GROUP BY day_time,operator_id
        ")->result_array();
        $bet = [];
        foreach ($result as $row) {
            $bet["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //取得返點金額
        $result = $this->agent_return_point_db->escape(false)->select("
            SUM(t.amount) AS return_point_amount,
            DATE_FORMAT(t.create_time, '%Y-%m-%d') AS day_time,
            t1.operator_id
        ")->join($join)->where([
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time,
            't1.status'       => 0,
            't1.type'         => 0
        ])->group('day_time,operator_id')->result();
        $return = [];
        foreach ($result as $row) {
            $return["{$row['day_time']}-{$row['operator_id']}"] = $row;
        }

        //相差天數
        $difference_day = bcdiv(bcsub(strtotime($end_date), strtotime($start_date)), 86400);
        //所有運營商ID
        $operator = $this->operator_db->getList();
        $operator = array_keys($operator);
        //資料初始化
        $insert = [];
        $update = [];

        //組合報表資料
        for ($i = 0; $i <= $difference_day; $i++) {
            $day_time = date("Y-m-d", strtotime("+{$i} day", strtotime($start_date)));

            foreach ($operator as $operator_id) {
                $tmp = [
                    'day_time'              => $day_time,
                    'operator_id'           => $operator_id,
                    'register_people'       => 0,
                    'login_people'          => 0,
                    'first_recharge_people' => 0,
                    'first_recharge_money'  => 0,
                    'recharge_people'       => 0,
                    'withdraw_people'       => 0,
                    'recharge_money'        => 0,
                    'withdraw_money'        => 0,
                    'real_income'           => 0,
                    'bet_people'            => 0,
                    'bet_number'            => 0,
                    'p_value'               => 0,
                    'c_value'               => 0,
                    'return_point_amount'   => 0,
                    'income'                => 0,
                    'update_time'           => $now
                ];
                $key = $day_time . '-' . $operator_id;

                //註冊人數
                if (isset($register[$key])) {
                    $tmp['register_people'] = $register[$key]['register_people'];
                }
                //登錄人數
                if (isset($login[$key])) {
                    $tmp['login_people'] = $login[$key]['login_people'];
                }
                //首充人數、首充金額
                if (isset($first[$key])) {
                    $tmp['first_recharge_people'] = $first[$key]['first_recharge_people'];
                    $tmp['first_recharge_money'] = $first[$key]['first_recharge_money'];
                }
                //充值人數、充值金額
                if (isset($recharge[$key])) {
                    $tmp['recharge_people'] = $recharge[$key]['recharge_people'];
                    $tmp['recharge_money'] = $recharge[$key]['recharge_money'];
                }
                //提現人數、提現金額
                if (isset($withdraw[$key])) {
                    $tmp['withdraw_people'] = $withdraw[$key]['withdraw_people'];
                    $tmp['withdraw_money'] = $withdraw[$key]['withdraw_money'];
                }
                //資金匯總
                $tmp['real_income'] = bcsub($tmp['recharge_money'], $tmp['withdraw_money'], 2);
                if (isset($artificial[$key])) {
                    $tmp['real_income'] = bcadd($tmp['real_income'], $artificial[$key]['artificial_recharge_money'], 2);
                }
                //投注人數、投注注數、投注金額、中獎金額
                if (isset($bet[$key])) {
                    $tmp['bet_people'] = $bet[$key]['bet_people'];
                    $tmp['bet_number'] = $bet[$key]['bet_number'];
                    $tmp['p_value'] = $bet[$key]['p_value'];
                    $tmp['c_value'] = $bet[$key]['c_value'];
                }
                //返點金額
                if (isset($return[$key])) {
                    $tmp['return_point_amount'] = $return[$key]['return_point_amount'];
                }
                //盈虧
                $tmp['income'] = bcsub(bcsub($tmp['p_value'], $tmp['c_value'], 2), $tmp['return_point_amount'], 2);

                //以資料庫是否已有該日資料來判斷要新增還是更新
                if (isset($report[$key])) {
                    $tmp['id'] = $report[$key]['id'];
                    $update[] = $tmp;
                } else {
                    $tmp['create_time'] = $now;
                    $insert[] = $tmp;
                }
            }
        }

        $this->trans_start();
        if ($insert != []) {
            $this->insert_batch($insert);
        }
        if ($update != []) {
            $this->update_batch($update, 'id');
        }
        $this->trans_complete();

        if ($this->trans_status() === false) {
            return 'error';
        } else {
            return 'success';
        }
    }
}
