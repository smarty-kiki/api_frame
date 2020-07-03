<?php

return [
    'data_type' => 'string',
    'database_field' => [
        'length' => 15,
    ],
    'formater' => [
        [
            'reg' => '/^(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}$/',
            'failed_message' => '不是有效的 IP 格式',
        ],
    ],
    'display_name' => 'IP 地址',
];
