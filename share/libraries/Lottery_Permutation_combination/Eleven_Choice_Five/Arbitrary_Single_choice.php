<?php

/**
 * 任意选择 单式
 * Created by PhpStorm.
 * User: amao
 * User: Rex
 * Date: 18-11-14
 * Time: 上午4:45
 */
class Arbitrary_Single_choice extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 11选5 任意选择 单式 1中1
     */
    public function eleven_Arbitrary_Single_One_To_One($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<1){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 1);
        foreach ($result as $key=>$val){
            $this->data[]=$val[0];
        }
        return $this->data;
    }

    /**
     * 11选5 任意选择 单式 2中2
     */
    public function eleven_Arbitrary_Single_Two_To_Two($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 2);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }

    /**
     * 11选5 任意选择 单式 3中3
     */
    public function eleven_Arbitrary_Single_Three_To_Three($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<3){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 3);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return  $this->data;
    }

    /**
     * 11选5 任意选择 单式 4中4
     */
    public function eleven_Arbitrary_Single_Four_To_Four($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<4){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 4);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }

    /**
     * 11选5 任意选择 单式 5中5
     */
    public function eleven_Arbitrary_Single_Five_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<5){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 5);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }

    /**
     * 11选5 任意选择 单式 6中5
     */
    public function eleven_Arbitrary_Single_Six_To_Five($arr){
        $arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<6){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 6);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }


    /**
     * 11选5 任意选择 单式 7中5
     */
    public function eleven_Arbitrary_Single_Seven_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<7){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 7);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return  $this->data;
    }

    /**
     * 11选5 任意选择 单式 8中5
     */
    public function eleven_Arbitrary_Single_Eight_To_Five($arr){
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10","11");//选号
        $arr_one= explode(',', $arr);
        if (count($arr_one)<8){
            return array();
        }
        $this->data=array();
        $result=$this->combinations($arr_one, 8);
        foreach ($result as $key=>$val){
            sort($val);//升序
            $this->data[]=implode(' ',$val);
        }
        return $this->data;
    }

}