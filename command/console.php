<?php

function _get_defined_function_completion_infos()
{/*{{{*/
    $function_infos = [];

    foreach (get_defined_functions() as $type => $functions) {
        foreach ($functions as $function) {
            if (! starts_with($function, '_')) {

                $reffunc = new reflectionfunction($function);
                $function_param_infos = [];
                $function_option_param_infos = [];

                foreach ($reffunc->getParameters() as $param) {
                    if ($param->isOptional() && $type == 'user') {
                        $function_option_param_infos[] = '$'.$param->name;
                    } else {
                        $function_param_infos[] = '$'.$param->name;
                    }
                }

                $function_infos[] = $function.'('.
                    implode(', ', $function_param_infos).
                    (empty($function_option_param_infos)?'':('/*, '.implode(', ', $function_option_param_infos).'*/'))
                    .')';
            }
        }
    }

    return $function_infos;
}/*}}}*/

function _get_defined_entity_completion_infos()
{/*{{{*/
    $entity_infos = [];

    $entity_paths = glob(DOMAIN_DIR.'/entity/*.php');
    foreach ($entity_paths as $entity_path) {

        $old_classes = get_declared_classes();
        include $entity_path;
        $new_classes = get_declared_classes();
        $added_classes = array_diff($new_classes, $old_classes);

        foreach ($added_classes as $class) {

            $entity_info = [
                'structs' => [],
                'public_functions' => [],
                'find_one_functions' => [],
                'find_all_functions' => [],
            ];

            $vars = get_class_vars($class);

            $entity_info['structs'] = array_merge([
                'id', 'version', 'create_time', 'update_time', 'delete_time'
            ], array_keys($vars['structs']));

            $methods = get_class_methods($class);

            foreach ($methods as $method) {

                if (! starts_with($method, '_')) {

                    $refmethod = new reflectionmethod($class, $method);

                    if ($refmethod->isPublic() && ! $refmethod->isStatic()) {

                        $method_param_infos = [];
                        $method_option_param_infos = [];

                        foreach ($refmethod->getParameters() as $param) {

                            if ($param->isOptional()) {
                                $method_option_param_infos[] = '$'.$param->name;
                            } else {
                                $method_param_infos[] = '$'.$param->name;
                            }
                        }

                        $entity_info['public_functions'][] = $method.'('.
                            implode(', ', $method_param_infos).
                            (empty($method_option_param_infos)?'':('/*, '.implode(', ', $method_option_param_infos).'*/'))
                            .')';
                    }
                }
            }

            $dao = $class.'_dao';
            $methods = get_class_methods($dao);

            foreach ($methods as $method) {

                if (starts_with($method, 'find_all')) {

                    $refmethod = new reflectionmethod($dao, $method);

                    if ($refmethod->isPublic() && ! $refmethod->isStatic()) {

                        $method_param_infos = [];
                        $method_option_param_infos = [];

                        foreach ($refmethod->getParameters() as $param) {

                            if ($param->isOptional()) {
                                $method_option_param_infos[] = '$'.$param->name;
                            } else {
                                $method_param_infos[] = '$'.$param->name;
                            }
                        }

                        $entity_info['find_all_functions'][] = $method.'('.
                            implode(', ', $method_param_infos).
                            (empty($method_option_param_infos)?'':('/*, '.implode(', ', $method_option_param_infos).'*/'))
                            .')';
                    }
                } else if (starts_with($method, 'find')) {

                    $refmethod = new reflectionmethod($dao, $method);

                    if ($refmethod->isPublic() && ! $refmethod->isStatic()) {

                        $method_param_infos = [];
                        $method_option_param_infos = [];

                        foreach ($refmethod->getParameters() as $param) {

                            if ($param->isOptional()) {
                                $method_option_param_infos[] = '$'.$param->name;
                            } else {
                                $method_param_infos[] = '$'.$param->name;
                            }
                        }

                        $entity_info['find_one_functions'][] = $method.'('.
                            implode(', ', $method_param_infos).
                            (empty($method_option_param_infos)?'':('/*, '.implode(', ', $method_option_param_infos).'*/'))
                            .')';
                    }
                }
            }

            $entity_infos[$class] = $entity_info;
        }
    }

    return $entity_infos;
}/*}}}*/

function _get_defined_entity_relation_completion_infos()
{/*{{{*/
    $relation_infos = [];

    $entity_paths = glob(DOMAIN_DIR.'/entity/*.php');

    foreach ($entity_paths as $entity_path) {

        $code = file_get_contents($entity_path);

        $relation_info = [];

        $entity_name = '';

        foreach (explode("\n", $code) as $code_line)
        {
            $match = [];
            preg_match_all('/^class ([a-z_]*) .*$/', $code_line, $match);

            if ($match[1]) {
                $entity_name = $match[1][0];
            }

            $match = [];
            preg_match_all('/\$this->(belongs_to|has_many|has_one)\((.*)\);/', $code_line, $match);
            if ($match[0]) {

                $param_string = str_replace(['\'', '"', ' '], '', $match[2][0]);
                $params = explode(',', $param_string);

                $relation_info[$params[0]] = [
                    'type' => $match[1][0],
                    'entity' => ($params[1] ?? $params[0]),
                ];
            }
        }

        if ($entity_name) {
            $relation_infos[$entity_name] = $relation_info;
        }
    }

    return $relation_infos;
}/*}}}*/

