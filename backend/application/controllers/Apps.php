<?php defined('BASEPATH') || exit('No direct script access allowed');

class Apps extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Apps_model', 'apps_db');
        $this->load->model('Operator_model', 'operator_db');
        $this->apps_db->is_action_log = true;
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
        $total = $this->apps_db->where($where)->count();

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
        $result = $this->apps_db->where($where)
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

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['status'] = 1;
        $row['is_vip'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->apps_db->rules());

            if ($this->form_validation->run() == true) {
                if ($row['operator_id'] == 0) {
                    $operator = $this->operator_db->getList(0);
                    foreach ($operator as $key => $value) {
                        $row['operator_id'] = $key;
                        $id = $this->apps_db->insert($row);
                    }
                } else {
                    $id = $this->apps_db->insert($row);
                }

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->apps_db->row($id);
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

            $this->apps_db->update([
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
            $this->form_validation->set_rules($this->apps_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->apps_db->update($row);
                $this->session->set_flashdata('message', '編輯成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->apps_db->row($id);
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
            $this->apps_db->delete($id);
            $this->session->set_flashdata('message', '刪除成功!');
            echo 'done';
        }
    }

    public function jump_url_check($jump_url)
    {
        $is_vip = $this->input->post('is_vip');
        if ($is_vip == 1 && strpos($jump_url, 'isVipApp=1') === false) {
            $this->form_validation->set_message('jump_url_check', '若为VIP包则跳转URL中需有参数 isVipApp=1');
            return false;
        } elseif ($is_vip == 0 && strpos($jump_url, 'isVipApp=1') !== false) {
            $this->form_validation->set_message('jump_url_check', '若不是VIP包则跳转URL中不需有参数 isVipApp=1');
            return false;
        } else {
            return true;
        }
    }
}
