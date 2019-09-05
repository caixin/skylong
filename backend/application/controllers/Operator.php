<?php defined('BASEPATH') || exit('No direct script access allowed');

class Operator extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('operator_model', 'operator_db');
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

        // get total.
        $total = $this->operator_db->where($where)->count();

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
        $result = $this->operator_db->where($where)
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

    public function create($id = 0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['domain_url'] = [];
        $row['classic_adjustment'] = 0;
        $row['official_adjustment'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->operator_db->rules());

            if ($this->form_validation->run() == true) {
                $default_id = $this->operator_db->getDefaultID();

                $row['domain_url'] = implode(',', $row['domain_url']);
                $operator_id = $this->operator_db->insert($row);
                //複製網站基本設置
                $sysconfig = $this->sysconfig_db->where(['operator_id' => $default_id])->result();
                foreach ($sysconfig as $key => $arr) {
                    unset($arr['id']);
                    $arr['operator_id'] = $operator_id;
                    $sysconfig[$key] = $arr;
                }
                $this->sysconfig_db->insert_batch($sysconfig);
                //新增默認分層
                $arr = $this->user_group_db->where([
                    'operator_id' => $default_id,
                    'is_default'  => 1,
                ])->result_one();
                unset($arr['id']);
                $arr['operator_id'] = $operator_id;
                $this->user_group_db->insert($arr);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->operator_db->row($id);
                $row['domain_url'] = explode(',', $row['domain_url']);
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

            $this->form_validation->set_rules($this->operator_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['domain_url'] = implode(',', $row['domain_url']);
                $this->operator_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->operator_db->row($id);
            $row['domain_url'] = explode(',', $row['domain_url']);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->operator_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
