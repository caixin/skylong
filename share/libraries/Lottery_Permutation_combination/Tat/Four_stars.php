<?php

/**
 * 四星
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午2:19
 */
class Four_stars extends Common_combination
{
    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 四星前四直选复
     * @param $arr
     * @return array
     */
    public function four_Front_F_Compound($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<4) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
            explode(',', $arr[3]),
        );
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//万位
//            array(0,1,2,3,4,5,6,7,8,9),//千位
//            array(0,1,2,3,4,5,6,7,8,9),//百位
//            array(0,1,2,3,4,5,6,7,8,9),//十位
//        );
        return $this->compound_Combinations($arr)[0];
    }

    /**
     * 四星后四直选复
     * @param $arr
     * @return array
     */
    public function four_Back_F_Compound($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<4) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
            explode(',', $arr[3]),
        );
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),//千位
//            array(0,1,2,3,4,5,6,7,8,9),//百位
//            array(0,1,2,3,4,5,6,7,8,9),//十位
//            array(0,1,2,3,4,5,6,7,8,9),//个位
//        );
        return $this->compound_Combinations($arr)[0];
    }


    /**
     * 四星 后4组选24
     * @param $arr_one
     * @return array
     */
    public function four_Back_Group24($arr)
    {
        $arr_one=explode(',', $arr);
        if (count($arr_one)<4) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 4);
        foreach ($result as $key=>$value) {
            $result[$key]=implode("", $value);
        }
        return $result;
    }


    /**
     * 四星 后4组选12
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function four_Back_Group12($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//二从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
                array_unshift($result, $value);//把重复数据插入到数组开头
                $result_c=$this->combinations($result, 3);//得到排列组合
                //遍历得到的组合
                foreach ($result_c as $key=>$val) {
                    //拿从号的号码 与排列的号码对比 只取从号在排列数据的第一位的队列
                    if ($val[0]==$value) {
                        $val[]=$value;
                        rsort($val);//desc array
                        $this->data[]=implode("", $val);
                    }
                }
        }
        return $this->data;
    }


    /**
     * 四星 后4组选6
     * @param $arr_one
     * @return array
     */
    public function four_Back_Group6($arr)
    {
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//二从号
        $result=$this->combinations($arr_one, 2);
        foreach ($result as &$value) {
            $value[2]=$value[0];
            $value[3]=$value[1];
            rsort($value);
            $value=implode("", $value);
        }
        return $result;
    }

    /**
     * 四星 后4组选4
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function four_Back_Group4($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<1) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//三从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $val[]=$value;
                    $val[]=$value;
                    rsort($val);//desc array
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }
}
