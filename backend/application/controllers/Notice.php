<?php defined('BASEPATH') || exit('No direct script access allowed');

class Notice extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notice_model', 'notice_db');
        $this->load->model('Operator_model', 'operator_db');
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
        $search_params = param_process($params, ['sort', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $total = $this->notice_db->where($where)->count();

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
        $result = $this->notice_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $this->operator_db->getList(0)
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['status'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->notice_db->rules());

            if ($this->form_validation->run() == true) {
                if ($row['operator_id'] == 0) {
                    $operator = $this->operator_db->getList(0);
                    foreach ($operator as $key => $value) {
                        $row['operator_id'] = $key;
                        $id = $this->notice_db->insert($row);
                    }
                } else {
                    $id = $this->notice_db->insert($row);
                }

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id = '')
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');

            $this->notice_db->update([
                'id'     => $id,
                'status' => $status,
            ]);
            echo 'done';
            return;
        }

        $this->load->library('form_validation');
        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $this->form_validation->set_rules($this->notice_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->notice_db->update($row);
                $this->session->set_flashdata('message', '編輯成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->notice_db->row($id);
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
            $this->notice_db->delete($id);
            $this->session->set_flashdata('message', '刪除成功!');
            echo 'done';
        }
    }
}
