<?php defined('BASEPATH') || exit('No direct script access allowed');

class Module extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('module_model', 'module_db');
        $this->load->model('module_operator_model', 'module_operator_db');
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
        $total = $this->module_db->where($where)->count();

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
        $result = $this->module_db->where($where)
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

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['domain_url'] = [];

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();
            $param = [];
            foreach ($row['param_key'] as $key => $val) {
                $param[$val] = $row['param_val'][$key];
            }
            unset($row['param_key'],$row['param_val']);

            $this->form_validation->set_rules($this->module_db->rules());

            if ($this->form_validation->run() == true) {
                $row['param'] = json_encode($param);
                $this->module_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            } else {
                $row['param'] = $param;
            }
        } else {
            if ($id != 0) {
                $row = $this->module_db->row($id);
                $row['param'] = (array)json_decode($row['param'], true);
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
            $param = [];
            foreach ($row['param_key'] as $key => $val) {
                $param[$val] = $row['param_val'][$key];
            }
            unset($row['param_key'],$row['param_val']);

            $this->form_validation->set_rules($this->module_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['param'] = json_encode($param);
                $this->module_db->update($row);
                //各營運商更新
                $result = $this->module_operator_db->where([
                    'module_id' => $id,
                ])->result();
                foreach ($result as $arr) {
                    $param_old = json_decode($arr['param'], true);
                    $param_new = [];
                    foreach ($param as $key => $val) {
                        if (isset($param_old[$key])) {
                            $param_new[$key] = $param_old[$key];
                        } else {
                            $param_new[$key] = $val;
                        }
                    }
                    $this->module_operator_db->update([
                        'operator_id' => $arr['operator_id'],
                        'module_id'   => $arr['module_id'],
                        'param'       => json_encode($param_new),
                    ]);
                }

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->module_db->row($id);
            $row['param'] = (array)json_decode($row['param'], true);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->module_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
