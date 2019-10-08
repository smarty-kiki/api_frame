<?php

define('DESCRIPTION_DIR', DOMAIN_DIR.'/description');
define('DESCRIPTION_EXTENSION_DIR', COMMAND_DIR.'/description_extension');
define('DESCRIPTION_STRUCT_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/struct');
define('DESCRIPTION_CONTROLLER_EXTENSION_DIR', DESCRIPTION_EXTENSION_DIR.'/controller');

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

function _get_struct_info_from_extension($extension)
{/*{{{*/
    $path = DESCRIPTION_STRUCT_EXTENSION_DIR.'/'.$extension.'.php';
    if (is_file($path)) {
        return include $path;
    }

    return false;
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
