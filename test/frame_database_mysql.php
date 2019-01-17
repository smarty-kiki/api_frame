<?php

include __DIR__.'/../bootstrap.php';

class frame_database_mysql_test extends phpunit_framework_testcase
{
    public function setUp()
    {/*{{{*/
        db_structure('
            CREATE TABLE IF NOT EXISTS `test_table` (
                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `test_col1` VARCHAR(255) NULL,
                `test_col2` VARCHAR(500) NULL,
                PRIMARY KEY (`id`)
            ) engine=InnoDB default charset=utf8');
    }/*}}}*/

    public function tearDown()
    {/*{{{*/
        db_structure('DROP TABLE IF EXISTS `test_table`');
    }/*}}}*/

    public function test_db_crud()
    {/*{{{*/
        $cols = db_query('select * from `test_table`');
        $this->assertEquals([], $cols);

        $id = db_insert("insert into `test_table` (`test_col1`, `test_col2`) values ('test_value1', 'test_value2')");
        $this->assertEquals(1, $id);

        $cols = db_query('select * from `test_table`');
        $this->assertEquals([
            ['id' => 1, 'test_col1' => 'test_value1', 'test_col2' => 'test_value2']
        ], $cols);

        $row_count = db_update("update `test_table` set `test_col1` = 'test_value11', `test_col2` = 'test_value21' where id = :id", [':id' => $id]);
        $this->assertEquals(1, $row_count);

        $cols = db_query('select * from `test_table`');
        $this->assertEquals([
            ['id' => 1, 'test_col1' => 'test_value11', 'test_col2' => 'test_value21']
        ], $cols);

        $col = db_query_first('select * from `test_table`');
        $this->assertEquals(['id' => 1, 'test_col1' => 'test_value11', 'test_col2' => 'test_value21'], $col);

        $row_count = db_delete('delete from `test_table` where id = :id', [':id' => $id]);
        $this->assertEquals(1, $row_count);

        $cols = db_query('select * from `test_table`');
        $this->assertEquals([], $cols);
    }/*}}}*/

    public function test_db_close()
    {/*{{{*/
        $row_count = db_write('set @test="hello world"');
        $this->assertEquals(0, $row_count);

        $res = db_query_value('@test', 'select @test');
        $this->assertEquals('hello world', $res);

        db_close();

        $res = db_query_value('@test', 'select @test');
        $this->assertEquals(null, $res);
    }/*}}}*/

    public function test_db_simple_crud()
    {/*{{{*/
        $cols = db_simple_query('test_table', []);
        $this->assertEquals([], $cols);

        $id = db_simple_insert('test_table', [
            'test_col1' => 'test_value1',
            'test_col2' => 'test_value2',
        ]);
        $this->assertEquals(1, $id);

        $cols = db_simple_query('test_table', ['test_col1' => 'test_value1']);
        $this->assertEquals([
            ['id' => 1, 'test_col1' => 'test_value1', 'test_col2' => 'test_value2']
        ], $cols);

        $row_count = db_simple_update('test_table', 
            ['id' => $id],
            [
                'test_col1' => 'test_value11',
                'test_col2' => 'test_value21',
            ]);
        $this->assertEquals(1, $row_count);

        $cols = db_simple_query('test_table', []);
        $this->assertEquals([
            ['id' => 1, 'test_col1' => 'test_value11', 'test_col2' => 'test_value21']
        ], $cols);

        $col = db_simple_query_first('test_table', []);
        $this->assertEquals(['id' => 1, 'test_col1' => 'test_value11', 'test_col2' => 'test_value21'], $col);

        $row_count = db_simple_delete('test_table', ['id' => $id]);
        $this->assertEquals(1, $row_count);

        $cols = db_simple_query('test_table', []);
        $this->assertEquals([], $cols);
    }/*}}}*/

    public function test_db_transaction()
    {/*{{{*/
        $cols = db_simple_query('test_table', []);
        $this->assertEquals([], $cols);

        try {
            $res = [];

            db_transaction(function () use (&$res) {

                $id = db_simple_insert('test_table', [
                    'test_col1' => 'test_value1',
                    'test_col2' => 'test_value2',
                ]);

                $res = db_simple_query('test_table', ['test_col1' => 'test_value1']);

                throw new Exception();

            });

        } catch (Exception $ex) {

            $this->assertEquals([
                ['id' => 1, 'test_col1' => 'test_value1', 'test_col2' => 'test_value2']
            ], $res);

            $cols = db_simple_query('test_table', []);

            $this->assertEquals([], $cols);
        }
    }/*}}}*/
}
