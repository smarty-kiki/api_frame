<?php

define('MIGRATION_DIR', COMMAND_DIR.'/migration');
define('MIGRATION_TABLE', 'migrations');

function migration_file_path($name)
{/*{{{*/
    return MIGRATION_DIR.'/'.date('Y_m_d_H_i_s_').$name.'.sql';
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

command('migrate:install', '初始化 migrate 所需的表结构', function ()
{/*{{{*/
    db_structure(
        'CREATE TABLE IF NOT EXISTS `'.MIGRATION_TABLE.'` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `batch` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
});/*}}}*/

command('migrate:uninstall', '删除 migrate 所需的表结构', function ()
{/*{{{*/
    db_structure('DROP TABLE `'.MIGRATION_TABLE.'`');
});/*}}}*/

command('migrate', '执行 migrate', function ()
{/*{{{*/
    $files = scandir(MIGRATION_DIR);
    $old_migrations = array_merge(['.', '..', '.gitkeep'], db_query_column('migration', 'select * from '.MIGRATION_TABLE));
    $new_migrations = array_diff($files, $old_migrations);

    $last_batch = db_query_value('max_batch', 'select max(batch) max_batch from '.MIGRATION_TABLE);

    foreach ($new_migrations as $filename) {
        list($ups, $downs) = _migration_file_explode(MIGRATION_DIR.'/'.$filename);

        foreach ($ups as $up) {
            db_structure($up);
        }

        db_insert('insert into '.MIGRATION_TABLE.' set migration = :migration, batch = :batch', [
            ':migration' => $filename,
            ':batch' => $last_batch + 1,
        ]);

        echo $filename." up!\n";
    }
});/*}}}*/

command('migrate:dry-run', '展示将要跑的 sql', function ()
{/*{{{*/
    $files = scandir(MIGRATION_DIR);
    $old_migrations = array_merge(['.', '..', '.gitkeep'], db_query_column('migration', 'select * from '.MIGRATION_TABLE));
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
        list($ups, $downs) = _migration_file_explode(MIGRATION_DIR.'/'.$filename);

        foreach ($downs as $down) {
            db_structure($down);
        }

        db_delete('delete from '.MIGRATION_TABLE.' where batch = :batch', [
            ':batch' => $last_batch,
        ]);

        echo "$filename down!\n";
    }
});/*}}}*/

command('migrate:make', '新建 migration', function ()
{/*{{{*/
    $name = command_paramater('name');

    $file = migration_file_path($name);
    error_log("# up\n这里写结构变更 SQL\n\n# down\n这里写回滚 SQL", 3, $file);
    echo "generate $file success!\n";
});/*}}}*/

command('migrate:reset', '回滚所有 migrate', function ()
{/*{{{*/
    $migrations = db_query_column('migration', 'select migration from '.MIGRATION_TABLE.' order by id desc');

    foreach ($migrations as $filename) {
        list($ups, $downs) = _migration_file_explode(MIGRATION_DIR.'/'.$filename);

        foreach ($downs as $down) {
            db_structure($down);
        }

        db_delete('delete from '.MIGRATION_TABLE);

        echo "$filename down!\n";
    }
});/*}}}*/
