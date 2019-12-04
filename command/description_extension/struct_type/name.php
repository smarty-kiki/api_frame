<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 15,
    ],
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 15',
            'failed_message' => '名称不能超过 15 个字',
        ],
    ],
    'display_name' => '名称',
];

