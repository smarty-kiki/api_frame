<?php

return [
    'data_type' => 'number',
    'formater' => [
        [
            'function' => 'mb_strlen($value) <= 11',
            'failed_message' => 'ID 不能超过 15 个字',
        ],
        [
            'function' => 'is_numeric($value)',
            'failed_message' => 'ID 必须为整数',
        ],
    ],
    'display_name' => 'ID',
];
