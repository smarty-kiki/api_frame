<?php

include __DIR__.'/../bootstrap.php';

class frame_function_test extends phpunit_framework_testcase
{
    public function test_array_get()
    {/*{{{*/
        // 单层 key 获取

        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
            '' => 0,
        ];

        $this->assertEquals('A', array_get($from_array, 'a'));
        $this->assertTrue(2 === array_get($from_array, 'b'));
        $this->assertEquals(null, array_get($from_array, 'c', 'C'));
        $this->assertEquals('D', array_get($from_array, 'd', 'D'));
        $this->assertEquals('D', array_get($from_array, 'd', function () {
            return 'D';
        }));
        $this->assertEquals(0, array_get($from_array, '', 'D'));
        $this->assertEquals('D', array_get($from_array, null, 'D'));
        $this->assertEquals([
            'a' => 'D',
            'b' => 'D',
            'c' => 'D',
            ''  => 'D',
        ], array_get($from_array, '*', 'D'));

        // 多层 key 获取

        $from_array = [
            'a' => [
                'a' => 'A',
                'b' => 2,
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
            '' => [
                '' => [
                    '' => 0,
                ],
            ]
        ];

        $this->assertEquals('A', array_get($from_array, 'a.a'));
        $this->assertEquals(['b' => 2], array_get($from_array, 'b'));
        $this->assertTrue(2 === array_get($from_array, 'b.b'));
        $this->assertEquals(null, array_get($from_array, 'c.c', 'C'));
        $this->assertEquals('D', array_get($from_array, 'd.d', 'D'));
        $this->assertEquals('D', array_get($from_array, null, 'D'));
        $this->assertEquals(0, array_get($from_array, '..', 'D'));
        $this->assertEquals([
            'a' => 'A',
            'b' => 'D',
            'c' => 'D',
            ''  => 'D',
        ], array_get($from_array, '*.a', 'D'));
        $this->assertEquals([
            'a' => [
                'a' => 'D',
                'b' => 'D',
            ],
            'b' => [
                'b' => 'D',
            ],
            'c' => [
                'c' => 'D',
            ],
            ''  => [
                '' => 'D',
            ],
        ], array_get($from_array, '*.*.a', 'D'));
    }/*}}}*/

    public function test_array_set()
    {/*{{{*/
        // 单层 key 设置

        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ];


        $to_array = array_set($from_array, 'b', 'b');
        $this->assertEquals([
            'a' => 'A',
            'b' => 'b',
            'c' => null,
        ], $to_array);
        $this->assertEquals([
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ], $from_array);


        $to_array = array_set($from_array, 'd', 'd');
        $this->assertEquals([
            'a' => 'A',
            'b' => 2,
            'c' => null,
            'd' => 'd',
        ], $to_array);
        $this->assertEquals([
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ], $from_array);


        $to_array = array_set($from_array, 0, 'z');
        $this->assertEquals([
            0 => 'z',
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ], $to_array);
        $this->assertEquals([
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ], $from_array);


        // 多层 key 设置

        $from_array = [
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ];


