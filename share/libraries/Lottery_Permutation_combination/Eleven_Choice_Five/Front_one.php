<?php
/**
 * 前1
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-11
 * Time: 下午5:40
 */

class Front_one extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 11选5 前1直选复式
     */
    public function eleven_Front_One_Direct_Compound($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//前一号码
        $arr_one= explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 1);
        return $this->data;
    }
}