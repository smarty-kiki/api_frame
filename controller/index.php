<?php

if_get('/', function ()
{
    $time = microtime(true);
    echo microtime(true) - $time;

    return 'ok';
});
