<?php

function _generate_error_code_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_error_code_template_from_extension();

    otherwise($content, '没找到 error_code 模版');

    $content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "<?php

%s";

    $content = sprintf($template, $content);

    return str_replace('^^', '', $content);
}/*}}}*/

function _merge_error_code_content_by_annotate($entity_name, $content_outside, $content_inside)
{/*{{{*/
    $annotate_start = '/* generated '.$entity_name.' start */';
    $annotate_end = '/* generated '.$entity_name.' end */';
    $more_line = '//more';

    $res_lines = [];

    $inside_start = false;
    $inside_focus = false;
    $outside_focus = true;
    $never_find_annotate = true;

    foreach (explode("\n", $content_outside) as $outside_line) {

        if (trim($outside_line) === $more_line && $never_find_annotate) {

            foreach (explode("\n", $content_inside) as $inside_line) {

                if ($inside_focus) {

                    $res_lines[] = $inside_line;
                }

                if (trim($inside_line) === $annotate_start) {

                    $res_lines[] = $inside_line;
                    $inside_focus = true;
                    $inside_start = true;
                }

                if (trim($inside_line) === $annotate_end) {

                    $inside_focus = false;
                    $inside_start = false;
                    break;
                }
            }
        }

        if ($outside_focus) {

            $res_lines[] = $outside_line;

        } elseif ($inside_start) {

            foreach (explode("\n", $content_inside) as $inside_line) {

                if ($inside_focus) {

                    $res_lines[] = $inside_line;
                }

                if (trim($inside_line) === $annotate_start) {

                    $inside_focus = true;
                }

                if (trim($inside_line) === $annotate_end) {

                    $inside_focus = false;
                    $inside_start = false;
                    break;
                }
            }
        }

        if (trim($outside_line) === $annotate_start) {

            $outside_focus = false;
            $inside_start = true;
            $never_find_annotate = false;
        }

        if (trim($outside_line) === $annotate_end) {

            $outside_focus = true;
        }
    }

    return implode("\n", $res_lines);
}/*}}}*/

function _generate_error_code_docs_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_error_code_docs_template_from_extension();

    otherwise($content, '没找到 docs/error_code 模版');

    $content = blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "# 错误码

%s";

    $content = sprintf($template, $content);

    return str_replace('^^', '', $content);
}/*}}}*/

function _merge_error_code_docs_content_by_annotate($entity_name, $content_outside, $content_inside)
{/*{{{*/
    $annotate_start = '[^_^]: '.$entity_name.'_start';
    $annotate_end = '[^_^]: '.$entity_name.'_end';
    $more_line = '[^_^]: more';

    $res_lines = [];

    $inside_start = false;
    $inside_focus = false;
    $outside_focus = true;
    $never_find_annotate = true;

    foreach (explode("\n", $content_outside) as $outside_line) {

        if (trim($outside_line) === $more_line && $never_find_annotate) {

            foreach (explode("\n", $content_inside) as $inside_line) {

                if ($inside_focus) {

                    $res_lines[] = $inside_line;
                }

                if (trim($inside_line) === $annotate_start) {

                    $res_lines[] = $inside_line;
                    $inside_focus = true;
                    $inside_start = true;
                }

                if (trim($inside_line) === $annotate_end) {

                    $inside_focus = false;
                    $inside_start = false;
                    break;
                }
            }
        }

        if ($outside_focus) {

            $res_lines[] = $outside_line;

        } elseif ($inside_start) {

            foreach (explode("\n", $content_inside) as $inside_line) {

                if ($inside_focus) {

                    $res_lines[] = $inside_line;
                }

                if (trim($inside_line) === $annotate_start) {

                    $inside_focus = true;
                }

                if (trim($inside_line) === $annotate_end) {

                    $inside_focus = false;
                    $inside_start = false;
                    break;
                }
            }
        }

        if (trim($outside_line) === $annotate_start) {

            $outside_focus = false;
            $inside_start = true;
            $never_find_annotate = false;
        }

        if (trim($outside_line) === $annotate_end) {

            $outside_focus = true;
        }
    }

    return implode("\n", $res_lines);
}/*}}}*/

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

