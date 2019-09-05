<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent_code extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('agent_code_model', 'agent_code_db');
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
        $join[] = [$this->table_.'user t1', 't.uid = t1.id', 'left'];
        $join[] = [$this->table_.'user t2', 't1.agent_pid = t2.id', 'left'];
        $total = $this->agent_code_db->where($where)->join($join)->count();

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
        $result = $this->agent_code_db->where($where)
            ->select('t.*,t1.agent_id,t1.user_name,t1.agent_pid,t2.user_name pname')
            ->join($join)
            ->order($order)
            ->limit([$offset, $this->per_page])
            ->result();

        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
            'agent'      => $this->admin_db->getAgentList(),
        ]);
    }

    public function edit($id)
    {
        $this->load->library('form_validation');

        $row = [];
        $detail = $this->agent_code_detail_db->getCodeSetting($id);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $row = $this->input->post();
            $row['code'] = $id;
            $detail = $row['detail'];
            unset($row['detail']);
            $this->agent_code_db->update($row);
            //更新代理反點
            $update = [];
            foreach ($detail as $id => $return_point) {
                $update[] = [
                    'id'           => $id,
                    'return_point' => $return_point,
                ];
            }
            $this->agent_code_detail_db->update_batch($update, 'id');
            $this->session->set_flashdata('message', '编辑成功!');
            echo "<script>parent.window.layer.close();parent.location.reload();</script>";
            return;
        } else {
            $row = $this->agent_code_db->row($id);
            $row['detail'] = array_column($detail, 'return_point', 'id');
        }

        $data['row'] = $row;
        $data['agent'] = $this->admin_db->getAgentList();
        $data['lottery'] = array_column($detail, 'lottery_name', 'id');

        $this->layout->sidebar = false;
        $this->layout->view($this->cur_url, $data);
    }

    public function delete()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id');
            $this->agent_code_db->delete($id);

            $this->session->set_flashdata('message', '删除成功!');
            echo 'done';
        }
    }
}
