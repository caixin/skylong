<?php defined('BASEPATH') || exit('No direct script access allowed');

class Code_amount extends MY_Controller
{
    public $uid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->load->model('code_amount_log_model', 'code_amount_log_db');
        $this->load->model('code_amount_assign_model', 'code_amount_assign_db');
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
        //預設查詢條件
        if (!isset($where['money_type'])) {
            $where['money_type'] = 0;
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->code_amount_log_db->where($where)->join($join)->count();

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
        $result = $this->code_amount_log_db->where($where)
            ->select('t.*,t1.user_name,t1.type user_type')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['code_amount'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->code_amount_log_db->rules());

            if ($this->form_validation->run() == true) {
                $row['uid'] = $this->uid;
                unset($row['user_name']);
                if ($row['type'] == 2) {
                    $row['code_amount'] = $row['code_amount'] * -1;
                }
                $this->code_amount_log_db->is_action_log = true;
                $id = $this->code_amount_log_db->insert($row);

                //變動打碼量
                $this->code_amount_db->setAmount($row['uid'], $row['code_amount'], $id, $row['money_type']);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function check()
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
        if (!isset($where['money_type'])) {
            $where['money_type'] = 0;
        }
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 90);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->code_amount_db->where($where)->join($join)->count();

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
        $result = $this->code_amount_db->where($where)
            ->select('t.*,t1.user_name,t1.type user_type')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }

    public function assign()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 90);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'code_amount_log t1', 't.code_amount_log_id = t1.id', 'left'];
        $join[] = [$this->table_ . 'code_amount t2', 't.code_amount_id = t2.id', 'left'];
        $total = $this->code_amount_assign_db->where($where)->join($join)->count();

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
        $result = $this->code_amount_assign_db->where($where)
            ->select('t.*,t1.type,t1.category,t1.bet_record_id,t1.code_amount,t1.description,t2.type code_type,t2.code_amount_need')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $code_amount = $code_amount_use = 0;
        foreach ($result as $key => $row) {
            $bet = $this->code_amount_log_db->getBetRecord($row['category'], $row['bet_record_id']);
            $row['total_p_value'] = $bet !== null ? $bet['total_p_value'] : 0;
            $row['c_value'] = $bet !== null ? $bet['c_value'] : 0;
            $row['lottery_name'] = $bet !== null ? $bet['lottery_name'] : '无';

            $code_amount = bcadd($code_amount, $row['code_amount'], 2);
            $code_amount_use = bcadd($code_amount_use, $row['code_amount_use'], 2);
            $result[$key] = $row;
        }

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'          => $result,
            'total'           => $total,
            'where'           => $where,
            'order'           => $order,
            'params_uri'      => $params_uri,
            'code_amount'     => $code_amount,
            'code_amount_use' => $code_amount_use,
        ]);
    }

    public function user_name_check($user_name)
    {
        $row = $this->user_db->where([
            't.user_name' => $user_name,
        ])->result_one();

        if ($row === null) {
            $this->form_validation->set_message('user_name_check', "找不到 {$user_name} 此用户名。");
            return false;
        } else {
            $this->uid = $row['id'];
            return true;
        }
    }
}
