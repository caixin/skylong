<?php defined('BASEPATH') || exit('No direct script access allowed');

class Sysconfig_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'sysconfig';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'varname', 'label' => '变量名称', 'rules' => "trim|required|is_unique[$this->_table_name.varname]"],
            ['field' => 'value', 'label' => '变量值', 'rules' => 'trim|required'],
            ['field' => 'info', 'label' => '变量说明', 'rules' => 'trim|required'],
        ];
    }

    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::insert($row, $return_string);
    }

    public function insert_batch($data)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::insert_batch($data);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::update($row, $return_string);
    }

    public function update_where($row=null)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::update_where($row);
    }

    public function update_batch($data, $key)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::update_batch($data, $key);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("make_sysconfig");

        return parent::delete($id);
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        
        if (isset($this->_where['operator_ids'])) {
            $this->db->where_in('t.operator_id', $this->_where['operator_ids']);
            unset($this->_where['operator_ids']);
        }

        return $this;
    }

    public function groupList($result)
    {
        $data = [];
        foreach ($result as $row) {
            $data[$row['groupid']][] = $row;
        }
        ksort($data);
        return $data;
    }

    /**
     * 取得網站基本設置
     *
     * @param integer $operator_id 營運商ID
     * @return array
     */
    public function make_sysconfig($operator_id=0)
    {
        if ($make_sysconfig = $this->cache->redis->get('make_sysconfig')) {
            if (isset($make_sysconfig[$operator_id])) {
                return $make_sysconfig[$operator_id];
            }
        }

        $operator_ids = [0,$operator_id];
        $result = $this->where(['operator_ids'=>$operator_ids])->result();
        $data = [];
        foreach ($result as $row) {
            $data[$row['varname']] = $row['type'] == 'number' ? intval($row['value']) : $row['value'];
        }
        
        $make_sysconfig[$operator_id] = $data;
        $this->cache->redis->save('make_sysconfig', $make_sysconfig, 86400);
        return $data;
    }

    public static $groupList = [
        1 => '站点设置',
        2 => '維護设置',
        3 => '温馨提示',
        4 => '客服设置',
        5 => 'WebSocket设置',
    ];

    public static $typeList = [
        'string'   => '文本输入',
        'boolean'  => 'boolean值',
        'textarea' => '文本域',
        'number'   => '数字输入',
    ];
}
