<?php

function _generate_controller_file($table, $entity_structs, $entity_relationships)
{/*{{{*/
    $resource_plural = $table.'s';
    $resource_id_key = $table.'_id';

    $list_str = [];
    $input_str = [];
    foreach ($entity_structs as $struct) {
        $struct_name = $struct['name'];

        $list_str[] = "\$inputs['$struct_name']";
        $input_str[] = "'$struct_name'";
    }

    $input_content = "\$inputs = [];
    list(
        ".implode(",\n        ", $list_str)."
    ) = input_list(
        ".implode(",\n        ", $input_str)."
    );
    \$inputs = array_filter(\$inputs);";

    $content = "<?php

if_get('/%s', function ()
{/*{{{*/
    %s

    return [
        'succ' => true,
        'data' => dao('%s')->find_all_by_column(\$inputs),
    ];
});/*}}}*/

if_put('/%s', function ()
{/*{{{*/
    %s

    $%s = %s::create();

    foreach (\$inputs as \$property => \$value) {
        $%s->{\$property} = \$value;
    }

    return [
        'succ' => true,
        'data' => $%s,
    ];
});/*}}}*/

if_get('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    return [
        'succ' => true,
        'data' => $%s,
    ];
});/*}}}*/

if_post('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    %s

    foreach (\$inputs as \$property => \$value) {
        $%s->{\$property} = \$value;
    }

    return [
        'succ' => true,
        'data' => $%s,
    ];
});/*}}}*/

if_delete('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    $%s->delete();

    return [
        'succ' => true,
        'data' => $%s,
    ];
});/*}}}*/";

    return sprintf($content, 
        // if_get all
        $resource_plural,
        $input_content,
        $table,
        // if_put
        $resource_plural,
        $input_content,
        $table, $table,
        $table,
        $table,
        // if_get one
        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $table,
        // if_post
        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $input_content,
        $table,
        $table,
        // if_delete
        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $table,
        $table
    );
}/*}}}*/

command('controller:make-restful-from-db', '从数据库表结构初始化 restful 风格 controller', function ()
{/*{{{*/
    $table = command_paramater('table_name');

    $schema_infos = db_query("show create table `$table`");
    $schema_info = reset($schema_infos);

    $entity_structs = $entity_relationships = [];

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

        preg_match('/^KEY `fk_'.$table.'_(.*)_idx` \(`(.*)`\)/', $line, $matches);
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

    error_log(_generate_controller_file($table, $entity_structs, $entity_relationships), 3, $file = CONTROLLER_DIR.'/'.$table.'.php');
    echo $file."\n";
});/*}}}*/

command('controller:make-restful-from-description', '从实体描述文件初始化 restful 风格 controller', function ()
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

    error_log(_generate_controller_file($entity_name, $entity_structs, $entity_relationships), 3, $file = CONTROLLER_DIR.'/'.$entity_name.'.php');
    echo $file."\n";
});/*}}}*/
