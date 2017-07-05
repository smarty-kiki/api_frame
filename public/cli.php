<?php

// init
include __DIR__.'/../bootstrap.php';
include FRAME_DIR.'/command.php';

// init miss match handler
if_command_not_found(function ($rules, $descriptions) {
    echo "未匹配到命令，支持以下命令:\n";
    foreach ($rules as $num => $rule) {
        echo str_pad($rule, 50, ' ').$descriptions[$num]."\n";
    }
});

// registe command
include COMMAND_DIR.'/migrate.php';

// fix
command_not_found();
