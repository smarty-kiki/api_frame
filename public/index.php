<?php

// init
include __DIR__.'/../bootstrap.php';
include FRAME_DIR.'/micro_api.php';

set_error_handler('api_err_action', E_ALL);
set_exception_handler('api_ex_action');
register_shutdown_function('api_fatel_err_action');

if_has_exception(function ($ex) {
    return json([
        'succ' => false,
        'msg' => $ex->getMessage(),
    ]);
});

if_verify(function ($action, $args) {
    return unit_of_work(function () use ($action, $args){

        $data = call_user_func_array($action, $args);

        header('Content-type: application/json');

        return json($data);
    });
});

// init interceptor

// init 404 handler
if_not_found(function () {
    return json([
        'succ' => false,
        'msg' => '404 not found',
    ]);
});

// init controller
include CONTROLLER_DIR.'/index.php';

// fix
not_found();
