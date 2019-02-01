<?php
/**
 * 实现在已有数据库表的情况下，将数据库的表转换成描述文件的功能
 *
 * @author 蒙韶颖 <mengshaoying@aliyun.com>
 */

/**
 * 将数据转换成数组结构
 *
 * @param string $type 字段类型，如varchar(255)
 * @param string $format 字段数据格式
 * @param string $format_description 描述文字
 * @param string $display_name 显示名称，文字描述
 * @param string $description 注释
 * @param bool $allow_null 是否允许为空
 * @param string $default 字段默认值
 * @return array
 */
function _build_yaml_structs_unit($type, $format, $format_description, $display_name, $description, $allow_null, $default)
{
    return array_filter([
        'type' => $type,
        'format' => $format,
        'format_description' => $format_description,
        'display_name' => $display_name,
        'description' => $description,
        'allow_null' => $allow_null,
        'default' => $default,
    ], 'not_null');
}

/**
 * 创建 yaml 文件
 *
 * 如果文件存在则中断脚本并提示文件存在。
 *
 * @param string $file 文件路径
 * @param array $structure 被转换的数据
 * @return bool 返回 true 表示转换成功
 */
function _build_yaml($file, $structure)
{
    if (is_file($file)) {
        echo 'warring, file "'.$file.'" exists!' . PHP_EOL;
        echo 'stop!' . PHP_EOL;
        die();
    }
    return yaml_emit_file($file, $structure, YAML_UTF8_ENCODING);
}

/**
 * 获取当前配置文件的数据库的所有表名
 *
 * @return array 1维数组，内容为所有表名称
 */
function _get_all_tables()
{
    $query_result = db_query('SHOW TABLES');
    $tables = [];
    foreach ($query_result as $arr_data) {
        foreach ($arr_data as $table_name) {
            $tables[] = $table_name;
        }
    }
    return $tables;
}

/**
 * 获取指定的表的注释
 *
 * @param string $table
 * @return string
 */
function _get_table_comment($table)
{
    $config = config('mysql');
    $sql = 'SELECT `TABLE_COMMENT` AS `comment` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=:db_name AND `TABLE_NAME`=:table';
    return db_query_value('comment', $sql, [':table' => $table, ':db_name' => $config['default']['database']]);
}

/**
 * 获取指定的表的所有字段信息
 *
 * @param string $table
 * @return array
 */
function _get_query_columns_datas($table)
{
    $config = config('mysql');
    $sql = 'SELECT `COLUMN_NAME`,`COLUMN_DEFAULT`,`IS_NULLABLE`,`DATA_TYPE`,`CHARACTER_MAXIMUM_LENGTH`,`CHARACTER_SET_NAME`,`COLUMN_TYPE`,`COLUMN_COMMENT` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`=:db_name AND `TABLE_NAME`=:table';
    return db_query($sql, [':table' => $table, ':db_name' => $config['default']['database']]);
}

/**
 * 根据指定的表名称，生成它的 yaml 文件
 *
 * 中间发生文件已存在或生成文件失败则中断脚本并提示信息
 *
 * @param string $table
 * @return void
 */
function _create_yaml_file($table)
{
    echo 'create a yaml file from table: "' . $table . '"' . PHP_EOL;
    $structure = [];
    $structure['display_name'] = _get_table_comment($table);
    $structure['description'] = $structure['display_name'];
    foreach (_get_query_columns_datas($table) as $column_data) {
        if (in_array($column_data['COLUMN_NAME'], ['id', 'create_time', 'update_time', 'delete_time', 'version'])) {
            continue;
        }
        $structure['structs'][$column_data['COLUMN_NAME']] = _build_yaml_structs_unit(
            $column_data['COLUMN_TYPE'],
            null,
            $column_data['COLUMN_COMMENT'],
            $column_data['COLUMN_COMMENT'],
            $column_data['COLUMN_COMMENT'],
            $column_data['IS_NULLABLE'] != 'NO',
            $column_data['COLUMN_DEFAULT']
        );
    }
    $structure['relationships'] = [];
    $structure['snaps'] = [];
    $file = DESCRIPTION_DIR . DIRECTORY_SEPARATOR . $table . '.yml';
    if (true === _build_yaml($file, $structure)) {
        echo 'build "'.$file.'" success.' . PHP_EOL;
    } else {
        echo 'build "'.$file.'" fail!' . PHP_EOL;
        echo 'stop!' . PHP_EOL;
        die();
    }
}

/**
 * 根据已有的数据库表生成 yaml 描述文件
 *
 * 自动排除 migrations 表，如果这张表存在的话
 */
command('reverse', '将数据库的表转换成实体描述文件', function ()
{
    echo 'Directory:' . DESCRIPTION_DIR . PHP_EOL;
    echo 'Start...' . PHP_EOL;
    $tables = _get_all_tables();
    foreach ($tables as $table) {
        if ($table == 'migrations') {
            continue;
        }
        _create_yaml_file($table);
    }
    echo 'Finish.' . PHP_EOL;
});
