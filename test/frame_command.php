<?php

include __DIR__.'/../bootstrap.php';

include FRAME_DIR.'/command.php';

class frame_command_test extends phpunit_framework_testcase
{
    public function setUp()
    {/*{{{*/
        global $argv;

        $argv = [];
        $argv[] = 'command.php';
        $argv[] = 'test:test';
        $argv[] = '-enable';
        $argv[] = '--memory=128';
    }/*}}}*/

    public function test_command_paramater()
    {/*{{{*/
        $this->assertTrue(command_paramater('enable'));
        $this->assertEquals(128, command_paramater('memory'));
    }/*}}}*/
}
