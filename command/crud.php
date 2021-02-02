<?php

function _generate_controller_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_controller_template_from_extension('list');

    otherwise($content, '没找到 controller 的 list 模版');

    $list_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_controller_template_from_extension('add');

    otherwise($content, '没找到 controller 的 add 模版');

    $add_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_controller_template_from_extension('detail');

    otherwise($content, '没找到 controller 的 detail 模版');

    $detail_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_controller_template_from_extension('update');

    otherwise($content, '没找到 controller 的 update 模版');

    $update_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_controller_template_from_extension('delete');

    otherwise($content, '没找到 controller 的 delete 模版');

    $delete_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "<?php

%s
%s
%s
%s
%s";

    $content = sprintf($template, $list_content, $add_content, $detail_content, $update_content, $delete_content);

    return str_replace('^^', '', $content);
}/*}}}*/

function _generate_controller_data_type_add($data_type)
{/*{{{*/
    $content = _get_data_type_controller_from_extension('add', $data_type);

    otherwise($content, '没找到 controller/add/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_data_type_detail($data_type)
{/*{{{*/
    $content = _get_data_type_controller_from_extension('detail', $data_type);

    otherwise($content, '没找到 controller/detail/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_data_type_update($data_type)
{/*{{{*/
    $content = _get_data_type_controller_from_extension('update', $data_type);

    otherwise($content, '没找到 controller/update/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_data_type_list($data_type)
{/*{{{*/
    $content = _get_data_type_controller_from_extension('list', $data_type);

    otherwise($content, '没找到 controller/list/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_group_list($struct_group_type)
{/*{{{*/
    $content = _get_struct_group_controller_from_extension('list', $struct_group_type);

    otherwise($content, '没找到 controller/list/struct_group/'.$struct_group_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_docs_api_template_from_extension('list');

    otherwise($content, '没找到 docs/api 的 list 模版');

    $list_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_docs_api_template_from_extension('add');

    otherwise($content, '没找到 docs/api 的 add 模版');

    $add_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_docs_api_template_from_extension('detail');

    otherwise($content, '没找到 docs/api 的 detail 模版');

    $detail_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_docs_api_template_from_extension('update');

    otherwise($content, '没找到 docs/api 的 update 模版');

    $update_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $content = _get_docs_api_template_from_extension('delete');

    otherwise($content, '没找到 docs/api 的 delete 模版');

    $delete_content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "# {$entity_info['display_name']}  
{$entity_info['description']}

%s
%s
%s
%s
%s";

    $content = sprintf($template, $list_content, $add_content, $detail_content, $update_content, $delete_content);

    return str_replace('^^', '', $content);
}/*}}}*/

function _generate_docs_api_data_type_add($data_type)
{/*{{{*/
    $content = _get_data_type_docs_api_from_extension('add', $data_type);

    otherwise($content, '没找到 docs/api/add/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_data_type_detail($data_type)
{/*{{{*/
    $content = _get_data_type_docs_api_from_extension('detail', $data_type);

    otherwise($content, '没找到 docs/api/detail/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_data_type_update($data_type)
{/*{{{*/
    $content = _get_data_type_docs_api_from_extension('update', $data_type);

    otherwise($content, '没找到 docs/api/update/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_data_type_list($data_type)
{/*{{{*/
    $content = _get_data_type_docs_api_from_extension('list', $data_type);

    otherwise($content, '没找到 docs/api/list/data_type/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

command('crud:make-from-description', '通过描述文件生成 CRUD 控制器', function ()
{/*{{{*/
    $entity_names = _get_entity_name_by_command_paramater();

    foreach ($entity_names as $entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $controller_file_string = _generate_controller_file($entity_name, $entity_info, $relationship_infos);

        // 写文件
        error_log($controller_file_string, 3, $controller_file = CONTROLLER_DIR.'/'.$entity_name.'.php');
        echo "generate $controller_file success!\n";
        echo "todo ".ROOT_DIR."/public/index.php include $controller_file\n";
    }
});/*}}}*/

command('crud:make-docs-from-description', '通过描述文件生成 CRUD 相关接口文档', function ()
{/*{{{*/
    $entity_names = _get_entity_name_by_command_paramater();

    foreach ($entity_names as $entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $docs_api_file_string = _generate_docs_api_file($entity_name, $entity_info, $relationship_infos);

        // 写文件
        $docs_api_file_relative_path = 'api/'.$entity_name.'.md';
        error_log($docs_api_file_string, 3, $docs_api_file = DOCS_DIR.'/'.$docs_api_file_relative_path);
        echo "generate $docs_api_file success!\n";
        echo "todo ".DOCS_DIR."/sidebar.md include $docs_api_file_relative_path\n";
    }
});/*}}}*/
