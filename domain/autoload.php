<?php

spl_autoload_register(function ($class_name) {

    $class_maps = array(
        'order_dao' => 'dao/order.php',
        'order' => 'entity/order.php',
    );

    if (isset($class_maps[$class_name])) {
         include __DIR__.'/'.$class_maps[$class_name];
    }
});
