<?php

class good extends entity
{
    public $original = [
        'price' => '',
    ];

    public function __construct()
    {/*{{{*/
    }/*}}}*/

    public static function get_system_code()
    {/*{{{*/
        return null;
    }/*}}}*/

    public static function create($price)
    {/*{{{*/
        $c = parent::init();
        $c->price = $price;

        return $c;
    }/*}}}*/
}
