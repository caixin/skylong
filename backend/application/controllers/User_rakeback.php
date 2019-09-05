<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_rakeback extends MY_Controller
{
    public $id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_rakeback_model', 'user_rakeback_db');
        $this->load->model('user_group_model', 'user_group_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
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

        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $where['operator_id'] = $operator_id;
                break;
            }
        }

        // get total.
        $join[] = [$this->table_ . 'ettm_lottery_type t1', 't.lottery_type_id = t1.id', 'left'];
        $join[] = [$this->table_ . 'ettm_lottery t2', 't.lottery_id = t2.id', 'left'];
        $total = $this->user_rakeback_db->where($where)->join($join)->count();

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
        $result = $this->user_rakeback_db->where($where)
            ->select('t.*,t1.name lottery_type_name,t2.name lottery_name')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        foreach ($result as $key => $row) {
            $row['lottery_type_name'] = $row['lottery_type_id'] == 0 ? '全部' : $row['lottery_type_name'];
            $row['lottery_name'] = $row['lottery_id'] == 0 ? '全部' : $row['lottery_name'];
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'user_group' => $this->user_group_db->getList($where['operator_id']),
            'operator'   => $operator,
        ]);
    }

    public function create($operator_id, $id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['operator_id'] = $operator_id;
        $row['start_money'] = 1;
        $row['rakeback_per'] = 1;
        $row['rakeback_max'] = 1;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_rakeback_db->rules());

            if ($this->form_validation->run() == true) {
                $this->user_rakeback_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->user_rakeback_db->row($id);
            }
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['user_group'] = $this->user_group_db->getList($row['operator_id']);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');

            $this->user_group_db->update([
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

            $this->form_validation->set_rules($this->user_rakeback_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;

                $this->user_rakeback_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->user_rakeback_db->row($id);
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['user_group'] = $this->user_group_db->getList($row['operator_id']);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->user_rakeback_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
