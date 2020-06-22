<?php

define('MIGRATION_DIR', COMMAND_DIR.'/migration');
define('MIGRATION_TMP_DIR_NAME', 'tmp');
define('MIGRATION_TMP_DIR', MIGRATION_DIR.'/'.MIGRATION_TMP_DIR_NAME);
define('MIGRATION_TABLE', 'migrations');
define('MIGRATION_SQL_SHIFT_STRING', '    ');

function _migration_files()
{/*{{{*/
    $files = scandir(MIGRATION_DIR);

    return array_diff($files, ['.', '..', '.gitkeep', MIGRATION_TMP_DIR_NAME]);
}/*}}}*/

function _migration_tmp_files()
{/*{{{*/
    $files = scandir(MIGRATION_TMP_DIR);

    $files = array_diff($files, ['.', '..', '.gitkeep']);

    return array_map(function ($path) {
        return MIGRATION_TMP_DIR_NAME.'/'.$path;
    }, $files);
}/*}}}*/

function migration_file_path($name)
{/*{{{*/
    return MIGRATION_DIR.'/'.date('Y_m_d_H_i_s_').$name.'.sql';
}/*}}}*/

function migration_tmp_file_path($name)
{/*{{{*/
    return MIGRATION_TMP_DIR.'/'.date('Y_m_d_H_i_s_').$name.'.sql';
}/*}}}*/

function _migration_file_explode($filepath)
{/*{{{*/
    $ups = $downs = [];
    if (ends_with($filepath, '.sql')) {
        $content = file_get_contents($filepath);

        $downs_exploded = explode('# down', $content);
        $down_str = $downs_exploded[1];
        $downs = array_filter(explode(';', trim($down_str)));

        $ups_exploded = explode('# up', $downs_exploded[0]);
        $up_strs = $ups_exploded[1];
        $ups = array_filter(explode(';', trim($up_strs)));
    }

    return [$ups, $downs];
}/*}}}*/

function _migration_file_implode($ups, $downs, $filepath)
{/*{{{*/
    $string = "# up\n";

    if ($ups) {
        $string .= implode(";\n", $ups).";\n";
    } else {
        $string .= "这里写结构变更 SQL\n";
    }

    $string .= "\n# down\n";

    if ($downs) {
        $string .= implode(";\n", $downs).";\n";
    } else {
        $string .= "这里写回滚 SQL\n";
    }

    file_put_contents($filepath, $string);
}/*}}}*/

function _migration_run($files)
{/*{{{*/
    $old_migrations = db_query_column('migration', 'select * from '.MIGRATION_TABLE);
    $new_migrations = array_diff($files, $old_migrations);

    $last_batch = db_query_value('max_batch', 'select max(batch) max_batch from '.MIGRATION_TABLE);

    foreach ($new_migrations as $filename) {
        $filepath = MIGRATION_DIR.'/'.$filename;

        list($ups, $downs) = _migration_file_explode($filepath);

        foreach ($ups as $up) {
            db_structure($up);
        }

        db_insert('insert into '.MIGRATION_TABLE.' set migration = :migration, batch = :batch', [
            ':migration' => $filename,
            ':batch' => $last_batch + 1,
        ]);

        echo "migrate $filepath success up!\n";
    }
}/*}}}*/

function _migration_reset()
{/*{{{*/
    $migrations = db_query_column('migration', 'select migration from '.MIGRATION_TABLE.' order by id desc');

    foreach ($migrations as $filename) {

        $filepath = MIGRATION_DIR.'/'.$filename;

        if (is_file($filepath)) {

            list($ups, $downs) = _migration_file_explode($filepath);

            foreach ($downs as $down) {
                db_structure($down);
            }

            echo "migrate $filepath success down!\n";
        } else {

            echo "migrate $filepath failure down!\n";
        }
    }

    db_delete('delete from '.MIGRATION_TABLE);
}/*}}}*/

