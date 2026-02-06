<?php

define('DESCRIPTION_DIR', DOMAIN_DIR.'/description');
define('DESCRIPTION_EXTENSION_DIR', COMMAND_DIR.'/description/description_extension');
define('DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/struct_type');
define('DESCRIPTION_DATA_TYPE_EXTENSION_DIR', DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR.'/data_type');
define('DESCRIPTION_STRUCT_GROUP_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/struct_group');
define('DESCRIPTION_UTIL_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/util');
define('DESCRIPTION_CONTROLLER_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/controller');
define('DESCRIPTION_ERROR_CODE_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/error_code');
define('DESCRIPTION_DOCS_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/docs');
define('DESCRIPTION_ENTITY_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/entity');
define('DESCRIPTION_DAO_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/dao');
define('DESCRIPTION_MIGRATION_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/migration');

function _get_data_type_controller_from_extension($action, $data_type)
{/*{{{*/
    $path = DESCRIPTION_CONTROLLER_EXTENSION_DIR.'/'.$action.'/data_type/'.$data_type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_struct_group_controller_from_extension($action, $struct_group_type)
{/*{{{*/
    $path = DESCRIPTION_CONTROLLER_EXTENSION_DIR.'/'.$action.'/struct_group/'.$struct_group_type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_error_code_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_ERROR_CODE_EXTENSION_DIR.'/error_code.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_error_code_docs_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/error_code/docs.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_util_template_from_extension($action)
{/*{{{*/
    $path = DESCRIPTION_UTIL_EXTENSION_DIR.'/'.$action.'/util.php';
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

function _get_data_type_docs_api_from_extension($action, $data_type)
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/api/'.$action.'/data_type/'.$data_type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_struct_group_docs_api_from_extension($action, $data_type)
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/api/'.$action.'/struct_group/'.$data_type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_docs_api_template_from_extension($action)
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/api/'.$action.'/docs.php';
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

function _get_struct_group_entity_template_from_extension($struct_group_type)
{/*{{{*/
    $path = DESCRIPTION_ENTITY_EXTENSION_DIR.'/struct_group/'.$struct_group_type.'.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_docs_entity_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/entity/entity/docs.php';
    if (is_file($path)) {
        return file_get_contents($path);
    }

    return false;
}/*}}}*/

function _get_docs_entity_relationship_template_from_extension()
{/*{{{*/
    $path = DESCRIPTION_DOCS_EXTENSION_DIR.'/entity/relationship/docs.php';
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

function _get_struct_group_dao_template_from_extension($struct_group_type)
{/*{{{*/
    $path = DESCRIPTION_DAO_EXTENSION_DIR.'/struct_group/'.$struct_group_type.'.php';
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

function _get_struct_types_from_extension()
{/*{{{*/
    $file_paths = glob(DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR.'/*.yml');

    return array_build($file_paths, function ($k, $file_path) {

        return [$k, pathinfo($file_path)['filename']];
    });
}/*}}}*/

function _get_data_types_from_extension()
{/*{{{*/
    $file_paths = glob(DESCRIPTION_DATA_TYPE_EXTENSION_DIR.'/*.yml');

    return array_build($file_paths, function ($k, $file_path) {

        return [$k, pathinfo($file_path)['filename']];
    });
}/*}}}*/

command('description:demo-description', '创建 demo description 文件', function ()
{/*{{{*/
    $demo_string = <<<EOF
---
display_name: 环境
# description: 环境
struct_groups:
  - closed_time_interval:
    name: check
    display_name: 检查
structs:
  struct_name1:
    type: ip
    data_type: string
    database_field:
      type: varchar
      length: 15
      allow_null: true
      default: null
    validator:
      - reg: /^(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}$/
        failed_message: IP 不是有效的 IP 格式
    display_name: IP 地址
    require: true
repeat_check_structs:
  - struct_name1
...
EOF;

    error_log($demo_string, 3, $description_file = DESCRIPTION_DIR.'/demo.yml'); echo $description_file."\n";

});/*}}}*/

command('description:make-entity-description', '通过交互式输入创建领域实体描述文件', function ()
{/*{{{*/
    $entity_name = command_read('Entity name:', 'new_entity');
    $entity_display_name = command_read("Display name:", $entity_name);
    $entity_description = command_read("Description:", $entity_display_name);

    $description_info = [
        'display_name' => $entity_display_name,
    ];

    if ($entity_description !== $entity_display_name) {
        $description_info['description'] = $entity_description;
    }

    $description_info['structs'] = [];

    $struct_type_enum = _get_struct_types_from_extension();
    $data_type_enum = _get_data_types_from_extension();

    $s = 0;
    while (command_read_bool('Add struct')) {
        $s += 1;

        $struct_name = command_read("#$s Name:", 'struct_'.$s);

        $struct = [];

        $struct['type'] = command_read("#$s Type:", 0, $struct_type_enum);

        if (! command_read_bool("#$s Require:", 'y')) {

            $struct['require'] = false;
        }

        $description_info['structs'][$struct_name] = $struct;
    }

    $description_string = yaml_emit($description_info, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    error_log($description_string, 3, $description_file = DESCRIPTION_DIR.'/'.$entity_name.'.yml'); echo $description_file."\n";

});/*}}}*/

command('description:make-relationship-description', '通过交互式输入创建领域实体关系描述文件', function ()
{/*{{{*/
    $path = DESCRIPTION_DIR.'/.relationship.yml';

    if (! is_file($path)) {
        file_put_contents($path, "---\n...");
    }

    $relationships = (array) yaml_parse_file($path);

    $file_paths = glob(DESCRIPTION_DIR.'/*.yml');

    $entity_names = array_build($file_paths, function ($k, $file_path) {

        return [$k, pathinfo($file_path)['filename']];
    });

    $s = 0;
    while (command_read_bool('Add relationship')) {
        $s += 1;

        $relationship = [
            'from' => [
                'entity' => '',
                'to_attribute_name' => '',
                'to_display' => '',
                'to_snaps' => [],
            ],
            'to' => [
                'entity' => '',
                'from_attribute_name' => '',
                'from_display' => '',
                'from_snaps' => [],
            ],
            'relationship_type' => '',
            'associate_delete' => '',
            'require' => '',
        ];

        $relationship['from']['entity'] = $from_entity = command_read("#$s From entity:", 0, $entity_names);
        $relationship['from']['to_attribute_name'] = command_read("#$s From entity to attribute name:", $from_entity);
        $relationship['from']['to_display'] = command_read("#$s From entity to display name:", '$this->id');

        $relationship['to']['entity'] = $to_entity = command_read("#$s To entity:", 0, array_merge(array_diff($entity_names, [$from_entity]), [$from_entity]));
        $relationship['to']['from_attribute_name'] = command_read("#$s To entity from attribute name:", $to_entity);
        $relationship['to']['from_display'] = command_read("#$s To entity from display name:", '$this->id');

        $relationship['relationship_type'] = command_read("#$s Relationship type:", 0, ['has_many', 'has_one']);
        $relationship['associate_delete'] = command_read_bool("#$s Associate delete:");
        $relationship['require'] = command_read_bool("#$s Require:");

        $relationships[] = $relationship;
    }

    $description_string = yaml_emit($relationships, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    file_put_contents($path, $description_string); echo $path."\n";
});/*}}}*/

function description_get_entity($entity_name)
{/*{{{*/
    $path = DESCRIPTION_DIR.'/'.$entity_name.'.yml';

    otherwise(is_file($path), "实体 $entity_name 描述文件没找到");

    static $container = [];

    if (! isset($container[$entity_name])) {
        /*{{{*/

        $description = yaml_parse_file($path);

        otherwise(isset($description['display_name']), "$path 中需设置 display_name");

        $description['description'] = $description['description'] ?? $description['display_name'];

        $description['struct_groups'] = $description['struct_groups'] ?? [];

        foreach ($description['struct_groups'] as $key => &$struct_group) {
            if (is_array($struct_group)) {
                $struct_group_type = key($struct_group);
                $struct_group = description_get_struct_group($struct_group_type, $struct_group[$struct_group_type]);
            } else {
                $struct_group = description_get_struct_group($struct_group);
            }

            $struct_group['structs'] = array_map(function ($struct) use ($struct_group_type, $key) {
                $struct['struct_group_type'] = $struct_group_type;
                $struct['struct_group_index'] = $key;
                return $struct;
            }, $struct_group['structs']);

            $description['structs'] = array_replace($struct_group['structs'], $description['structs']);
        }

        foreach ($description['structs'] as $struct_name => &$struct) {

            $validator_from_description_file = $struct['validator'] ?? [];

            if (isset($struct['type'])) {

                $struct = array_replace_recursive(description_get_struct_type($struct['type']), $struct);

                unset($struct['type']);
            }

            otherwise(isset($struct['data_type']), '字段必须设置 data_type');

            $struct = array_replace_recursive(description_get_data_type($struct['data_type']), $struct);

            $struct['require'] = $struct['require'] ?? true;
            $struct['display_name'] = $struct['display_name'] ?? $struct_name;
            $struct['description'] = $struct['description'] ?? $struct['display_name'];

            if ($struct['data_type'] === 'enum') {

                if ($validator_from_description_file) {

                    $struct['validator'] = $validator_from_description_file;
                }

                otherwise(isset($struct['validator']), 'data_type 为 enum 时需要设置 validator');
                otherwise(is_array($struct['validator']), 'data_type 为 enum 时 validator 需要是数组');
            } else {

                if (isset($struct['validator'])) {

                    foreach ($struct['validator'] as &$validator) {

                        otherwise(is_array($validator), 'validator 中的元素需要是数组');

                        if (isset($validator['reg'])) {

                            $validator['failed_message'] = $validator['failed_message'] ?? "$struct_name 需满足正则表达式 {$validator['reg']}";

                        } elseif (isset($validator['function'])) {

                            $validator['failed_message'] = $validator['failed_message'] ?? "$struct_name 需满足逻辑 {$validator['function']}";
                        }
                    }
                }
            }

            if (isset($struct['option'])) {

                $option = $struct['option'];

                $struct_string = yaml_emit($struct);

                foreach ($option as $key => $value) {
                    $struct_string = str_replace('$('.$key.')', $value, $struct_string);
                }

                $struct = yaml_parse($struct_string);
            }
        }

        $description['repeat_check_structs'] = $description['repeat_check_structs'] ?? [];

        foreach ($description['repeat_check_structs'] as $struct_name) {
            otherwise(isset($description['structs'][$struct_name]), $entity_name.' description repeat_check_structs 中的 '.$struct_name.' 在 structs 中不存在');
        }

        /*}}}*/
        return $container[$entity_name] = $description;
    }

    return $container[$entity_name];
}/*}}}*/

function description_get_struct_type($struct_type)
{/*{{{*/
    $path = DESCRIPTION_STRUCT_TYPE_EXTENSION_DIR.'/'.$struct_type.'.yml';

    otherwise(is_file($path), "字段类型 $struct_type 配置文件没找到");

    static $container = [];

    if (! isset($container[$struct_type])) {
        /*{{{*/

        $res = (array) yaml_parse_file($path);

        otherwise(isset($res['data_type']), "$path 中需设置 data_type");
        otherwise(isset($res['display_name']), "$path 中需设置 display_name");

        if (! isset($res['description'])) {
            $res['description'] = $res['display_name'];
        }
        /*}}}*/
        return $container[$struct_type] = $res;
    }

    return $container[$struct_type];
}/*}}}*/

function description_get_struct_group($struct_group_type, $struct_group_info = [])
{/*{{{*/
    $path = DESCRIPTION_STRUCT_GROUP_EXTENSION_DIR.'/'.$struct_group_type.'.yml';

    otherwise(is_file($path), "字段类型组 $struct_group_type 配置文件没找到");

    $group_origin_str = $group_str = file_get_contents($path);

    $res = [
        'type' => $struct_group_type,
        'structs' => [],
        'struct_name_maps' => [],
        'struct_group_info' => $struct_group_info,
    ];

    if ($struct_group_info) {
        foreach ($struct_group_info as $key => $value) {
            $group_str = str_replace('$('.$key.')', $value, $group_str);
        }
    }

    $res['structs'] = yaml_parse($group_str);

    $res['struct_name_maps'] = array_combine(
        array_keys(yaml_parse($group_origin_str)),
        array_keys($res['structs'])
    );

    return $res;
}/*}}}*/

function description_get_data_type($data_type)
{/*{{{*/
    $path = DESCRIPTION_DATA_TYPE_EXTENSION_DIR.'/'.$data_type.'.yml';

    otherwise(is_file($path), "数据类型 $data_type 配置文件没找到");

    static $container = [];

    if (! isset($container[$data_type])) {
        /*{{{*/
        $res = (array) yaml_parse_file($path);

        otherwise(isset($res['database_field']), "$path 中需设置 database_field");
        otherwise(array_key_exists('type', $res['database_field']), "$path 中的 database_field 中需设置 type");
        otherwise(array_key_exists('length', $res['database_field']), "$path 中的 database_field 中需设置 length");
        /*}}}*/
        return $container[$data_type] = $res;
    }

    return $container[$data_type];
}/*}}}*/

function description_get_relationship()
{/*{{{*/
    $path = DESCRIPTION_DIR.'/.relationship.yml';

    if (! is_file($path)) {

        return [];
    }

    static $res = [];

    if (empty($res)) {

        $relationships = (array) yaml_parse_file($path);

        foreach ($relationships as $n => $relationship) {

            $num = $n + 1;

            otherwise(isset($relationship['from']), "第 $num 条记录需要设置 from");
            otherwise(isset($relationship['to']), "第 $num 条记录需要设置 to");
            otherwise(isset($relationship['relationship_type']), "第 $num 条记录需要设置 relationship_type");
            otherwise(isset($relationship['associate_delete']), "第 $num 条记录需要设置 associate_delete");
            otherwise(isset($relationship['require']), "第 $num 条记录需要设置 require");

            $relationship_type = $relationship['relationship_type'];
            otherwise(in_array($relationship_type, ['has_many', 'has_one']), "第 $num 条记录的 relationship_type 只能为 has_many 或 has_one");

            // from
            $from = $relationship['from'];
            otherwise(isset($from['entity']), "第 $num 条记录的 from 记录需要设置 entity");
            $from_entity = $from['entity'];

            if (! isset($from['to_attribute_name'])) {
                $from['to_attribute_name'] = $from['entity'];
            }
            if (! isset($from['to_display'])) {
                $from['to_display'] = '$this->id';
            }
            if (! isset($from['to_snaps'])) {
                $from['to_snaps'] = [];
            }

            // to
            $to = $relationship['to'];
            otherwise(isset($to['entity']), "第 $num 条记录的 to 记录需要设置 entity");
            $to_entity = $to['entity'];

            if (! isset($to['from_attribute_name'])) {
                if ($relationship_type === 'has_many') {
                    $to['from_attribute_name'] = english_word_pluralize($to['entity']);
                } else {
                    $to['from_attribute_name'] = $to['entity'];
                }
            }
            if (! isset($to['from_display'])) {
                $to['from_display'] = '$this->id';
            }
            if (! isset($to['from_snaps'])) {
                $to['from_snaps'] = [];
            }

            $associate_delete = $relationship['associate_delete'];
            otherwise(is_bool($associate_delete), "第 $num 条记录的 associate_delete 只能为布尔值");

            $require = $relationship['require'];
            otherwise(is_bool($require), "第 $num 条记录的 require 只能为布尔值");

            if (! isset($res[$from_entity])) {
                $res[$from_entity] = [
                    'display_for_relationships' => [], 
                    'relationships' => [], 
                ];
            }

            $to_entity_info = description_get_entity($to_entity);
            $res[$from_entity]['relationships'][$to['from_attribute_name']] = [
                'entity' => $to_entity,
                'entity_display_name' => $to_entity_info['display_name'],
                'attribute_name' => $to['from_attribute_name'],
                'self_attribute_name' => $from['to_attribute_name'],
                'self_display' => $from['to_display'],
                'snaps' => $to['from_snaps'],
                'relationship_type' => $relationship_type,
                'reverse_relationship_type' => 'belongs_to',
                'associate_delete' => $associate_delete,
                'require' => $require,
            ];
            $res[$from_entity]['display_for_relationships']['display_for_'.$to['entity'].'_'.$from['to_attribute_name']] = $from['to_display'];

            if (! isset($res[$to_entity])) {
                $res[$to_entity] = [
                    'display_for_relationships' => [], 
                    'relationships' => [], 
                ];
            }

            $from_entity_info = description_get_entity($from_entity);
            $res[$to_entity]['relationships'][$from['to_attribute_name']] = [
                'entity' => $from_entity,
                'entity_display_name' => $from_entity_info['display_name'],
                'attribute_name' => $from['to_attribute_name'],
                'self_attribute_name' => $to['from_attribute_name'],
                'self_display' => $to['from_display'],
                'snaps' => $from['to_snaps'],
                'relationship_type' => 'belongs_to',
                'reverse_relationship_type' => $relationship_type,
                'associate_delete' => $associate_delete,
                'require' => $require,
            ];
            $res[$to_entity]['display_for_relationships']['display_for_'.$from['entity'].'_'.$to['from_attribute_name']] = $to['from_display'];
        }
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

        return [
            'relationships' => [],
            'display_for_relationships' => [],
        ];
    }

    $relationship_infos = $container[$entity_name];

    foreach ($relationship_infos['relationships'] as $attribute_name => &$relationship) {

        foreach ($relationship['snaps'] as $snap_relation_to_with_dot => &$structs) {

            $last_entity_name = $entity_name;

            foreach (explode('.', $snap_relation_to_with_dot) as $snap_relation_to) {

                otherwise(isset($container[$last_entity_name]) && isset($container[$last_entity_name]['relationships'][$snap_relation_to]),
                    "$entity_name 的 snap $snap_relation_to_with_dot 中 $last_entity_name 与 $snap_relation_to 没有关联关系");

                $last_entity_name = $container[$last_entity_name]['relationships'][$snap_relation_to]['entity'];
            }

            $last_entity_info = description_get_entity($last_entity_name);
            $last_entity_structs = $last_entity_info['structs'];

            $new_structs = [];

            foreach ($structs as $struct_name) {

                otherwise(isset($last_entity_structs[$struct_name]), "$entity_name 的 snap $snap_relation_to_with_dot 中 $last_entity_name 没有字段 $struct_name");

                $tmp = $last_entity_structs[$struct_name];
                $tmp['target_struct_name'] = $struct_name;
                $tmp['display_name'] = $last_entity_info['display_name'].$tmp['display_name'];
                $tmp['description'] = '冗余自'.$last_entity_info['display_name'].','.$tmp['description'];

                $new_structs['snap_'.$last_entity_name.'_'.$struct_name] = $tmp;
            }

            $structs = $new_structs;
        }
    }

    return $relationship_infos;
}/*}}}*/
