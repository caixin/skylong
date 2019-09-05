<?php

class Qishu_model_test extends TestCase
{
    public function setUp()
    {
        $this->resetInstance();
        $this->CI->load->model('Qishu_model');
        $this->obj = $this->CI->Qishu_model;
    }

    public function test_getDayQishu()
    {
        $lottery = $this->obj->where(array('t.is_delete'=>0))->result();
        
        foreach ($lottery as $row) {
            $expected = 0;
            switch ($row['id']) {
                case 1: $expected = 42; break;
                case 2: $expected = 42; break;
                case 3: $expected = 40; break;
                case 4: $expected = 41; break;
                case 5: $expected = 41; break;
                case 6: $expected = 48; break;
                case 7: $expected = 59; break;
                case 8: $expected = 42; break;
                case 10: $expected = 179; break;
                case 11: $expected = 42; break;
                case 12: $expected = 42; break;
                case 13: $expected = 43; break;
                case 14: $expected = 44; break;
                case 15: $expected = 180; break;
                case 16: $expected = 1; break;
                case 17: $expected = 1; break;
                case 18: $expected = 1440; break;
                case 19: $expected = 1440; break;
                case 20: $expected = 1440; break;
                case 21: $expected = 1440; break;
                case 23: $expected = 45; break;
                case 24: $expected = 41; break;
                case 25: $expected = 41; break;
                case 26: $expected = 41; break;
                case 27: $expected = 42; break;
                case 28: $expected = 44; break;
                case 29: $expected = 41; break;
                case 30: $expected = 45; break;
                case 31: $expected = 1440; break;
            }

            $count = count($this->obj->getDayQishu($row['id']));
            $this->assertEquals($expected, $count, "ID:$row[id] 彩種:$row[key_word]");
        }
    }
}
