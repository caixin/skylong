<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_role extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/admin_role_model', 'admin_role_db');
        $this->load->model('operator_model', 'operator_db');
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

        $operator = $this->operator_db->getList();

        // get total.
        $total = $this->admin_role_db->where($where)->count();

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
        $result = $this->admin_role_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $allow_operator = [];
            if ($row['allow_operator'] != '') {
                foreach (explode(',', $row['allow_operator']) as $val) {
                    $allow_operator[] = $operator[$val];
                }
            }
            $row['allow_operator'] = implode(',', $allow_operator);
            $result[$key] = $row;
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

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['allow_nav'] = [];

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->admin_role_db->rules());

            if ($this->form_validation->run() == true) {
                $row['allow_operator'] = implode(',', $row['allow_operator']);
                $row['allow_nav'] = json_encode($row['allow_nav']);
                $id = $this->admin_role_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->admin_role_db->row($id);
                $row['allow_operator'] = explode(',', $row['allow_operator']);
                $row['allow_nav'] = json_decode($row['allow_nav'], true);
            }
        }

        $data['row'] = $row;
        $data['nav'] = $this->session->userdata('roleid') == 1 ?
            $this->navList : $this->admin_role_db->filterAllowNav($this->navList, $this->allow_url);
        $data['operator'] = $this->operator_db->getList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->admin_role_db->rules());

            if ($this->form_validation->run() == true) {
                $row['allow_operator'] = implode(',', $row['allow_operator']);
                $row['allow_nav'] = json_encode($row['allow_nav']);
                $row['id'] = $id;

                $this->admin_role_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->admin_role_db->row($id);
            $row['allow_operator'] = explode(',', $row['allow_operator']);
            $row['allow_nav'] = json_decode($row['allow_nav'], true);
        }

        $data['row'] = $row;
        $data['nav'] = $this->session->userdata('roleid') == 1 ?
            $this->navList : $this->admin_role_db->filterAllowNav($this->navList, $this->allow_url);
        $data['operator'] = $this->operator_db->getList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->admin_role_db->update([
                'id'        => $id,
                'is_delete' => 1
            ]);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
