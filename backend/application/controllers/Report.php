<?php defined('BASEPATH') || exit('No direct script access allowed');

class Report extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
    }

    public function index()
    {
        redirect($this->router->class . "/lottery");
    }

    public function summary()
    {
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }
        $where['t1.type'] = 0;

        $income = $expenditure = [];
        $item = $virtual = $real = 0;
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        //充值
        foreach (recharge_order_model::$typeList as $key => $val) {
            $row = $this->recharge_order_db->escape(false)->where($where + ['t.type' => $key, 't.status' => 1])
                ->select('COUNT(t.uid) count,SUM(t.money) money')->join($join)
                ->result_one();
            $income[] = [
                'type'       => "$val ( $row[count] )",
                'money'      => (float) $row['money'],
                'detail_url' => site_url("recharge_order/index/sidebar/0/type/$key/check_time1/$where[create_time1]/check_time2/$where[create_time2]"),
            ];
            $item = bcadd($item, $row['money'], 2);
        }
        //人工存款
        $row = $this->user_money_log_db->escape(false)->where($where + ['t.type' => 2, 't.money_type' => 0])
            ->select('COUNT(t.uid) count,SUM(t.money_add) money')->join($join)
            ->result_one();
        $income[] = [
            'type'       => "人工存款 ( $row[count] )",
            'money'      => (float) $row['money'],
            'detail_url' => site_url("user/money_log/sidebar/0/type/2/create_time1/$where[create_time1]/create_time2/$where[create_time2]"),
        ];
        $item = bcadd($item, $row['money'], 2);
        //會員出款
        $row = $this->user_withdraw_db->escape(false)->where($where + ['t.status' => 1])
            ->select('COUNT(t.uid) count,SUM(t.money) money')->join($join)
            ->result_one();
        $expenditure[] = [
            'type'       => "会员出款 ( $row[count] )",
            'money'      => (float) $row['money'],
            'detail_url' => site_url("user_withdraw/index/sidebar/0/check_time1/$where[create_time1]/check_time2/$where[create_time2]"),
        ];
        $real = bcsub($item, $row['money'], 2);
        foreach ([3, 4, 7, 8, 13] as $type) {
            $row = $this->user_money_log_db->escape(false)->where($where + ['t.type' => $type, 't.money_type' => 0])
                ->select('COUNT(t.uid) count,SUM(t.money_add) money')->join($join)
                ->result_one();
            $expenditure[] = [
                'type'       => user_money_log_model::$typeList[$type] . " ( $row[count] )",
                'money'      => (float) $row['money'],
                'detail_url' => site_url("user/money_log/sidebar/0/type/$type/create_time1/$where[create_time1]/create_time2/$where[create_time2]"),
            ];
            if (in_array($type, [4, 7, 8])) {
                $virtual = bcadd($virtual, $row['money'], 2);
            }
        }

        $this->layout->view($this->cur_url, [
            'income'      => $income,
            'expenditure' => $expenditure,
            'where'       => $where,
            'params_uri'  => $params_uri,
            'item'        => $item,
            'virtual'     => $virtual,
            'real'        => $real,
        ]);
    }

    public function lottery()
    {
        $this->load->model('daily_user_report_model', 'daily_user_report_db');
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['profit', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d');
        }

        if (!isset($where['type'])) {
            $where['type'] = 0;
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->daily_user_report_db->join($join)->where($where)->count();

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        // get main data.
        $select = 't.uid,t1.type user_type,t1.user_name,t1.mobile,t.category,
                   SUM(t.bet_number) bet_number,SUM(t.bet_money) bet_money,
                   SUM(t.c_value) c_value,SUM(t.bet_eff) bet_eff,
                   SUM(t.bet_money - t.c_value) profit';
        $result = $this->daily_user_report_db->escape(false)->where($where)
            ->select($select)->join($join)->group('t.uid,t.category')
            ->order($order)->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $row['profit'] = $row['profit'] > 0 ? "+$row[profit]" : $row['profit'];
            $result[$key] = $row;
        }

        //總計
        $select = 'SUM(t.bet_number) bet_number,SUM(t.bet_money) bet_money,
                   SUM(t.c_value) c_value,SUM(bet_eff) bet_eff,
                   SUM(t.bet_money - t.c_value) profit';
        $footer = $this->daily_user_report_db->escape(false)->where($where)
            ->select($select)->join($join)
            ->result_one();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'footer'     => $footer,
        ]);
    }

    public function user_effect()
    {
        $this->load->model('daily_user_report_model', 'daily_user_report_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['user_name', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        $where['t1.status'] = 0;
        $where['t1.type'] = 0;
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d');
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        $select = 't.uid,t1.user_name,t1.type user_type,t1.mobile,
                   t2.username agent_name,t1.agent_code,SUM(t.bet_number) bet_count,
                   SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,
                   SUM(t.c_value - t.total_p_value) profit';
        //經典彩下注統計
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'admin t2', 't1.agent_id = t2.id', 'left'];
        $classic_count = $this->ettm_classic_bet_record_db->select('t.uid')->join($join)->where($where)->group('t.uid')->count();
        $classic_sql = $this->ettm_classic_bet_record_db->escape(false)->join($join)
            ->select($select . ',1 category,SUM(IF(t.is_lose_win = 0,t.total_p_value,IF(t.c_value - t.total_p_value > t.total_p_value, t.total_p_value, t.c_value - t.total_p_value))) bet_eff')
            ->where($where)
            ->group('t.uid')
            ->get_compiled_select();

        //官方彩下注統計
        $official_count = $this->ettm_official_bet_record_db->select('t.uid')->join($join)->where($where)->group('t.uid')->count();
        $official_sql = $this->ettm_official_bet_record_db->escape(false)->join($join)
            ->select($select . ',2 category, t.total_p_value bet_eff')
            ->where($where)
            ->group('t.uid')
            ->get_compiled_select();

        //特色棋牌下注統計
        $special_count = $this->ettm_special_bet_record_db->select('t.uid')->join($join)->where($where)->group('t.uid')->count();
        $special_sql = $this->ettm_special_bet_record_db->escape(false)->join($join)
            ->select($select . ',3 category, SUM(IF(t.is_lose_win = 0,t.p_value,IF(t.c_value - t.total_p_value > t.p_value, t.p_value, t.c_value - t.total_p_value))) bet_eff')
            ->where($where)
            ->group('t.uid')
            ->get_compiled_select();

        $category = isset($where['category']) ? $where['category'] : 0;
        switch ($category) {
            case 1: //經典
                $count = $classic_count;
                $sql = $classic_sql;
                break;
            case 2: //官方
                $count = $official_count;
                $sql = $official_sql;
                break;
            case 3: //特色
                $count = $special_count;
                $sql = $special_sql;
                break;
            default: //全部
                $count = $classic_count + $official_count + $special_count;
                $sql = "$classic_sql UNION ALL $official_sql UNION ALL $special_sql";
                break;
        }

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $count,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        $sql .= " ORDER BY uid,category LIMIT $offset,$this->per_page";
        $result = $this->base_model->query($sql)->result_array();

        $footer = [
            'bet_count'     => 0,
            'total_p_value' => 0,
            'c_value'       => 0,
            'bet_eff'       => 0,
            'profit'        => 0,
            'member_count'  => 0,
        ];

        $uids = [];
        foreach ($result as $row) {
            $footer['bet_count']     += $row['bet_count'];
            $footer['total_p_value'] += $row['total_p_value'];
            $footer['c_value']       += $row['c_value'];
            $footer['bet_eff']       += $row['bet_eff'];
            $footer['profit']        += $row['profit'];
            $uids[] = $row['uid'];
        }
        $footer['member_count'] = count(array_unique($uids));

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $count,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'footer'     => $footer,
        ]);
    }

    public function recharge()
    {
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('recharge_offline_model', 'recharge_offline_db');
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        //預設查詢條件
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d');
            $where['create_time2'] = date('Y-m-d');
        } else {
            $where['create_time2'] = $where['create_time1'];
        }

        $where['t.status'] = 1;
        $where['t1.status'] = 0;
        $where['t1.type'] = 0;

        // get total.
        $group = 't.uid, t.type, t.offline_channel';
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->recharge_order_db->select('t.uid')->where($where)->join($join)->group($group)->count();

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        // get main data.
        $select = "t.uid,t1.user_name,t1.real_name,t1.mobile,t.type,t.offline_channel,0 grand_total,
                  COUNT(t.uid) today_total,SUM(t.money) money,t1.create_time register_time,'' first_recharge";

        $result = $this->recharge_order_db
            ->select($select)
            ->where($where)
            ->join($join)
            ->group($group)
            ->limit([$offset, $this->per_page])
            ->result();

        $report = [];
        foreach ($result as $row) {
            if ((date('Y-m-d', strtotime($row['register_time']))) == date($where['create_time1'])) {
                $row['first_recharge'] = '首充';
            }
            $report["$row[uid]-$row[type]-$row[offline_channel]"] = $row;
        }

        if ($result != []) {
            $grandresult = $this->recharge_order_db->select('
                t.uid, t.type, t.offline_channel, count(t.uid) grand_total
            ')->where([
                't.uid' => array_column($result, 'uid'),
            ])->group('t.uid, t.type, t.offline_channel')->result();

            foreach ($grandresult as $row) {
                if (isset($report["$row[uid]-$row[type]-$row[offline_channel]"])) {
                    $report["$row[uid]-$row[type]-$row[offline_channel]"]['grand_total'] = $row['grand_total'];
                }
            }
        }
        //統計
        $footersql = $this->recharge_order_db
            ->select('SUM(t.money) money')
            ->where($where)
            ->join($join)
            ->group($group)
            ->get_compiled_select();
        
        $sql = "SELECT count(*) count, SUM(t.money) money FROM ($footersql) t";
        $footer = $this->recharge_order_db->query($sql)->row_array();

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'     => $report,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'footer'     => $footer
        ]);
    }

    public function digest()
    {
        $this->load->model('daily_digest_model', 'daily_digest_db');
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['day_time', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        //預設查詢條件
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-01');
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d');
        }

        // get total.
        $total = $this->daily_digest_db->select('t.day_time')->where($where)->group('t.day_time')->count();

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        // get main data.
        $select = '
            t.day_time,
            SUM(t.register_people) AS register_people,
            SUM(t.login_people) AS login_people,
            SUM(t.first_recharge_people) AS first_recharge_people,
            SUM(t.first_recharge_money) AS first_recharge_money,
            SUM(t.recharge_people) AS recharge_people,
            SUM(t.withdraw_people) AS withdraw_people,
            SUM(t.recharge_money) AS recharge_money,
            SUM(t.withdraw_money) AS withdraw_money,
            SUM(t.real_income) AS real_income,
            SUM(t.bet_people) AS bet_people,
            SUM(t.bet_number) AS bet_number,
            SUM(t.p_value) AS p_value,
            SUM(t.c_value) AS c_value,
            SUM(t.return_point_amount) AS return_point_amount,
            SUM(t.income) AS income
        ';
        $result = $this->daily_digest_db->escape(false)
            ->select($select)
            ->where($where)
            ->order($order)
            ->group('day_time')
            ->limit([$offset, $this->per_page])
            ->result();

        //總計
        $footer = $this->daily_digest_db->escape(false)
            ->select($select)
            ->where($where)
            ->result_one();

        $latest = $this->daily_digest_db->select('update_time')
            ->order(['update_time', 'desc'])
            ->result_one();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'footer'     => $footer,
            'latest'     => $latest,
        ]);
    }

    //匯出
    public function digest_export()
    {
        $this->load->model('daily_digest_model', 'daily_digest_db');
        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['day_time', 'desc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];

        //預設查詢條件
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-01');
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d');
        }

        $select = '
            t.day_time,
            SUM(t.register_people) AS register_people,
            SUM(t.login_people) AS login_people,
            SUM(t.first_recharge_people) AS first_recharge_people,
            SUM(t.first_recharge_money) AS first_recharge_money,
            SUM(t.recharge_people) AS recharge_people,
            SUM(t.withdraw_people) AS withdraw_people,
            SUM(t.recharge_money) AS recharge_money,
            SUM(t.withdraw_money) AS withdraw_money,
            SUM(t.real_income) AS real_income,
            SUM(t.bet_people) AS bet_people,
            SUM(t.bet_number) AS bet_number,
            SUM(t.p_value) AS p_value,
            SUM(t.c_value) AS c_value,
            SUM(t.return_point_amount) AS return_point_amount,
            SUM(t.income) AS income
        ';
        $result = $this->daily_digest_db->escape(false)
            ->select($select)
            ->where($where)
            ->order($order)
            ->group('day_time')
            ->result();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription('none');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '日期');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '注册人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '登录人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, '首充人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, '首充金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, '充值人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, '提现人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, '充值金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, '提现金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, '资金汇总');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, '投注人数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, '投注注数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, '投注金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 1, '中奖金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 1, '返点金额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, 1, '盈亏');

        $r = 1;
        foreach ($result as $row) {
            $r++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, $row['day_time']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, $row['register_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, $row['login_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $row['first_recharge_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $r, $row['first_recharge_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $r, $row['recharge_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $r, $row['withdraw_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $row['recharge_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $r, $row['withdraw_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $r, $row['real_income']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $r, $row['bet_people']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $r, $row['bet_number']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $r, $row['p_value']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $r, $row['c_value']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $r, $row['return_point_amount']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $r, $row['income']);
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->router->method . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
