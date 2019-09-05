<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_type_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_lottery_type';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '彩种名称', 'rules' => 'trim|required'],
            ['field' => 'key_word', 'label' => 'Keyword', 'rules' => 'trim|required']
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['key_word'])) {
            $this->db->where('t.key_word', $this->_where['key_word']);
            unset($this->_where['key_word']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['mode'])) {
            $this->db->where('t.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }
    }

    public function getTypeList($mode = 0)
    {
        $where = [];
        if ($mode > 0) {
            $where['mode'] = $mode;
        }
        $result = $this->where($where)->order(['sort', 'asc'])->result();

        return array_column($result, 'name', 'id');
    }

    public static $modeList = [
        1 => '经典彩',
        2 => '官方彩',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'       => '编号',
        'name'     => '彩种名称',
        'key_word' => 'Keyword',
        'pic_icon' => '图片URL',
        'sort'     => '排序',
        'mode'     => '玩法模式',
    ];
}
