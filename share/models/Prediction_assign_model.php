<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_assign_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'prediction_assign';
        $this->_key = 'id';
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
        if (isset($this->_where['recharge_order_id'])) {
            $this->db->where('t.recharge_order_id', $this->_where['recharge_order_id']);
            unset($this->_where['recharge_order_id']);
        }
        if (isset($this->_where['prediction_relief_id'])) {
            $this->db->where('t.prediction_relief_id', $this->_where['prediction_relief_id']);
            unset($this->_where['prediction_relief_id']);
        }
        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }
        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
    }
}
