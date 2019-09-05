<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_classic_wanfa_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_classic_wanfa';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_type_id', 'label' => '彩种类别', 'rules' => 'trim|required'],
            ['field' => 'name', 'label' => '玩法名称', 'rules' => 'trim|required'],
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

        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('t.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
    }

    public function getListByLottery($lottery_type_id)
    {
        $result = $this->where([
            'lottery_type_id' => $lottery_type_id,
        ])->order(['pid'=>'asc','sort'=>'asc'])->result();

        return $result;
    }

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'lottery_type_id' => '彩种类别',
        'pid'             => '父级ID',
        'name'            => '玩法名称',
        'sort'            => '排序',
        'is_delete'       => '是否删除',
    ];
}
