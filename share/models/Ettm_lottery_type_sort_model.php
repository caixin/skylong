<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_type_sort_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_db_default = 'ettm_lottery_type_db';
        $this->load->model('ettm_lottery_type_model', $this->_db_default);

        $this->_table_default = $this->table_ . 'ettm_lottery_type';
        $this->_related_key = 'lottery_type_id';
        $this->_table_name = $this->table_ . 'ettm_lottery_type_sort';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'sort', 'label' => '排序', 'rules' => 'trim|required']
        ];
    }

    public function update($row, $return_string = false)
    {
        $data = $this->where([
            'operator_id'     => $row['operator_id'],
            'lottery_type_id' => $row['lottery_type_id'],
        ])->result_one();

        if ($data === null) {
            return parent::insert($row, $return_string);
        } else {
            $row['id'] = $data['id'];
            return parent::update($row);
        }
    }

    public function _do_where()
    {
        unset($this->_where['operator_id']);

        if (isset($this->_where['key_word'])) {
            $this->db->where('default.key_word', $this->_where['key_word']);
            unset($this->_where['key_word']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('default.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['mode'])) {
            $this->db->where('default.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'operator_id'     => '运营商ID',
        'lottery_type_id' => '彩种类别ID',
        'sort'            => '排序',
    ];
}
