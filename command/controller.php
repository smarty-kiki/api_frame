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

    foreach ($entity_relationships as $relationship) {

        $relationship_type = $relationship['type'];
        $relationship_name = $relationship['relation_name'];

        if ($relationship_type === 'belongs_to') {

            $list_str[] = "\$inputs['".$relationship_name."_id']";
            $input_str[] = "'".$relationship_name."_id'";
        }
    }

    $input_content = "\$inputs = [];
    list(
        ".implode(",\n        ", $list_str)."
    ) = input_list(
        ".implode(",\n        ", $input_str)."
    );
    \$inputs = array_filter(\$inputs, 'not_null');";

    $content = "<?php

if_get('/%s', function ()
{/*{{{*/
    %s

    return [
        'succ' => true,
        'data' => dao('%s')->find_all_by_column(\$inputs),
    ];
});/*}}}*/

if_post('/%s/add', function ()
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

if_post('/%s/update/*', function ($%s)
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

if_post('/%s/delete/*', function ($%s)
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

command('controller:make-restful-from-description', '从实体描述文件初始化 restful 风格 controller', function ()
{/*{{{*/
    $entity_names = _get_entity_name_by_command_paramater();

    foreach ($entity_names as $entity_name) {

        $description = _get_value_from_description_file($entity_name);

        $structs = array_get($description, 'structs', []);

        $entity_structs = [];

        foreach ($structs as $column => $struct) {

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
        }

        error_log(_generate_controller_file($entity_name, $entity_structs, $entity_relationships), 3, $file = CONTROLLER_DIR.'/'.$entity_name.'.php');
        echo $file."\n";
    }
});/*}}}*/
