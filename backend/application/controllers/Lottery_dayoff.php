<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_dayoff extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_dayoff_model', 'ettm_lottery_dayoff_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
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

        if (!isset($where['dayoff1'])) {
            $where['dayoff1'] = date('Y') . '-01-01';
        }
        if (!isset($where['dayoff2'])) {
            $where['dayoff2'] = date('Y') . '-12-31';
        }

        // get total.
        $total = $this->ettm_lottery_dayoff_db->where($where)->count();

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
        $result = $this->ettm_lottery_dayoff_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $this->ettm_lottery_db->getLotteryListByTypeid(7),
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_dayoff_db->rules());

            if ($this->form_validation->run() == true) {
                $this->ettm_lottery_dayoff_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
        $data['lottery'] = $this->ettm_lottery_db->getLotteryListByTypeid(7);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_dayoff_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->ettm_lottery_dayoff_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_lottery_dayoff_db->row($id);
        }

        $data['row'] = $row;
        $data['lottery'] = $this->ettm_lottery_db->getLotteryListByTypeid(7);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_lottery_dayoff_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
