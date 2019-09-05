<?php defined('BASEPATH') || exit('No direct script access allowed');

class Module_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'module';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '模组名称', 'rules' => 'trim|required'],
            ['field' => 'keyword', 'label' => '关键字', 'rules' => 'trim|required'],
        ];
    }

    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("module-getEnable");

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("module-getEnable");
        
        return parent::update($row, $return_string);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("module-getEnable");

        return parent::delete($id);
    }

    public function _do_where()
    {
        if (isset($this->_where['name'])) {
            $this->db->where('t.name', $this->_where['name']);
            unset($this->_where['name']);
        }
        if (isset($this->_where['keyword'])) {
            $this->db->where('t.keyword', $this->_where['keyword']);
            unset($this->_where['keyword']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'      => '编号',
        'name'    => '模组名称',
        'keyword' => '关键字',
        'status'  => '状态',
    ];
}
