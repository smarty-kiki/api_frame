<?php

class customer extends entity
{
    public $original = [
        'name' => '',
    ];

    public function __construct()
    {/*{{{*/
    }/*}}}*/

    public static function get_system_code()
    {/*{{{*/
        return null;
    }/*}}}*/

    public static function create($name)
    {/*{{{*/
        $c = parent::init();
        $c->name = $name;

        return $c;
    }/*}}}*/
}
