<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('prediction_model', 'prediction_db');
        $this->load->model('prediction_buy_model', 'prediction_buy_db');
        $this->load->model('prediction_relief_model', 'prediction_relief_db');
        $this->load->model('prediction_assign_model', 'prediction_assign_db');
        $this->load->model('prediction_robot_bet_model', 'prediction_robot_bet_db');
        $this->load->model('prediction_robot_setting_model', 'prediction_robot_setting_db');
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
        $total = $this->prediction_db->where($where)->count();

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
        $result = $this->prediction_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->prediction_db->rules());

            if ($this->form_validation->run() == true) {
                $this->prediction_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->prediction_db->row($id);
        }

        $data['row'] = $row;
        $data['lottery'] = $this->ettm_lottery_db->getLotteryList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id = '')
    {
        $this->load->library('form_validation');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $this->form_validation->set_rules($this->prediction_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->prediction_db->update($row);

                $this->session->set_flashdata('message', '編輯成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->prediction_db->row($id);
        }

        $data['row'] = $row;
        $data['lottery'] = $this->ettm_lottery_db->getLotteryList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->prediction_db->update([
                'id'        => $id,
                'is_delete' => 1
            ]);
            $this->session->set_flashdata('message', '刪除成功!');
            echo 'done';
        }
    }

    public function buy()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $join[] = [$this->table_.'user t1','t.uid = t1.id','left'];
        $join[] = [$this->table_.'prediction t2','t.prediction_id = t2.id','left'];
        $total = $this->prediction_buy_db->where($where)->join($join)->count();

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
        $result = $this->prediction_buy_db->select('t.*,t1.user_name,t2.lottery_id,t2.name')
            ->where($where)
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
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function relief()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $join[] = [$this->table_.'user t1','t.uid = t1.id','left'];
        $join[] = [$this->table_.'prediction t2','t.prediction_id = t2.id','left'];
        $total = $this->prediction_relief_db->where($where)->join($join)->count();

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
        $result = $this->prediction_relief_db->select('t.*,t1.user_name,t2.lottery_id,t2.name')
            ->where($where)
            ->join($join)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $payload = json_decode($row['payload'], true);
            $row['buy'] = '';
            foreach ($payload['digits'] as $digits => $price) {
                $row['buy'] .= $row['name'].Prediction_buy_model::$digitsList[$digits]."：{$price}元<br>";
            }
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function assign()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        // get total.
        $join[] = [$this->table_.'prediction_relief t1','t.prediction_relief_id = t1.id','left'];
        $join[] = [$this->table_.'recharge_order t2','t.recharge_order_id = t2.id','left'];
        $join[] = [$this->table_.'user t3','t1.uid = t3.id','left'];
        $join[] = [$this->table_.'prediction t4','t1.prediction_id = t4.id','left'];
        $total = $this->prediction_assign_db->where($where)->join($join)->count();

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
        $result = $this->prediction_assign_db->select('t.*,
                t1.qishu,t1.bet_money,
                t2.order_sn,t2.money,
                t3.user_name,
                t4.lottery_id,t4.name
            ')
            ->where($where)
            ->join($join)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        
        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }
}
