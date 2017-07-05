<?php
spl_autoload_register(function ($class_name) {
    $class_maps = array(
        'customer_dao' => 'dao/customer.php',
        'good_dao' => 'dao/good.php',
        'order_dao' => 'dao/order.php',
        'order_item_dao' => 'dao/order_item.php',
        'customer' => 'entity/customer.php',
        'good' => 'entity/good.php',
        'order' => 'entity/order.php',
        'order_item' => 'entity/order_item.php',
    );
    if (isset($class_maps[$class_name])) {
         include __DIR__.'/'.$class_maps[$class_name];
    }
});
