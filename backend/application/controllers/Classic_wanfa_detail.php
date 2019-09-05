<?php defined('BASEPATH') || exit('No direct script access allowed');

class Classic_wanfa_detail extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
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
        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $total = $this->ettm_classic_wanfa_detail_db->where($where)->join($join)->count();

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
        $result = $this->ettm_classic_wanfa_detail_db->where($where)
            ->select('t.*,t1.pid,t1.name')->join($join)->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'       => $result,
            'total'        => $total,
            'where'        => $where,
            'order'        => $order,
            'params_uri'   => $params_uri,
            'lottery_type' => $this->ettm_lottery_type_db->getTypeList(1),
            'parent_wanfa' => array_column($this->ettm_classic_wanfa_db->where(['pid'=>0])->result(), 'name', 'id'),
        ]);
    }

    public function create()
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_classic_wanfa_detail_db->rules());

            if ($this->form_validation->run() == true) {
                unset($row['wanfa_pid']);
                $this->ettm_classic_wanfa_detail_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        }

        $data['row'] = $row;
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

            $this->form_validation->set_rules($this->ettm_classic_wanfa_detail_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                unset($row['wanfa_pid']);
                $this->ettm_classic_wanfa_detail_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_classic_wanfa_detail_db->row($id);
            $wanfa = $this->ettm_classic_wanfa_db->row($row['wanfa_id']);
            $row['wanfa_pid'] = $wanfa['pid'];
        }

        $data['row'] = $row;
        $data['lottery_type'] = $this->ettm_lottery_type_db->getTypeList(1);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_classic_wanfa_detail_db->update([
                'id'        => $id,
                'is_delete' => 1
            ]);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }

    //匯入
    public function import()
    {
        $file = $_FILES["file"]['tmp_name'];
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        $excel = $reader->load($file);
        $sheet = $excel->getSheet(0);
        $total = $sheet->getHighestRow();
        $data_list = [];

        for ($r = 2; $r <= $total; $r++) {
            if (trim($sheet->getCellByColumnAndRow(1, $r)->getValue()) == '') {
                continue;
            }
            $data = [];
            $data['id']              = (string)$sheet->getCellByColumnAndRow(0, $r)->getValue();
            $data['odds']            = (string)$sheet->getCellByColumnAndRow(4, $r)->getValue();
            $data['odds_special']    = (string)$sheet->getCellByColumnAndRow(5, $r)->getValue();
            $data['line_a_profit']   = (string)$sheet->getCellByColumnAndRow(6, $r)->getValue();
            $data['line_a_special']  = (string)$sheet->getCellByColumnAndRow(7, $r)->getValue();
            $data['qishu_max_money'] = (string)$sheet->getCellByColumnAndRow(8, $r)->getValue();
            $data['bet_max_money']   = (string)$sheet->getCellByColumnAndRow(9, $r)->getValue();
            $data['bet_min_money']   = (string)$sheet->getCellByColumnAndRow(10, $r)->getValue();
            $data['sort']            = (string)$sheet->getCellByColumnAndRow(11, $r)->getValue();
            $data['update_time']     = date('Y-m-d H:i:s');
            $data['update_by']       = $this->session->userdata('username');
            $data_list[] = $data;
        }

        $this->base_model->trans_start();
        $status = $this->ettm_classic_wanfa_detail_db->update_batch($data_list, 'id');
        $this->base_model->trans_complete();

        if ($this->base_model->trans_status()) {
            $result = ['status' => 1, 'message' => '汇入成功'];
        } else {
            $result = ['status' => 0, 'message' => '请尝试重新汇入'];
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    //匯出
    public function export()
    {
        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];

        $lottery_type = $this->ettm_lottery_type_db->getTypeList(1);
        $parent_wanfa = array_column($this->ettm_classic_wanfa_db->where(['pid' => 0])->result(), 'name', 'id');

        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $result = $this->ettm_classic_wanfa_detail_db->where($where)
            ->select('t.*,t1.pid,t1.name')->join($join)->order($order)
            ->result();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription('none');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '编号');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '彩种类别');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '玩法類型');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, '玩法值');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, '满盘赔率');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, '特殊賠率');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'A盘获利(%)');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'A盘特殊(%)');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, '单期最大限额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, '单笔最大限额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, '最小下注额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, '排序');

        $r = 1;
        foreach ($result as $row) {
            $r++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, $row['id']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, $lottery_type[$row['lottery_type_id']]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, (isset($parent_wanfa[$row['pid']]) ? $parent_wanfa[$row['pid']] : '') . ' → ' . $row['name']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $row['values']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $r, $row['odds']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $r, $row['odds_special']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $r, $row['line_a_profit']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $row['line_a_special']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $r, $row['qishu_max_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $r, $row['bet_max_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $r, $row['bet_min_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $r, $row['sort']);
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->router->class . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
