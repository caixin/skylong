<?php defined('BASEPATH') || exit('No direct script access allowed');

class Sysconfig extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($groupid=1)
    {
        $this->load->library('form_validation');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), "$this->cur_url/$groupid"));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(4);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }

        $result = $this->sysconfig_db->where([
            'operator_ids' => [0,$where['operator_id']],
            'groupid >'    => 0,
        ])->order([
            'operator_id' => 'desc',
            'sort'        => 'asc'
        ])->result();
        $result = $this->sysconfig_db->groupList($result);

        $this->layout->view($this->cur_url, [
            'result'   => $result,
            'where'    => $where,
            'groupid'  => $groupid,
            'operator' => $operator,
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->sysconfig_db->rules());

            if ($this->form_validation->run() == true) {
                $operator = $this->operator_db->getList(1);
                if ($row['operator_id'] == 0) {
                    $this->sysconfig_db->insert($row);
                } else {
                    foreach ($operator as $operator_id => $name) {
                        $row['operator_id'] = $operator_id;
                        $this->sysconfig_db->insert($row);
                    }
                }

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit()
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $operator_id = $this->input->post('operator_id');
            $groupid = $this->input->post('groupid');
            $varname = $this->input->post('varname');
            $sort = $this->input->post('sort');
            $update = [];
            foreach ($varname as $key => $val) {
                $update[] = [
                    'id'    => $key,
                    'value' => $val,
                    'sort'  => $sort[$key]
                ];
            }
            $this->sysconfig_db->update_batch($update, 'id');

            $this->session->set_flashdata('message', '编辑成功!');
            redirect("{$this->router->class}/index/$groupid/operator_id/$operator_id");
        }
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->sysconfig_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
