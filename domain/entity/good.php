<?php

class good extends entity
{
    /* generated code start */
    public $structs = [
        'name' => '',
    ];

    public static $entity_display_name = '商品';
    public static $entity_description = '商品';

    public static $struct_data_types = [
        'name' => 'string',
    ];

    public static $struct_display_names = [
        'name' => '名称',
    ];

    public static $struct_descriptions = [
        'name' => '名称',
    ];

    public static $struct_is_required = [
        'name' => true,
    ];

    public function __construct()
    {/*{{{*/
    }/*}}}*/

    public static function create($name)
    {/*{{{*/
        $good = parent::init();

        $good->name = $name;

        return $good;
    }/*}}}*/

    public static function struct_formaters($property)
    {/*{{{*/
        $formaters = [
            'name' => [
                [
                    'function' => function ($value) {
                        return mb_strlen($value) <= 30;
                    },
                    'failed_message' => '不能超过 30 字',
                ],
            ],
        ];

        return $formaters[$property] ?? false;
    }/*}}}*/
    /* generated code end */
}
