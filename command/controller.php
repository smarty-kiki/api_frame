<?php

function _generate_controller_file($table, $entity_structs, $entity_relationships)
{
    $resource_plural = $table.'s';
    $resource_id_key = $table.'_id';

    $list_str = [];
    $input_str = [];
    foreach ($entity_structs as $struct) {
        $list_str[] = "\$inputs['$struct']";
        $input_str[] = "'$struct'";
    }

    $input_content = "\$inputs = [];\n    list(".implode(", ", $list_str).") = input_list(".implode(", ", $input_str).");\n    \$inputs = array_filter(\$inputs);";

    $content = "<?php

if_get('/%s', function ()
{/*{{{*/
    %s

    return dao('%s')->find_all_by_column(\$inputs);
});/*}}}*/

if_put('/%s', function ()
{/*{{{*/
    %s

    foreach (\$inputs as \$property => \$value) {
        $%s = %s::create();
        $%s->{\$property} = \$value;
    }

    return $%s;
});/*}}}*/

if_get('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    return $%s;
});/*}}}*/

if_post('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    %s

    foreach (\$inputs as \$property => \$value) {
        $%s->{\$property} = \$value;
    }

    return $%s;
});/*}}}*/

if_delete('/%s/*', function ($%s)
{/*{{{*/
    $%s = dao('%s')->find($%s);
    otherwise($%s->is_not_null(), '%s not found');

    $%s->delete();

    return $%s;
});/*}}}*/";

    return sprintf($content, 
        $resource_plural,
        $input_content,
        $table,

        $resource_plural,
        $input_content,
        $table, $table,
        $table,
        $table,

        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $table,

        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $input_content,
        $table,
        $table,

        $resource_plural, $resource_id_key,
        $table, $table, $resource_id_key,
        $table, $table,
        $table,
        $table
    );
}

command('controller:make-restful-from-db', '初始化 controller', function ()
{/*{{{*/
    $table = command_paramater('table_name');

    $schema_infos = db_query("show create table `$table`");
    $schema_info = reset($schema_infos);

    $entity_structs = $entity_relationships = [];

    foreach (explode("\n", $schema_info['Create Table']) as $line) {
        $line = trim($line);

        if (stristr($line, 'CREATE TABLE')) continue;
        if (stristr($line, 'PRIMARY KEY')) continue;
        if (stristr($line, ') ENGINE=')) continue;
        if (stristr($line, '`id`')) continue;
        if (stristr($line, '`version`')) continue;
        if (stristr($line, '`create_time`')) continue;
        if (stristr($line, '`update_time`')) continue;
        if (stristr($line, '`delete_time`')) continue;


        preg_match('/^`(.*)`/', $line, $matches);
        if ($matches) {
            $entity_structs[] = $matches[1];
            continue;
        }

        preg_match('/^KEY.*\(`(.*)`\)/', $line, $matches);
        if ($matches) {
            $relate_to = str_replace('_id', '', $matches[1]);
            $entity_relationships[] = $relate_to;
        }
    }

    error_log(_generate_controller_file($table, $entity_structs, $entity_relationships), 3, $file = CONTROLLER_DIR.'/'.$table.'.php');
    echo $file."\n";
});/*}}}*/
