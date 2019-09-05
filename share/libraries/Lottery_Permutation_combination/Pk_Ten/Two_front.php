<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-25
 * Time: 上午9:53
 */
class Two_front extends  Common_combination
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * pk 前二复式
     */
    public function Two_Front_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one=explode(',',$arr[0]);
        $arr_two=explode(',',$arr[1]);
//        $arr_one=array("01","02","03","04","05","06","07","08","09","10");//第一位
//        $arr_two=array("01","02","03","04","05","06","07","08","09","10");//第二位
        $data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $data[]=implode("",$val);
                }
            }
        }
       return $data;
    }


}