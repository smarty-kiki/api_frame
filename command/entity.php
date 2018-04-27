<?php

define('DAO_DIR', DOMAIN_DIR.'/dao');
define('ENTITY_DIR', DOMAIN_DIR.'/entity');

function _generate_entity_file($entity_name, $entity_structs, $entity_relationships)
{/*{{{*/
    $content = '<?php

class %s extends entity
{
    public $structs = [
        %s
    ];

    public function __construct()
    {/*{{{*/
        %s
    }/*}}}*/

    public static function create()
    {/*{{{*/
        return parent::init();
    }/*}}}*/
}';

    $structs_str = [];
    foreach ($entity_structs as $struct) {
        $structs_str[] = "'".$struct['name']."' => '',";
    }

    $relationship_str = [];
    foreach ($entity_relationships as $relationship) {
        if ($relationship['relation_name'] === $relationship['relate_to']) {
            $relationship_str[] = "\$this->{$relationship['type']}('{$relationship['relate_to']}');";
        } else {
            $relationship_str[] = "\$this->{$relationship['type']}('{$relationship['relation_name']}', '{$relationship['relate_to']}', '{$relationship['relation_name']}_id');";
        }
    }

    return sprintf($content, $entity_name, implode("\n        ", $structs_str), implode("\n        ", $relationship_str));
}/*}}}*/

function _generate_dao_file($entity_name, $entity_structs, $entity_relationships)
{/*{{{*/
    return "<?php

class {$entity_name}_dao extends dao
{
    protected \$table_name = '{$entity_name}';
    protected \$db_config_key = '".unit_of_work_db_config_key()."';
}";
}/*}}}*/

function _generate_migration_file($entity_name, $entity_structs, $entity_relationships)
{/*{{{*/
    $content = "# up
        CREATE TABLE `%s` (
            `id` bigint(20) NOT NULL,
            `version` int(11) NOT NULL,
            `create_time` datetime DEFAULT NULL,
            `update_time` datetime DEFAULT NULL,
            `delete_time` datetime DEFAULT NULL,
            %s
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    # down
    drop table `%s`;";

    $columns = [];

    foreach ($entity_structs as $struct) {
        $column = sprintf('`%s` %s', $struct['name'], $struct['datatype']);
        if (! $struct['allow_null']) {
            $column .= ' NOT NULL';
        }
        if ($default = $struct['default']) {
            if (is_string($default)) {
                $column .= " DEFAULT '$default'";
            } else {
                $column .= " DEFAULT $default";
            }
        } else {
            $column .= ' DEFAULT NULL';
        }

        $columns[] = $column.',';
    }

    $indexs = [];
    foreach ($entity_relationships as $relationship) {
        if ($relationship['type'] === 'belongs_to') {
            $columns[] = "`{$relationship['relate_to']}_id` bigint(20) NOT NULL,";
            $indexs[] = "KEY `fk_{$entity_name}_{$relationship['relate_to']}_idx` (`{$relationship['relation_name']}_id`),";
        }
    }

    return sprintf($content, $entity_name, implode("\n    ", array_merge($columns, $indexs)), $entity_name);
}/*}}}*/

command('entity:make', '初始化 entity、dao、migration', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name');

    $entity_structs = [];

    $s = 0;

    while (command_read_bool('Add struct')) {

        $s += 1;

        $entity_structs[] = [
            'name' => command_read("#$s Column Name:"),
            'datatype' => command_read("#$s Data type:", 0, ['varchar(45)', 'int(11)', 'datetime', 'date', 'time', 'bigint(20)']),
            'allow_null' => command_read_bool("#$s Allow Null"),
            'default' => command_read("#$s Default:", null),
        ];

        foreach ($entity_structs as $struct) {
            echo json_encode($struct)."\n";
        }
    }

    $entity_relationships = [];

    $r = 0;

    while (command_read_bool('Add relationship')) {

        $r += 1;

        $entity_relationships[] = [
            'type' => command_read("#$r Type:", 0, ['belongs_to', 'has_one', 'has_many']),
            'relate_to' => command_read("#$r Relate to:"),
            'relation_name' => command_read("#$r Relation name:"),
        ];

        foreach ($entity_relationships as $relationship) {
            echo json_encode($relationship)."\n";
        }
    }

    error_log(_generate_entity_file($entity_name, $entity_structs, $entity_relationships), 3, $file = ENTITY_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_dao_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DAO_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_migration_file($entity_name, $entity_structs, $entity_relationships), 3, $file = migration_file_path($entity_name));
    echo $file."\n";

});/*}}}*/

command('entity:make-from-db', '从数据库表结构初始化 entity、dao、migration', function ()
{/*{{{*/
    $table_infos = db_query('show tables', [], unit_of_work_db_config_key());

    foreach ($table_infos as $table_info) {
        $entity_structs = $entity_relationships = [];
        $entity_name = $table = reset($table_info);

        if ($entity_name === MIGRATION_TABLE) {
            continue;
        }

        $schema_infos = db_query("show create table `$table`", [], unit_of_work_db_config_key());
        $schema_info = reset($schema_infos);

        $lines = explode("\n", $schema_info['Create Table']);

        foreach ($lines as $i => $line) {

            $line = trim($line);

            if (stristr($line, 'CONSTRAINT')) {
                unset($lines[$i]);
                continue;
            }

            if (stristr($line, 'CREATE TABLE')) continue;
            if (stristr($line, 'PRIMARY KEY')) continue;
            if (stristr($line, ') ENGINE=')) continue;
            if (stristr($line, '`id`')) continue;
            if (stristr($line, '`version`')) continue;
            if (stristr($line, '`create_time`')) continue;
            if (stristr($line, '`update_time`')) continue;
            if (stristr($line, '`delete_time`')) continue;


            preg_match('/^`(.*)`/', $line, $matches);
            if ($matches) {
                $entity_structs[] = [
                    'name' => $matches[1],
                ];
                continue;
            }

            preg_match('/^KEY `fk_'.$entity_name.'_(.*)_idx` \(`(.*)`\)/', $line, $matches);
            if ($matches) {
                $relate_to = preg_replace('/[0-9]/', '', $matches[1]);
                $relation_name = str_replace('_id', '', $matches[2]);
                $entity_relationships[] = [
                    'type' => 'belongs_to',
                    'relate_to' => $relate_to,
                    'relation_name' => $relation_name,
                ];
            }
        }

        $up_sql = str_replace(",\n)", "\n)", implode("\n", $lines));

        $migration = sprintf("# up\n%s;\n\n# down\ndrop table `%s`;", $up_sql, $entity_name);

        echo $entity_name.":\n";
        error_log(_generate_entity_file($entity_name, $entity_structs, $entity_relationships), 3, $file = ENTITY_DIR.'/'.$entity_name.'.php');
        echo $file."\n";
        error_log(_generate_dao_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DAO_DIR.'/'.$entity_name.'.php');
        echo $file."\n";
        error_log($migration, 3, $file = migration_file_path($entity_name));
        echo $file."\n";
    }
});/*}}}*/
