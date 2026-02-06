<?php

if_post('/demos/add', function ()
{/*{{{*/
    $name = input('name');
    otherwise_error_code('DEMO_REQUIRE_NAME', not_null($name));

    list($note) = input_list('note');

    $new_demo = demo::create($name);

    if (not_null($note)) {
        $new_demo->note = $note;
    }

    queue_push('demo', [
        'demo_id' => $new_demo->id,
        'name' => $new_demo->name,
    ]);

    return [
        'code' => 0,
        'msg' => '',
        'data' => [
            'id' => $new_demo->id,
        ],
    ];
});/*}}}*/

if_get('/demos', function ()
{/*{{{*/
    list($inputs['name'], $inputs['note']) = input_list('name', 'note');

    $inputs = array_filter($inputs, 'not_null');

    $demos = dao('demo')->find_all_by_column($inputs);

    return [
        'code' => 0,
        'msg'  => '',
        'count' => count($demos),
        'demos' => array_build($demos, function ($id, $demo) {
            return [
                null,
                [
                    'id' => $demo->id,
                    'name' => $demo->name,
                    'note' => $demo->note,
                    'create_time' => $demo->create_time,
                    'update_time' => $demo->update_time,
                ]
            ];
        }),
    ];
});/*}}}*/

if_get('/demos/detail/*', function ($demo_id)
{/*{{{*/
    $demo = dao('demo')->find($demo_id);
    otherwise_error_code('DEMO_NOT_FOUND', $demo->is_not_null());

    return [
        'code' => 0,
        'msg' => '',
        'data' => [
            'id' => $demo->id,
            'name' => $demo->name,
            'note' => $demo->note,
            'create_time' => $demo->create_time,
            'update_time' => $demo->update_time,
        ],
    ];
});/*}}}*/

if_post('/demos/update/*', function ($demo_id)
{/*{{{*/
    list($name, $note) = input_list('name', 'note');

    $demo = dao('demo')->find($demo_id);
    otherwise_error_code('DEMO_NOT_FOUND', $demo->is_not_null());

    if (not_null($name)) { $demo->name = $name; }
    if (not_null($note)) { $demo->note = $note; }

    return [
        'code' => 0,
        'msg'  => '',
        'data' => [],
    ];
});/*}}}*/

if_post('/demos/delete/*', function ($demo_id)
{/*{{{*/
    $demo = dao('demo')->find($demo_id);
    otherwise_error_code('DEMO_NOT_FOUND', $demo->is_not_null());

    $demo->delete();

    return [
        'code' => 0,
        'msg' => '',
        'data' => [],
    ];
});/*}}}*/
