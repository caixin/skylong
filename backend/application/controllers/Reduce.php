<?php defined('BASEPATH') || exit('No direct script access allowed');

class Reduce extends MY_Controller
{
    public $id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_reduce_model', 'ettm_reduce_db');
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

        // get total.
        $total = $this->ettm_reduce_db->where($where)->count();

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
        $result = $this->ettm_reduce_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $items = json_decode($row['items'], true);
            $row['items_str'] = '';
            foreach ((array)$items as $item) {
                $row['items_str'] .= "區間:$item[interval], 降賠%數:$item[value]%, 次數:$item[count]<br>";
            }
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'       => $result,
            'total'        => $total,
            'where'        => $where,
            'order'        => $order,
            'params_uri'   => $params_uri,
            'operator'     => $this->operator_db->getList(0),
            'lottery_type' => $this->ettm_lottery_type_db->getTypeList(1),
            'lottery'      => $this->ettm_lottery_db->getLotteryList(1),
        ]);
    }

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['items'] = [];

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();
            $items = [];
            foreach ($row['interval'] as $key => $val) {
                $items[] = [
                    'interval' => $val,
                    'value'    => $row['value'][$key],
                    'count'    => $row['count'][$key],
                ];
            }
            unset($row['interval'],$row['value'],$row['count']);

            $this->form_validation->set_rules($this->ettm_reduce_db->rules());

            if ($this->form_validation->run() == true) {
                $row['items'] = json_encode($items);
                $this->ettm_reduce_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            } else {
                $row['items'] = $items;
            }
        } else {
            if ($id != 0) {
                $row = $this->ettm_reduce_db->row($id);
                $row['items'] = (array)json_decode($row['items'], true);
            }
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['lottery_type'] = $this->ettm_lottery_type_db->getTypeList(1);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $this->id = $id;
            $items = [];
            foreach ($row['interval'] as $key => $val) {
                $items[] = [
                    'interval' => $val,
                    'value'    => $row['value'][$key],
                    'count'    => $row['count'][$key],
                ];
            }
            unset($row['interval'],$row['value'],$row['count']);

            $this->form_validation->set_rules($this->ettm_reduce_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['items'] = json_encode($items);
                $this->ettm_reduce_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            } else {
                $row['items'] = $items;
            }
        } else {
            $row = $this->ettm_reduce_db->row($id);
            $row['items'] = (array)json_decode($row['items'], true);
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['lottery_type'] = $this->ettm_lottery_type_db->getTypeList(1);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_reduce_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }

    public function unique_check()
    {
        $row = $this->ettm_reduce_db->where([
            't.id <>'         => $this->id,
            'operator_id'     => $this->input->post('operator_id'),
            'lottery_type_id' => $this->input->post('lottery_type_id'),
            'lottery_id'      => $this->input->post('lottery_id'),
            'type'            => $this->input->post('type'),
        ])->result_one();

        if ($row !== null) {
            $this->form_validation->set_message('unique_check', '运营商名称,彩种大类,彩种,类型 相同的資料已存在。');
            return false;
        } else {
            return true;
        }
    }
}
