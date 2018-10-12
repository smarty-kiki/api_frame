<?php

define('DESCRIPTION_DIR', DOMAIN_DIR.'/description');

function _generate_description_file($entity_name, $entity_structs, $entity_relationships)
{/*{{{*/
    $yaml = [];

    var_dump($entity_relationships);
    var_dump($entity_structs);
    var_dump($entity_name);exit;
    // todo yaml 文件的输出

    return yaml_emit($yaml);
}/*}}}*/

command('description:make-domain-description', '通过交互式输入创建领域实体描述文件', function () {

    $entity_name = command_paramater('entity_name');

    $display_name = command_read("Display name:");

    $entity_structs = [];

    $s = 0;

    while (command_read_bool('Add struct')) {

        $s += 1;

        $name = command_read("#$s Column name:");
        $struct_display_name = command_read("#$s Display name:");
        $struct_discription = command_read("#$s Discription:");

        $datatype = command_read("#$s Data type:", 0, ['varchar', 'int(11)', 'datetime', 'date', 'time', 'bigint(20)']);
        if ($datatype === 'varchar') {
            $datatype = $datatype.'('.command_read("#$s Varchar length:", 45).')';
        }

        $allow_null = command_read_bool("#$s Allow Null");

        $format = $format_description = null;
        if ($format = command_read("#$s Format:", null)) {
            $tmp['format'] = $format;

            if ($format_description = command_read("#$s Format description:", null)) {
                $tmp['format_description'] = $format_description;
            }
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
            'relate_to' => command_read("#$r Relate to:"),
            'relation_name' => command_read("#$r Relation name:"),
        ];

        foreach ($entity_relationships as $relationship) {
            echo json_encode($relationship)."\n";
        }
    }

    $n = 0;

    while (command_read_bool('Add snap')) {

        $n += 1;

        //todo  snap 关联关系及字段的补全
        $snap_name = command_read("#$n Snap relationship name:");

        $snap_structs = [];
        while ($snap_struct = command_read("#$n Add snap struct:", null)) {
            $snap_structs[] = $snap_struct;
        }

        if ($snap_structs) {

            $snaps[] = [
                'snap_name' => $snap_name,
                'structs' => $snap_structs,
            ];
        }

        foreach ($snaps as $snap) {
            echo json_encode($snap)."\n";
        }
    }

    error_log(_generate_description_file($entity_name, $entity_structs, $entity_relationships), 3, $file = DESCRIPTION_DIR.'/'.$entity_name.'.yml');
    echo $file."\n";
});
