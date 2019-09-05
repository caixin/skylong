<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_lottery';
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
        unset($this->_where['operator_id']);

        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('t.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        } else {
            $this->db->where('t.is_delete', 0);
        }

        if (isset($this->_where['key_word'])) {
            $this->db->where('t.key_word', $this->_where['key_word']);
            unset($this->_where['key_word']);
        }
        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('t.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['mode'])) {
            $this->db->where('t.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
        if (isset($this->_where['is_hot'])) {
            $this->db->where('t.is_hot', $this->_where['is_hot']);
            unset($this->_where['is_hot']);
        }
    }

    public function getLotteryList($mode = 0)
    {
        $where = [];
        if ($mode > 0) {
            $where['mode'] = $mode;
        }

        $result = $this->where($where)->order(['lottery_type_id' => 'asc', 'id' => 'asc'])->result();

        return array_column($result, 'name', 'id');
    }

    public function getLotteryListByTypeid($type_id)
    {
        $result = $this->where([
            'lottery_type_id' => $type_id
        ])->result();

        return array_column($result, 'name', 'id');
    }

    public static $modeList = [
        1 => '经典彩',
        2 => '官方彩',
        4 => '特色棋牌',
    ];

    public static $statusList = [
        0 => '维护中',
        1 => '开启',
        //3 => '代开盘', //不顯示
    ];

    public static $is_hotList = [
        1 => '是',
        0 => '否',
    ];

    public static $hot_logoList = [
        1 => '是',
        0 => '否',
    ];

    public static $is_customList = [
        1 => '是',
        0 => '否',
    ];

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'lottery_type_id' => '彩种类别',
        'name'            => '彩种名称',
        'key_word'        => 'Keyword',
        'pic_icon'        => '图片URL',
        'jump_url'        => '跳转链接',
        'benchmark'       => '初始期数',
        'day_start'       => '开盘时间',
        'day_end'         => '封盘时间',
        'open_start'      => '开奖起始时间',
        'open_end'        => '开奖结束时间',
        'interval'        => '间隔时间(秒)',
        'halftime_start'  => '中场休息起始',
        'halftime_end'    => '中场休息结束',
        'adjust'          => '调整时间',
        'sort'            => '排序',
        'status'          => '状态',
        'is_hot'          => '是否为热门彩种',
        'hot_logo'        => '是否有hot LOGO',
        'is_custom'       => '是否为自订彩种',
        'is_delete'       => '是否删除',
    ];
}
