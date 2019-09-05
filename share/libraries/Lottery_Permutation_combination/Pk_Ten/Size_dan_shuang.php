<?php

/**
 * 大小单双
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午5:53
 */
class Size_dan_shuang extends  Common_combination
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * pk冠军大小单双
     */
    public function one_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }


    /**
     * pk亚军大小单双
     */
    public function two_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }


    /**
     * pk季军大小单双
     */
    public function three_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk四名大小单双
     */
    public function four_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk五名大小单双
     */
    public function five_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk六名大小单双
     */
    public function six_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk七名大小单双
     */
    public function seven_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk八名大小单双
     */
    public function eight_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk九名大小单双
     */
    public function nine_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }

    /**
     * pk十名大小单双
     */
    public function ten_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }


    /**
     * pk冠亚季大小单双
     */
    public function one_two_three_Big_Smll_Single_Pair($arr){
        $arr_one= explode(',', $arr);
        //$arr_one=array("大","小","单","双");
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }

        return $data;

    }
}