<?php

queue_job('demo', function ($data, $job_id) {/*{{{*/

    sleep(1);

    $demo_id = array_get($data, 'demo_id');
    $name = array_get($data, 'name');

    log_module('queue', 'demo successful! demo_id: '.$demo_id.' name: '.$name.' job_id: '.$job_id);

    return true;

}, 10, [1, 1, 1], 'default');/*}}}*/
