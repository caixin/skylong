<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_type_sort extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('ettm_lottery_type_sort_model', 'ettm_lottery_type_sort_db');
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
        $total = $this->ettm_lottery_type_sort_db->where($where)->count_change();

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
        $result = $this->ettm_lottery_type_sort_db->where($where)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result_change();

        foreach ($result as $key => $row) {
            $mode = bindec_array($row['mode']);
            $mode_str = [];
            foreach ($mode as $val) {
                $mode_str[] = Ettm_lottery_type_model::$modeList[$val];
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
            'operator'   => $operator,
        ]);
    }

    public function edit($operator_id, $id)
    {
        $this->load->library('form_validation');

        $this->operator_id = $operator_id;
        $row = [];

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();

            $this->form_validation->set_rules($this->ettm_lottery_type_sort_db->rules());

            if ($this->form_validation->run() == true) {
                $row['operator_id'] = $operator_id;
                $row['lottery_type_id'] = $id;
                $this->ettm_lottery_type_sort_db->update($row);

                $this->session->set_flashdata('message', '编辑成功!');
                echo "<script>parent.window.layer.close();parent.location.reload();</script>";
                return;
            }
        } else {
            $row = $this->ettm_lottery_type_sort_db->row_change($id);
        }

        $data['row'] = $row;

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }
}
