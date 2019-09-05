<?php

/**
 * 三码
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-20
 * Time: 上午9:43
 */
class Three_code extends Common_combination
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 低频彩 三码直选复式
     * @param $arr
     * @return array
     */
    public function low_Three_Direct_Compound($arr){
        $arr=explode('|', $arr);

        if (count($arr)<3){
            return array();
        }
        $data=array();
        for ($i=0;$i<count($arr);$i++){
            $data[]=explode(',', $arr[$i]);
        }
        $arr=$data;
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//一位
//            array(0,1,2,3,4,5,6,7,8,9),//二位
//            array(0,1,2,3,4,5,6,7,8,9),//三位
//        );
        $result=$this->compound_Combinations($arr)[0];
       return $result;
    }

    /**
     * 低频彩 三码 直选和值
     * @param $bet_arr
     * @return array
     */
    public function low_Three_Direct_Sum($bet_arr){
        $arr="0,1,2,3,4,5,6,7,8,9";
        if (isset($bet_arr['open_number'])){
            $arr=$bet_arr['open_number'];
        };
        $arr_one=explode(',', $arr);
        $arr_sum=explode(',', $bet_arr['bet_number']);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
//        $arr_sum=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27);//和值
       $result= $this->three_Sum_Formula($arr_one,$arr_sum,'low');
        return $result;
    }

    /**
     * 低频彩 三码 组3
     * @param $arr
     * @return array
     */
    public function low_Three_Group3($arr){
        $arr_one=explode(',', $arr);
        $arr_two=$arr_one;
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    array_unshift($val,$value);
                    $data[]=implode("",$val);
                }
            }
        }
       return $data;
    }

    /**
     * 低频彩 三码 组6
     * @param $arr
     * @return array
     */
    public function low_Three_Group6($arr){
        $arr_one=explode(',', $arr);
        //$arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $result_c=$this->combinations($arr_one, 3);
        $data=array();
        foreach ($result_c as $key=>$val) {
            $data[]=implode("",$val);
        }
        return $data;
    }
}