<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_login_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'user_login_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'uid', 'label' => 'UID', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        $this->load->model('ip2location_model', 'ip2location_db');
        $ip = $this->input->ip_address();
        $ip_info = $this->ip2location_db->getIpData($ip);
        $ip_info = $ip_info === null ? []:$ip_info;
        $row['ip'] = $ip;
        $row['ip_info'] = json_encode($ip_info);
        $row['ua'] = $this->input->user_agent();

        return parent::insert($row, $return_string);
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t1.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            //篩選運營商
            foreach ($this->_join as $arr) {
                if (strpos($arr[0], $this->table_.'user ') !== false) {
                    $table = trim(str_replace($this->table_.'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }

        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t1.agent_id', $this->session->userdata('id'));
        }
        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['mobile'])) {
            $this->db->where('t1.mobile', $this->_where['mobile']);
            unset($this->_where['mobile']);
        }

        if (isset($this->_where['ip'])) {
            $this->db->where('t.ip', $this->_where['ip']);
            unset($this->_where['ip']);
        }

        if (isset($this->_where['source'])) {
            $this->db->where('t.source', $this->_where['source']);
            unset($this->_where['source']);
        }

        if (isset($this->_where['platform'])) {
            $this->db->where('t.platform', $this->_where['platform']);
            unset($this->_where['platform']);
        }

        if (isset($this->_where['user_type'])) {
            $this->db->where('t1.type', $this->_where['user_type']);
            unset($this->_where['user_type']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
        return $this;
    }

    /**
     * 統計期間內不重複登入人數
     * @param string $start_time 起始時間
     * @param string $end_time 結束時間
     * @param int $operator_id 營運商ID
     * @return int 人數
     */
    public function getLoginUser($start_time, $end_time, $operator_id)
    {
        $join[] = [$this->table_.'user t1','t.uid = t1.id','left'];
        $count = $this->user_login_log_db->select('uid')->where([
            't1.type'      => 0,
            'operator_id'  => $operator_id,
            'create_time1' => $start_time,
            'create_time2' => $end_time,
        ])->join($join)->group('uid')->count();

        return $count;
    }
}