function _migration_db_detail()
{/*{{{*/
    $detail = [
        'table' => [],
        'field' => [],
        'index' => [],
    ];
    $tables = db_query('show table status');

    if ($tables) {
        foreach ($tables as $key_table => $table) {
            $table_name = $table['Name'];
            $detail['table'][$table_name] = $table;

            $fields = db_query("show full fields from `$table_name`");
            if ($fields) {
                $detail['field'][$table_name] = array_build($fields, function ($key, $value) {
                    return [
                        $value['Field'],
                        $value,
                    ];
                });
            } else {
                $detail['field'][$table_name] = [];
            }

            $indexes = db_query("show index from `$table_name`");
            if ($indexes) {
                $res_indexes = [];
                foreach ($indexes as $key_index => $index) {
                    if (! isset($indexes[$index['Key_name']])) {
                        $index['Column_name'] = [$index['Seq_in_index'] => $index['Column_name']];
                        $res_indexes[$index['Key_name']] = $index;
                    } else {
                        $res_indexes[$index['Key_name']]['Column_name'][$index['Seq_in_index']] = $index['Column_name'];
                    }
                }
                $detail['index'][$table_name] = $res_indexes;
            } else {
                $detail['index'][$table_name] = [];
            }
        }

        return $detail;
    } else {
        return false;
    }
}/*}}}*/

