<?php

define('DESCRIPTION_DIR', DOMAIN_DIR.'/description');
define('DESCRIPTION_EXTENSION_DIR', COMMAND_DIR.'/description_extension');
define('DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/struct_type');
define('DESCRIPTION_DATA_TYPE_EXTENSION_DIR', DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR.'/data_type');
define('DESCRIPTION_CONTROLLER_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/controller');
define('DESCRIPTION_ENTITY_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/entity');
define('DESCRIPTION_DAO_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/dao');
define('DESCRIPTION_MIGRATION_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/migration');

function _get_entity_name_by_command_paramater()
{/*{{{*/
    $entity_name = command_paramater('entity_name', '*');

    if ($entity_name === '*') {

        $file_paths = glob(DESCRIPTION_DIR.'/*.yml');

        $entity_names = array_build($file_paths, function ($k, $file_path) {

            return [$k, pathinfo($file_path)['filename']];
        });
    } else {
        $entity_names = [$entity_name];
    }

    return $entity_names;
}/*}}}*/

function _get_struct_controller_from_extension($action, $type)
{/*{{{*/
    $path = DESCRIPTION_CONTROLLER_EXTENSION_DIR.'/'.$action.'/struct/'.$type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_controller_template_from_extension($action)
{/*{{{*/
    $path = DESCRIPTION_CONTROLLER_EXTENSION_DIR.'/'.$action.'/controller.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_entity_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_ENTITY_EXTENSION_DIR.'/entity.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_dao_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_DAO_EXTENSION_DIR.'/dao.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_migration_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_MIGRATION_EXTENSION_DIR.'/migration.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _generate_description_file($entity_name, $display_name, $description, $entity_structs, $entity_relationships, $entity_snaps)
{/*{{{*/
    $structs = [];

    foreach ($entity_structs as $entity_struct) {

        $struct_name = array_shift($entity_struct);

        $tmp_struct = array_transfer($entity_struct, [
            'datatype'           => 'type',
            'format'             => 'format',
            'format_description' => 'format_description',
            'display_name'       => 'display_name',
            'description'        => 'description',
            'allow_null'         => 'allow_null',
            'default'            => 'default',
        ]);

        if (is_null($tmp_struct['default']) && ! $tmp_struct['allow_null']) {
            unset($tmp_struct['default']);
        }

        $structs[$struct_name] = $tmp_struct;
    }

    $relationships = [];

    foreach ($entity_relationships as $entity_relationship) {

        $relationship_name = $entity_relationship['relation_name'];

        $relationships[$relationship_name] = array_transfer($entity_relationship, [
            'relate_to' => 'entity',
            'type'      => 'type',
        ]);
    }

    $snaps = [];

    foreach ($entity_snaps as $entity_snap) {

        $snap_name = $entity_snap['snap_name'];

        $snaps[$snap_name] = array_transfer($entity_snap, [
            'structs' => 'structs',
        ]);
    }

    $yaml = [
        'display_name' => $display_name,
        'description' => $description,
        'structs' => $structs,
        'relationships' => $relationships,
        'snaps' => $snaps,
    ];

    return yaml_emit($yaml, YAML_UTF8_ENCODING, YAML_LN_BREAK);
}/*}}}*/

//todo 支持 extension
command('description:make-domain-description', '通过交互式输入创建领域实体描述文件', function ()
{/*{{{*/

    $entity_name = command_paramater('entity_name');

    $display_name = command_read("Display name:", '实体名字');

    $description = command_read("Description:", '实体描述');

    $entity_structs = [];

    $s = 0;

    while (command_read_bool('Add struct')) {

        $s += 1;

        $name = command_read("#$s Column name:", 'column'.$s);
        $struct_display_name = command_read("#$s Display name:", '字段'.$s);
        $struct_discription = command_read("#$s Discription:", '字段'.$s.'的描述');

        $datatype = command_read("#$s Data type:", 0, ['varchar', 'int(11)', 'datetime', 'date', 'time', 'bigint(20)']);
        if ($datatype === 'varchar') {
            $datatype = $datatype.'('.command_read("#$s Varchar length:", 45).')';
        }

        $allow_null = command_read_bool("#$s Allow Null", 'n');

        $format = $format_description = null;
        if ($format_type = command_read("#$s Format type:", 0, [null, 'reg', 'enum'])) {

            if ($format_type === 'reg') {

                $format = command_read("#$s Format reg:", null);

            } elseif ($format_type === 'enum') {

                $format = [];

                while ($format_enum = command_read("#$s Add format enum (eg. 'valid 有效', default to quit):", null)) {
                    $exploded_enum = explode(' ', $format_enum);

                    if (count($exploded_enum) === 2)
                    {
                        $format[$exploded_enum[0]] = $exploded_enum[1];
                    }
                }
            }

            $format_description = command_read("#$s Format description:", null);
        }

        $tmp = [
            'name' => $name,
            'display_name' => $struct_display_name,
            'description' => $struct_discription,
            'datatype' => $datatype,
            'format' => $format,
            'format_description' => $format_description,
            'allow_null' => $allow_null,
        ];

        if ($allow_null) {
            $tmp['default'] = null;
        }

        $entity_structs[] = $tmp;

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
            'relate_to' => command_read("#$r Relate to:", 'related_entity'.$r),
            'relation_name' => command_read("#$r Relation name:", 'the_related_name'.$r),
        ];

        foreach ($entity_relationships as $relationship) {
            echo json_encode($relationship)."\n";
        }
    }

    $entity_snaps = [];

    $n = 0;

    while (command_read_bool('Add snap')) {

        $n += 1;

        //todo  snap 关联关系及字段的补全能力
        $snap_name = command_read("#$n Snap relationship name:", 'the_related_name'.$n);

        $snap_structs = [];
        while ($snap_struct = command_read("#$n Add snap struct (defqult to quit):", null)) {
            $snap_structs[] = $snap_struct;
        }

        if ($snap_structs) {

            $entity_snaps[] = [
                'snap_name' => $snap_name,
                'structs' => $snap_structs,
            ];
        }

        foreach ($entity_snaps as $snap) {
            echo json_encode($snap)."\n";
        }
    }

    error_log(_generate_description_file($entity_name, $display_name, $description, $entity_structs, $entity_relationships, $entity_snaps), 3, $file = DESCRIPTION_DIR.'/'.$entity_name.'.yml');
    echo $file."\n";
});/*}}}*/

function description_get_entity($entity_name)
{/*{{{*/
    $path = DESCRIPTION_DIR.'/'.$entity_name.'.yml';

    otherwise(is_file($path), "实体 $entity_name 描述文件没找到");

    $description = yaml_parse_file($path);

    otherwise(isset($description['display_name']), "$path 中需设置 display_name");

    if (! isset($description['description'])) {
        $description['description'] = $description['display_name'];
    }

    foreach ($description['structs'] as $struct_name => &$struct) {

        if (isset($struct['type'])) {

            $struct = array_replace_recursive(description_get_struct_type($struct['type']), $struct);

            unset($struct['type']);
        }

        otherwise(isset($struct['data_type']), '字段必须设置 data_type');

        $struct = array_replace_recursive(description_get_data_type($struct['data_type']), $struct);

        if (! isset($struct['require'])) {

            $struct['require'] = true;
        }

        if (! isset($struct['display_name'])) {

            $struct['display_name'] = $struct_name;
        }

        if (! isset($struct['description'])) {

            $struct['description'] = $struct['display_name'];
        }

        if ($struct['data_type'] === 'enum') {

            otherwise(isset($struct['formater']), 'data_type 为 enum 时需要设置 formater');
            otherwise(is_array($struct['formater']), 'data_type 为 enum 时 formater 需要是数组');
        } else {

            if (isset($struct['formater'])) {

                foreach ($struct['formater'] as &$formater) {

                    otherwise(is_array($formater), 'formater 中的元素需要是数组');

                    if (isset($formater['reg'])) {

                        if (! isset($formater['failed_message'])) {

                            $formater['failed_message'] = "$struct_name 需满足正则表达式 {$formater['reg']}";
                        }
                    } elseif (isset($formater['function'])) {

                        if (! isset($formater['failed_message'])) {

                            $formater['failed_message'] = "$struct_name 需满足逻辑 {$formater['function']}";
                        }
                    }
                }
            }
        }
    }

    return $description;
}/*}}}*/

function description_get_struct_type($struct_type)
{/*{{{*/
    $path = DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR.'/'.$struct_type.'.php';

    otherwise(is_file($path), "字段类型 $struct_type 配置文件没找到");

    $res = include $path;

    otherwise(isset($res['data_type']), "$path 中需设置 data_type");
    otherwise(isset($res['display_name']), "$path 中需设置 display_name");

    if (! isset($res['description'])) {
        $res['description'] = $res['display_name'];
    }

    return $res;
}/*}}}*/

function description_get_data_type($data_type)
{/*{{{*/
    $path = DESCRIPTION_DATA_TYPE_EXTENSION_DIR.'/'.$data_type.'.php';

    otherwise(is_file($path), "数据类型 $data_type 配置文件没找到");

    $res = include $path;

    otherwise(isset($res['database_field']), "$path 中需设置 database_field");
    otherwise(array_key_exists('type', $res['database_field']), "$path 中的 database_field 中需设置 type");
    otherwise(array_key_exists('length', $res['database_field']), "$path 中的 database_field 中需设置 length");

    return $res;
}/*}}}*/

function description_get_relationship()
{/*{{{*/
    $path = DESCRIPTION_DIR.'/.relationship.yml';

    if (! is_file($path)) {

        return [];
    }

    $relationships = yaml_parse_file($path);

    $res = [];

    foreach ($relationships as $n => $relationship) {

        $num = $n + 1;

        otherwise(isset($relationship['from']), "第 $num 条记录需要设置 from");
        otherwise(isset($relationship['to']), "第 $num 条记录需要设置 to");
        otherwise(isset($relationship['relationship_type']), "第 $num 条记录需要设置 relationship_type");
        otherwise(isset($relationship['association_type']), "第 $num 条记录需要设置 association_type");

        // from
        $from = $relationship['from'];
        otherwise(isset($from['entity']), "第 $num 条记录的 from 记录需要设置 entity");
        $from_entity = $from['entity'];

        if (! isset($from['to_attribute_name'])) {
            $from['to_attribute_name'] = $from['entity'];
        }
        if (! isset($from['to_display_name'])) {
            $from['to_display_name'] = '$this->id';
        }
        if (! isset($from['to_snaps'])) {
            $from['to_snaps'] = [];
        }

        // to
        $to = $relationship['to'];
        otherwise(isset($to['entity']), "第 $num 条记录的 to 记录需要设置 entity");
        $to_entity = $to['entity'];

        if (! isset($to['from_attribute_name'])) {
            $to['from_attribute_name'] = $to['entity'];
        }
        if (! isset($to['from_display_name'])) {
            $to['from_display_name'] = '$this->id';
        }
        if (! isset($to['from_snaps'])) {
            $to['from_snaps'] = [];
        }

        $relationship_type = $relationship['relationship_type'];
        otherwise(in_array($relationship_type, ['has_many', 'has_one']), "第 $num 条记录的 relationship_type 只能为 has_many 或 has_one");

        $association_type = $relationship['association_type'];
        otherwise(in_array($association_type, ['aggregation', 'composition']), "第 $num 条记录的 association_type 只能为 aggregation 或 composition");

        if (! isset($res[$from_entity])) {
            $res[$from_entity] = [];
        }

        $to_entity_info = description_get_entity($to_entity);
        $res[$from_entity][$to['from_attribute_name']] = [
            'entity' => $to_entity,
            'entity_display_name' => $to_entity_info['display_name'],
            'attribute_name' => $to['from_attribute_name'],
            'display_name' => $to['from_display_name'],
            'snaps' => $to['from_snaps'],
            'relationship_type' => $relationship_type,
            'association_type' => $association_type,
        ];

        if (! isset($res[$to_entity])) {
            $res[$to_entity] = [];
        }

        $from_entity_info = description_get_entity($from_entity);
        $res[$to_entity][$from['to_attribute_name']] = [
            'entity' => $from_entity,
            'entity_display_name' => $from_entity_info['display_name'],
            'attribute_name' => $from['to_attribute_name'],
            'display_name' => $from['to_display_name'],
            'snaps' => $from['to_snaps'],
            'relationship_type' => 'belongs_to',
            'association_type' => null,
        ];
    }

    return $res;
}/*}}}*/

function description_get_relationship_with_snaps_by_entity($entity_name)
{/*{{{*/
    static $container = [];

    if (empty($container)) {

        $container = description_get_relationship();
    }

    if (! isset($container[$entity_name])) {

        return [];
    }

    $relationship_infos = $container[$entity_name];

    foreach ($relationship_infos as $attritube_name => &$relationship) {

        foreach ($relationship['snaps'] as $snap_relation_to_with_dot => &$structs) {

            $last_entity_name = $entity_name;

            foreach (explode('.', $snap_relation_to_with_dot) as $snap_relation_to) {

                otherwise(isset($container[$last_entity_name]) && isset($container[$last_entity_name][$snap_relation_to]),
                    "$entity_name 的 snap $snap_relation_to_with_dot 中 $last_entity_name 与 $snap_relation_to 没有关联关系");

                $last_entity_name = $container[$last_entity_name][$snap_relation_to]['entity'];
            }

            $last_entity_info = description_get_entity($last_entity_name);
            $last_entity_structs = $last_entity_info['structs'];

            $new_structs = [];

            foreach ($structs as $struct_name) {

                otherwise(isset($last_entity_structs[$struct_name]), "$entity_name 的 snap $snap_relation_to_with_dot 中 $last_entity_name 没有字段 $struct_name");

                $tmp = $last_entity_structs[$struct_name];
                $tmp['display_name'] = $last_entity_info['display_name'].$tmp['display_name'];
                $tmp['description'] = '冗余自'.$last_entity_info['display_name'].','.$tmp['description'];

                $new_structs['snap_'.$last_entity_name.'_'.$struct_name] = $tmp;
            }

            $structs = $new_structs;
        }
    }

    return $relationship_infos;
}/*}}}*/
