<?php

include __DIR__.'/../bootstrap.php';

class frame_otherwise_test extends phpunit_framework_testcase
{

    /**
     * @expectedException        Exception
     * @expectedExceptionCode    123
     * @expectedExceptionMessage test
     */
    public function test_otherwise()
    {/*{{{*/
        otherwise(false, 'test', 'Exception', 123);
    }/*}}}*/

}
