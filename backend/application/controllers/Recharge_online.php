<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge_online extends MY_Controller
{
    public $id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('recharge_online_model', 'recharge_online_db');
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

        $user_group = $this->user_group_db->getList();

        // get total.
        $total = $this->recharge_online_db->where($where)->count();

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
        $result = $this->recharge_online_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        foreach ($result as $key => $row) {
            $user_group_ids = explode(',', $row['user_group_ids']);
            $row['user_group'] = [];
            foreach ($user_group_ids as $val) {
                if (isset($user_group[$val])) {
                    $row['user_group'][] = $user_group[$val];
                }
            }
            $row['user_group'] = implode(',', $row['user_group']);
            $row['handsel_max'] = (float)$row['handsel_max'];
            $row['min_money'] = (float)$row['min_money'];
            $row['max_money'] = (float)$row['max_money'];
            $row['day_max_money'] = (float)$row['day_max_money'];
            $result[$key] = $row;
        }

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'user_group' => $user_group,
        ]);
    }

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->recharge_online_db->rules());

            if ($this->form_validation->run() == true) {
                $row['user_group_ids'] = implode(',', $row['user_group_ids']);
                $this->recharge_online_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->recharge_online_db->row($id);
                $row['user_group_ids'] = explode(',', $row['user_group_ids']);
            }
        }

        $data['row'] = $row;
        $data['user_group'] = $this->user_group_db->getList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');

            $this->recharge_online_db->update([
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

            $this->form_validation->set_rules($this->recharge_online_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['user_group_ids'] = implode(',', $row['user_group_ids']);

                $this->recharge_online_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->recharge_online_db->row($id);
            $row['user_group_ids'] = explode(',', $row['user_group_ids']);
        }

        $data['row'] = $row;
        $data['user_group'] = $this->user_group_db->getList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->recharge_online_db->update([
                'id'        => $id,
                'is_delete' => 1
            ]);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
