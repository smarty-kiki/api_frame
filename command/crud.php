<?php

function _generate_controller_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $inputs = [];

    foreach ($entity_info['structs'] as $struct_name => $struct) {
        $inputs[] = $struct_name;
    }

    foreach ($relationship_infos['relationships'] as $attritube_name => $relationship) {

        if ($relationship['relationship_type'] === 'belongs_to') {
            $inputs[] = $attritube_name.'_id';
        }

        foreach ($relationship['snaps'] as $structs) {
            foreach ($structs as $struct_name => $struct) {
                $inputs[] = $struct_name;
            }
        }
    }

    $content = _get_controller_template_from_extension('list');

    otherwise($content, '没找到 controller 的 list 模版');

    $list_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('add');

    otherwise($content, '没找到 controller 的 add 模版');

    $add_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('detail');

    otherwise($content, '没找到 controller 的 detail 模版');

    $detail_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('update');

    otherwise($content, '没找到 controller 的 update 模版');

    $update_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('delete');

    otherwise($content, '没找到 controller 的 delete 模版');

    $delete_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $template = "<?php

%s
%s
%s
%s
%s";

    $content = sprintf($template, $list_content, $add_content, $detail_content, $update_content, $delete_content);

    return str_replace('^', '', $content);
}/*}}}*/

function _generate_controller_struct_add($struct_type)
{/*{{{*/
    $content = _get_struct_controller_from_extension('add', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 add 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_detail($struct_type)
{/*{{{*/
    $content = _get_struct_controller_from_extension('detail', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 detail 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_update($struct_type)
{/*{{{*/
    $content = _get_struct_controller_from_extension('update', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 update 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_list($struct_type)
{/*{{{*/
    $content = _get_struct_controller_from_extension('list', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 list 模版');

    return $content;
}/*}}}*/

command('crud:make-from-description', '通过描述文件生成 CRUD 控制器', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name');

    $entity_info = description_get_entity($entity_name);

    $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

    $controller_file_string = _generate_controller_file($entity_name, $entity_info, $relationship_infos);

    // 写文件
    error_log($controller_file_string, 3, $controller_file = CONTROLLER_DIR.'/'.$entity_name.'.php');
    echo $controller_file."\n";
    echo "\n将 $controller_file 加入到 public/index.php 即可响应请求\n";
});/*}}}*/
