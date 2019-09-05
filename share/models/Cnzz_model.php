<?php defined('BASEPATH') || exit('No direct script access allowed');

class Cnzz_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'cnzz';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'domain_url', 'label' => '网域名称', 'rules' => 'trim|required'],
            ['field' => 'cnzz_url', 'label' => '链接', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['domain_url'])) {
            $this->db->like('t.domain_url', $this->_where['domain_url'], 'both');
            unset($this->_where['domain_url']);
        }
    }

    public function getUrl()
    {
        $domain = $this->input->server('SERVER_NAME');
        $row = $this->where(['domain_url'=>$domain])->result_one();
        return isset($row['cnzz_url']) ? $row['cnzz_url']:'';
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'domain_url' => '网域名称',
        'cnzz_url'   => '链接',
    ];
}
