<?php defined('BASEPATH') || exit('No direct script access allowed');

class Official_wanfa extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_official_wanfa_model', 'ettm_official_wanfa_db');
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
        $total = $this->ettm_official_wanfa_db->where($where)->count();

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
        $result = $this->ettm_official_wanfa_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'       => $result,
            'total'        => $total,
            'where'        => $where,
            'order'        => $order,
            'params_uri'   => $params_uri,
            'lottery_type' => $this->ettm_lottery_type_db->getTypeList(2),
            'parent_wanfa' => $this->ettm_official_wanfa_db->getList(0),
        ]);
    }

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_official_wanfa_db->rules());

            if ($this->form_validation->run() == true) {
                $this->ettm_official_wanfa_db->insert($row);

                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->ettm_official_wanfa_db->row($id);
            }
        }

        $data['row'] = $row;
        $data['lottery_type'] = $this->ettm_lottery_type_db->getTypeList(2);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_official_wanfa_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $this->ettm_official_wanfa_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_official_wanfa_db->row($id);
        }

        $data['row'] = $row;
        $data['lottery_type'] = $this->ettm_lottery_type_db->getTypeList(2);

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_official_wanfa_db->update([
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
            $data['id']               = (string)$sheet->getCellByColumnAndRow(0, $r)->getValue();
            $data['odds']             = (string)$sheet->getCellByColumnAndRow(5, $r)->getValue();
            $data['line_a_profit']    = (string)$sheet->getCellByColumnAndRow(6, $r)->getValue();
            $data['max_return']       = (string)$sheet->getCellByColumnAndRow(7, $r)->getValue();
            $data['max_bet_number']   = (string)$sheet->getCellByColumnAndRow(8, $r)->getValue();
            $data['max_bet_money']    = (string)$sheet->getCellByColumnAndRow(9, $r)->getValue();
            $data['sort']             = (string)$sheet->getCellByColumnAndRow(10, $r)->getValue();
            $data['update_time']      = date('Y-m-d H:i:s');
            $data['update_by']        = $this->session->userdata('username');
            $data_list[] = $data;
        }

        $this->base_model->trans_start();
        $this->ettm_official_wanfa_db->update_batch($data_list, 'id');
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

        $lottery_type = $this->ettm_lottery_type_db->getTypeList(2);
        $parent_wanfa = $this->ettm_official_wanfa_db->getList(0);

        $result = $this->ettm_official_wanfa_db->where($where)
            ->order($order)
            ->result();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription('none');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '编号');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '彩种类别');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '上层玩法');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, '玩法名称');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Keyword');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, '满盘赔率');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'A盘获利(%)');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, '最大返点');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, '最大注数');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, '最大投注额');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, '排序');

        $r = 1;
        foreach ($result as $row) {
            $r++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $r, $row['id']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $r, $lottery_type[$row['lottery_type_id']]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $r, isset($parent_wanfa[$row['pid']]) ? $parent_wanfa[$row['pid']] : '无');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $row['name']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $r, $row['key_word']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $r, $row['odds']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $r, $row['line_a_profit']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $row['max_return']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $r, $row['max_bet_number']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $r, $row['max_bet_money']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $r, $row['sort']);
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->router->class . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
