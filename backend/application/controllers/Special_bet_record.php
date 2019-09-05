<?php defined('BASEPATH') || exit('No direct script access allowed');

class Special_bet_record extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_special_model', 'ettm_special_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->ettm_special_bet_record_db->is_action_log = true;
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
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time() - 86400 * 30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d');
        }
        if (!isset($where['money_type'])) {
            $where['money_type'] = 0;
        }

        $lottery = $this->ettm_lottery_db->getLotteryListByTypeid(5);

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $total = $this->ettm_special_bet_record_db->where($where)->join($join)->count();

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
        $result = $this->ettm_special_bet_record_db->where($where)
            ->select('t.*,t1.type user_type,t1.user_name')->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        $total_p_value = $c_value = $bet_eff = $profit = 0;
        foreach ($result as $key => $row) {
            $row['total_p_value'] = (float)$row['total_p_value'];
            $row['c_value'] = (float)$row['c_value'];
            $row['bet_eff'] = 0;
            $row['profit'] = 0;
            $row['profit_color'] = '#000';
            if ($row['status'] == 1) {
                $row['bet_eff'] = $this->ettm_special_bet_record_db->getBetEffect($row['total_p_value'], $row['c_value'], $row['is_lose_win'], $row['p_value']);
                $row['profit'] = (float)bcsub($row['total_p_value'], $row['c_value'], 2);
                $row['profit'] = $row['profit'] > 0 ? '+' . $row['profit'] : $row['profit'];
            }
            $result[$key] = $row;
            //當頁總計
            $total_p_value = bcadd($total_p_value, $row['total_p_value'], 2);
            $c_value       = bcadd($c_value, $row['c_value'], 2);
            $bet_eff       = bcadd($bet_eff, $row['bet_eff'], 2);
            $profit        = bcadd($profit, $row['profit'], 2);
        }

        $footer['total_p_value'] = (float)$total_p_value;
        $footer['c_value']       = (float)$c_value;
        $footer['bet_eff']       = (float)$bet_eff;
        $footer['profit']        = (float)$profit;
        //總計
        $footer_total = $this->ettm_special_bet_record_db->escape(false)
            ->select('SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,
                SUM(CASE t.status WHEN 1 THEN (CASE t.is_lose_win WHEN 0 THEN t.p_value ELSE (CASE WHEN t.c_value - (t.total_p_value - t.p_value) - t.p_value > t.p_value THEN t.p_value ELSE t.c_value - (t.total_p_value - t.p_value) - t.p_value END) END) ELSE 0 END) bet_eff')
            ->where($where)->join($join)->result_one();
        $footer_total['total_p_value'] = (float)$footer_total['total_p_value'];
        $footer_total['c_value']       = (float)$footer_total['c_value'];
        $footer_total['bet_eff']       = (float)$footer_total['bet_eff'];
        $footer_total['profit']        = (float)bcsub($footer_total['total_p_value'], $footer_total['c_value'], 2);

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
            'special'      => $this->ettm_special_db->getList($lottery),
            'lottery'      => $lottery,
        ]);
    }

    /**
     * 退款
     */
    public function refund()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $data = $this->ettm_special_bet_record_db->row($id);

            //退款事務
            $this->base_model->trans_start();
            $this->ettm_special_bet_record_db->refundBet($data['lottery_id'], $data['qishu'], $data['uid']);
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() !== false) {
                $this->session->set_flashdata('message', '退款成功!');
                echo 'done';
            }
        }
    }

    /**
     * 遊戲紀錄
     */
    public function record()
    {
        $this->load->library('pagination');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['lottery_time', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        //預設查詢條件
        if (!isset($where['money_type'])) {
            $where['money_type'] = 0;
        }

        // get total.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_ . 'ettm_lottery_record t2', 't.lottery_id = t2.lottery_id AND t.qishu = t2.qishu', 'left'];
        $total = $this->ettm_special_bet_record_db->select('t.*')->where($where)->join($join)->group('t.special_id,t.qishu')->count();

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
        $result = $this->ettm_special_bet_record_db->escape(false)->where($where)
            ->select('t.special_id,t.qishu,COUNT(DISTINCT t.uid) bet_count,SUM(t.total_p_value) total_p_value,COUNT(DISTINCT IF(t.is_lose_win,uid,null)) win_bet_count,
                    SUM(t.c_value) c_value,SUM(t.total_p_value - c_value) profit,t2.lottery_time,t2.numbers,t2.status')
            ->join($join)->group('t.special_id,t.qishu')->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        $total_p_value = $c_value = $profit = 0;
        foreach ($result as $key => $row) {
            //型態 - 牛牛相關
            $row['card_type'] = '';
            $card = $this->ettm_special_db->getNiuCard(explode(',', $row['numbers']));
            foreach ($card as $k => $arr) {
                $row['card_type'] .= ettm_special_model::$niuBetValuesList[$k] . '：<font color="blue">' . ettm_special_model::$niuList[$arr['point']] . '</font>';
                $row['card_type'] .= $k % 3 == 2 ? '<br />' : ' &nbsp; ';
            }

            $row['total_p_value'] = (float)$row['total_p_value'];
            $row['c_value'] = (float)$row['c_value'];
            $row['profit'] = 0;
            $row['profit_color'] = '#000';
            if ($row['status'] == 1) {
                $row['profit'] = (float)bcsub($row['total_p_value'], $row['c_value'], 2);
                $row['profit_color'] = $row['profit'] > 0 ? 'red' : ($row['profit'] < 0 ? 'green' : '#000');
                $row['profit'] = $row['profit'] > 0 ? '+' . $row['profit'] : $row['profit'];
            }
            $row['detail_url'] = site_url("{$this->router->class}/index/sidebar/0/special_id/$row[special_id]/qishu/$row[qishu]");
            $result[$key] = $row;
            //當頁總計
            $total_p_value = bcadd($total_p_value, $row['total_p_value'], 2);
            $c_value       = bcadd($c_value, $row['c_value'], 2);
            $profit        = bcadd($profit, $row['profit'], 2);
        }

        $footer['total_p_value'] = (float)$total_p_value;
        $footer['c_value']       = (float)$c_value;
        $footer['profit']        = (float)$profit;
        $footer['profit_color']  = $footer['profit'] > 0 ? 'red' : ($footer['profit'] < 0 ? 'green' : 'blue');
        //總計
        $footer_total = $this->ettm_special_bet_record_db->escape(false)
            ->select('SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,SUM(t.total_p_value - c_value) profit')
            ->where($where)->join($join)->result_one();
        $footer_total['total_p_value'] = (float)$footer_total['total_p_value'];
        $footer_total['c_value']       = (float)$footer_total['c_value'];
        $footer_total['profit']        = (float)bcsub($footer_total['total_p_value'], $footer_total['c_value'], 2);
        $footer_total['profit_color']  = $footer_total['profit'] > 0 ? 'red' : ($footer_total['profit'] < 0 ? 'green' : 'blue');

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
            'special'      => $this->ettm_special_db->getList(),
        ]);
    }
}
