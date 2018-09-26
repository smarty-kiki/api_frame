<?php

define('DAO_DIR', DOMAIN_DIR.'/dao');
define('ENTITY_DIR', DOMAIN_DIR.'/entity');
define('DESCRIPTION_DIR', DOMAIN_DIR.'/description');

function _get_value_from_description_file($entity_name, $key = null, $default = null)
{/*{{{*/
    $entity_file_path = DESCRIPTION_DIR.'/'.$entity_name.'.yml';

    otherwise(file_exists($entity_file_path), "实体 $entity_name 描述文件没找到");

    $description = yaml_parse_file(DESCRIPTION_DIR.'/'.$entity_name.'.yml');

    if (is_null($key)) {
        return $description;
    }

    return array_get($description, $key, $default);
}/*}}}*/

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

        $relationship_type = $relationship['type'];
        $relationship_name = $relationship['relation_name'];
        $relationship_relate_to = $relationship['relate_to'];

        if ($relationship_name === $relationship_relate_to) {
            $relationship_str[] = "\$this->{$relationship_type}('{$relationship_relate_to}');";
        } else {
            $relationship_str[] = "\$this->{$relationship_type}('{$relationship_name}', '{$relationship_relate_to}', '{$relationship_name}_id');";
        }

        if ($relationship_type !== 'has_many') {
            $structs_str[] = "'".$relationship_name."_id' => '',";
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

        $name = command_read("#$s Column Name:");

        $datatype = command_read("#$s Data type:", 0, ['varchar', 'int(11)', 'datetime', 'date', 'time', 'bigint(20)']);
        if ($datatype === 'varchar') {
            $datatype = $datatype.'('.command_read("#$s Varchar length:", 45).')';
        }

        $allow_null = command_read_bool("#$s Allow Null");
        $default = command_read("#$s Default:", null);

        $entity_structs[] = [
            'name' => $name,
            'datatype' => $datatype,
            'allow_null' => $allow_null,
            'default' => $default,
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

    echo "\n 需要重新生成 domain/autoload.php 以加载 $entity_name\n";
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

    echo "\n 需要重新生成 domain/autoload.php 以加载新类\n";
});/*}}}*/

command('entity:make-from-description', '从实体描述文件初始化 entity、dao、migration', function ()
{/*{{{*/

    $entity_name = command_paramater('entity_name');

    $description = _get_value_from_description_file($entity_name);

    $structs = array_get($description, 'structs', []);

    foreach ($structs as $column => $struct) {
        $entity_structs[] = [
            'name' => $column,
            'datatype' => $struct['type'],
            'allow_null' => array_get($struct, 'allow_null', false),
            'default' => array_get($struct, 'default', null),
        ];
    }

    $relationships = array_get($description, 'relationships', []);

    foreach ($relationships as $relation_name => $relationship) {

        $relation_entity_name = $relationship['entity'];
        $relation_type = $relationship['type'];

        $entity_relationships[] = [
            'type' => $relation_type,
            'relate_to' => $relation_entity_name,
            'relation_name' => $relation_name,
        ];

        if ($relation_type !== 'has_many') {

            _get_value_from_description_file($relation_entity_name);
        }
    }

    $snaps = array_get($description, 'snaps', []);

    foreach ($snaps as $snap_relation_to_with_dot => $snap) {

        $parent_description = $description;

        foreach (explode('.', $snap_relation_to_with_dot) as $snap_relation_to) {

            $snap_relation = array_get($parent_description, "relationships.".$snap_relation_to, false);

            otherwise($snap_relation, "与冗余的 $snap_relation_to 没有关联关系");
            otherwise($snap_relation['type'] !== 'has_many', "冗余的 $snap_relation_to 为 has_many 关系，无法冗余字段");

            $parent_description = _get_value_from_description_file($snap_relation['entity']);
        }

        $snap_relation_to_structs = $parent_description['structs'];

        foreach ($snap['structs'] as $column) {

            otherwise(array_key_exists($column, $snap_relation_to_structs), "需要冗余的字段 $column 在 $snap 中不存在");

            $struct = $snap_relation_to_structs[$column];

            $entity_structs[] = [
                'name' => $column,
                'datatype' => $struct['type'],
                'allow_null' => array_get($struct, 'allow_null', false),
                'default' => array_get($struct, 'default', null),
            ];
        }
    }

    error_log(_generate_entity_file($entity_name, $entity_structs, $entity_relationships), 3, $file = ENTITY_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_dao_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DAO_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_migration_file($entity_name, $entity_structs, $entity_relationships), 3, $file = migration_file_path($entity_name));
    echo $file."\n";

    echo "\n 需要重新生成 domain/autoload.php 以加载 $entity_name\n";
});/*}}}*/
