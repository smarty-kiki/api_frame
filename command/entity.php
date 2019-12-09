<?php

define('DAO_DIR', DOMAIN_DIR.'/dao');
define('ENTITY_DIR', DOMAIN_DIR.'/entity');

function _generate_entity_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_entity_template_from_extension();

    $entity_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "<?php

%s";

    $entity_content = sprintf($template, $entity_content);

    return str_replace('^', '', $entity_content);
}/*}}}*/

function _generate_dao_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_dao_template_from_extension();

    $dao_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    $template = "<?php

%s";

    $dao_content = sprintf($template, $dao_content);

    return str_replace('^', '', $dao_content);
}/*}}}*/

function _generate_migration_file($entity_name, $entity_info, $relationship_infos)
{/*{{{*/
    $content = _get_migration_template_from_extension();

    $migration_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_info' => $entity_info,
        'relationship_infos' => $relationship_infos,
    ]);

    return str_replace('^', '', $migration_content);
}/*}}}*/

function _merge_content_by_annotate($content_outside, $content_inside)
{/*{{{*/
    static $annotate_start = '/* generated code start */';
    static $annotate_end = '/* generated code end */';

    $res_lines = [];

    $inside_start = false;
    $inside_focus = false;
    $outside_focus = true;

    foreach (explode("\n", $content_outside) as $outside_line) {

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
        }

        if (trim($outside_line) === $annotate_end) {

            $outside_focus = true;
        }
    }

    return implode("\n", $res_lines);
}/*}}}*/

command('entity:make-from-description', '从实体描述文件初始化 entity、dao、migration', function ()
{/*{{{*/
    $entity_names = _get_entity_name_by_command_paramater();

    foreach ($entity_names as $entity_name) {

        $entity_info = description_get_entity($entity_name);

        $relationship_infos = description_get_relationship_with_snaps_by_entity($entity_name);

        $entity_path = ENTITY_DIR.'/'.$entity_name.'.php';
        $entity_new_content = _generate_entity_file($entity_name, $entity_info, $relationship_infos);
        if (is_file($entity_path)) {
            $entity_new_content = _merge_content_by_annotate(file_get_contents($entity_path), $entity_new_content);
        }

        $dao_path = DAO_DIR.'/'.$entity_name.'.php';
        $dao_new_content = _generate_dao_file($entity_name, $entity_info, $relationship_infos);
        if (is_file($dao_path)) {
            $dao_new_content = _merge_content_by_annotate(file_get_contents($dao_path), $dao_new_content);
        }

        error_log(_generate_migration_file($entity_name, $entity_info, $relationship_infos), 3, $file = migration_file_path($entity_name));
        echo $file."\n";
        file_put_contents($entity_path, $entity_new_content); echo $entity_path."\n";
        file_put_contents($dao_path, $dao_new_content); echo $dao_path."\n";

        echo "\n需要重新生成 domain/autoload.php 以加载 $entity_name\n";
    }
});/*}}}*/
