<?php

command('queue:worker', '启动队列 worker', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');
    $memory_limit = command_paramater('memory_limit', 1048576 * 128);

    ini_set('memory_limit', $memory_limit.'b');

    queue_finish_action(function () {
        local_cache_delete_all();
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

command('queue:pause', '暂停队列任务派发', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');
    $delay = command_paramater('delay', 3600);

    queue_pause($tube, $config_key, $delay);

    sleep($delay);
});/*}}}*/

command('queue:peek-buried', '处理 buried 状态的任务', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');

    $fp = _beanstalk_connection($config_key);
    _beanstalk_watch($fp, $tube);
    if ($tube !== 'default') {
        _beanstalk_ignore($fp, 'default');
    }

    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

    while (true) {

        try {
            $job_instance = _beanstalk_peek_buried($fp);
            $job_id = $job_instance['id'];
            $job_instance['body'] = unserialize($job_instance['body']);
            echo "\033[32m"; var_dump($job_instance); echo "\033[0m";

            $action = command_read('Action', 0, ['kick', 'delete']);

            switch ($action) {
                case 'kick':
                    _beanstalk_kick_job($fp, $job_id);
                    break;
                case 'delete':
                    _beanstalk_delete($fp, $job_id);
                    break;
            }
        } catch (throwable $ex) {
            echo "\033[31m".$ex->getMessage()."\033[0m\n";
            break;
        }
        echo "\n";
    }
});/*}}}*/
