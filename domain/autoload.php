<?php

spl_autoload_register(function ($class_name) {

    $class_maps = [
        'good_dao' => 'dao/good.php',
        'good' => 'entity/good.php',
    ];

    if (isset($class_maps[$class_name])) {
        include __DIR__.'/'.$class_maps[$class_name];
    }
});
