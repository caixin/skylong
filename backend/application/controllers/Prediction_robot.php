<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_robot extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('prediction_robot_bet_model', 'prediction_robot_bet_db');
        $this->load->model('prediction_robot_setting_model', 'prediction_robot_setting_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('operator_model', 'operator_db');
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

        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $this->operator_id = $where['operator_id'];

        // get total.
        $total = $this->prediction_robot_setting_db->where($where)->count();

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
        $result = $this->prediction_robot_setting_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();
        foreach ($result as $key => $row) {
            $total_formula = json_decode($row['total_formula'], true);
            $str = '';
            foreach ($total_formula as $arr) {
                $str .= "$arr[hour_start]点到$arr[hour_end]点，";
                $str .= "投注总额为$arr[total_min]~$arr[total_max]，";
                $str .= "若有3个号码开出$arr[total_middle]~$arr[total_max]，";
                $str .= "则改为$arr[total_min]~$arr[total_middle]<br>";
            }
            $row['total_formula'] = $str;
            $bet_formula = json_decode($row['bet_formula'], true);
            $str = '';
            foreach ($bet_formula as $arr) {
                $str .= "投注时间：$arr[bet_time]%，";
                $str .= "投注机率为$arr[bet_action]%，";
                $str .= "投注总金额为$arr[bet_min]~$arr[bet_max]%<br>";
            }
            $row['bet_formula'] = $str;
            
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function create($operator_id, $id=0)
    {
        $this->load->library('form_validation');

        $row['operator_id'] = $operator_id;
        $row['total_formula'] = [];
        $row['bet_formula'] = [];

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();
            $total = $bet = [];
            foreach ($row['hour_start'] as $key => $val) {
                $total[] = [
                    'hour_start'   => $val,
                    'hour_end'     => $row['hour_end'][$key],
                    'total_min'    => $row['total_min'][$key],
                    'total_max'    => $row['total_max'][$key],
                    'total_middle' => $row['total_middle'][$key],
                    'over_number'  => $row['over_number'][$key],
                ];
            }
            unset($row['hour_start'],$row['hour_end'],$row['total_min'],$row['total_max'],$row['total_middle'],$row['over_number']);

            foreach ($row['bet_time'] as $key => $val) {
                $bet[] = [
                    'bet_time'   => $val,
                    'bet_action' => $row['bet_action'][$key],
                    'bet_min'    => $row['bet_min'][$key],
                    'bet_max'    => $row['bet_max'][$key],
                ];
            }
            unset($row['bet_time'],$row['bet_action'],$row['bet_min'],$row['bet_max']);

            $this->form_validation->set_rules($this->prediction_robot_setting_db->rules());

            if ($this->form_validation->run() == true) {
                $row['total_formula'] = json_encode($total);
                $row['bet_formula'] = json_encode($bet);
                $this->prediction_robot_setting_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            } else {
                $row['total_formula'] = $total;
                $row['bet_formula'] = $bet;
            }
        } else {
            if ($id != 0) {
                $row = $this->prediction_robot_setting_db->row($id);
                $row['total_formula'] = (array)json_decode($row['total_formula'], true);
                $row['bet_formula'] = (array)json_decode($row['bet_formula'], true);
            }
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['lottery'] = $this->ettm_lottery_db->getLotteryList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id = '')
    {
        $this->load->library('form_validation');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $total = $bet = [];
            foreach ($row['hour_start'] as $key => $val) {
                $total[] = [
                    'hour_start'   => $val,
                    'hour_end'     => $row['hour_end'][$key],
                    'total_min'    => $row['total_min'][$key],
                    'total_max'    => $row['total_max'][$key],
                    'total_middle' => $row['total_middle'][$key],
                    'over_number'  => $row['over_number'][$key],
                ];
            }
            unset($row['hour_start'],$row['hour_end'],$row['total_min'],$row['total_max'],$row['total_middle'],$row['over_number']);

            foreach ($row['bet_time'] as $key => $val) {
                $bet[] = [
                    'bet_time'   => $val,
                    'bet_action' => $row['bet_action'][$key],
                    'bet_min'    => $row['bet_min'][$key],
                    'bet_max'    => $row['bet_max'][$key],
                ];
            }
            unset($row['bet_time'],$row['bet_action'],$row['bet_min'],$row['bet_max']);

            $this->form_validation->set_rules($this->prediction_robot_setting_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['total_formula'] = json_encode($total);
                $row['bet_formula'] = json_encode($bet);
                $this->prediction_robot_setting_db->update($row);

                $this->session->set_flashdata('message', '編輯成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            } else {
                $row['total_formula'] = $total;
                $row['bet_formula'] = $bet;
            }
        } else {
            $row = $this->prediction_robot_setting_db->row($id);
            $row['total_formula'] = (array)json_decode($row['total_formula'], true);
            $row['bet_formula'] = (array)json_decode($row['bet_formula'], true);
        }

        $data['row'] = $row;
        $data['operator'] = $this->operator_db->getList(0);
        $data['lottery'] = $this->ettm_lottery_db->getLotteryList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->prediction_robot_setting_db->delete($id);
            $this->session->set_flashdata('message', '刪除成功!');
            echo 'done';
        }
    }

    public function bet()
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

        //預設營運商
        $operator = $this->operator_db->getList(0);
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }
        $this->operator_id = $where['operator_id'];

        // get total.
        $join[] = [$this->table_.'prediction t1','t.prediction_id = t1.id', 'left'];
        $total = $this->prediction_robot_bet_db->where($where)->join($join)->count();

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
        $result = $this->prediction_robot_bet_db->select('t.*,t1.lottery_id,t1.name')
            ->where($where)
            ->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
            'lottery'    => $this->ettm_lottery_db->getLotteryList(),
        ]);
    }

    public function bet_edit($id = '')
    {
        $this->load->library('form_validation');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $this->form_validation->set_rules($this->prediction_robot_bet_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->prediction_robot_bet_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->prediction_robot_bet_db->row($id);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }
}
