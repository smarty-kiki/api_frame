<?php

return [
    'api_key' => '',
    'timeout' => 300,
    'retry' => 3,

    'default_option' => [
        'model' => 'MiniMax-M2.7',
        'stream' => false,
        'max_completion_tokens' => 4096,
        'temperature' => 0.2,
        'top_p' => 0.95,
        'tools' => [],
        'tool_choice' => 'auto',
    ],
];