function _migration_detail_diff_to_sql($new, $old)
{/*{{{*/
    $sqls = [];

    $field_sql_string = function ($field, $table) {

        switch ($field['Null']) {
            case 'NO':
                $null = ' not null';
                break;
            case 'YES':
                $null = ' null';
                break;
            default:
                $null = '';
        }

        $collation = ($table['Collation'] == $field['Collation'] || $field['Collation'] === null)? '': (' character set '.(explode('_', $field['Collation'])[0]).' collate '.$field['Collation']);
        $default = ($field['Default'] === null)? '': (" default '".stripslashes($field['Default'])."'");
        $extra = ($field['Extra'] === '')? '': (' '.strtolower($field['Extra']));
        $comment = ($field['Comment'] === '')? '': (" comment '".stripslashes($field['Comment'])."'");

        return "`{$field['Field']}` ".strtolower($field['Type']).$collation.$null.$default.$extra.$comment;
    };

    foreach ($old['table'] as $table_name => $old_table) {

        // drop old table
        if (! isset($new['table'][$table_name])) {
            unset($old['field'][$table_name]);
            unset($old['index'][$table_name]);
            $sqls[] = "drop table `$table_name`";
        }
    }

    foreach ($new['table'] as $table_name => $new_table) {

        // create new table
        if (! isset($old['table'][$table_name])) {
            $sql_str = [];

            // fields sql
            foreach ($new['field'][$table_name] as $field) {

                $sql_str[] = $field_sql_string($field, $new_table);
            }

            // indexes sql
            foreach ($new['index'][$table_name] as $index_name => $index) {
                if ($index_name == 'PRIMARY') {
                    $sql_str[] = 'primary key (`'.implode('`,`', $index['Column_name']).'`)';
                } else {
                    $sql_str[] = ($index['Non_unique'] == 0? 'unique': 'index')."`$index_name`".' (`'.implode('`, `', $index['Column_name']).'`)';
                }
            }

            list($charset) = explode('_', $new_table['Collation']);

            $sqls[] = "create table if not exists `$table_name` (\n".MIGRATION_SQL_SHIFT_STRING.
                implode(",\n".MIGRATION_SQL_SHIFT_STRING, $sql_str)."\n".
                ') engine = '.strtolower($new_table['Engine']).' default charset = '.$charset;
        } else {
            // change table option
            $old_table = $old['table'][$table_name];

            $table_changes = [];

            if ($new_table['Engine'] !== $old_table['Engine']) {
                $table_changes['Engine'] = $new_table['Engine'];
            }
            if ($new_table['Row_format'] !== $old_table['Row_format']) {
                $table_changes['Row_format'] = $new_table['Row_format'];
            }
            if ($new_table['Collation'] !== $old_table['Collation']) {
                $table_changes['Collation'] = $new_table['Collation'];
            }
            if ($new_table['Comment'] !== $old_table['Comment']) {
                $table_changes['Comment'] = $new_table['Comment'];
            }

            if (! empty($table_changes)) {
                $sql = "alter table `$table_name`";
                foreach ($table_changes as $option => $value) {
                    if ($option == 'Collation') {
                        list($charset) = explode('_', $value);
                        $sql .= ' default character set '.strtolower($charset).' collate '.strtolower($value);
                    } else {
                        $sql .= ' '.strtolower($option).' = '.strtolower($value);
                    }
                }
                $sqls[] = $sql;
            }

            // change field
            foreach ($old['field'][$table_name] as $old_field_name => $old_field) {

                // drop old field
                if (! isset($new['field'][$table_name][$old_field_name])) {

                    $sqls[] = "alter table `$table_name` drop `$old_field_name`";
                } else {

                    // change field
                    $new_field = $new['field'][$table_name][$old_field_name];

                    if (
                        $new_field['Type'] !== $old_field['Type']
                        || (
                            (
                                $new_field['Collation'] !== $old_field['Collation']
                            )
                            || (
                                $new_field['Collation'] !== $new_table['Collation'] && not_null($new_field['Collation'])
                            )
                        )
                        || $new_field['Null'] !== $old_field['Null']
                        || $new_field['Default'] !== $old_field['Default']
                        || $new_field['Extra'] !== $old_field['Extra']
                        || $new_field['Comment'] !== $old_field['Comment']
                    ) {

                        $sqls[] = "alter table `$table_name` change `{$old_field_name}` ".$field_sql_string($new_field, $new_table);
                    }
                }
            }

            $last_field = '';
            foreach ($new['field'][$table_name] as $new_field_name => $new_field) {

                // add new field
                if (! isset($old['field'][$table_name][$new_field_name])) {

                    $after = $last_field? " after `$last_field`": ' first';

                    $sqls[] = "alter table `$table_name` add ".$field_sql_string($new_field, $new_table).$after;
                }

                $last_field = $new_field_name;
            }

            // change index
            foreach ($old['index'][$table_name] as $old_index_name => $old_index) {

                // drop old index
                if (! isset($new['index'][$table_name][$old_index_name])) {

                    if ($old_index_name == 'PRIMARY') {
                        $sqls[] = "alter table `$table_name` drop primary key";
                    } else {
                        $sqls[] = "alter table `$table_name` drop index `$old_index_name`";
                    }

                } else {

                    // change index
                    $new_index = $new['index'][$table_name][$old_index_name];

                    if (
                        $new_index['Non_unique'] !== $old_index['Non_unique']
                        || $new_index['Column_name'] !== $old_index['Column_name']
                        || $new_index['Collation'] !== $old_index['Collation']
                        || $new_index['Index_type'] !== $old_index['Index_type']
                    ) {
                        if ($old_index_name == 'PRIMARY') {
                            $sqls[] = "alter table `$table_name` drop primary key";
                            $sqls[] = "alter table `$table_name` add primary key (`".implode('`, `', $new_index['Column_name']).'`)';
                        } else {
                            $sqls[] = "alter table `$table_name` drop index `$old_index_name`";
                            $sqls[] = "alter table `$table_name` add ".($new_index['Non_unique'] == 0? 'unique': 'index')." `$old_index_name`".' (`'.implode('`, `', $new_index['Column_name']).'`)';
                        }
                    }
                }
            }

            foreach ($new['index'][$table_name] as $new_index_name => $new_index) {

                // add new index
                if (! isset($old['index'][$table_name][$new_index_name])) {

                    if ($new_index_name == 'PRIMARY') {
                        $sqls[] = "alter table `$table_name` add primary key (`".implode('`, `', $new_index['Column_name']).'`)';
                    } else {
                        $sqls[] = "alter table `$table_name` add ".($new_index['Non_unique'] == 0? 'unique': 'index')." `$new_index_name`".' (`'.implode('`, `', $new_index['Column_name']).'`)';
                    }
                }
            }
        }
    }

    return $sqls;
}/*}}}*/

command('migrate:install', '初始化 migrate 所需的表结构', function ()
{/*{{{*/
    db_structure(
        'create table if not exists `'.MIGRATION_TABLE.'` (
            `id` int(10) unsigned not null auto_increment,
            `migration` varchar(255) collate utf8_unicode_ci not null,
            `batch` int(11) not null,
            primary key (`id`)
        ) engine=innodb default charset=utf8 collate=utf8_unicode_ci');
});/*}}}*/

command('migrate:uninstall', '删除 migrate 所需的表结构', function ()
{/*{{{*/
    db_structure('drop table `'.MIGRATION_TABLE.'`');
});/*}}}*/

