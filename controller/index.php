<?php

if_get('/', function ()
{
    $a = db_query('show tables');
    var_dump($a);
    return 'hello world';
});
