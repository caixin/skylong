<?php
/**
 *
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-11
 * Time: 下午5:52
 */

class Front_two extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 11选5 前2直选复式
     */
    public function eleven_Front_Two_Direct_Compound($arr){
//        $arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//一位
//        $arr_two=array("01","02","03","04","05","06","07","08","09","10","11");//二位
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one=explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $this->data[]=$val;
                }
            }
        }
        return $this->data;
    }

    /**
     * 11选5 前2组选复式
     */
    public function eleven_Front_Two_Group_Compound($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one=explode(',', $arr);
        $this->data=array();
        $this->data=$this->combinations($arr_one, 2);
        return $this->data;
    }

    /**
     * 11选5 前2直选 单式
     */
    public function eleven_Front_Two_Direct_Single($arr){
//        $arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//一位
//        $arr_two=array("01","02","03","04","05","06","07","08","09","10","11");//二位
        $arr=explode('|', $arr);
        if (count($arr)<2){
            return array();
        }
        $arr_one=explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two,array($value) );//去掉重复数据
            array_unshift($result,$value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $this->data[]=implode(' ',$val);
                }
            }
        }
        return $this->data;
    }

    /**
     * 11选5 前2组选 单式
     */
    public function eleven_Front_Two_Group_Single($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one=explode(',', $arr);
        $this->data=array();
        $result=$this->combinations($arr_one, 2);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }

}