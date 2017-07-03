<?php

if_get('/', function () {

    $time = microtime(true);
    $res = db_transaction(function () {
        for ($i = 0; $i < 100; $i ++) {
            $res = db_query('select * from demo');
        }
        return $res;
    });
    echo microtime(true) - $time;

    return 'ok';
});
