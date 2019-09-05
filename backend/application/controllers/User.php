<?php defined('BASEPATH') || exit('No direct script access allowed');

class User extends MY_Controller
{
    public $operator_id = 0;
    public $agent_id = 0;
    public $agent_pid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('user_group_model', 'user_group_db');
        $this->load->model('user_bank_model', 'user_bank_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->load->model('user_login_log_model', 'user_login_log_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        $this->user_db->is_action_log = true;
    }

    public function online()
    {
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
        $where['unlock_time <'] = date('Y-m-d H:i:s');
        if (!isset($where['last_active_time1'])) {
            $where['last_active_time1'] = date('Y-m-d H:i:s', time() - $this->site_config['online_status']);
        }

        if (!isset($where['type'])) {
            $where['type'] = 0;
        }

        // get total.
        $join[] = [$this->table_ . 'admin t1', 't.agent_id = t1.id', 'left'];
        $total = $this->user_db->where($where)->join($join)->count();

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
        $result = $this->user_db->where($where)
            ->select('t.*,t1.username agent_name')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $this->operator_db->getList(0),
        ]);
    }

    public function kick()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->user_db->update([
                'id'          => $id,
                'unlock_time' => date('Y-m-d H:i:s', time() + 360),
            ]);
            echo 'done';
        }
    }

    public function mark()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->user_db->update([
                'id'     => $id,
                'status' => 3,
            ]);
            echo 'done';
        }
    }

    public function index()
    {
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

        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }
        if (!isset($where['type'])) {
            $where['type'] = 0;
        }

        // get total.
        $join[] = [$this->table_ . 'admin t1', 't.agent_id = t1.id', 'left'];
        $join[] = [$this->table_ . 'agent_code t2', 't.agent_code = t2.code', 'left'];
        $total = $this->user_db->where($where)->join($join)->count();

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
        $result = $this->user_db->where($where)
            ->select('t.*,t1.username agent_name,t2.type code_type')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $page_money = $page_money1 = $page_profit = 0;
        foreach ($result as $key => $row) {
            $row['code_color'] = $row['agent_code'] == '' ? '' : agent_code_model::$typeColor[$row['code_type']];

            $page_money = bcadd($page_money, $row['money'], 2);
            $page_money1 = bcadd($page_money1, $row['money1'], 2);
            $page_profit = bcadd($page_profit, $row['profit'], 2);
            $result[$key] = $row;
        }

        $sum = $this->user_db->where($where)
            ->select('SUM(t.money) money,SUM(t.money1) money1,SUM(t.profit) profit')
            ->join($join)->result_one();

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'       => $result,
            'total'        => $total,
            'where'        => $where,
            'order'        => $order,
            'params_uri'   => $params_uri,
            'operator'     => $operator,
            'page'         => $page,
            'page_money'   => $page_money,
            'page_money1'  => $page_money1,
            'page_profit'  => $page_profit,
            'total_money'  => $sum['money'],
            'total_money1' => $sum['money1'],
            'total_profit' => $sum['profit'],
            'user_group'   => $this->user_group_db->getList($where['operator_id']),
            'agent'        => $this->admin_db->getAgentList(),
        ]);
    }

    public function create($operator_id)
    {
        $this->load->library('form_validation');
        //預設值
        $row['status'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_db->rules());

            if ($this->form_validation->run() == true) {
                if ($this->input->post('super_user') == 1) {
                    $row['type'] = 1;
                }
                unset($row['super_user']);
                $row['operator_id'] = $this->operator_id;
                $row['agent_id']    = $this->agent_id;
                $row['agent_pid']   = $this->agent_pid;
                $this->user_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row']        = $row;
        $data['user_group'] = $this->user_group_db->getList($operator_id);
        $data['operator']   = $this->operator_db->getList(0);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_db->edit_rules());

            if ($this->form_validation->run() == true) {
                if ($this->input->post('super_user') == 1) {
                    $row['type'] = 1;
                    $row['agent_code'] = '';
                }
                unset($row['super_user']);
                $row['id'] = $id;
                $row['operator_id'] = $this->operator_id;
                $row['agent_id'] = $this->agent_id;
                $row['agent_pid'] = $this->agent_pid;
                $this->user_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->user_db->row($id);
            $row['super_user'] = 0;
            if ($row['operator_id'] == 0) {
                $row['super_user'] = 1;
            }
        }

        $data['row']        = $row;
        $data['user_group'] = $this->user_group_db->getList($row['operator_id']);
        $data['agent']      = $this->admin_db->getAgentList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit_pwd($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $row['id'] = $id;
            $this->user_db->update($row);

            $this->session->set_flashdata('message', '编辑成功!');
            echo "<script>parent.window.layer.close();parent.location.reload();</script>";
            return;
        } else {
            $row = $this->user_db->row($id);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit_money($id)
    {
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->load->library('form_validation');
        //預設值
        $row['money_type'] = 0;
        $row['multiple'] = 1;
        $row['add_money'] = 1;
        $row['type'] = 2;
        $row['remark'] = '';
        $user = $this->user_db->row($id);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_db->edit_money_rules());

            if ($this->form_validation->run() == true) {
                $add_money = $row['type'] == 3 ? $row['add_money'] * -1 : $row['add_money'];
                $remark = $row['remark'] == '' ? user_money_log_model::$typeList[$row['type']] : $row['remark'];
                $this->user_db->addMoney($id, create_order_sn('R'), $row['type'], $add_money, $remark, 0, 0, 0, $row['money_type']);

                //新增打碼量
                if ($row['type'] != 3 && $row['multiple'] > 0) {
                    $this->code_amount_db->insert([
                        'uid'              => $id,
                        'money_type'       => $row['money_type'],
                        'type'             => $row['type'] == 2 ? 3 : 4,
                        'money'            => $add_money,
                        'description'      => $remark,
                        'multiple'         => $row['multiple'],
                        'code_amount_need' => bcmul($add_money, $row['multiple'], 2),
                    ]);
                }

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }
        $row['user_name'] = $user['user_name'];
        $row['money'] = (float) $user['money'];
        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function detail($id)
    {
        $this->load->library('form_validation');

        $data['row'] = $this->user_db->row($id);
        $data['user_group'] = $this->user_group_db->getList();
        $data['agent'] = $this->admin_db->getAgentList();
        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    //備註
    public function remark($id, $delete = '')
    {
        $user = $this->user_db->row($id);
        $result = $user['remark'] == '' ? [] : json_decode($user['remark'], true);

        if ($delete !== '') {
            unset($result[$delete]);
            $this->user_db->update([
                'id'     => $id,
                'remark' => json_encode($result),
            ]);
            $this->session->set_flashdata('message', '删除成功!');
            redirect("$this->cur_url/$id");
        }

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $remark = $this->input->post('remark');

            $result[] = [
                'note'        => $remark,
                'create_time' => date('Y-m-d H:i:s'),
                'create_by'   => $this->session->userdata('username'),
            ];
            $this->user_db->update([
                'id'     => $id,
                'remark' => json_encode($result),
            ]);

            $this->session->set_flashdata('message', '编辑成功!');
            redirect("$this->cur_url/$id");
        }

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'id'     => $id,
            'result' => $result,
        ]);
    }

    //ios綁定vip
    public function vip_info_ios($id)
    {
        $user = $this->user_db->row($id);
        $vip_info_ios = $user['vip_info_ios'] == '' ? [] : json_decode($user['vip_info_ios'], true);

        if ($this->input->is_ajax_request()) {
            $key = $this->input->post('key');
            $binding = $this->input->post('binding');
            $vip_info_ios[$key]['binding'] = $binding;
            $this->user_db->update([
                'id' => $id,
                'vip_info_ios' => json_encode($vip_info_ios, JSON_UNESCAPED_UNICODE),
            ]);
            echo 'done';
            return;
        }

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'id'           => $id,
            'vip_info_ios' => $vip_info_ios,
        ]);
    }

    public function batch()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $id = $this->input->post('id');
            $params_uri = $this->input->post('params_uri');
            $page = $this->input->post('page');

            if ($id !== null) {
                if ($this->input->post('group_btn') !== null) {
                    $this->user_db->where(['ids' => $id])->update_where([
                        'user_group_id' => $this->input->post('user_group_id')
                    ]);
                }
                if ($this->input->post('agent_btn') !== null) {
                    $agent_code = $this->input->post('agent_code');
                    $agent = $this->agent_code_db->row($agent_code);
                    $user = $this->user_db->row($agent['uid']);

                    foreach ($id as $uid) {
                        $this->user_db->update([
                            'id'         => $uid,
                            'agent_id'   => $user['agent_id'],
                            'agent_pid'  => $agent['uid'],
                            'agent_code' => $agent_code
                        ]);
                    }
                }
            }

            $this->session->set_flashdata('message', '批量设置成功!');
            redirect("{$this->router->class}/index/$params_uri/page/$page");
        }
    }

    public function money_log()
    {
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
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }
        if (!isset($where['money_type'])) {
            $where['money_type'] = 0;
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d H:i:s', time() - 86400 * 7);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d H:i:s');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'admin t2', 't1.agent_id = t2.id', 'left'];
        $total = $this->user_money_log_db->join($join)->where($where)->count();

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
        $result = $this->user_money_log_db->where($where)
            ->select('t.*,t1.user_name,t1.agent_id,t1.mobile,t1.type user_type,t2.username agent_name')
            ->join($join)->order($order)->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $bet = '';
            switch ($row['category']) {
                case 1:
                    $bet = 'classic_bet_record';
                    break;
                case 2:
                    $bet = 'official_bet_record';
                    break;
                case 3:
                    $bet = 'special_bet_record';
                    break;
            }
            if ($row['category'] > 0) {
                $field = 'qishu';
                if ($row['type'] == 5) {
                    $field = 'order_sn';
                }
                $row['url'] = site_url("$bet/index/sidebar/0/lottery_id/$row[lottery_id]/$field/$row[order_sn]");
            }

            $result[$key] = $row;
        }

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
        ]);
    }

    public function login_log()
    {
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
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 7);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'admin t2', 't1.agent_id = t2.id', 'left'];
        $total = $this->user_login_log_db->join($join)->where($where)->count();

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
        $result = $this->user_login_log_db->where($where)
            ->select('t.*,t1.user_name,t1.agent_id,t1.mobile,t1.type user_type,t2.username agent_name')
            ->join($join)->order($order)->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $info = json_decode($row['ip_info'], true);
            $row['country'] = empty($info) ? '' : "$info[country_name]/$info[region_name]";
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
        ]);
    }

    public function register_log()
    {
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
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'admin t1', 't.agent_id = t1.id', 'left'];
        $total = $this->user_db->join($join)->where($where)->count();

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
        $result = $this->user_db->where($where)
            ->select('t.*,t1.username agent_name')->join($join)
            ->order($order)->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $info = json_decode($row['create_ip_info'], true);
            $row['country'] = empty($info) ? '' : "$info[country_name]/$info[region_name]";
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
        ]);
    }

    public function agent_code_check($agent_code)
    {
        if ($this->input->post('super_user') == 1) {
            $this->operator_id = 0;
            $this->agent_id    = 0;
            $this->agent_pid   = 0;
            return true;
        } else {
            $row = $this->agent_code_db->where([
                't.code' => $agent_code
            ])->result_one();

            if ($row === null) {
                $this->form_validation->set_message('agent_code_check', "查无此 $agent_code {field}。");
                return false;
            } else {
                $user = $this->user_db->row($row['uid']);
                $this->operator_id = $user['operator_id'];
                $this->agent_id    = $user['agent_id'];
                $this->agent_pid   = $user['id'];
                return true;
            }
        }
    }
}
