<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/admin_model', 'admin_db');
        $this->load->model('backend/admin_role_model', 'admin_role_db');
        $this->load->model('user_model', 'user_db');
        $this->admin_db->is_action_log = true;
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
        $search_params = param_process($params, ['id', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $join[] = [$this->table_ . 'admin_role t1', 't.roleid = t1.id', 'left'];
        $join[] = [$this->table_ . 'user t2', 't.uid = t2.id', 'left'];
        $total = $this->admin_db->where($where)->join($join)->count();

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
        $result = $this->admin_db->where($where)
            ->select('t.*,t2.user_name')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'role'       => array_column($this->admin_role_db->result(), 'name', 'id')
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['otp_check'] = 0;
        $row['status'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->admin_db->rules());

            if ($this->form_validation->run() == true) {
                $user = $row;
                unset($row['security_pwd']);
                $id = $this->admin_db->insert($row);
                //如為代理則新增玩家帳號
                if ($row['is_agent'] == 1) {
                    $role = $this->admin_role_db->row($row['roleid']);
                    $uid = $this->user_db->insert([
                        'user_name'    => $user['mobile'],
                        'user_pwd'     => $user['password'],
                        'security_pwd' => $user['security_pwd'],
                        'real_name'    => $user['username'],
                        'mobile'       => $user['mobile'],
                        'agent_id'     => $id,
                        'operator_id'  => $role['allow_operator'],
                    ]);
                    $this->admin_db->update([
                        'id'  => $id,
                        'uid' => $uid,
                    ]);
                }

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
        $data['role'] = $this->admin_role_db->getRoleList($this->session->userdata('roleid'));

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $column = $this->input->post('column');
            $value = $this->input->post('value');

            $this->admin_db->update([
                'id'    => $id,
                $column => $value
            ]);
            echo 'done';
            return;
        }

        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $row['id'] = $id;
            $this->admin_db->update($row);

            $this->session->set_flashdata('message', '编辑成功!');
            echo "<script>parent.window.layer.close();parent.location.reload();</script>";
            return;
        } else {
            $row = $this->admin_db->row($id);
        }

        $data['row'] = $row;
        $data['role'] = $this->admin_role_db->getRoleList($this->session->userdata('roleid'));

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->admin_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }

    public function action_log()
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
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $total = $this->admin_action_log_db->where($where)->count();

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
        $result = $this->admin_action_log_db->where($where)
            ->order($order)
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

    public function login_log()
    {
        $this->load->model('backend/admin_login_log_model', 'admin_login_log_db');
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
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $total = $this->admin_login_log_db->where($where)->count();

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
        $result = $this->admin_login_log_db->where($where)
            ->order($order)
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

    public function clear_cache()
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $clear = $this->input->post('clear');
            if (isset($clear[0])) {
                $this->cache->redis->clean();
            }
            redirect($this->cur_url);
        }

        $this->layout->view($this->cur_url);
    }

    public function username_check($username)
    {
        if ($this->input->post('is_agent') == 1) {
            $row = $this->user_db->where([
                't.user_name' => $username,
            ])->result_one();

            if ($row === null) {
                return true;
            } else {
                $this->form_validation->set_message('username_check', '此 {field} 已存在。');
                return false;
            }
        }
        return true;
    }
}
