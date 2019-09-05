<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_withdraw extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->user_withdraw_db->is_action_log = true;
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
        $total = $this->user_withdraw_db->where($where)->join($join)->count();

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
        $result = $this->user_withdraw_db->where($where)
            ->select('t.*,t1.user_name,t1.type user_type')
            ->join($join)->order($order)
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
        ]);
    }

    public function check($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->user_withdraw_db->rules());

            if ($this->form_validation->run() == true) {
                //事務
                $this->base_model->trans_start();
                $row['id'] = $id;
                $row['check_time'] = date('Y-m-d');
                $row['check_by'] = $this->session->userdata('username');
                $this->user_withdraw_db->update($row);

                $data = $this->user_withdraw_db->row($id);
                if ($data['status'] == 2) {
                    //提現失敗
                    $this->user_db->addMoney($data['uid'], $data['order_sn'], 1, $data['money'], '审合不通过无法提现');
                }
                $user = $this->user_db->row($data['uid']);
                $update['id'] = $user['id'];
                //審核完成 將凍結金額扣除
                $frozen = bcsub($user['money_frozen'], $data['money'], 2);
                $update['money_frozen'] = $frozen < 0 ? 0 : $frozen;
                //標記提現用戶
                if ($data['status'] == 1 && ($user['mode'] & 4) == 0) {
                    $update['mode'] = $user['mode'] + 4;
                }
                $this->user_db->update($update);
                $this->base_model->trans_complete();
                //完成事務

                $this->session->set_flashdata('message', '审核完成!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $row = $this->user_withdraw_db->select('t.*,t1.user_name')->join($join)
                ->where(['t.id' => $id])->result_one();
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }
}