command('migrate', '执行 migrate', function ()
{/*{{{*/
    $is_tmp_files = command_paramater('tmp_files', false);

    $files = $is_tmp_files ? _migration_tmp_files(): _migration_files();

    _migration_run($files);
});/*}}}*/

command('migrate:dry-run', '展示将要跑的 sql', function ()
{/*{{{*/
    $is_tmp_files = command_paramater('tmp_files', false);

    $files = $is_tmp_files ? _migration_tmp_files(): _migration_files();

    $old_migrations = db_query_column('migration', 'select * from '.MIGRATION_TABLE);
    $new_migrations = array_diff($files, $old_migrations);

    $last_batch = db_query_value('max_batch', 'select max(batch) max_batch from '.MIGRATION_TABLE);

    foreach ($new_migrations as $filename) {
        list($ups, $downs) = _migration_file_explode(MIGRATION_DIR.'/'.$filename);

        echo '------------'.$filename."-----------\n";
        foreach ($ups as $up) {
            echo $up."\n";
        }
    }

});/*}}}*/

command('migrate:rollback', '回滚最后一次 migrate', function ()
{/*{{{*/
    $last_batch = db_query_value('max_batch', 'select max(batch) max_batch from '.MIGRATION_TABLE);
    $last_batch_migrations = db_query_column('migration', 'select migration from '.MIGRATION_TABLE.' where batch = :batch order by id desc', [
        ':batch' => $last_batch,
    ]);

    foreach ($last_batch_migrations as $filename) {
        $filepath = MIGRATION_DIR.'/'.$filename;

        if (is_file($filepath)) {

            list($ups, $downs) = _migration_file_explode($filepath);

            foreach ($downs as $down) {
                db_structure($down);
            }

            echo "migrate $filepath success down!\n";
        } else {

            echo "migrate $filepath failure down!\n";
        }
    }

    db_delete('delete from '.MIGRATION_TABLE.' where batch = :batch', [
        ':batch' => $last_batch,
    ]);
});/*}}}*/

command('migrate:make', '新建 migration', function ()
{/*{{{*/
    $name = command_paramater('name');

    $new_db_detail = _migration_db_detail();

    _migration_reset();
    $tables = db_query('show table status');
    if ($tables) {
        foreach ($tables as $key_table => $table) {
            $table_name = $table['Name'];
            if ($table_name !== MIGRATION_TABLE) {
                db_structure("drop table `$table_name`");
            }
        }
    }

    _migration_run(_migration_files());
    $old_db_detail = _migration_db_detail();

    $up_sqls = _migration_detail_diff_to_sql($new_db_detail, $old_db_detail);
    $down_sqls = _migration_detail_diff_to_sql($old_db_detail, $new_db_detail);

    if ($up_sqls && $down_sqls) {

        $file = migration_file_path($name);
        _migration_file_implode($up_sqls, $down_sqls, $file);
        echo "generate $file success!\n";

        _migration_run(_migration_files());
    } else {
        echo "\033[31mno different!\n\033[0m";

        _migration_run(_migration_files());
    }
});/*}}}*/

command('migrate:reset', '回滚所有 migrate', function ()
{/*{{{*/
    _migration_reset();
});/*}}}*/

command('migrate:generate-diff', '生成 tmp migration 与正式 migration 的差别变更', function ()
{/*{{{*/
    _migration_reset();
    _migration_run(_migration_files());
    $old_db_detail = _migration_db_detail();

    _migration_reset();
    _migration_run(_migration_tmp_files());
    $new_db_detail = _migration_db_detail();

    $up_sqls = _migration_detail_diff_to_sql($new_db_detail, $old_db_detail);
    $down_sqls = _migration_detail_diff_to_sql($old_db_detail, $new_db_detail);

    if ($up_sqls && $down_sqls) {

        $file = migration_file_path('diff_generated');
        _migration_file_implode($up_sqls, $down_sqls, $file);
        echo "generate $file success!\n";

        _migration_reset();
        _migration_run(_migration_files());
    } else {
        echo "\033[31mno different!\n\033[0m";

        _migration_reset();
        _migration_run(_migration_files());
    }
});/*}}}*/
