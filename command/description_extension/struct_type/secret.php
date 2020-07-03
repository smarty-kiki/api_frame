<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 50,
    ],
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 50',
            'failed_message' => '不能超过 50 个字',
        ],
    ],
    'display_name' => '密钥',
];

