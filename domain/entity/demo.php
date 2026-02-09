<?php

class demo extends entity
{
    /* generated code start */
    public $structs = [
        'name' => '',
        'note' => '',
    ];

    public static $struct_data_types = [
        'name' => 'string',
        'note' => 'string',
    ];

    public static $struct_display_names = [
        'name' => '名称',
        'note' => '备注',
    ];


    public static $struct_is_required = [
        'name' => true,
        'note' => false,
    ];

    public function __construct()
    {/*{{{*/
    }/*}}}*/

    public static function create($name): demo    {/*{{{*/
        $demo = parent::init();

        $demo->name = $name;

        return $demo;
    }/*}}}*/

    public static function struct_validators($property)
    {/*{{{*/
        $validators = [
        ];

        return $validators[$property] ?? false;
    }/*}}}*/
    /* generated code end */
}
