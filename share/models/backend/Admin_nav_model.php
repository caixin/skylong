<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_nav_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'admin_nav';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '导航名称', 'rules' => 'trim|required'],
            ['field' => 'url', 'label' => 'URL', 'rules' => "trim|required|callback_url_check"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-allNav");
        $this->cache->redis->delete("$this->_table_name-getAllUrl");

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-allNav");
        $this->cache->redis->delete("$this->_table_name-getAllUrl");
        
        return parent::update($row, $return_string);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-allNav");
        $this->cache->redis->delete("$this->_table_name-getAllUrl");

        return parent::delete($id);
    }

    public function _do_where()
    {
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }

        if (isset($this->_where['url'])) {
            $this->db->like('t.url', $this->_where['url'], 'both');
            unset($this->_where['url']);
        }

        return $this;
    }

    public function getDropDownList()
    {
        $result = $this->order(['sort','asc'])->result();
        $result = $this->treeSort($result);
        $data = [];
        foreach ($result as $row) {
            $data[$row['id']] = $row['prefix'] . $row['name'];
        }
        return $data;
    }

    public function getAllUrl()
    {
        if ($result = $this->cache->redis->get("$this->_table_name-getAllUrl")) {
            return $result;
        }

        $result = $this->where(['status' => 1])->result();
        $result = array_column($result, 'url');

        $this->cache->redis->save("$this->_table_name-getAllUrl", $result, 86400);
        return $result;
    }

    public function allNav()
    {
        if ($result = $this->cache->redis->get("$this->_table_name-allNav")) {
            return $result;
        }
        
        $result = $this->where(['status' => 1])->order(['sort', 'asc'])->result();
        $result = array_column($result, null, 'id');
        
        $this->cache->redis->save("$this->_table_name-allNav", $result, 86400);
        return $result;
    }

    public function getBreadcrumb($result, $id)
    {
        if ($id == 0) {
            return [];
        }

        $data = $this->getBreadcrumb($result, $result[$id]['pid']);
        $data[] = $result[$id];
        return $data;
    }

    public function treeNav($result, $pid = 0)
    {
        $data = [];
        foreach ($result as $row) {
            if ($row['pid'] == $pid) {
                $row['sub'] = $this->treeNav($result, $row['id']);
                $row['sub_urls'] = array_column($row['sub'], 'url');
                $data[] = $row;
            }
        }
        return $data;
    }

    public function treeSort($result, $pid = 0, $array = [], $prefix = '')
    {
        foreach ($result as $row) {
            if ($row['pid'] == $pid) {
                $row['prefix'] = $prefix;
                $array[] = $row;
                $array = $this->treeSort($result, $row['id'], $array, $prefix . '∟ ');
            }
        }
        return $array;
    }

    public static $prefixColorList = [
        ''     => 'red',
        '∟ '   => 'green',
        '∟ ∟ ' => 'black',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];
    //操作日誌欄位轉換
    public static $columnList = [
        'id'     => '编号',
        'pid'    => '父导航',
        'name'   => '导航名称',
        'url'    => 'URL',
        'sort'   => '排序',
        'status' => '状态',
    ];
}
