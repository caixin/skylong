<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_record extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('prediction_buy_model', 'prediction_buy_db');
        $this->load->model('ettm_lottery_open_model');
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
        $search_params = param_process($params, ['qishu', 'desc']);
        $page          = $search_params['page'];
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        //預設彩種
        $lottery = $this->ettm_lottery_db->getLotteryList();
        if (!isset($where['lottery_id'])) {
            foreach ($lottery as $key => $val) {
                $where['lottery_id'] = $key;
                break;
            }
        }
        //預設顯示已開獎期數+五期
        if (!isset($where['qishu']) && !isset($where['lottery_time1']) && !isset($where['lottery_time2'])) {
            $max = $this->ettm_lottery_record_db->where([
                'lottery_id' => $where['lottery_id'],
                'status >='  => 1,
            ])->order(['qishu', 'desc'])->result_one();
            if ($max !== null) {
                $where['t.qishu <='] = $max['qishu'] + 5;
            }
        }

        // get total.
        $total = $this->ettm_lottery_record_db->where($where)->count();

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
        $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
        $result = $this->ettm_lottery_record_db->where($where)
            ->select('t.*,t1.name lottery_name')
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        //注單統計
        $bet_where['t.lottery_id'] = $where['lottery_id'];
        $bet_where['t.money_type'] = 0;
        $bet_where['t.qishu'] = array_merge([0], array_column($result, 'qishu'));
        $bet_join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $classic_sql = $this->ettm_classic_bet_record_db->select('qishu,uid,bet_number,total_p_value,c_value,is_lose_win')->join($bet_join)->where($bet_where)->get_compiled_select();
        $official_sql = $this->ettm_official_bet_record_db->select('qishu,uid,bet_number,total_p_value,(c_value+return_money) c_value,is_lose_win')->join($bet_join)->where($bet_where)->get_compiled_select();
        $special_sql = $this->ettm_special_bet_record_db->select('qishu,uid,bet_number,total_p_value,c_value,is_lose_win')->join($bet_join)->where($bet_where)->get_compiled_select();
        $sql = "SELECT qishu,COUNT(DISTINCT uid) bet_count, SUM(bet_number) bet_number,SUM(total_p_value) total_p_value,COUNT(DISTINCT IF(is_lose_win,uid,null)) win_bet_count,SUM(c_value) c_value,SUM(total_p_value - c_value) profit
                FROM ($classic_sql UNION ALL $official_sql UNION ALL $special_sql) t GROUP BY qishu";
        $bet = $this->base_model->query($sql)->result_array();
        $bet = array_column($bet, null, 'qishu');
        //寫入開獎結果
        foreach ($result as $key => $row) {
            $row['bet_count'] = 0;
            $row['bet_number'] = 0;
            $row['total_p_value'] = 0;
            $row['win_bet_count'] = 0;
            $row['c_value'] = 0;
            $row['profit'] = 0;
            if (isset($bet[$row['qishu']])) {
                $arr = $bet[$row['qishu']];
                $row['bet_count'] = (int) $arr['bet_count'];
                $row['bet_number'] = (int) $arr['bet_number'];
                $row['total_p_value'] = (float) $arr['total_p_value'];
                if ($row['status'] == 1) { //正常開獎才有中獎數據
                    $row['win_bet_count'] = (int) $arr['win_bet_count'];
                    $row['c_value'] = (float) $arr['c_value'];
                }
                $row['profit'] = (float) $arr['profit'];
                $row['profit'] = $row['profit'] > 0 ? '+' . $row['profit'] : $row['profit'];
            }
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'lottery'    => $lottery,
        ]);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_record_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['status'] = 1;
                $this->ettm_lottery_record_db->update($row);

                $row = $this->ettm_lottery_record_db->row($id);
                $operator = $this->operator_db->getList(1);
                foreach ($operator as $id => $name) {
                    $this->operator_id = $id;
                    $this->ettm_lottery_open_model->openAction($row['lottery_id'], $row['qishu']);
                }

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_lottery_record_db->row($id);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    /**
     * 退款
     */
    public function refund()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $record = $this->ettm_lottery_record_db->row($id);
            $lottery = $this->ettm_lottery_db->row($record['lottery_id']);

            //退款事務
            $this->base_model->trans_start();
            $classic_ids = $this->ettm_classic_bet_record_db->refundBet($record['lottery_id'], $record['qishu']);
            $official_ids = $this->ettm_official_bet_record_db->refundBet($record['lottery_id'], $record['qishu']);
            //熱門預測退款
            $this->prediction_buy_db->refund($record['lottery_id'], $record['qishu']);
            $this->ettm_lottery_record_db->update([
                'id'     => $id,
                'status' => 2,
            ]);
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() !== false) {
                //退款完成 - 操作日誌
                $message = '';
                if ($classic_ids !== []) {
                    $message .= ",影响经典注单ID:" . implode(',', $classic_ids);
                }
                if ($official_ids !== []) {
                    $message .= ",影响官方注单ID:" . implode(',', $official_ids);
                }
                $this->admin_action_log_db->insert([
                    'sql_str' => '',
                    'message' => $this->title . "(彩种:$lottery[name],期数:$record[qishu] $message)",
                    'status' => 1,
                ]);
                $this->session->set_flashdata('message', '退款成功!');
                echo 'done';
            }
        }
    }

    /**
     * 重新派獎
     */
    public function reaward()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $record = $this->ettm_lottery_record_db->row($id);
            $lottery = $this->ettm_lottery_db->row($record['lottery_id']);

            //還原注單事務
            $this->base_model->trans_start();
            $classic_ids = $this->ettm_classic_bet_record_db->restoreBet($record['lottery_id'], $record['qishu']);
            $official_ids = $this->ettm_official_bet_record_db->restoreBet($record['lottery_id'], $record['qishu']);
            $special_ids = $this->ettm_special_bet_record_db->restoreBet($record['lottery_id'], $record['qishu']);
            $this->ettm_lottery_record_db->update([
                'id'     => $id,
                'status' => 1,
            ]);
            $this->base_model->trans_complete();

            $message = '';
            if ($classic_ids !== []) {
                $message .= ",影响经典注单ID:" . implode(',', $classic_ids);
            }
            if ($official_ids !== []) {
                $message .= ",影响官方注单ID:" . implode(',', $official_ids);
            }
            if ($special_ids !== []) {
                $message .= ",影响特色注单ID:" . implode(',', $special_ids);
            }

            //熱門預測：將購買預測狀態(status)改為 0待處理
            $this->prediction_buy_db->reaward($record['lottery_id'], $record['qishu']);
            //派彩
            $this->ettm_lottery_open_model->openAction($record['lottery_id'], $record['qishu']);
            //重新派獎完成 - 操作日誌
            $this->admin_action_log_db->insert([
                'sql_str' => '',
                'message' => $this->title . "(彩种:$lottery[name],期数:$record[qishu] $message)",
                'status'  => 1,
            ]);
            $this->session->set_flashdata('message', '重新派獎成功!');
            echo 'done';
        }
    }
}
