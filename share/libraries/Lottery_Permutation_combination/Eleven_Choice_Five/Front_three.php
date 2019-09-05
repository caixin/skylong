<?php
/**
 *
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-11
 * Time: 下午5:52
 */

class Front_three extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 11选5 前三直选复式
     */
    public function eleven_Front_Three_Direct_Compound($arr){
//        $arr = array(
//            array("01","02","03","04","05","06","07","08","09","10","11"),//一位
//            array("01","02","03","04","05","06","07","08","09","10","11"),//二位
//            array("01","02","03","04","05","06","07","08","09","10","11"),//三位
//        );
        $arr=explode('|', $arr);
        if (count($arr)<3){
            return array();
        }
        $arr_one=explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
        $arr_three=explode(',', $arr[2]);
        $arr=array($arr_one,$arr_two,$arr_three);
        $result=$this->compound_Combinations($arr)[0];
        $this->data=array();
        foreach ($result as $val) {
            $val_one=substr($val, 0,2);
            $val_two=substr($val, 2,2);
            $val_three=substr($val, 4,4);
            $val=$val_one.",".$val_two.",".$val_three;
            if (substr_count($val,$val_one)==1&&substr_count($val,$val_two)==1&&substr_count($val,$val_three)==1) {
                $this->data[]=array($val_one,$val_two,$val_three);
            }
        }
       return $this->data;
    }

    /**
     * 11选5 前3组选复式
     */
    public function eleven_Front_Three_Group_Compound($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one=explode(',', $arr);
        $this->data=array();
        $this->data=$this->combinations($arr_one, 3);
        return $this->data;
    }

    /**
     * 11选5 前三直选 单式
     */
    public function eleven_Front_Three_Direct_Single($arr){
//        $arr = array(
//            array("01","02","03","04","05","06","07","08","09","10","11"),//一位
//            array("01","02","03","04","05","06","07","08","09","10","11"),//二位
//            array("01","02","03","04","05","06","07","08","09","10","11"),//三位
//        );
        $arr=explode('|', $arr);
        if (count($arr)<3){
            return array();
        }
        $arr_one=explode(',', $arr[0]);
        $arr_two=explode(',', $arr[1]);
        $arr_three=explode(',', $arr[2]);
        $arr=array($arr_one,$arr_two,$arr_three);
        $result=$this->compound_Combinations($arr)[0];
        $this->data=array();
        foreach ($result as $val) {
            $val_one=substr($val, 0,2);
            $val_two=substr($val, 2,2);
            $val_three=substr($val, 4,4);
            $val=$val_one.",".$val_two.",".$val_three;
            if (substr_count($val,$val_one)==1&&substr_count($val,$val_two)==1&&substr_count($val,$val_three)==1) {
                $this->data[]=implode(' ',array($val_one,$val_two,$val_three));
            }
        }
        return $this->data;
    }

    /**
     * 11选5 前3组选 单式
     */
    public function eleven_Front_Three_Group_Single($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one=explode(',', $arr);
        $this->data=array();
        $result=$this->combinations($arr_one, 3);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }
}