<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ip2location_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = 'ip2location';
        $this->_key = 'ip_from';
    }

    public function rules()
    {
        return [
            ['field' => 'ip_from', 'label' => 'ip_from', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['ip_from'])) {
            $this->db->where('t.ip_from <=', $this->_where['ip_from']);
            unset($this->_where['ip_from']);
        }
        if (isset($this->_where['ip_to'])) {
            $this->db->where('t.ip_to >=', $this->_where['ip_to']);
            unset($this->_where['ip_to']);
        }
        if (isset($this->_where['country_code'])) {
            $this->db->where('t.country_code', $this->_where['country_code']);
            unset($this->_where['country_code']);
        }
        if (isset($this->_where['country_name'])) {
            $this->db->like('t.country_name', $this->_where['country_name'], 'both');
            unset($this->_where['country_name']);
        }
        return $this;
    }
    
    /**
     * 取得IP位置資訊
     * @param string $ip IP位址
     * @return array IP資訊
     */
    public function getIpData($ip)
    {
        return $this->where(['ip_from'=>ip2long($ip)])
            ->order(['ip_from','desc'])
            ->result_one();
    }
}
