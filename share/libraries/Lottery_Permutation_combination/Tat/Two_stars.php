<?php

/**
 * Created by PhpStorm.
 * User: amou
 * Date: 18-5-25
 * Time: 下午3:24
 */
class Two_stars extends Common_combination
{
    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 二星前2直选复
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function two_Front_Direct_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }

        $arr_one=  explode(',', $arr[0]);
        $arr_two=  explode(',', $arr[1]);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//十位
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//个位
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            if (in_array($value,$arr_two)){
                $this->data[]=implode("",array($value,$value));//把从复的数组组成一组
            }
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $this->data[]=implode("",$val);
                }
            }
        }
       return $this->data;
    }


    /**
     * 二星后2直选复
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function two_Back_Direct_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one=  explode(',', $arr[0]);
        $arr_two=  explode(',', $arr[1]);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//十位
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//个位
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            if (in_array($value,$arr_two)){
                $this->data[]=implode("",array($value,$value));//把从复的数组组成一组
            }
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $this->data[]=implode("",$val);
                }
            }
        }
        return $this->data;
    }

    /**
     * 二星前2直选和值
     */
    public function two_Front_Sum($bet_arr){
            $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
            if (isset($bet_arr['open_number'])){
                $arr_one=explode(',', $bet_arr['open_number']);
            };
            $arr_sum=explode(',', $bet_arr['bet_number']);
            if (count($arr_sum)<1){
                return array();
            }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
//        $arr_sum=array(1,6);//和值
        $this->data=array();
       $this->data=$this->two_Sum_Formula($arr_one,$arr_sum);
        return $this->data;
    }

    /**
     * 二星后2直选和值
     */
    public function two_Back_Sum($bet_arr){
        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
        if (isset($bet_arr['open_number'])){
            $arr_one=explode(',', $bet_arr['open_number']);
        };
        $arr_sum=explode(',', $bet_arr['bet_number']);
        if (count($arr_sum)<1){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
//        $arr_sum=array(1,6);//和值
        $this->data=array();
        $this->data=$this->two_Sum_Formula($arr_one,$arr_sum);
        return $this->data;
    }


    /**
     *二星前组选复
     */
    public function two_Front_Group_Compound($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<0){
            return array();
        }
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $value) {
            rsort($value);//desc array
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

    /**
     *二星后组选复
     */
    public function two_Back_Group_Compound($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<0){
            return array();
        }
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $value) {
            rsort($value);//desc array
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

}