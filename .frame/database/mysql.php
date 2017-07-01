<?php

/**
 * get complete database config.
 *
 * @param array $config
 *
 * @return array
 */
function db_config_complete($config = null)
{
    static $defaults = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'password' => '',
        'database' => 'test',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ],

    ];

    if (!empty($config)) {
        return array_replace_recursive($defaults, $config);
    }

    return $defaults;
}

/**
 * Set or get default database config.
 *
 * @param array $config
 *
 * @return array
 */
function db_config($config = null)
{
    static $container = [];

    if (!empty($config)) {
        return $container = db_config_complete($config);
    }

    if (empty($container)) {
        return $container = db_config_complete();
    }

    return $container;
}

/**
 * db_connection.
 * 
 * @param mixed $config
 */
function db_connection($config)
{
    $host = $config['host'];
    $database = $config['database'];
    $port = $config['port'];
    $username = $config['username'];
    $password = $config['password'];
    $charset = $config['charset'];
    $collation = $config['collation'];
    $options = $config['options'];

    $dsn = "mysql:host={$host};dbname={$database};port={$port}";

    return db_pdo($dsn, $username, $password, $charset, $collation, $options);
}

function db_pdo($dsn, $username, $password, $charset, $collation, $options = [])
{
    static $container = [];

    $identifier = $dsn.'|'.$username.'|'.$password;

    if (!isset($container[$identifier])) {
        $connection = new PDO($dsn, $username, $password, $options);

        $connection->prepare("set names '{$charset}' collate '{$collation}'")->execute();

        $container[$identifier] = $connection;
    }

    return $container[$identifier];
}

function db_binds($sql_template, array $binds)
{
    $res_binds = [];

    foreach ($binds as $key => $value) {
        if (is_array($value)) {
            $subbind_keys = [];
            foreach ($value as $i => $sub_value) {
                $subbind_keys[] = $subbind_key = $key.$i.'p';
                $res_binds[$subbind_key] = $sub_value;
            }

            $sql_template = str_replace($key, '('.implode(',', $subbind_keys).')', $sql_template);
        } else {
            $res_binds[$key] = $value;
        }
    }

    return [$sql_template, $res_binds];
}

function db_execute($sql_template, $binds = [])
{
    $config = db_config();

    $connection = db_connection($config);

    list($sql_template, $binds) = db_binds($sql_template, (array) $binds);

    $st = $connection->prepare($sql_template);

    $st->execute((array) $binds);

    return [$st, $connection];
}

function db_query($sql_template, $binds = [])
{
    list($st) = db_execute($sql_template, $binds);

    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function db_query_first($sql_template, $binds = [])
{
    $sql_template = str_finish($sql_template, ' limit 1');

    list($st) = db_execute($sql_template, $binds);

    return $st->fetch(PDO::FETCH_ASSOC);
}

function db_query_column($column, $sql_template, $binds = [])
{
    $rows = db_query($sql_template, $binds);

    $res = [];

    foreach ($rows as $row) {
        $res[] = $row[$column];
    }

    return $res;
}

function db_query_value($value, $sql_template, $binds = [])
{
    $row = db_query_first($sql_template, $binds);

    return $row[$value];
}

function db_update($sql_template, $binds = [])
{
    list($st) = db_execute($sql_template, $binds);

    return $st->rowCount();
}

function db_delete($sql_template, $binds = [])
{
    list($st) = db_execute($sql_template, $binds);

    return $st->rowCount();
}

function db_insert($sql_template, $binds = [])
{
    list($st, $connection) = db_execute($sql_template, $binds);

    return $connection->lastInsertId();
}

function db_structure($sql)
{
    list($st, $connection) = db_execute($sql);

    return $st->rowCount();
}

function transaction(closure $action)
{
    $config = db_config();

    $connection = db_connection($config);

    $began = $connection->beginTransaction();

    if (!$began) {
        throw new Exception('can not start transaction');
    }

    try {
        $res = $action();

        $connection->commit();

        return $res;
    } catch (Exception $ex) {
        $connection->rollBack();

        throw $ex;
    }
}
