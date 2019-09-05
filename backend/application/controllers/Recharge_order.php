<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge_order extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('recharge_offline_model', 'recharge_offline_db');
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->recharge_order_db->is_action_log = true;
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
        $search_params = param_process($params, ['id', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['create_time1']) && !isset($where['check_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2']) && !isset($where['check_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'recharge_online t2', 't.line_id = t2.id', 'left'];
        $join[] = [$this->table_ . 'recharge_offline t3', 't.line_id = t3.id', 'left'];
        $total = $this->recharge_order_db->where($where)->join($join)->count();

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
        $result = $this->recharge_order_db->where($where)
            ->select('t.*,t1.user_name,t1.type user_type,t2.handsel_percent handsel_percent1,t2.handsel_max handsel_max1,t3.handsel_percent handsel_percent2,t3.handsel_max handsel_max2')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $handsel = bcmul($row['money'], bcdiv($row["handsel_percent$row[type]"], 100, 4), 2);
            $row['handsel'] = $handsel > $row["handsel_max$row[type]"] ? $row["handsel_max$row[type]"] : $handsel;
            
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
        ]);
    }

    public function check($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->recharge_order_db->rules());

            if ($this->form_validation->run() == true) {
                //事務
                $this->base_model->trans_start();
                $row['id'] = $id;
                $row['check_time'] = date('Y-m-d H:i:s');
                $row['check_by'] = $this->session->userdata('username');
                $this->recharge_order_db->update($row);

                if ($row['status'] == 1) {
                    $this->recharge_order_db->orderSuccess($id, 2);
                }
                $this->base_model->trans_complete();
                //完成事務

                $this->session->set_flashdata('message', '审核完成!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $row = $this->recharge_order_db->select('t.*,t1.user_name')->join($join)
                ->where(['t.id' => $id])->result_one();
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }
}
