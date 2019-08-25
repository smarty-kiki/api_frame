<?php

define('DAO_DIR', DOMAIN_DIR.'/dao');
define('ENTITY_DIR', DOMAIN_DIR.'/entity');

function _get_value_from_description_file($entity_name, $key = null, $default = null)
{/*{{{*/
    $entity_file_path = DESCRIPTION_DIR.'/'.$entity_name.'.yml';

    otherwise(is_file($entity_file_path), "实体 $entity_name 描述文件没找到");

    $description = yaml_parse_file(DESCRIPTION_DIR.'/'.$entity_name.'.yml');

    if (is_null($key)) {
        return $description;
    }

    return array_get($description, $key, $default);
}/*}}}*/

function _generate_entity_file($entity_name, $entity_structs, $entity_relationships, $entity_options = [])
{/*{{{*/
    $content = '<?php

class %s extends entity
{
    public $structs = [
        %s
    ];

    public static $entity_display_name = \'%s\';
    public static $entity_description = \'%s\';

    public static $struct_types = [
        %s
    ];

    public static $struct_display_names = [
        %s
    ];

    public static $struct_descriptions = [
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
    $display_names_str = [];
    $descriptions_str = [];
    $formats_str = [];
    $format_descriptions_str = [];

    $property_block = [];
    $function_block = [];

    foreach ($entity_structs as $struct) {

        $struct_name = $struct['name'];
        $struct_format = $struct['format'];
        $struct_display_name = $struct['display_name'];
        $struct_description = $struct['description'];

        // generate structs
        if (array_key_exists('default', $struct)) {

            $struct_default = $struct['default'];

            if (is_string($struct_default)) {
                $structs_str[] = "'$struct_name' => '$struct_default',";
            } elseif (is_null($struct_default)) {
                $structs_str[] = "'$struct_name' => '',";
            } else {
                $structs_str[] = "'$struct_name' => $struct_default,";
            }
        } else {
            $structs_str[] = "'$struct_name' => '',";
        }

        // generate struct_types
        $struct_type = entity::convert_struct_format($struct['datatype'], $struct_format);

        $types_str[] = "'$struct_name' => '$struct_type',";

        // generate display_names
        $display_names_str[] =  "'$struct_name' => '$struct_display_name',";

        // generate descriptions
        $descriptions_str[] =  "'$struct_name' => '$struct_description',";

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
        $relationship_struct_display_name = $relationship['entity_display_name'].'ID';
        $relationship_struct_name = $relationship_name.'_id';

        if ($relationship_name === $relationship_relate_to) {
            $relationship_str[] = "\$this->{$relationship_type}('{$relationship_relate_to}');";
        } else {
            if ($relationship_type === 'belongs_to') {
                $relationship_str[] = "\$this->{$relationship_type}('{$relationship_name}', '{$relationship_relate_to}', '{$relationship_struct_name}');";
            } else {
                $relationship_str[] = "\$this->{$relationship_type}('{$relationship_name}', '{$relationship_relate_to}');";
            }
        }

        if ($relationship_type === 'belongs_to') {
            $structs_str[] = "'$relationship_struct_name' => '',";
            $types_str[] = "'$relationship_struct_name' => 'number',";
            $display_names_str[] =  "'$relationship_struct_name' => '$relationship_struct_display_name',";
            $descriptions_str[] =  "'$relationship_struct_name' => '$relationship_struct_display_name',";
        }
    }

    return sprintf($content,
        $entity_name,
        implode("\n        ", $structs_str),
        array_get($entity_options, 'display_name', ''),
        array_get($entity_options, 'description', ''),
        implode("\n        ", $types_str),
        implode("\n        ", $display_names_str),
        implode("\n        ", $descriptions_str),
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
                if ($struct['allow_null']) {
                    $column .= " DEFAULT NULL";
                }
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

            if ($relationship['relation_name'] === $relationship['relate_to']) {
                $indexs[] = "KEY `fk_{$relationship['relation_name']}_idx` (`{$relationship['relation_name']}_id`, `delete_time`),";
            } else {
                $indexs[] = "KEY `fk_{$relationship['relation_name']}_{$relationship['relate_to']}_idx` (`{$relationship['relation_name']}_id`, `delete_time`),";
            }
        }
    }

    return sprintf($content, $entity_name, implode("\n    ", array_merge($columns, $indexs)), $entity_name);
}/*}}}*/

command('entity:make-from-description', '从实体描述文件初始化 entity、dao、migration', function ()
{/*{{{*/
    $entity_names = _get_entity_name_by_command_paramater();

    foreach ($entity_names as $entity_name) {

        $description = _get_value_from_description_file($entity_name);

        $structs = array_get($description, 'structs', []);
        $entity_display_name = array_get($description, 'display_name', '');
        $entity_description = array_get($description, 'description', '');

        $entity_structs = [];
        foreach ($structs as $column => $struct) {

            if ($extension = array_get($struct, 'extension', null)) {
                if ($extension_struct = _get_struct_info_from_extension($extension)) {
                    $struct = array_merge($extension_struct, array_filter($struct));
                }
            }

            $tmp = [
                'name' => $column,
                'datatype' => $struct['type'],
                'display_name' => $struct['display_name'],
                'description' => $struct['description'],
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

        $entity_relationships = [];
        foreach ($relationships as $relation_name => $relationship) {

            $relation_entity_name = $relationship['entity'];
            $relation_type = $relationship['type'];

            $relation_description = _get_value_from_description_file($relation_entity_name);

            $entity_relationships[] = [
                'type' => $relation_type,
                'relate_to' => $relation_entity_name,
                'relation_name' => $relation_name,
                'entity_display_name' => $relation_description['display_name'],
            ];
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
                    'display_name' => $parent_description['display_name'].$struct['display_name'],
                    'description' => $struct['description'],
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

        error_log(_generate_entity_file($entity_name, $entity_structs, $entity_relationships, ['display_name' => $entity_display_name, 'description' => $entity_description]), 3, $file = ENTITY_DIR.'/'.$entity_name.'.php');
        echo $file."\n";
        error_log(_generate_dao_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DAO_DIR.'/'.$entity_name.'.php');
        echo $file."\n";
        error_log(_generate_migration_file($entity_name, $entity_structs, $entity_relationships), 3, $file = migration_file_path($entity_name));
        echo $file."\n";

        echo "\n需要重新生成 domain/autoload.php 以加载 $entity_name\n";
    }
});/*}}}*/
