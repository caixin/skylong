<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_cheat_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'ettm_lottery_cheat_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'type', 'label' => '类型', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        $date = date('Y-m-d H:i:s');
        $data = $row;
        $data['create_time'] = $date;

        if ($return_string) {
            return $this->db->insert_string($this->_table_name, $data);
        } else {
            $this->db->insert($this->_table_name, $data);
            return $this->db->insert_id();
        }
    }

    public function _do_where()
    {
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
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
}
