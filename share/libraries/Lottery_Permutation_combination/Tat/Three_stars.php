<?php

/**
 * 三星
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午2:26
 */
class Three_stars extends Common_combination
{
    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 三星前三直选复
     * @param $arr
     * @return array
     */
    public function three_Front_T_Compound($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<3) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
        );
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//万位
//            array(0,1,2,3,4,5,6,7,8,9),//千位
//            array(0,1,2,3,4,5,6,7,8,9),//百位
//        );
        return $this->compound_Combinations($arr)[0];
    }


    /**
     * 三星中三直选复
     * @param $arr
     * @return array
     */
    public function three_In_T_Compound($arr)
    {
        $arr= explode('|', $arr);
        if (count($arr)<3) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
        );
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//千位
//            array(0,1,2,3,4,5,6,7,8,9),//百位
//            array(0,1,2,3,4,5,6,7,8,9),//十位
//        );
        return $this->compound_Combinations($arr)[0];
    }

    /**
     * 三星后三直选复
     * @param $arr
     * @return array
     */

    public function three_Back_T_Compound($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<3) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
        );
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//百位
//            array(0,1,2,3,4,5,6,7,8,9),//十位
//            array(0,1,2,3,4,5,6,7,8,9),//个位
//        );
        return $this->compound_Combinations($arr)[0];
    }


    /**
     * 三星后3直选跨度
     */
    public function three_Back_T_Diff($bet_arr)
    {
        $arr=array(0,1,2,3,4,5,6,7,8,9);
        if (isset($bet_arr['open_number'])) {
            $arr=explode(',', $bet_arr['open_number']);
        };

        $arr_one=array($arr,$arr,$arr);
//        $arr_one=array(
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//        );//开奖号码
        $result_c=$this->compound_Combinations($arr_one);//得到排列组合
        $this->data=array();
        foreach ($result_c as $value) {
            foreach ($value as $val) {
                $number_arr=array();
                $number_arr[]=substr($val, 0, 1);
                $number_arr[]=substr($val, 1, 1);
                $number_arr[]=substr($val, 2, 2);
                rsort($number_arr);

                $this->data[]=array($number_arr[0],$number_arr[2],$number_arr[1],$val[0],$val[1],$val[2]);
            }
        }
        $arr_diff=explode(',', $bet_arr['bet_number']);//选号
        $this->data=$this->three_Diff_Formula($this->data, $arr_diff);
        return $this->data;
    }

    /**
     * 三星后3和值尾数
     * @param $arr_one
     * @param $arr_sum
     * @return array
     */
    public function three_Back_T_Sum($bet_arr)
    {
        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
        if (isset($bet_arr['open_number'])) {
            $arr_one=explode(',', $bet_arr['open_number']);
        };

        $arr_sum=explode(',', $bet_arr['bet_number']);
        if (count($arr_sum)<1) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//0-9数快奖金号码组组合
//        $arr_sum=array(0,1,2,3,4,5,6,7,8,9);//和值
        $this->data=array();
        $this->data=$this->three_Sum_Formula($arr_one, $arr_sum);
        return $this->data;
    }


    /**
     * 三星前3组3
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function three_Front_T_Group3($arr)
    {
        $arr_one=explode(',', $arr);
        $arr_two=explode(',', $arr);
        if (count($arr_one)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    array_unshift($val, $value);
                    rsort($val);
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }

    /**
     * 三星前3组6
     * @param $arr
     * @return array
     */
    public function three_Front_T_Group6($arr)
    {
        $arr=explode(',', $arr);
        if (count($arr)<3) {
            return array();
        }
//        $arr=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result=$this->combinations($arr, 3);//得到排列组合
        $this->data=array();
        foreach ($result as $key=>$value) {
            sort($value);
            $this->data[]=implode("", $value);
        }
        return $this->data;
    }


    /**
     * 三星中3组3
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function three_In_T_Group3($arr)
    {
        $arr_one=explode(',', $arr);
        $arr_two=explode(',', $arr);
        if (count($arr_one)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    array_unshift($val, $value);
                    rsort($val);
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }


    /**
     * 三星中3组6
     * @param $arr
     * @return array
     */
    public function three_In_T_Group6($arr)
    {
        $arr=explode(',', $arr);
        if (count($arr)<3) {
            return array();
        }
//        $arr=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result=$this->combinations($arr, 3);//得到排列组合
        $this->data=array();
        foreach ($result as $key=>$value) {
            rsort($value);
            $this->data[]=implode("", $value);
        }
        return $this->data;
    }

    /**
     * 三星后3组3
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function three_Back_T_Group3($arr)
    {
        $arr_one=explode(',', $arr);
        $arr_two=explode(',', $arr);
        if (count($arr_one)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//组合数
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//组合数
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    array_unshift($val, $value);
                    rsort($val);
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }

    /**
     * 三星后3组6
     * @param $arr
     * @return array
     */
    public function three_Back_T_Group6($arr)
    {
        $arr=explode(',', $arr);
        if (count($arr)<3) {
            return array();
        }
//        $arr=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result=$this->combinations($arr, 3);//得到排列组合
        $this->data=array();
        foreach ($result as $key=>$value) {
            rsort($value);
            $this->data[]=implode("", $value);
        }
        return $this->data;
    }
}
