<?php

define('DAO_DIR', DOMAIN_DIR.'/dao');
define('ENTITY_DIR', DOMAIN_DIR.'/entity');

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

    public static $struct_types = [
        %s
    ];
%s
    public static $struct_formats = [
        %s
    ];

    public static $struct_format_descriptions = [
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
%s
}';

    $structs_str = [];
    $types_str = [];
    $formats_str = [];
    $format_descriptions_str = [];

    $property_block = [];
    $function_block = [];

    foreach ($entity_structs as $struct) {

        $struct_name = $struct['name'];
        $struct_format = $struct['format'];

        // generate structs
        $structs_str[] = "'$struct_name' => '',";

        // generate struct_types
        $struct_type = 'text';
        $maps = [
            'varchar' => 'text',
            'text' => 'text',
            'int' => 'number',
            'bigint' => 'number',
            'enum' => 'enum',
        ];

        foreach ($maps as $pattern => $type) {
            if (is_array($struct_format)) {
                $struct_type = 'enum';
                break;
            } else if (stristr($struct['datatype'], $pattern)) {
                $struct_type = $type;
                break;
            }
        }

        $types_str[] = "'$struct_name' => '$struct_type',";

        // generate struct_formats
        if (! is_null($struct_format)) {
            if (is_array($struct_format)) {

                // generate const and map
                $const_str = [];
                $map_str = ["    const ".strtoupper($struct_name)."_MAPS = ["];

                foreach ($struct_format as $value => $description) {
                    $const_name = strtoupper($struct_name.'_'.$value);
                    $const_str[] = sprintf("    const %s = '%s';", $const_name, strtoupper($value));
                    $map_str[] = sprintf("        self::%s => '%s',", $const_name, $description);
                }

                $map_str[] = '    ];';

                $property_block[] = implode("\n", $const_str);
                $property_block[] = implode("\n", $map_str);

                $function_block[] = sprintf(
                    "    public function get_%s_description()\n".
                    "    {\n".
                    "        return self::%s[\$this->%s];\n".
                    "    }",
                    $struct_name, strtoupper($struct_name)."_MAPS", $struct_name);

                $formats_str[] = "'$struct_name' => self::".strtoupper($struct_name)."_MAPS,";

            } else {
                $formats_str[] = "'$struct_name' => '$struct_format',";
            }
        }

        $format_description = $struct['format_description'];
        if (! is_null($struct_format)) {
            $format_descriptions_str[] = "'$struct_name' => '$format_description',";
        }
    }

    $property_str = $property_block? "\n".implode("\n\n", $property_block)."\n": '';
    $function_str = $function_block? "\n".implode("\n\n", $function_block)."\n": '';

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
            $types_str[] = "'".$relationship_name."_id' => 'number',";
        }
    }

    return sprintf($content,
        $entity_name,
        implode("\n        ", $structs_str),
        implode("\n        ", $types_str),
        $property_str,
        implode("\n        ", $formats_str),
        implode("\n        ", $format_descriptions_str),
        implode("\n        ", $relationship_str),
        $function_str
    );
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
        if (array_key_exists('default', $struct)) {
            $default = $struct['default'];

            if (is_string($default)) {
                $column .= " DEFAULT '$default'";
            } elseif (is_null($default)) {
                $column .= " DEFAULT NULL";
            } else {
                $column .= " DEFAULT $default";
            }
        } 
        $columns[] = $column.',';
    }

    $indexs = [];
    foreach ($entity_relationships as $relationship) {
        if ($relationship['type'] === 'belongs_to') {
            $columns[] = "`{$relationship['relation_name']}_id` bigint(20) NOT NULL,";
            $indexs[] = "KEY `fk_{$entity_name}_{$relationship['relate_to']}_idx` (`{$relationship['relation_name']}_id`),";
        }
    }

    return sprintf($content, $entity_name, implode("\n    ", array_merge($columns, $indexs)), $entity_name);
}/*}}}*/

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

        $tmp = [
            'name' => $column,
            'datatype' => $struct['type'],
            'format' => array_get($struct, 'format', null),
            'format_description' => array_get($struct, 'format_description', null),
            'allow_null' => array_get($struct, 'allow_null', false),
        ];

        if (array_key_exists('default', $struct)) {
            $tmp['default'] = $struct['default'];
        }

        $entity_structs[] = $tmp;
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

        $snap_relation_name = '';

        foreach (explode('.', $snap_relation_to_with_dot) as $snap_relation_to) {

            $snap_relation = array_get($parent_description, "relationships.".$snap_relation_to, false);

            otherwise($snap_relation, "与冗余的 $snap_relation_to 没有关联关系");
            otherwise($snap_relation['type'] !== 'has_many', "冗余的 $snap_relation_to 为 has_many 关系，无法冗余字段");

            $parent_description = _get_value_from_description_file($snap_relation['entity']);
            $snap_relation_name = $snap_relation_to;
        }

        $snap_relation_to_structs = $parent_description['structs'];

        foreach ($snap['structs'] as $column) {

            otherwise(array_key_exists($column, $snap_relation_to_structs), "需要冗余的字段 $column 在 $snap_relation_to_with_dot 中不存在");

            $struct = $snap_relation_to_structs[$column];

            $tmp = [
                'name' => 'snap_'.$snap_relation_name.'_'.$column,
                'datatype' => $struct['type'],
                'format' => array_get($struct, 'format', null),
                'format_description' => array_get($struct, 'format_description', null),
                'allow_null' => array_get($struct, 'allow_null', false),
            ];

            if (array_key_exists('default', $struct)) {
                $tmp['default'] = $struct['default'];
            }

            $entity_structs[] = $tmp;
        }
    }

    error_log(_generate_entity_file($entity_name, $entity_structs, $entity_relationships), 3, $file = ENTITY_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_dao_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DAO_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
    error_log(_generate_migration_file($entity_name, $entity_structs, $entity_relationships), 3, $file = migration_file_path($entity_name));
    echo $file."\n";

    echo "\n需要重新生成 domain/autoload.php 以加载 $entity_name\n";
});/*}}}*/
