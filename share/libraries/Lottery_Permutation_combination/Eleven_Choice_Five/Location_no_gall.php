<?php

/**
 * 不定胆
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午3:46
 */
class Location_no_gall extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 11选5 前3不定位
     */
    public function eleven_First_Three_bdw_Gamll($arr){
//        $arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 1);
        return $this->data;
    }



}