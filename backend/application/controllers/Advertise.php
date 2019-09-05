<?php defined('BASEPATH') || exit('No direct script access allowed');

class Advertise extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('advertise_model', 'advertise_db');
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
        $search_params = param_process($params, ['operator_id,id', 'asc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $join[] = [$this->table_ . 'operator t1', 't.operator_id=t1.id', 'left'];
        $total = $this->advertise_db->where($where)->join($join)->count();

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
        $result = $this->advertise_db->where($where)
            ->select('t.*,t1.name as operator_name')
            ->join($join)
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
        $row['sort'] = 0;
        $row['status'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();
            $this->form_validation->set_rules($this->advertise_db->rules());

            if ($this->form_validation->run() == true) {
                if ($row['operator_id'] == 0) {
                    $operator = $this->operator_db->getList(0);
                    foreach ($operator as $key => $value) {
                        $row['operator_id'] = $key;
                        $this->advertise_db->insert($row);
                    }
                } else {
                    $this->advertise_db->insert($row);
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

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');

            $this->advertise_db->update([
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
            $this->form_validation->set_rules($this->advertise_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->advertise_db->update($row);
                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->advertise_db->row($id);
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
            $this->advertise_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
