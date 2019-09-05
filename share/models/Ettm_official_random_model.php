<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_official_random_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'ettm_official_wanfa';
        $this->_key = 'id';
    }

    public function getWanfaRandomData($wanfa_id, $bet_number = 1)
    {
        $wanfa = $this->row($wanfa_id);
        $wanfa_p = $this->row($wanfa['pid']);

        if ($wanfa === null || $wanfa_p === null) {
            return [];
        }
        switch ($wanfa['lottery_type_id']) {
            case 1:
                return $this->random_tat($wanfa_p['key_word'], $wanfa['key_word'], $bet_number);
            case 5:
                return $this->random_pk10($wanfa_p['key_word'], $wanfa['key_word'], $bet_number);
            case 6:
                return $this->random_11x5($wanfa_p['key_word'], $wanfa['key_word'], $bet_number);
            case 7:
                return $this->random_dpc($wanfa_p['key_word'], $wanfa['key_word'], $bet_number);
            default:
                return [];
        }
    }

    /**
     * pk10
     * @param  [type] $p_wanfa_key_word [description]
     * @param  [type] $wanfa_key_word   [description]
     * @param  [type] $bet_number       [注數]
     * @return [type]           [description]
     */
    private function random_pk10($p_wanfa_key_word, $wanfa_key_word, $bet_number)
    {
        $data = [];
        $numbers_arr = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10',];

        //前一
        if ($wanfa_key_word == "one_Front_Compound") { //直选复式
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }
        //前二
        elseif ($wanfa_key_word == "Two_Front_Compound") { //直选复式
            $data = $this->get_random_data_group([1, 1], $bet_number, $numbers_arr);
        }
        //前三
        elseif ($wanfa_key_word == "Three_Front_Compound") { //直选复式
            $data = $this->get_random_data_group([1, 1, 1], $bet_number, $numbers_arr);
        }
        //定位胆
        elseif ($wanfa_key_word == "dw_Gall") { //定位胆
            $data = $this->get_random_data_location_gall(10, 1, $bet_number, $numbers_arr);
        }
        //龙虎
        elseif (in_array($wanfa_key_word, ['one_Dragon_Tiger', 'two_Dragon_Tiger', 'three_Dragon_Tiger', 'four_Dragon_Tiger', 'five_Dragon_Tiger', 'one_Two_Dragon_Tiger', 'one_Two_Three_Dragon_Tiger', 'one_Two_Dragon_Tiger',])) {
            $data = $this->get_random_data([1], $bet_number, ['龙', '虎',]);
        }
        //大小单双
        elseif (in_array($wanfa_key_word, ['one_Big_Smll_Single_Pair', 'two_Big_Smll_Single_Pair', 'three_Big_Smll_Single_Pair', 'four_Big_Smll_Single_Pair', 'five_Big_Smll_Single_Pair', 'six_Big_Smll_Single_Pair', 'seven_Big_Smll_Single_Pair', 'eight_Big_Smll_Single_Pair', 'nine_Big_Smll_Single_Pair', 'ten_Big_Smll_Single_Pair', 'one_two_three_Big_Smll_Single_Pair',])) {
            $data = $this->get_random_data([1], $bet_number, ['大', '小', '单', '双',]);
        }
        //冠亚季
        elseif ($wanfa_key_word == "one_Two_Three_Select") {
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }

        //
        return $data;
    }

    /**
     * 低頻彩
     * @param  [type] $p_wanfa_key_word [description]
     * @param  [type] $wanfa_key_word   [description]
     * @param  [type] $bet_number       [注數]
     * @return [type]           [description]
     */
    private function random_dpc($p_wanfa_key_word, $wanfa_key_word, $bet_number)
    {
        $data = [];
        $numbers_arr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,];

        //三碼
        if ($wanfa_key_word == "low_Three_Direct_Compound") { //直選複式
            $data = $this->get_random_data([1, 1, 1], $bet_number, $numbers_arr);
        } elseif ($wanfa_key_word == "low_Three_Direct_Sum") { //直选和值
            $data = $this->get_random_data([1], $bet_number, range(0, 27));
        } elseif ($wanfa_key_word == "low_Three_Group3") { //组三
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif ($wanfa_key_word == "low_Three_Group6") { //组六
            $data = $this->get_random_data([3], $bet_number, $numbers_arr);
        }
        //後二
        elseif ($wanfa_key_word == "low_Two_Back_Direct_Compound") { //直选复式
            $data = $this->get_random_data([1, 1], $bet_number, $numbers_arr);
        } elseif ($wanfa_key_word == "low_Two_Back_Group_Compound") { //组选复式
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        }
        //前二
        elseif ($wanfa_key_word == "low_Two_Front_Direct_Compound") { //直选复式
            $data = $this->get_random_data([1, 1], $bet_number, $numbers_arr);
        } elseif ($wanfa_key_word == "low_Two_Front_Group_Compound") { //组选复式
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        }
        //定位胆
        elseif ($wanfa_key_word == "low_Dw") { //直选复式
            $data = $this->get_random_data_location_gall(3, 1, $bet_number, $numbers_arr);
        }
        //不定胆
        elseif ($wanfa_key_word == "low_One_bdw") { //一码
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        } elseif ($wanfa_key_word == "low_Two_bdw") { //二码
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        }
        //大小单双
        elseif ($wanfa_key_word == "low_Front_Big_Smll_Single_Pair") { //前二大小单双
            $data = $this->get_random_data([1, 1], $bet_number, ['大', '小', '单', '双',]);
        } elseif ($wanfa_key_word == "low_Back_Big_Smll_Single_Pair") { //后二大小单双
            $data = $this->get_random_data([1, 1], $bet_number, ['大', '小', '单', '双',]);
        }

        //
        return $data;
    }

    /**
     * 時時彩
     * @param  [type] $p_wanfa_key_word [description]
     * @param  [type] $wanfa_key_word   [description]
     * @param  [type] $bet_number       [注數]
     * @return [type]           [description]
     */
    private function random_tat($p_wanfa_key_word, $wanfa_key_word, $bet_number)
    {
        $data = [];
        $numbers_arr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,];

        //五星
        if ($p_wanfa_key_word == "Five_stars") {
            if ($wanfa_key_word == "five_Direct_Compound") { //直選複式
                $data = $this->get_random_data([1, 1, 1, 1, 1], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group120") { //組選120
                $data = $this->get_random_data_group([5], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group60") { //組選60
                $data = $this->get_random_data_group([1, 3], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group30") { //組選30
                $data = $this->get_random_data_group([2, 1], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group20") { //組選20
                $data = $this->get_random_data_group([1, 2], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group10") { //組選10
                $data = $this->get_random_data_group([1, 1], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "five_Group5") { //組選5
                $data = $this->get_random_data_group([1, 1], $bet_number, $numbers_arr);
            }
        }
        //四星
        elseif ($p_wanfa_key_word == "Four_stars") {
            if ($wanfa_key_word == "four_Front_F_Compound") { //前四直选
                $data = $this->get_random_data([1, 1, 1, 1], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "four_Back_F_Compound") { //后四直选
                $data = $this->get_random_data([1, 1, 1, 1], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "four_Back_Group24") { //组选24
                $data = $this->get_random_data_group([4], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "four_Back_Group12") { //组选12
                $data = $this->get_random_data_group([1, 2], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "four_Back_Group6") { //组选6
                $data = $this->get_random_data_group([2], $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "four_Back_Group4") { //组选4
                $data = $this->get_random_data_group([1, 1], $bet_number, $numbers_arr);
            }
        }
        //定位胆
        elseif ($p_wanfa_key_word == "Location_gall") {
            $data = $this->get_random_data_location_gall(5, 1, $bet_number, $numbers_arr);
        }
        //不定胆
        elseif ($p_wanfa_key_word == "Location_no_gall") {
            $NumCnt_arr = [
                'bdw_Three_Front_One_Gall'  => 1, //前三一码
                'bdw_Three_In_One_Gall'     => 1, //中三一码
                'bdw_Three_Back_One_Gall'   => 1, //后三一码
                'bdw_Three_Front_Two_Gall'  => 2, //前三二码
                'bdw_Three_Back_Two_Gall'   => 2, //后三二码
                'bdw_Five_Stars_Three_Gall' => 3, //五星三码
                'bdw_Five_Two_Gall'         => 2, //五星二码
                'bdw_Four_Back_Two_Gall'    => 2, //后四二码
                'bdw_Four_Back_One_Gall'    => 1, //后四一码
            ];
            if (!empty($NumCnt_arr[$wanfa_key_word])) {
                $NumCnt = $NumCnt_arr[$wanfa_key_word];
                $data = $this->get_random_data([$NumCnt], $bet_number, $numbers_arr);
            }
        }
        //任選
        elseif ($p_wanfa_key_word == "Arbitrary_choice") {
            if ($wanfa_key_word == "arbitrary_Choice_Direct_Two") { //任二复式
                $data = $this->get_random_data_location_gall(5, 2, $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "arbitrary_Choice_Direct_Three") { //任三复式
                $data = $this->get_random_data_location_gall(5, 3, $bet_number, $numbers_arr);
            } elseif ($wanfa_key_word == "arbitrary_Choice_Direct_Four") { //任四复式
                $data = $this->get_random_data_location_gall(5, 4, $bet_number, $numbers_arr);
            }
        }
        //趣味
        elseif ($p_wanfa_key_word == "Interest") {
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }
        //大小單雙
        elseif ($p_wanfa_key_word == "Size_dan_shuang") {
            if (in_array($wanfa_key_word, ['tow_Front_Big_Smll_Single_Pair',])) { //前二大小单双
                $data = $this->get_random_data([1, 1], $bet_number, ['大', '小', '单', '双',]);
            } elseif (in_array($wanfa_key_word, ['tow_Back_Big_Smll_Single_Pair',])) { //后二大小单双
                $data = $this->get_random_data([1, 1], $bet_number, ['大', '小', '单', '双',]);
            } elseif (in_array($wanfa_key_word, ['three_Front_Big_Smll_Single_Pair',])) { //前三大小单双
                $data = $this->get_random_data([1, 1, 1], $bet_number, ['大', '小', '单', '双',]);
            } elseif (in_array($wanfa_key_word, ['three_Back_Big_Smll_Single_Pair',])) { //后三大小单双
                $data = $this->get_random_data([1, 1, 1], $bet_number, ['大', '小', '单', '双',]);
            }
        }
        //前三、中三、后三 的 p_wanfa_key_word 值是相同的
        //前二、后二 的 p_wanfa_key_word 值是相同的
        //前三
        if (in_array($wanfa_key_word, ['three_Front_T_Compound',])) { //直选复式
            $data = $this->get_random_data([1, 1, 1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Front_T_Group3',])) { //组三
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Front_T_Group6',])) { //组六
            $data = $this->get_random_data([3], $bet_number, $numbers_arr);
        }
        //前二
        elseif (in_array($wanfa_key_word, ['two_Front_Direct_Compound',])) { //直选复式
            $data = $this->get_random_data([1, 1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['two_Front_Group_Compound',])) { //组选复式
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['two_Front_Sum',])) { //直选和值
            $data = $this->get_random_data([1], $bet_number, range(0, 18));
        }
        //中三
        elseif (in_array($wanfa_key_word, ['three_In_T_Compound',])) { //直选复式
            $data = $this->get_random_data([1, 1, 1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_In_T_Group3',])) { //组三
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_In_T_Group6',])) { //组六
            $data = $this->get_random_data([3], $bet_number, $numbers_arr);
        }
        //後三
        elseif (in_array($wanfa_key_word, ['three_Back_T_Compound',])) { //直选复式
            $data = $this->get_random_data([1, 1, 1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Back_T_Group3',])) { //组三
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Back_T_Group6',])) { //组六
            $data = $this->get_random_data([3], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Back_T_Diff',])) { //直选跨度
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['three_Back_T_Sum',])) { //和值尾数
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }

        //後二
        elseif (in_array($wanfa_key_word, ['two_Back_Direct_Compound',])) { //直选复式
            $data = $this->get_random_data([1, 1], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['two_Back_Group_Compound',])) { //组选复式
            $data = $this->get_random_data([2], $bet_number, $numbers_arr);
        } elseif (in_array($wanfa_key_word, ['two_Back_Sum',])) { //直选和值
            $data = $this->get_random_data([1], $bet_number, range(0, 18));
        }

        //
        return $data;
    }

    /**
     * 11選5
     * @param  [type] $p_wanfa_key_word [description]
     * @param  [type] $wanfa_Key_word [description]
     * @param  [type] $bet_number     [注數]
     * @return [type]           [description]
     */
    private function random_11x5($p_wanfa_key_word, $wanfa_key_word, $bet_number)
    {
        $data = [];
        $numbers_arr = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11',];


        //前一
        if ($p_wanfa_key_word == "Front_one") {
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }
        //前二
        elseif ($p_wanfa_key_word == "Front_two") {
            //直選複式
            if (in_array($wanfa_key_word, ['eleven_Front_Two_Direct_Compound',])) {
                $data = $this->get_random_data_group([1, 1], $bet_number, $numbers_arr);
            }
            //組選複式、單式，直選單式
            elseif (in_array($wanfa_key_word, ['eleven_Front_Two_Group_Compound', 'eleven_Front_Two_Group_Single', 'eleven_Front_Two_Direct_Single',])) {
                $data = $this->get_random_data_group([2], $bet_number, $numbers_arr);
            }
        }
        //前三
        elseif ($p_wanfa_key_word == "Front_three") {
            //直選複式
            if (in_array($wanfa_key_word, ['eleven_Front_Three_Direct_Compound',])) {
                $data = $this->get_random_data_group([1, 1, 1], $bet_number, $numbers_arr);
            }
            //組選複式、單式，直選單式
            elseif (in_array($wanfa_key_word, ['eleven_Front_Three_Group_Compound', 'eleven_Front_Three_Group_Single', 'eleven_Front_Three_Direct_Single',])) {
                $data = $this->get_random_data_group([3], $bet_number, $numbers_arr);
            }
        }
        //不定胆
        elseif ($p_wanfa_key_word == 'Location_no_gall') {
            $data = $this->get_random_data([1], $bet_number, $numbers_arr);
        }
        //定位胆
        elseif ($p_wanfa_key_word == 'Location_gall') {
            $data = $this->get_random_data_location_gall(5, 1, $bet_number, $numbers_arr);
        }
        //任選複式
        elseif ($p_wanfa_key_word == 'Arbitrary_choice') {
            $NumCnt_arr = [
                'eleven_Arbitrary_One_To_One'     => 1,
                'eleven_Arbitrary_Two_To_Two'     => 2,
                'eleven_Arbitrary_Three_To_Three' => 3,
                'eleven_Arbitrary_Four_To_Four'   => 4,
                'eleven_Arbitrary_Five_To_Five'   => 5,
                'eleven_Arbitrary_Six_To_Five'    => 6,
                'eleven_Arbitrary_Seven_To_Five'  => 7,
                'eleven_Arbitrary_Eight_To_Five'  => 8,
            ];
            if (!empty($NumCnt_arr[$wanfa_key_word])) {
                $NumCnt = $NumCnt_arr[$wanfa_key_word];
                $data = $this->get_random_data([$NumCnt], $bet_number, $numbers_arr);
            }
        }
        //任選單式
        elseif ($p_wanfa_key_word == 'Arbitrary_Single_choice') {
            $NumCnt_arr = [
                'eleven_Arbitrary_Single_One_To_One'     => 1,
                'eleven_Arbitrary_Single_Two_To_Two'     => 2,
                'eleven_Arbitrary_Single_Three_To_Three' => 3,
                'eleven_Arbitrary_Single_Four_To_Four'   => 4,
                'eleven_Arbitrary_Single_Five_To_Five'   => 5,
                'eleven_Arbitrary_Single_Six_To_Five'    => 6,
                'eleven_Arbitrary_Single_Seven_To_Five'  => 7,
                'eleven_Arbitrary_Single_Eight_To_Five'  => 8,
            ];
            if (!empty($NumCnt_arr[$wanfa_key_word])) {
                $NumCnt = $NumCnt_arr[$wanfa_key_word];
                $data = $this->get_random_data([$NumCnt], $bet_number, $numbers_arr);
            }
        }
        //趣味
        elseif ($p_wanfa_key_word == "Interest") {
            $data = $this->get_random_data([1], $bet_number, ['03', '04', '05', '06', '07', '08', '09',]);
        }

        //
        return $data;
    }

    /**
     * 隨機注數組合
     * @param  [integer]  $LNumCnt     [幾個第x位,取幾個號碼]ex. [1,2,3] 3個位置，第1個位置取1個號碼，第2個位置取2個號碼，.....
     * @param  [integer]  $bet_number  [注數]
     * @param  [array]    $numbers_arr [號碼]
     * @return [type]               [description]
     */
    private function get_random_data($LNumCnt, $bet_number = 1, $numbers_arr)
    {
        $data = [];
        $LNum = count($LNumCnt); //幾個位置

        for ($b = 0; $b < $bet_number; $b++) { //注數
            $bet_data = [];
            for ($c = 0; $c < $LNum; $c++) { //位置
                $cnt = $LNumCnt[$c];
                shuffle($numbers_arr);
                $myNumbers = array_slice($numbers_arr, 0, $cnt);
                $bet_data[] = $myNumbers;
            }
            $data[] = $bet_data;
        }
        //
        return $data;
    }

    /**
     * 隨機注數組合
     * (定位膽)(任選複式)專用
     * @param  [integer]  $cntNum      [幾個第x位]
     * @param  [integer]  $selCnt      [選幾個位置]
     * @param  [integer]  $bet_number  [注數]
     * @param  [array]    $numbers_arr [號碼]
     * @return [type]               [description]
     */
    private function get_random_data_location_gall($cntNum = 5, $selCnt = 1, $bet_number = 1, $numbers_arr)
    {
        $data = [];

        for ($b = 0; $b < $bet_number; $b++) {
            $bet_data = [];
            $location_range = range(1, $cntNum); //產生x個位置
            shuffle($location_range);
            $myLocation = array_slice($location_range, 0, $selCnt); //選幾個位置

            for ($c = 1; $c <= $cntNum; $c++) {
                if (in_array($c, $myLocation)) {
                    shuffle($numbers_arr);
                    $bet_data[] = [$numbers_arr[0]];
                } else {
                    $bet_data[] = [];
                }
            }
            $data[] = $bet_data;
        }
        // print_r($data);
        //
        return $data;
    }

    /**
     * 隨機注數組合
     * (時時彩，組選XX專用)每個位置，號碼不能重複
     * @param  [integer]  $LNumCnt     [幾個第x位,取幾個號碼]ex. [1,2,3] 3個位置，第1個位置取1個號碼，第2個位置取2個號碼，.....
     * @param  [integer]  $bet_number  [注數]
     * @param  [array]    $numbers_arr [號碼]
     * @return [type]                   [description]
     */
    private function get_random_data_group($LNumCnt, $bet_number = 1, $numbers_arr)
    {
        $data = [];
        $LNum = count($LNumCnt); //幾個位置

        for ($b = 0; $b < $bet_number; $b++) { //注數
            $bet_data = [];
            shuffle($numbers_arr);
            $numbers_arr_new = $numbers_arr;
            for ($c = 0; $c < $LNum; $c++) {
                $cnt = $LNumCnt[$c];
                $myNumbers = array_slice($numbers_arr_new, 0, $cnt); //取號碼(不重複)
                $bet_data[] = $myNumbers;
                $numbers_arr_new = array_diff($numbers_arr_new, $myNumbers); //取出沒使用過的號碼
            }
            $data[] = $bet_data;
        }
        //
        return $data;
    }
}
