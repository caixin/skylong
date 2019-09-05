<?php

/**
 * 不定胆
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午3:46
 */
class Location_no_gall extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 低频彩 一码 不定位
     */
    public function low_One_bdw($arr){
        $arr_one= explode(',', $arr);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $result=$this->combinations($arr_one, 1);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

    /**
     * 低频彩 二码 不定位
     */
    public function low_Two_bdw($arr){
        $arr_one= explode(',', $arr);
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

}