<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 200,
    ],
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 200',
            'failed_message' => '名称不能超过 200 个字',
        ],
    ],
    'display_name' => '描述',
];