function _generate_controller_struct_group_add($struct_group_type)
{/*{{{*/
    $content = _get_struct_group_controller_from_extension('add', $struct_group_type);

    otherwise($content, '没找到 controller/add/struct_group/'.$struct_group_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_group_detail($struct_group_type)
{/*{{{*/
    $content = _get_struct_group_controller_from_extension('detail', $struct_group_type);

    otherwise($content, '没找到 controller/detail/struct_group/'.$struct_group_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_controller_struct_group_update($struct_group_type)
{/*{{{*/
    $content = _get_struct_group_controller_from_extension('update', $struct_group_type);

    otherwise($content, '没找到 controller/update/struct_group/'.$struct_group_type.'.php 模版');

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

function _generate_docs_api_struct_group_add($data_type)
{/*{{{*/
    $content = _get_struct_group_docs_api_from_extension('add', $data_type);

    otherwise($content, '没找到 docs/api/add/struct_group/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_struct_group_detail($data_type)
{/*{{{*/
    $content = _get_struct_group_docs_api_from_extension('detail', $data_type);

    otherwise($content, '没找到 docs/api/detail/struct_group/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_struct_group_update($data_type)
{/*{{{*/
    $content = _get_struct_group_docs_api_from_extension('update', $data_type);

    otherwise($content, '没找到 docs/api/update/struct_group/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

function _generate_docs_api_struct_group_list($data_type)
{/*{{{*/
    $content = _get_struct_group_docs_api_from_extension('list', $data_type);

    otherwise($content, '没找到 docs/api/list/struct_group/'.$data_type.'.php 模版');

    return $content;
}/*}}}*/

command('crud:make-from-description', '通过描述文件生成 CRUD 控制器', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name', '');

    if ($entity_name) {

        $output_file_name = command_paramater('output_file', '');

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $controller_file_string = _generate_controller_file($entity_name, $entity_info, $relationship_infos);

        if (empty($output_file_name)) {
            $output_file_name = CONTROLLER_DIR.'/'.$entity_name.'.php';
        }

        // 写文件
        error_log($controller_file_string, 3, $output_file_name);
        echo $output_file_name."\n";
    }
});/*}}}*/

command('crud:make-error-code-from-description', '通过描述文件生成 error-code', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name', '');

    if ($entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $config_file = config_dir()[0].'/error_code.php';
        $config_file_string = _generate_error_code_file($entity_name, $entity_info, $relationship_infos);

        if (is_file($config_file)) {
            $config_file_string = _merge_error_code_content_by_annotate($entity_name, file_get_contents($config_file), $config_file_string);
        }

        // 写文件
        file_put_contents($config_file, $config_file_string);
        echo $config_file."\n";
    }
});/*}}}*/

command('crud:make-docs-from-description', '通过描述文件生成 CRUD 相关接口文档', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name', '');

    if ($entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $docs_api_file_string = _generate_docs_api_file($entity_name, $entity_info, $relationship_infos);

        // 写文件
        $docs_api_file_relative_path = 'api/'.$entity_name.'.md';
        error_log($docs_api_file_string, 3, $docs_api_file = DOCS_DIR.'/'.$docs_api_file_relative_path);
        echo $docs_api_file."\n";
    }
});/*}}}*/

command('crud:make-error-code-docs-from-description', '通过描述文件生成 error-code 相关文档', function ()
{/*{{{*/
    $entity_name = command_paramater('entity_name', '');

    if ($entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $markdown_file = DOCS_DIR.'/error_code.md';
        $markdown_file_string = _generate_error_code_docs_file($entity_name, $entity_info, $relationship_infos);

        if (is_file($markdown_file)) {
            $markdown_file_string = _merge_error_code_docs_content_by_annotate($entity_name, file_get_contents($markdown_file), $markdown_file_string);
        }

        // 写文件
        file_put_contents($markdown_file, $markdown_file_string);
        echo $markdown_file."\n";
    }
});/*}}}*/
