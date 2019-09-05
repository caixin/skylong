<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-20
 * Time: 上午9:59
 */
class Two_code extends  Common_combination
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 低频彩 二码 后二直选复式
     */
    public function low_Two_Back_Direct_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one= explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//十位
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//个位
        $data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            if (in_array($value,$arr_two)){
                $data[]=implode("",array($value,$value));//把从复的数组组成一组
            }
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $data[]=implode("",$val);
                }
            }
        }
       return $data;
    }

    /**
     * 低频彩 二码 后二组选复式
     */
    public function low_Two_Back_Group_Compound($arr){
        $arr_one=explode(',',$arr);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $result_c=$this->combinations($arr_one, 2);
        $data=array();
        foreach ($result_c as $key=>$val) {
            $data[]=implode("",$val);
        }
        return $data;
    }

    /**
     * 低频彩 二码 前二直选复式
     */
    public function low_Two_Front_Direct_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one= explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//十位
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//百位
        $data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            if (in_array($value,$arr_two)){
                $data[]=implode("",array($value,$value));//把从复的数组组成一组
            }
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $data[]=implode("",$val);
                }
            }
        }
        return $data;
    }

    /**
     * 低频彩 二码 前二组选复式
     */
    public function low_Two_Front_Group_Compound($arr){
        $arr_one= explode(',', $arr);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $result_c=$this->combinations($arr_one, 2);
        $data=array();
        foreach ($result_c as $key=>$val) {
            $data[]=implode("",$val);
        }
        return $data;
    }
}