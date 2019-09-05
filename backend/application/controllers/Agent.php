<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
        $this->load->model('agent_return_point_model', 'agent_return_point_db');
        $this->load->model('backend/admin_model', 'admin_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->load->model('code_amount_log_model', 'code_amount_log_db');
    }

    public function return_point()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['uid', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $group = 't.uid,t.category,t.lottery_id,t.qishu';
        $total = $this->agent_return_point_db->select('t.uid')->where($where)->join($join)->group($group)->count();

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
        $result = $this->agent_return_point_db->escape(false)->where($where)
            ->select('t.uid,t.category,t.lottery_id,t.qishu,SUM(t.amount) amount,MAX(t.create_time) create_time,t1.type user_type,t1.user_name')
            ->join($join)->order($order)->group($group)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function return_point_detail($uid, $category, $lottery_id, $qishu)
    {
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url . "/$uid/$category/$lottery_id/$qishu"));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(7);
        $search_params = param_process($params, ['uid', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        $where['uid'] = $uid;
        $where['category'] = $category;
        $where['lottery_id'] = $lottery_id;
        $where['qishu'] = $qishu;

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.from_uid = t1.id', 'left'];
        $result = $this->agent_return_point_db->select('t.*,t1.user_name')->where($where)
            ->join($join)->order($order)->result();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'result'  => $result,
            'order'   => $order,
            'where'   => $where,
            'params_uri' => $params_uri,
            'cur_url' => $this->cur_url . "/$uid/$category/$lottery_id/$qishu",
            'lottery' => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function code()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['agent_id', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['starttime'])) {
            $where['starttime'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['endtime'])) {
            $where['endtime'] = date('Y-m-d');
        }
        $where['level'] = 1;

        $withdraw = $recharge = [];
        //代理會員-提現
        $result = $this->user_withdraw_db->getAgentWithdrawUser($where['starttime'], $where['endtime']);
        foreach ($result as $row) {
            if (!isset($withdraw[$row['agent_code']])) {
                $withdraw[$row['agent_code']] = 0;
            }
            $withdraw[$row['agent_code']] += $row['money'];
        }
        //代理會員-充值
        $result = $this->recharge_order_db->getAgentRechargeUser($where['starttime'], $where['endtime']);
        foreach ($result as $row) {
            if (!isset($recharge[$row['agent_code']])) {
                $recharge[$row['agent_code']] = 0;
            }
            $recharge[$row['agent_code']] += $row['money'];
        }
        //彩金
        $bonus = $this->user_money_log_db->getAgentMoneyByAgentCode($where['starttime'], $where['endtime'], '7,8');
        //反水
        $rakeback = $this->user_money_log_db->getAgentMoneyByAgentCode($where['starttime'], $where['endtime'], '4');
        //投注额（官方,经典,特色）
        $bet = $this->user_money_log_db->getAgentMoneyByAgentCode($where['starttime'], $where['endtime'], '5,10,18');
        //中奖额（官方,经典,特色）
        $winnings = $this->user_money_log_db->getAgentMoneyByAgentCode($where['starttime'], $where['endtime'], '6,12,14,16');
        //打碼量
        $code_amount = $this->code_amount_log_db->getCodeAmountStats($where['starttime'], $where['endtime']);

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->agent_code_db->where($where)->join($join)->count();

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
        $result = $this->agent_code_db->escape(false)->where($where)
            ->select('t.*,t1.agent_id')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $codes = $this->agent_code_db->getSubCode($row['code']);
            //線下人數
            $row['user_number'] = $this->user_db->where([
                'agent_code' => $codes,
                'type'       => 0,
            ])->count();
            //充值
            $row['recharge_money'] = 0;
            foreach ($recharge as $code => $money) {
                if (in_array($code, $codes)) {
                    $row['recharge_money'] = (float)bcadd($row['recharge_money'], $money, 2);
                }
            }
            //提現
            $row['withdraw_money'] = 0;
            foreach ($withdraw as $code => $money) {
                if (in_array($code, $codes)) {
                    $row['withdraw_money'] = (float)bcadd($row['withdraw_money'], $money, 2);
                }
            }
            //彩金
            $row['bonus_money'] = 0;
            foreach ($bonus as $code => $money) {
                if (in_array($code, $codes)) {
                    $row['bonus_money'] = (float)bcadd($row['bonus_money'], $money, 2);
                }
            }
            //反水
            $row['rakeback_money'] = 0;
            foreach ($rakeback as $code => $money) {
                if (in_array($code, $codes)) {
                    $row['rakeback_money'] = (float)bcadd($row['rakeback_money'], $money, 2);
                }
            }
            //投注金额
            $bet_money = 0;
            foreach ($bet as $code => $money) {
                if (in_array($code, $codes)) {
                    $bet_money = (float)bcadd($bet_money, $money, 2);
                }
            }
            //中奖金额
            $winnings_money = 0;
            foreach ($winnings as $code => $money) {
                if (in_array($code, $codes)) {
                    $winnings_money = (float)bcadd($winnings_money, $money, 2);
                }
            }
            //輸贏
            $row['profit'] = (float)bcmul(bcadd(bcadd(bcadd($bet_money, $winnings_money, 2), $row['bonus_money'], 2), $row['rakeback_money'], 2), -1, 2);

            //返點
            $sub_uid = $this->user_db->getAgentCodeAllSubUID($row['code']);
            $row['return_point'] = $this->agent_return_point_db->getReturnPointStats($where['starttime'], $where['endtime'], $row['uid'], $sub_uid);

            //打碼量
            $row['code_amount'] = 0;
            foreach ($code_amount as $code => $money) {
                if (in_array($code, $codes)) {
                    $row['code_amount'] = (float)bcadd($row['code_amount'], $money, 2);
                }
            }
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'agent'      => $this->admin_db->getAgentList(),
        ]);
    }

    public function code_detail($code)
    {
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url . "/$code"));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(4);
        $search_params = param_process($params, ['lottery_id', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        $where['code'] = $code;
        $result = $this->agent_code_detail_db->where($where)->order($order)->result();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'order'      => $order,
            'where'      => $where,
            'params_uri' => $params_uri,
            'cur_url'    => $this->cur_url . "/$code",
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function sub_user($starttime, $endtime)
    {
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url . "/$starttime/$endtime"));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(5);
        $search_params = param_process($params, ['id', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        $withdraw = $recharge = [];
        //代理會員-提現
        $result = $this->user_withdraw_db->getAgentWithdrawUser($starttime, $endtime);
        foreach ($result as $row) {
            if (!isset($withdraw[$row['uid']])) {
                $withdraw[$row['uid']] = 0;
            }
            $withdraw[$row['uid']] += $row['money'];
        }
        //代理會員-充值
        $result = $this->recharge_order_db->getAgentRechargeUser($starttime, $endtime);
        foreach ($result as $row) {
            if (!isset($recharge[$row['uid']])) {
                $recharge[$row['uid']] = 0;
            }
            $recharge[$row['uid']] += $row['money'];
        }
        //彩金
        $bonus = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, '7,8');
        //反水
        $rakeback = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, '4');

        $result = $this->user_db->where($where)->order($order)->result();
        foreach ($result as $key => $row) {
            $uids = $this->user_db->getAgentAllSubUID($row['id']);
            //線下人數
            $row['user_number'] = count($uids) - 1;
            //充值
            $row['recharge_money'] = 0;
            foreach ($recharge as $uid => $money) {
                if (in_array($uid, $uids)) {
                    $row['recharge_money'] = (float)bcadd($row['recharge_money'], $money, 2);
                }
            }
            //提現
            $row['withdraw_money'] = 0;
            foreach ($withdraw as $uid => $money) {
                if (in_array($uid, $uids)) {
                    $row['withdraw_money'] = (float)bcadd($row['withdraw_money'], $money, 2);
                }
            }
            //彩金
            $row['bonus_money'] = 0;
            foreach ($bonus as $uid => $money) {
                if (in_array($uid, $uids)) {
                    $row['bonus_money'] = (float)bcadd($row['bonus_money'], $money, 2);
                }
            }
            //反水
            $row['rakeback_money'] = 0;
            foreach ($rakeback as $uid => $money) {
                if (in_array($uid, $uids)) {
                    $row['rakeback_money'] = (float)bcadd($row['rakeback_money'], $money, 2);
                }
            }
            //返點
            $row['return_point'] = $this->agent_return_point_db->getReturnPointStats($starttime, $endtime, $row['id'], $uids);
            $result[$key] = $row;
        }

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'order'      => $order,
            'where'      => $where,
            'params_uri' => $params_uri,
            'starttime'  => $starttime,
            'endtime'    => $endtime,
            'cur_url'    => $this->cur_url . "/$starttime/$endtime",
        ]);
    }
}
