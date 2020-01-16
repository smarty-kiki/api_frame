<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 1000,
    ],
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 1000',
            'failed_message' => '不能超过 1000 个字符',
        ],
    ],
    'display_name' => 'URL',
];