command('console', '终端模式', function ()
{/*{{{*/
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

    $function_infos = _get_defined_function_completion_infos();
    $entity_infos = _get_defined_entity_completion_infos();
    $relation_infos = _get_defined_entity_relation_completion_infos();

    $plural_to_entity = [];

    foreach ($entity_infos as $entity_name => $entity_info) {

        $plural_to_entity[$entity_name] = $entity_name;

        $pluralize_entity_name = english_word_pluralize($entity_name);
        if ($pluralize_entity_name !== $entity_name) {
            $plural_to_entity[$pluralize_entity_name] = $entity_name;
        }
    }

    command_read_completions(function ($buffer_info) use (
        $function_infos, $entity_infos, $plural_to_entity, $relation_infos
    ) {
        $completions = [];

        //开头的变量名可以补全单数或者复数实体名
        if ($buffer_info['block_start'] === 1 && starts_with($buffer_info['line_buffer'], '$')) {
            $entity_names = array_keys($entity_infos);
            foreach ($entity_names as $entity_name) {
                $completions[] = $entity_name;

                $pluralize_entity_name = english_word_pluralize($entity_name);
                if ($pluralize_entity_name !== $entity_name) {
                    $completions[] = $pluralize_entity_name;
                }
            }
        }

        if ($buffer_info['block_start'] > 0) { // 在一行的非开头时

            $line_buffer_before_block = substr($buffer_info['line_buffer'], 0, $buffer_info['block_start']);

            // 如果开头变量名是个实体名，给补全中加入 dao 及方法
            $match = [];
            preg_match_all('/^\$(.*) = .*$/', $line_buffer_before_block, $match);
            if ($match[1]) {
                $param_name = array_pop($match[1]);
                if (isset($plural_to_entity[$param_name])) {
                    $entity_name = $plural_to_entity[$param_name];
                    foreach (
                        $entity_name !== $param_name ?
                        $entity_infos[$entity_name]['find_all_functions']:
                        $entity_infos[$entity_name]['find_one_functions']
                        as $function) {
                        $full_line = "\$$param_name = dao('$entity_name')->".$function;
                        if (starts_with($full_line, $line_buffer_before_block)) {
                            $completions[] = str_replace($line_buffer_before_block, '', $full_line);
                        }
                    }
                }
            }

            // 如果当前光标正在一个实体名的变量后，给补全中加入实体方法、属性、关联关系
            $match = [];
            preg_match_all('/.*\$(.*)->$/', $line_buffer_before_block, $match);
            if ($match[1]) {
                $param_name = array_pop($match[1]);
                if (isset($entity_infos[$param_name])) {
                    $entity_info = $entity_infos[$param_name];
                    $completions = array_merge($completions, $entity_info['structs'], $entity_info['public_functions']);

                    if (isset($relation_infos[$param_name])) {
                        $completions = array_merge($completions, array_keys($relation_infos[$param_name]));
                    }
                }
            }

            // 如果当前光标正在一个实体名变量后的关联关系后，给补全中加入最末端关联关系实体的方法、属性、关联关系
            $match = [];
            preg_match_all('/\$(.*?)->(.*->)$/', $line_buffer_before_block, $match);
            if ($match[1] && $match[2]) {
                $entity_name = array_pop($match[1]);
                $relation_str = $match[2][0];
                $relation_arr = explode('->', $relation_str);
                $matched = true;
                foreach ($relation_arr as $relation) {
                    if (isset($relation_infos[$entity_name])) {
                        if (isset($relation_infos[$entity_name][$relation])) {
                            if ($relation_infos[$entity_name][$relation]['type'] !== 'has_many') {
                                $entity_name = $relation_infos[$entity_name][$relation]['entity'];
                            } else {
                                $matched = false;
                            }
                        }
                    }
                }

                if ($matched && isset($entity_infos[$entity_name])) {
                    $entity_info = $entity_infos[$entity_name];
                    $completions = array_merge($completions, $entity_info['structs'], $entity_info['public_functions']);

                    if (isset($relation_infos[$entity_name])) {
                        $completions = array_merge($completions, array_keys($relation_infos[$entity_name]));
                    }
                }
            }


            if (! ends_with($line_buffer_before_block, ['>', '$', '"', '\'', ')', '}', ']'])) { // 只要光标前的字符不是这些特殊字符，就给补全中加入已注册的函数
                $completions = array_merge($completions, $function_infos);
            }
        } else { // 从头开始可以补全已注册的函数
            $completions = array_merge($completions, $function_infos);
        }

        return $completions;
    });

    while (true) {
        $code = command_read('Terminal', '"hello world"');
        readline_add_history($code);

        $code = str_finish($code, ';');
        $code = str_begin($code, 'return ');
        try {
            $res = eval($code);
            echo "\033[32m"; var_dump($res); echo "\033[0m";
        } catch (throwable $ex) {
            echo "\033[31m".$ex->getMessage()."\033[0m\n";
        }
        echo "\n";
    }
});/*}}}*/
