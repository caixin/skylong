<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_record_check extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('qishu_model', 'qishu_db');
    }

    public function index()
    {
        //預設彩種
        $result = $this->ettm_lottery_db->select('
                t.id AS lottery_id,
                t.name AS lottery_name,
                t.alarm
            ')
            ->where(['t.status' => 1, 't.lottery_type_id' => [1, 2, 3, 4, 5, 6]])
            ->order(['lottery_id', 'asc'])
            ->result();

        if ($this->input->is_ajax_request()) {
            $server_time = date('Y-m-d H:i:s');
            echo json_encode(['list' => $result, 'server_time' => $server_time]);
            return;
        }

        $this->layout->view($this->cur_url, [
            'result'    => $result,
        ]);
    }

    public function qishu_info($lottery_id)
    {
        if ($this->input->is_ajax_request()) {
            $qishu = $this->qishu_db->getQishu(1, $lottery_id);
            $result = $this->ettm_lottery_record_db->select('
                    lottery_id,
                    qishu,
                    numbers,
                    status,
                    update_time
                ')
                ->where([
                    'lottery_id' => $lottery_id,
                    'qishu' => $qishu['qishu']
                ])
                ->result_one();
            $result['next_qishu']   = $qishu['next_qishu'];
            $result['lottery_time'] = date('Y-m-d H:i:s', $qishu['lottery_time']);
            $result['count_down']   = date('Y-m-d H:i:s', $qishu['count_down']);

            echo json_encode($result);
            return;
        }
    }

    public function alarm_edit($lottery_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->library('form_validation');

            $this->form_validation->set_rules([
                ['field' => 'alarm', 'label' => '原报警秒数', 'rules' => 'trim|required|is_natural'],
                ['field' => 'new_alarm', 'label' => '新报警秒数', 'rules' => 'trim|required|is_natural|differs[alarm]'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                echo $error;
                return;
            }

            $new_alarm = $this->input->post('new_alarm');
            $this->ettm_lottery_db->update([
                'id'   => $lottery_id,
                'alarm' => $new_alarm,
            ]);
            echo 'done';
            return;
        }
    }

    public function history_unopen()
    {
        $where = [
            't.status' => 0,
            't.lottery_time >=' => date("Y-m-d 00:00:00", strtotime("-5 day")),
            't.lottery_time <=' => date("Y-m-d H:i:s", time() - 20 * 60)
        ];

        if ($this->input->is_ajax_request()) {
            $total = $this->ettm_lottery_record_db->where($where)->count();
            echo $total;
            return;
        }

        $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
        $result = $this->ettm_lottery_record_db->select('
                t.id,
                t.qishu,
                t1.name AS lottery_name
            ')
            ->join($join)
            ->where($where)
            ->order(['t.lottery_id' => 'asc', 't.lottery_time' => 'desc'])
            ->result();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, [
            'result'    => $result,
        ]);
    }
}
