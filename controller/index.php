<?php

if_get('/', function () {

    $time = microtime(true);
    for ($i = 0; $i < 10; $i ++) {
        $a = cache_get('test');
        cache_set('test', ['a' => 'n'], 13);
    }
    echo microtime(true) - $time;
    var_dump($a);

    return 'ok';
});
