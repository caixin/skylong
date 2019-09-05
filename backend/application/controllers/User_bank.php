<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_bank extends MY_Controller
{
    public $uid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_bank_model', 'user_bank_db');
        $this->user_bank_db->is_action_log = true;
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

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->user_bank_db->join($join)->where($where)->count();

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
        $result = $this->user_bank_db->where($where)
            ->select('t.*,t1.user_name,t1.real_name,t1.mobile,t1.type user_type')->join($join)
            ->order($order)->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['status'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_bank_db->rules());

            if ($this->form_validation->run() == true) {
                $row['uid'] = $this->uid;
                unset($row['user_name']);
                $this->user_bank_db->insert($row);
                //將銀行卡姓名更新至USER資料表
                $this->user_db->update([
                    'id'        => $this->uid,
                    'real_name' => $row['bank_real_name'],
                ]);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_bank_db->edit_rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->user_bank_db->update($row);
                //將銀行卡姓名更新至USER資料表
                $this->user_db->update([
                    'id'        => $row['uid'],
                    'real_name' => $row['bank_real_name'],
                ]);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->user_bank_db->row($id);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function user_name_check($user_name)
    {
        $user = $this->user_db->where([
            't.user_name' => $user_name,
        ])->result_one();

        if ($user === null) {
            $this->form_validation->set_message('user_name_check', "找不到 {$user_name} 此用户名。");
            return false;
        } else {
            $this->uid = $user['id'];
            $row = $this->user_bank_db->where([
                't.uid' => $this->uid,
            ])->result_one();

            if ($row === null) {
                return true;
            } else {
                $this->form_validation->set_message('user_name_check', "此用户名 {$user_name} 已绑定银行卡。");
                return false;
            }
        }
    }
}
