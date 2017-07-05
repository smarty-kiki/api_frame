<?php

if_get('/', function ()
{
    unit_of_work(function () {
    });

    return 'ok';
});
