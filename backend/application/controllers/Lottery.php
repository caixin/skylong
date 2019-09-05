<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
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
        $total = $this->ettm_lottery_db->where($where)->count();

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
        $result = $this->ettm_lottery_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        foreach ($result as $key => $row) {
            $mode = bindec_array($row['mode']);
            $mode_str = [];
            foreach ($mode as $val) {
                $mode_str[] = Ettm_lottery_model::$modeList[$val];
            }
            $row['mode_str'] = implode(',', $mode_str);
            $result[$key] = $row;
        }

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'type'       => $this->ettm_lottery_type_db->getTypeList()
        ]);
    }

    public function create($id=0)
    {
        $this->load->library('form_validation');
        //預設值
        $row['sort'] = 0;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_db->rules());

            if ($this->form_validation->run() == true) {
                $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
                $row['mode'] = array_sum($row['mode']);
                $id = $this->ettm_lottery_db->insert($row);
                //期數錄入
                $this->ettm_lottery_record_db->writeQishu($id, date('Y-m-d'));
                //代理反點新增
                $this->agent_code_detail_db->setNewLottery($id);
                $this->session->set_flashdata('message', '添加成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            if ($id != 0) {
                $row = $this->ettm_lottery_db->row($id);
                $row['mode'] = bindec_array($row['mode']);
            }
        }

        $data['row'] = $row;
        $data['type'] = $this->ettm_lottery_type_db->getTypeList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_db->rules());

            if ($this->form_validation->run() == true) {
                $row['id'] = $id;
                $row['mode'] = array_sum($row['mode']);
                $update_lottery_time = $row['update_lottery_time'];
                unset($row['update_lottery_time']);

                $this->ettm_lottery_db->update($row);

                if ($id == 9 && $update_lottery_time == 1) {
                    //加拿大PC28修改後 更新開獎時間
                    $this->load->model('qishu_model');
                    $this->qishu_model->setQishuLotteryTime();
                }

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_lottery_db->row($id);
            $row['mode'] = bindec_array($row['mode']);
        }

        $data['row'] = $row;
        $data['type'] = $this->ettm_lottery_type_db->getTypeList();

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->ettm_lottery_db->update([
                'id'        => $id,
                'is_delete' => 1
            ]);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
