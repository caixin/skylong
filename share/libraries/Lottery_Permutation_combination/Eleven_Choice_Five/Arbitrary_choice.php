<?php

/**
 * 任意选择
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午5:39
 */
class Arbitrary_choice extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 11选5 任意选择 1中1
     */
    public function eleven_Arbitrary_One_To_One($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 1);
        return $this->data;
    }

    /**
     * 11选5 任意选择 2中2
     */
    public function eleven_Arbitrary_Two_To_Two($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 2);
        return $this->data;
    }

    /**
     * 11选5 任意选择 3中3
     */
    public function eleven_Arbitrary_Three_To_Three($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<3){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 3);
        return  $this->data;
    }

    /**
     * 11选5 任意选择 4中4
     */
    public function eleven_Arbitrary_Four_To_Four($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<4){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 4);
        return $this->data;
    }

    /**
     * 11选5 任意选择 5中5
     */
    public function eleven_Arbitrary_Five_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<5){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 5);
        return $this->data;
    }

    /**
     * 11选5 任意选择 6中5
     */
    public function eleven_Arbitrary_Six_To_Five($arr){
        $arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<6){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 6);
        return $this->data;
    }


    /**
     * 11选5 任意选择 7中5
     */
    public function eleven_Arbitrary_Seven_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<7){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 7);
        return  $this->data;
    }

    /**
     * 11选5 任意选择 8中5
     */
    public function eleven_Arbitrary_Eight_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<8){
            return array();
        }
        $this->data=array();
        $this->data=$this->combinations($arr_one, 8);
        return $this->data;
    }

}