<?php

command('queue:worker', '启动队列 worker', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');
    $memory_limit = command_paramater('memory_limit', 1048576 * 128);

    ini_set('memory_limit', $memory_limit.'b');

    queue_finish_action(function () {
        local_cache_delete_all();
        beanstalk_close();
        cache_close();
        db_close();
    });

    queue_watch($tube, $config_key, $memory_limit);
});/*}}}*/

command('queue:status', '队列状态', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');

    echo queue_status($tube, $config_key)."\n";
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

command('queue:ready-to-buried', '将 ready 状态的任务快速改变为 buried 状态', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');

    $is_continue = command_read_bool('警告！这个操作除非手工 ctrl+c 停止，否则会持续改变 tube:'.$tube.' 中的任务状态为 buried 状态，确定开始？');

    if (! $is_continue) {
        exit;
    }

    $fp = _beanstalk_connection($config_key);

    _beanstalk_watch($fp, $tube);

    if ($tube !== 'default') {

        _beanstalk_ignore($fp, 'default');
    }

    for (;;) {

        $job_instance = _beanstalk_reserve($fp);
        $id = $job_instance['id'];
        $body = unserialize($job_instance['body']);
        $job_name = $body['job_name'];

        $job = queue_job_pickup($job_name);
        _beanstalk_bury($fp, $id);

        echo "job_id: ".$id." job_name: ".$job_name."\n";
    }

});/*}}}*/

command('queue:buried-dump', '将 buried 状态的任务快速导出文件并清理', function ()
{/*{{{*/
    $tube = command_paramater('tube', 'default');
    $config_key = command_paramater('config_key', 'default');
    $file_path = '/tmp/queue_buried_flush_tube_'.$tube.'_'.time().'.dump';

    $is_continue = command_read_bool('警告！这个操作会将 tube:'.$tube.' 中任务状态为 buried 的任务导出到文件 '.$file_path.' 并 delete 任务，确定开始？');

    if (! $is_continue) {
        exit;
    }

    $fp = _beanstalk_connection($config_key);

    _beanstalk_watch($fp, $tube);

    if ($tube !== 'default') {

        _beanstalk_ignore($fp, 'default');
    }

    while (true) {

        try {

            $job_instance = _beanstalk_peek_buried($fp);
            $job_id = $job_instance['id'];
            $body = unserialize($job_instance['body']);
            $job_name = $body['job_name'];
            error_log(json($job_instance)."\n", 3, $file_path);
            _beanstalk_delete($fp, $job_id);

            echo "job_id: ".$job_id." job_name: ".$job_name."\n";

        } catch (throwable $ex) {
            echo "\033[31m".$ex->getMessage()."\033[0m\n";
            break;
        }
    }

});/*}}}*/

command('queue:dump-import', '将导出的 dump 文件快速导入到队列并进入 ready 状态', function ()
{/*{{{*/
    $file_path = command_paramater('file_path');

    if (is_null($file_path) || false == is_file($file_path)) {
        echo "需传入 file_path 参数来指定要导入的文件\n";
        exit;
    }

    $job_string = file_get_contents($file_path);

    $job_lines = array_filter(explode("\n", $job_string));

    $is_continue = command_read_bool('警告！这个操作会导入文件 '.$file_path.' 共 '.count($job_lines).' 条任务, 任务 id 会重新生成，确定开始？');

    if (! $is_continue) {
        exit;
    }

    $error_line_count = 0;
    $error_file_path = $file_path.'.log';

    foreach ($job_lines as $job_line) {
        echo $job_line."\n";

        $job_instance = json_decode($job_line, true);

        $id = array_get($job_instance, 'id');
        if ($id) {

            $body = unserialize($job_instance['body']);
            $job_name = $body['job_name'];
            $data = $body['data'];

            queue_push($job_name, $data);

        } else {
            error_log($job_line."\n", 3, $error_file_path);
            $error_line_count++;
        }
    }

    echo "导入完毕，错误行数 $error_line_count\n";

    if ($error_line_count > 0) {
        echo "导入错误的任务可在 $error_file_path 中查看\n";
    }

});/*}}}*/
