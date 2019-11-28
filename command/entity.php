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
    $content = _get_entity_template_from_extension();

    $entity_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $entity_structs,
        'entity_relationships' => $entity_relationships,
        'entity_options' => $entity_options,
    ]);

    $template = "<?php

%s";

    $entity_content = sprintf($template, $entity_content);

    return str_replace('^', '', $entity_content);
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
    $content = _get_migration_template_from_extension();

    $migration_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $entity_structs,
        'entity_relationships' => $entity_relationships,
    ]);

    return str_replace('^', '', $migration_content);
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
