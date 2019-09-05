<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_nav extends MY_Controller
{
    public $id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/admin_nav_model', 'admin_nav_db');
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
        $total = $this->admin_nav_db->where($where)->count();

        // config pagination.
        $per_page = 99999; //導航列表不用分頁
        $offset = ($page - 1) * $per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $per_page,
            'cur_page'   => $page
        ]);

        // get main data.
        $result = $this->admin_nav_db->where($where)
            ->order($order)
            ->limit([$offset, $per_page])
            ->result();
        $result = $this->admin_nav_db->treeSort($result);

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }

    public function create($pid = 0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['pid'] = $pid;
        $row['sort'] = 0;
        $row['status'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->admin_nav_db->rules());

            if ($this->form_validation->run() == true) {
                $row['path'] = 0;
                if ($row['pid'] > 0) {
                    $parent = $this->admin_nav_db->row($row['pid']);
                    $row['path'] = $parent['path'] . '-' . $row['pid'];
                }
                $this->admin_nav_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
        $data['nav'] = $this->admin_nav_db->getDropDownList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');

            $this->admin_nav_db->update([
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
            $this->id = $id;

            $this->form_validation->set_rules($this->admin_nav_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['path'] = 0;
                if ($row['pid'] > 0) {
                    $parent = $this->admin_nav_db->row($row['pid']);
                    $row['path'] = $parent['path'] . '-' . $row['pid'];
                }

                $this->admin_nav_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                //redirect($this->session->userdata('uri'));
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->admin_nav_db->row($id);
        }

        $data['row'] = $row;
        $data['nav'] = $this->admin_nav_db->getDropDownList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->admin_nav_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }

    public function url_check($url)
    {
        $row = $this->admin_nav_db->where([
            't.url'   => $url,
            't.id <>' => $this->id
        ])->result_one();

        if ($row !== null) {
            $this->form_validation->set_message('url_check', '{field} 已存在。');
            return false;
        } else {
            return true;
        }
    }
}
