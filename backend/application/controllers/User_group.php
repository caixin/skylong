<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_group extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_group_model', 'user_group_db');
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

        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }

        // get total.
        $total = $this->user_group_db->where($where)->count();

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
        $result = $this->user_group_db->where($where)
            ->order($order)
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
            'operator'   => $operator,
        ]);
    }

    public function create($operator_id, $id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['operator_id'] = $operator_id;
        $row['status'] = 1;
        $row['min_extract_money'] = 0;
        $row['max_extract_money'] = 100;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_group_db->rules());

            if ($this->form_validation->run() == true) {
                $this->user_group_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->user_group_db->row($id);
            }
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');
            //關閉時將該分層會員移至系統默認
            if ($status == 0) {
                $this->user_group_db->setDefaultID($id);
            }
            $this->user_group_db->update([
                'id'     => $id,
                'status' => $status
            ]);
            echo 'done';
            return;
        }

        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_group_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->user_group_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->user_group_db->row($id);
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->user_group_db->setDefaultID($id);
            $this->user_group_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
