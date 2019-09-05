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
    public function eleven_dw_Gamll($arr){
        $arr=explode('|', $arr);
        if (count($arr)<1){
            return array();
        }
        $data=array();
        foreach ($arr as $key=>$val){
            $data[]=explode(',', $val);
        }
//        $arr = array(
//                array("01","02","03","04","05","06","07","08","09","10","11"),//一位
//				array("01","02","03","04","05","06","07","08","09","10","11"),//二位
//				array("01","02","03","04","05","06","07","08","09","10","11"),//三位
//				array("01","02","03","04","05","06","07","08","09","10","11"),//四位
//				array("01","02","03","04","05","06","07","08","09","10","11"),//五位
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