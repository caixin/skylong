<?php

class Common_test extends TestCase
{
    public function test_indexTabAction()
    {
        $output = $this->request('POST', 'common/indexTabAction', [
            'testing' => true,
            'mode' => 1
        ]);
        $output = json_decode($output, true);
        $this->assertEquals($output['status'], 1);
    }

    public function test_webParam()
    {
        $output = $this->request('POST', 'common/webParam', [
            'testing' => true
        ]);
        $output = json_decode($output, true);
        $this->assertEquals($output['status'], 1);
    }
}
