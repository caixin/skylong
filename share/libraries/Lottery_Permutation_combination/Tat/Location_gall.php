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
        $data=array();
        foreach ($arr as $key=>$val){
            $data[]=explode(',', $val);
        }
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),
//// 				array(0,1,2,3,4,5,6,7,8,9),
//// 				array(0,1,2,3,4,5,6,7,8,9),
//// 				array(0,1,2,3,4,5,6,7,8,9),
//// 				array(0,1,2,3,4,5,6,7,8,9),
//        );
        $new_arr=array();
        foreach ($data as $value) {
            $result=$this->combinations($value,1);
            $new_arr[]=$result;
        }
        $this->data=array();
        foreach ($new_arr as $key=>$value){
            foreach($value as $key=>$val){
                $this->data[]=$val[0];
            }
        }
        return $this->data;
    }


}