<?php

if_get('/', function ()
{
    return 'hello world';
});

if_get('/exception', function ()
{
    otherwise_error_code(10001,
        1 === 2,
        [
            ':replace' => '1 不等于 2',
        ]
    );

    return 'whatever';
});
