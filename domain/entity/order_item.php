<?php

class order_item extends entity
{
    public $original = [
        'order_id' => '',
        'good_id' => '',
    ];

    public function __construct()
    {/*{{{*/
        $this->belongs_to('order');
        $this->belongs_to('good');
    }/*}}}*/

    public static function get_system_code()
    {/*{{{*/
        return null;
    }/*}}}*/

    public static function create(order $order, good $good)
    {/*{{{*/
        $c = parent::init();
        $c->order = $order;
        $c->good = $good;

        return $c;
    }/*}}}*/
}
