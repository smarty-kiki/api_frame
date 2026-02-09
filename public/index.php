<?php

header('Access-Control-Allow-Origin: *');

// init
include __DIR__.'/../bootstrap.php';
include FRAME_DIR.'/http/php_fpm/application.php';

set_error_handler('http_err_action', E_ALL);
set_exception_handler('http_ex_action');
register_shutdown_function('http_fatal_err_action');

if_has_exception(function ($ex) {

    $error_info = otherwise_get_error_info($ex);

    if ($ex instanceof business_exception) {
        log_module('business_exception', $error_info['message']);
    } else {
        log_exception($ex);
    }

    header('Content-type: application/json');

    return json([
        'code' => $error_info['code'],
        'msg' => $error_info['message'],
        'data' => [],
    ]);
});

if_verify(function ($action, $args) {

    return unit_of_work(function () use ($action, $args){

        $res = [
            'code' => 0,
            'msg'  => '',
            'data' => call_user_func_array($action, $args),
        ];

        header('Content-type: application/json');

        return json($res);
    });
});

// init interceptor

// init 404 handler
if_not_found(function () {
    return json([
        'code' => 0,
        'msg' => '404 not found',
        'data' => [],
    ]);
});

// init controller
include CONTROLLER_DIR.'/demo.php';
include CONTROLLER_DIR.'/base.php';

// fix
not_found();
