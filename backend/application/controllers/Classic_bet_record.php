<?php defined('BASEPATH') || exit('No direct script access allowed');

class Classic_bet_record extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->ettm_classic_bet_record_db->is_action_log = true;
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

        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'ettm_classic_wanfa_detail t2', 't.wanfa_detail_id = t2.id', 'left'];
        $total = $this->ettm_classic_bet_record_db->where($where)->join($join)->count();

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
        $result = $this->ettm_classic_bet_record_db->where($where)
            ->select('t.*,t1.type user_type,t1.user_name,t2.values')->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        $total_p_value = $c_value = $bet_eff = $profit = 0;
        foreach ($result as $key => $row) {
            $row['bet_eff'] = 0;
            $row['profit'] = 0;
            $row['profit_color'] = '#000';
            if ($row['status'] == 1) {
                $row['bet_eff'] = $this->ettm_classic_bet_record_db->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win']);
                $row['profit'] = (float) bcsub($row['total_p_value'], $row['c_value'], 2);
                $row['profit'] = $row['profit'] > 0 ? '+' . $row['profit'] : $row['profit'];
            }
            $result[$key] = $row;
            //當頁總計
            $total_p_value = bcadd($total_p_value, $row['total_p_value'], 2);
            $c_value       = bcadd($c_value, $row['c_value'], 2);
            $bet_eff       = bcadd($bet_eff, $row['bet_eff'], 2);
            $profit        = bcadd($profit, $row['profit'], 2);
        }

        $footer['total_p_value'] = (float) $total_p_value;
        $footer['c_value']       = (float) $c_value;
        $footer['bet_eff']       = (float) $bet_eff;
        $footer['profit']        = (float) $profit;
        //總計
        $footer_total = $this->ettm_classic_bet_record_db->escape(false)
            ->select('SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,SUM(CASE t.status WHEN 1 THEN t.total_p_value - t.c_value ELSE 0 END) profit,
                     SUM(CASE t.status WHEN 1 THEN (CASE t.is_lose_win WHEN 0 THEN t.total_p_value ELSE (CASE WHEN t.c_value - t.total_p_value > t.total_p_value THEN t.total_p_value ELSE t.c_value - t.total_p_value END) END) ELSE 0 END) bet_eff')
            ->where($where)->join($join)->result_one();
        $footer_total['total_p_value'] = (float) $footer_total['total_p_value'];
        $footer_total['c_value']       = (float) $footer_total['c_value'];
        $footer_total['bet_eff']       = (float) $footer_total['bet_eff'];
        $footer_total['profit']        = (float) $footer_total['profit'];

        if (isset($where['sidebar']) && $where['sidebar'] == 0) {
            $this->layout->sidebar = false;
        }
        $this->layout->view($this->cur_url, [
            'result'       => $result,
            'total'        => $total,
            'where'        => $where,
            'order'        => $order,
            'params_uri'   => $params_uri,
            'footer'       => $footer,
            'footer_total' => $footer_total,
            'lottery'      => $this->ettm_lottery_db->getLotteryList(1),
        ]);
    }

    /**
     * 退款
     */
    public function refund()
    {
        $this->load->model('prediction_buy_model', 'prediction_buy_db');
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $data = $this->ettm_classic_bet_record_db->row($id);

            //退款事務
            $this->base_model->trans_start();
            $this->ettm_classic_bet_record_db->refundBet($data['lottery_id'], $data['qishu'], $data['uid']);
            //熱門預測退款
            $this->prediction_buy_db->refund($data['lottery_id'], $data['qishu'], $data['uid']);
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() !== false) {
                $this->session->set_flashdata('message', '退款成功!');
                echo 'done';
            }
        }
    }
}
