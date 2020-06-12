<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 30,
    ],
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 30',
            'failed_message' => '名称不能超过 30 个字',
        ],
    ],
    'display_name' => '名称',
];

