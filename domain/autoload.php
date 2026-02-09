<?php

spl_autoload_register(function ($class_name) {

    $class_maps = [
        'demo_dao' => 'dao/demo.php',
        'demo' => 'entity/demo.php',
    ];

    if (isset($class_maps[$class_name])) {
        include __DIR__.'/'.$class_maps[$class_name];
    }
});
