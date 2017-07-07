<?php

class order extends entity
{
    public $structs = [
        'customer_id' => '',
    ];

    public function __construct()
    {/*{{{*/
        $this->has_many('order_items', 'order_item');
        $this->belongs_to('customer');
    }/*}}}*/

    public static function get_system_code()
    {/*{{{*/
        return null;
    }/*}}}*/

    public static function create(customer $customer)
    {/*{{{*/
        $o = parent::init();
        $o->customer = $customer;

        return $o;
    }/*}}}*/
}
