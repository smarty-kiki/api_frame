<?php

queue_job('hello_world', function () {/*{{{*/

    sleep(1);

    log_module('queue', 'hello world successful!');

    return true;

}, 10, [1, 1, 1], 'default');/*}}}*/
