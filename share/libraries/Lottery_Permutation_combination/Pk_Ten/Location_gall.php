<?php

/**
 * 定位胆
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午3:41
 */
class Location_gall extends Common_combination
{
    public  $data =array();
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 定位
     * @param $arr
     * @return array
     */
    public function dw_Gall($arr){
        $arr=explode('|', $arr);
        if (count($arr)<1){
            return array();
        }
        $new_arr=array();
        foreach ($arr as $key=>$val){
            $new_arr[]=explode(',', $val);
        }
//        $arr = array(
//            array("01","02","03","04","05","06","07","08","09","10"),//第一名
//            array("01","02","03","04","05","06","07","08","09","10"),//第二名
//            array("01","02","03","04","05","06","07","08","09","10"),//第三名
//            array("01","02","03","04","05","06","07","08","09","10"),//第四名
//            array("01","02","03","04","05","06","07","08","09","10"),//第五名
//            array("01","02","03","04","05","06","07","08","09","10"),//第六名
//            array("01","02","03","04","05","06","07","08","09","10"),//第七名
//            array("01","02","03","04","05","06","07","08","09","10"),//第八名
//            array("01","02","03","04","05","06","07","08","09","10"),//第九名
//            array("01","02","03","04","05","06","07","08","09","10"),//第十名
//        );
        $data=array();
        foreach ($new_arr as $value) {
            $result=$this->combinations($value,1);
            foreach ($result as $key=>$val){
                $data[]=$val;
            }
        }


       return $data;
    }


}