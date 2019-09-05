<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-25
 * Time: 上午9:53
 */
class One_front extends  Common_combination
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * pk 前一复式
     */
    public function one_Front_Compound($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10");//第一位
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;
    }


}