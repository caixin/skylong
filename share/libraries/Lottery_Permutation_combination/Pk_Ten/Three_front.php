<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-25
 * Time: 上午9:53
 */
class Three_front extends  Common_combination
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * pk 前三复式
     */
    public function three_Front_Compound($arr){
        $arr=explode('|', $arr);
        if (count($arr)<3){
            return array();
        }
        $arr_one=explode(',',$arr[0]);
        $arr_two=explode(',',$arr[1]);
        $arr_three=explode(',',$arr[2]);
        $new_arr=array($arr_one,$arr_two,$arr_three);
//        $new_arr = array(
//            array("01","02","03","04","05","06","07","08","09","10"),//千位
//            array("01","02","03"),//百位
//            array("01","02","03"),//十位
//        );
        $result=$this->compound_Combinations($new_arr)[0];
        $data=array();
        foreach ($result as $val) {
            $val_one=substr($val, 0,2);
            $val_two=substr($val, 2,2);
            $val_three=substr($val, 4,4);
            $val=$val_one.",".$val_two.",".$val_three;
            if (substr_count($val,$val_one)==1&&substr_count($val,$val_two)==1&&substr_count($val,$val_three)==1) {
                $data[]=$val_one.$val_two.$val_three;
            }
        }
       return $data;
    }


}