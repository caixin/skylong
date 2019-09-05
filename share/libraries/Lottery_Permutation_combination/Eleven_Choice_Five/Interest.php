<?php

/**
 * 趣味
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午6:00
 */
class Interest extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 11选5趣味
     */
    public function eleven_Interest($arr){
        //$arr_one=array("03","04","05","06","07","08","09");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 1);
        return $this->data;
    }

}