        $to_array = array_set($from_array, 'a.a', 'AA');
        $this->assertEquals([
            'a' => [
                'a' => 'AA',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $to_array);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $from_array);


        $to_array = array_set($from_array, 'b', 'b');
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => 'b',
            'c' => [
                'c' => null,
            ],
        ], $to_array);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $from_array);


        $to_array = array_set($from_array, 'c.c', null);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $to_array);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $from_array);


        $to_array = array_set($from_array, 'd.d', 'D');
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
            'd' => [
                'd' => 'D',
            ],
        ], $to_array);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $from_array);


        $to_array = array_set($from_array, 0, 0);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
            0 => 0,
        ], $to_array);
        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ], $from_array);

    }/*}}}*/

    public function test_array_exists()
    {/*{{{*/
        // 单层 key 判断

        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ];

        $this->assertTrue(array_exists($from_array, 'a'));
        $this->assertTrue(array_exists($from_array, 'c'));
        $this->assertFalse(array_exists($from_array, 'd'));


        // 多层 key 判断

        $from_array = [
            'a' => [
                'a' => 'A',
            ],
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
        ];


        $this->assertTrue(array_exists($from_array, 'a.a'));
        $this->assertFalse(array_exists($from_array, 'b.c'));
        $this->assertTrue(array_exists($from_array, 'c'));
        $this->assertFalse(array_exists($from_array, 'd'));
    }/*}}}*/

    public function test_array_forget()
    {/*{{{*/
        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ];

        array_forget($from_array, 'a');

        $this->assertFalse(array_key_exists('a', $from_array));
        $this->assertTrue(array_key_exists('b', $from_array));
        $this->assertTrue(array_key_exists('c', $from_array));


        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ];

        array_forget($from_array, 'b');

        $this->assertTrue(array_key_exists('a', $from_array));
        $this->assertFalse(array_key_exists('b', $from_array));
        $this->assertTrue(array_key_exists('c', $from_array));

        $from_array = [
            'a' => 'A',
            'b' => 2,
            'c' => null,
        ];

        array_forget($from_array, 'c');

        $this->assertTrue(array_key_exists('a', $from_array));
        $this->assertTrue(array_key_exists('b', $from_array));
        $this->assertFalse(array_key_exists('c', $from_array));



    }/*}}}*/

    public function test_array_divide()
    {/*{{{*/
        $from_array = [
            'a' => 'A',
            2 => 3,
            '' => null,
        ];

        $to_array = array_divide($from_array);
        $this->assertEquals([
            ['a', 2, ''],
            ['A', 3, null],
        ], $to_array);

    }/*}}}*/

    public function test_array_build()
    {/*{{{*/
        $from_array = [
            'a.A',
            '2.3',
            '.',
        ];

        $this->assertEquals([
             'a' => 'A',
             2 => '3',
             '' => '',
        ], array_build($from_array, function ($key, $value) {
            return explode('.', $value);
        }));
    }/*}}}*/

    public function test_array_indexed()
    {/*{{{*/
        $from_array = [
            'a.A',
            '2.3',
            '.',
        ];

        $this->assertEquals([
            'a' => ['A'],
            2 => ['3'],
            '' => [''],
        ], array_indexed($from_array, function ($key, $value) {
            $tmp = explode('.', $value);

            return [$tmp[0], null, $tmp[1]];
        }));

        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            2 => [
                2 => '3',
            ],
            '' => [
                '' => '',
            ],
        ], array_indexed($from_array, function ($key, $value) {
            $tmp = explode('.', $value);

            return [$tmp[0], $tmp[0], $tmp[1]];
        }));
    }/*}}}*/

    public function test_array_list()
    {/*{{{*/
        $from_array = [
            'a' => 'A',
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
            '' => [
                '' => [
                    '' => 0,
                ],
            ]
        ];

        $to_array = array_list($from_array, ['a', 'c.c', '..', 'd']);

        $this->assertEquals(['A', null, 0, null], $to_array);
    }/*}}}*/

    public function test_array_transfer()
    {/*{{{*/
        $from_array = [
            'a' => 'A',
            'b' => [
                'b' => 2,
            ],
            'c' => [
                'c' => null,
            ],
            '' => [
                '' => [
                    '' => 0,
                ],
            ]
        ];

        $to_array = array_transfer($from_array, [
            'a' => 'a.a',
            'c.c' => 'c.c',
            '..' => '.',
            'd'  => 'd.d',
        ]);

        $this->assertEquals([
            'a' => [
                'a' => 'A',
            ],
            'c' => [
                'c' => null,
            ],
            '' => [
                '' => 0,
            ],
            'd' => [
                'd' => null,
            ],
        ], $to_array);
    }/*}}}*/

    public function test_str_cut()
    {/*{{{*/
        $this->assertEquals('hello', str_tail_cut('hello', 6, '...'));
        $this->assertEquals('hello', str_tail_cut('hello', 5, '...'));
        $this->assertEquals('h...', str_tail_cut('hello', 4, '...'));
        $this->assertEquals('he..', str_tail_cut('hello', 4, '..'));

        $this->assertEquals('hello', str_head_cut('hello', 6, '...'));
        $this->assertEquals('hello', str_head_cut('hello', 5, '...'));
        $this->assertEquals('...o', str_head_cut('hello', 4, '...'));
        $this->assertEquals('..lo', str_head_cut('hello', 4, '..'));
        $this->assertEquals('..lo!', str_head_cut('hello!', 5, '..'));

        $this->assertEquals('hello', str_middle_cut('hello', 6, '...'));
        $this->assertEquals('hello', str_middle_cut('hello', 5, '...'));
        $this->assertEquals('...o', str_middle_cut('hello', 4, '...'));
        $this->assertEquals('h..o', str_middle_cut('hello', 4, '..'));
        $this->assertEquals('h..o!', str_middle_cut('hello!', 5, '..'));
    }/*}}}*/

    public function test_value()
    {/*{{{*/
        $a = 'a';

        $this->assertEquals('a', value($a));

        $b = function () {
            return 'b';
        };

        $this->assertEquals('b', value($b));
    }/*}}}*/

    public function test_str_judge()
    {/*{{{*/
        $this->assertTrue(starts_with('hello!', 'he'));
        $this->assertFalse(starts_with('hello!', ''));
        $this->assertFalse(starts_with('hello!', null));
        $this->assertFalse(starts_with('hello!', 'e'));

        $this->assertTrue(ends_with('hello!', 'o!'));
        $this->assertFalse(ends_with('hello!', ''));
        $this->assertFalse(ends_with('hello!', null));
        $this->assertFalse(ends_with('hello!', 'o'));
    }/*}}}*/

    public function test_str_finish()
    {/*{{{*/
        $this->assertEquals('hey! man!', str_finish('hey!', ' man!'));
        $this->assertEquals('hey! man!', str_finish('hey! man!', ' man!'));
    }/*}}}*/

    public function test_is_url()
    {/*{{{*/
        $this->assertTrue(is_url('#test'));
        $this->assertTrue(is_url('//php-frame.cn/'));
        $this->assertTrue(is_url('http://php-frame.cn'));
        $this->assertTrue(is_url('mailto://kiki@smarty.so'));
        $this->assertTrue(is_url('tel://15012345678'));
        $this->assertTrue(is_url('https://php-frame.cn'));
        $this->assertTrue(is_url('sftp://php-frame.cn'));
    }/*}}}*/

    public function test_unparse_url()
    {/*{{{*/
        $this->assertEquals(
            '//www.baidu.com',
            unparse_url([
                'host' => 'www.baidu.com',
            ]));

        $this->assertEquals(
            '//www.baidu.com?name=kiki&age=18',
            unparse_url([
                'host' => 'www.baidu.com',
                'query' => 'name=kiki&age=18',
            ]));

        $this->assertEquals(
            '//www.baidu.com?name=kiki&age=18#haha',
            unparse_url([
                'host' => 'www.baidu.com',
                'query' => 'name=kiki&age=18',
                'fragment' => 'haha',
            ]));

        $this->assertEquals(
            '//www.baidu.com?name=kiki&age=18#haha',
            unparse_url([
                'host' => 'www.baidu.com',
                'query' => 'name=kiki&age=18',
                'fragment' => 'haha',
            ]));

        $this->assertEquals(
            'http://www.baidu.com?name=kiki&age=18',
            unparse_url([
                'host' => 'www.baidu.com',
                'query' => 'name=kiki&age=18',
                'scheme' => 'http',
            ]));

        $this->assertEquals(
            'http://www.baidu.com/action?name=kiki&age=18',
            unparse_url([
                'host' => 'www.baidu.com',
                'path' => '/action',
                'query' => 'name=kiki&age=18',
                'scheme' => 'http',
            ]));
    }/*}}}*/

    public function test_url_transfer()
    {/*{{{*/
        $this->assertEquals(
            'http://www.baidu.com/action?name=kiki&age=19',
            url_transfer('http://www.baidu.com/action?name=kiki&age=18', function ($url_info) {

                $url_info['query']['age'] = 19;

                return $url_info;
            })
        );
    }/*}}}*/

    public function test_config_dir()
    {/*{{{*/
        $this->assertEquals([
            '/var/www/api_frame/config',
        ], config_dir());
    }/*}}}*/

    public function test_env()
    {/*{{{*/
        $this->assertEquals('production', env());

        $this->assertTrue(is_env('production'));
        $this->assertFalse(is_env('development'));

        $_SERVER['ENV'] = 'development';

        $this->assertEquals('development', env());

        $this->assertTrue(is_env('development'));
        $this->assertFalse(is_env('production'));
    }/*}}}*/

    public function test_not_empty()
    {/*{{{*/
        $this->assertFalse(not_empty(''));
        $this->assertFalse(not_empty(0));
        $this->assertFalse(not_empty(null));
        $this->assertFalse(not_empty([]));
        $this->assertFalse(not_empty(false));

        $this->assertTrue(not_empty('a'));
        $this->assertTrue(not_empty(1));
        $this->assertTrue(not_empty(['a']));
        $this->assertTrue(not_empty(true));
    }/*}}}*/

    public function test_not_null()
    {/*{{{*/
        $this->assertTrue(not_null('a'));
        $this->assertTrue(not_null(''));
        $this->assertTrue(not_null(1));
        $this->assertTrue(not_null(0));
        $this->assertTrue(not_null([]));
        $this->assertTrue(not_null(['a']));
        $this->assertTrue(not_null(true));
        $this->assertTrue(not_null(false));
        $this->assertFalse(not_null(null));
    }/*}}}*/

    public function test_all_empty()
    {/*{{{*/
        $this->assertTrue(all_empty('', 0, false, [], null));
        $this->assertFalse(all_empty('', 1, false, [], null));
        $this->assertFalse(all_empty('a', 1, true, ['a']));
    }/*}}}*/

    public function test_all_null()
    {/*{{{*/
        $this->assertFalse(all_null('', 0, false, [], null));
        $this->assertFalse(all_null('', 1, false, [], null));
        $this->assertFalse(all_null('a', 1, true, ['a']));
        $this->assertTrue(all_null(null));
        $this->assertTrue(all_null(null, null));
        $this->assertFalse(all_null(null, null, 'a'));
    }/*}}}*/

    public function test_all_not_empty()
    {/*{{{*/
        $this->assertFalse(all_not_empty('', 0, false, [], null));
        $this->assertFalse(all_not_empty('', 1, false, [], null));
        $this->assertTrue(all_not_empty('a', 1, true, ['a']));
    }/*}}}*/

    public function test_all_not_null()
    {/*{{{*/
        $this->assertFalse(all_not_null('', 0, false, [], null));
        $this->assertFalse(all_not_null('', 1, false, [], null));
        $this->assertTrue(all_not_null('a', 1, true, ['a']));
        $this->assertFalse(all_not_null(null));
        $this->assertFalse(all_not_null(null, null));
        $this->assertFalse(all_not_null(null, null, ''));
    }/*}}}*/

    public function test_has_empty()
    {/*{{{*/
        $this->assertTrue(has_empty('', 0, false, [], null));
        $this->assertTrue(has_empty('', 1, false, [], null));
        $this->assertFalse(has_empty('a', 1, true, ['a']));
    }/*}}}*/

    public function test_has_null()
    {/*{{{*/
        $this->assertTrue(has_null('', 0, false, [], null));
        $this->assertTrue(has_null('', 1, false, [], null));
        $this->assertFalse(has_null('a', 1, true, ['a']));
        $this->assertFalse(has_null('', 0, false, []));
        $this->assertTrue(has_null(null));
        $this->assertTrue(has_null(null, ''));
    }/*}}}*/

    public function test_datetime()
    {/*{{{*/
        $this->assertEquals('2019-01-07 19:48:23', datetime('2019-01-07 19:48:23'));
        $this->assertEquals('2019-01-07 00:00:00', datetime('2019-01-07'));
        $this->assertEquals('2019-01-08 00:00:00', datetime('2019-01-08'));
        $this->assertEquals('2019-01-08 00:00:00', datetime('2019-01-07 +1 days'));
        $this->assertEquals('2019-02-08 00:00:00', datetime('2019-01-07 +1 days + 1 months'));
        $this->assertEquals('2019-01-11 00:00:00', datetime('2019-01-07 friday'));
        $this->assertEquals('2019-01-11 00:00:00', datetime('2019-01-11 friday'));
        $this->assertEquals('2019-01-18 00:00:00', datetime('2019-01-11 next friday'));
        $this->assertEquals('2019-01', datetime('2019-01-07 19:48:23', 'Y-m'));
    }/*}}}*/

    public function test_datetime_diff()
    {/*{{{*/
        $time1 = datetime('2019-01-07 19:48:23');
        $time2 = datetime('2019-01-08 19:48:23');

        $this->assertEquals(86400, datetime_diff($time1, $time2));
        $this->assertEquals(1440, datetime_diff($time1, $time2, '%tm'));
        $this->assertEquals(24, datetime_diff($time1, $time2, '%th'));
        $this->assertEquals(1, datetime_diff($time1, $time2, '%td'));
        $this->assertEquals('1 0 0 0 1 24 1440 86400', datetime_diff($time1, $time2, '%d %h %m %s %td %th %tm %ts'));
        $this->assertEquals('total 86400 seconds!', datetime_diff($time1, $time2, 'total %ts seconds!'));
    }/*}}}*/

    public function test_remote()
    {/*{{{*/
        $this->assertEquals('"hello world"', remote_get('http://127.0.0.1/'));
        $this->assertEquals('hello world', remote_get_json('http://127.0.0.1/'));
    }/*}}}*/

    public function test_instance()
    {/*{{{*/
        $obj = instance('stdClass');

        $this->assertFalse(property_exists($obj, 'name'));

        $obj->name = 'kiki';

        $this->assertTrue(property_exists($obj, 'name'));

        $obj2 = instance('stdClass');

        $this->assertTrue(property_exists($obj2, 'name'));
    }/*}}}*/

    public function test_json()
    {/*{{{*/
        $this->assertEquals('{"a":"a","b":2,"":null}', json([
            'a' => 'a',
            'b' => 2,
            '' => null,
        ]));
    }/*}}}*/

    public function test_option()
    {/*{{{*/
        option_define(
            'OPTION_TEST_1',
            'OPTION_TEST_2',
            'OPTION_TEST_3',
            'OPTION_TEST_4',
            'OPTION_TEST_5'
        );

        $option = OPTION_TEST_1 | OPTION_TEST_3;

        $this->assertTrue(has_option($option, OPTION_TEST_1));
        $this->assertFalse(has_option($option, OPTION_TEST_2));
        $this->assertTrue(has_option($option, OPTION_TEST_3));
        $this->assertFalse(has_option($option, OPTION_TEST_4));
        $this->assertFalse(has_option($option, OPTION_TEST_5));
    }/*}}}*/
}
