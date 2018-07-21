<?php

command('queue:worker', '启动队列 worker', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');
    $memory_limit = command_paramater('memory_limit', 1048576 * 128);

    ini_set('memory_limit', $memory_limit.'b');

    queue_finish_action(function () {
        cache_close();
        db_close();
    });

    queue_watch($tube, $config_key, $memory_limit);
});/*}}}*/

command('queue:status', '队列状态', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');

    echo queue_status($tube, $config_key);
});/*}}}*/
