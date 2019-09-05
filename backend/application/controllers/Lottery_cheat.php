<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_cheat extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_cheat_model', 'ettm_lottery_cheat_db');
    }

    /**
     * 控制獲利
     */
    public function index()
    {
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $where['type'] = 0;

        $this->layout->view($this->cur_url, [
            'result'   => $this->ettm_lottery_cheat_db->where($where)->result(),
            'lottery'  => array_column($this->ettm_lottery_db->where(['is_custom'=>1])->result(), 'name', 'id'),
            'where'      => $where,
            'operator' => $operator,
        ]);
    }

    /**
     * 控制不開豹子
     */
    public function triple()
    {
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $where['type'] = 1;

        $this->layout->view($this->cur_url, [
            'result'   => $this->ettm_lottery_cheat_db->where($where)->result(),
            'lottery'  => array_column($this->ettm_lottery_db->where(['is_custom'=>1])->result(), 'name', 'id'),
            'where'    => $where,
            'operator' => $operator,
        ]);
    }
    /**
     * 控制開獎號碼
     */
    public function numbers()
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
        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $where['type'] = 2;

        // get total.
        $total = $this->ettm_lottery_cheat_db->where($where)->count();

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        $result = $this->ettm_lottery_cheat_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => array_column($this->ettm_lottery_db->where(['is_custom'=>1,'lottery_type_id'=>4])->result(), 'name', 'id'),
            'operator'   => $operator,
        ]);
    }
    /**
     * 控制必赢机率
     */
    public function probability()
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
        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $where['type'] = 3;

        // get total.
        $total = $this->ettm_lottery_cheat_db->where($where)->count();

        // config pagination.
        $offset = ($page - 1) * $this->per_page;
        $this->pagination->initialize([
            'base_url'   => site_url("$this->cur_url/$params_uri/page"),
            'first_url'  => site_url("$this->cur_url/$params_uri/page/1"),
            'total_rows' => $total,
            'per_page'   => $this->per_page,
            'cur_page'   => $page
        ]);

        $result = $this->ettm_lottery_cheat_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => array_column($this->ettm_lottery_db->where(['is_custom'=>1])->result(), 'name', 'id'),
            'operator' => $operator,
        ]);
    }

    public function create($operator_id, $type)
    {
        $this->load->library('form_validation');
        //預設值
        $row['status'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_cheat_db->rules());

            if ($this->form_validation->run() == true) {
                $row['operator_id'] = $operator_id;
                $row['type'] = $type;
                $this->ettm_lottery_cheat_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
        $where['is_custom'] = 1;
        switch ($type) {
            case 1: $where['lottery_type_id'] = [3,7]; break;
            case 2: $where['lottery_type_id'] = 4; break;
        }
        $lottery = array_column($this->ettm_lottery_db->where($where)->result(), 'name', 'id');
        if ($type == 2) {
            $data['lottery'] = $lottery;
        } else {
            $result = array_column($this->ettm_lottery_cheat_db->where(['type'=>$type])->result(), 'lottery_id', 'lottery_id');
            $data['lottery'] = array_diff_key($lottery, $result);
        }
        $data['type'] = $type;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        if ($this->input->is_ajax_request()) {
            $status = $this->input->post('status');
            $row = $this->ettm_lottery_cheat_db->row($id);

            $this->ettm_lottery_cheat_db->update([
                'id'         => $id,
                'lottery_id' => $row['lottery_id'],
                'status'     => $status
            ]);
            echo 'done';
            return;
        }
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $row['id'] = $id;
            $this->ettm_lottery_cheat_db->update($row);

            $this->session->set_flashdata('message', '编辑成功!');
            echo "<script>parent.window.layer.close();parent.location.reload();</script>";
            return;
        } else {
            $row = $this->ettm_lottery_cheat_db->row($id);
        }

        $data['row'] = $row;
        $data['type'] = $row['type'];

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_lottery_cheat_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
