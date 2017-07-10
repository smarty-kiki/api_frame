<?php

if_get('/', function ()
{
    $time = microtime(true);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    queue_push('test', ['test' => 'haha']);
    echo microtime(true) - $time;

    return 'ok';
});
