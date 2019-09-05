<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-25
 * Time: 上午10:14
 */
class Dragon_tiger extends  Common_combination
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * pk一名龙虎
     */
    public function  one_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }
    /**
     * pk二名龙虎
     */
    public function two_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }
    /**
     * pk三名龙虎
     */
    public function three_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }
    /**
     * pk四名龙虎
     */
    public function four_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }
    /**
     * pk五名龙虎
     */
    public function five_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }

    /**
     * pk1,2,3名龙虎
     */
    public function one_Two_Three_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }

    /**
     * pk1,2名龙虎
     */
    public function one_Two_Dragon_Tiger($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("龙","虎");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;

    }

}