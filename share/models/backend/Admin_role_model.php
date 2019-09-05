<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_role_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'admin_role';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '角色名称', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('t.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        } else {
            $this->db->where('t.is_delete', 0);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['operator'])) {
            $this->db->where('find_in_set(' . $this->_where['operator'] . ',t.allow_operator) >', 0, false);
            unset($this->_where['operator']);
        }
    }

    /**
     * 過濾掉沒權限的導航清單
     */
    public function filterAllowNav($navList, $allow)
    {
        foreach ($navList as $key => $row) {
            if (!in_array($row['url'], $allow)) {
                unset($navList[$key]);
                continue;
            }

            $row['sub'] = $this->filterAllowNav($row['sub'], $allow);
            $navList[$key] = $row;
        }

        return $navList;
    }

    /**
     * 取得角色清單
     */
    public function getRoleList()
    {
        $result = $this->where([
            't.id >' => 1,
        ])->result();
        return array_column($result, 'name', 'id');
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'             => '编号',
        'name'           => '角色名称',
        'allow_operator' => '运营商权限',
        'allow_nav'      => '导航权限'
    ];
}
