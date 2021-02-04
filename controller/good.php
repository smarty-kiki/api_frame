<?php

if_get('/goods', function ()
{/*{{{*/
    list(
        $inputs['name']
    ) = input_list(
        'name'
    );

    $inputs = array_filter($inputs, 'not_null');

    $goods = dao('good')->find_all_by_column($inputs);

    return [
        'code' => 0,
        'msg'  => '',
        'count' => count($goods),
        'data' => array_build($goods, function ($id, $good) {
            return [
                null,
                [
                    'id' => $good->id,
                    'name' => $good->name,
                    'create_time' => $good->create_time,
                    'update_time' => $good->update_time,
                ]
            ];
        }),
    ];
});/*}}}*/

if_post('/goods/add', function ()
{/*{{{*/
    $name = input('name');


    $good = good::create(
        $name
    );

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/

//todo::detail

if_post('/goods/update/*', function ($good_id)
{/*{{{*/
    $name = input('name');

    $good = dao('good')->find($good_id);
    otherwise($good->is_not_null(), 'good 不存在');


    $good->name = $name;

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/

if_post('/goods/delete/*', function ($good_id)
{/*{{{*/
    $good = dao('good')->find($good_id);
    otherwise($good->is_not_null(), 'good 不存在');

    $good->delete();

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/
