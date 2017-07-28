<?php

function _mongodb_connection($host, $port, $database)
{/*{{{*/
    static $container = [];

    $identifier = "mongodb://$host:$port";

    if (! isset($container[$identifier])) {

        $connection = new MongoDB\Client($identifier);

        $container[$identifier] = $connection->{$database};
    }

    return $container[$identifier];
}/*}}}*/

function _mongodb_database_closure($config_key, closure $closure)
{/*{{{*/
    static $configs = [];

    if (empty($configs)) {
        $configs = config('mongodb');
    }

    $config = $configs[$config_key];

    $connection = _mongodb_connection(
        $config['host'],
        $config['port'],
        $config['database']
    );

    return call_user_func($closure, $connection);
}/*}}}*/

function storage_insert($table, array $data, $config_key = 'default')
{/*{{{*/
    return _mongodb_database_closure($config_key, function ($connection) use ($table, $data) {

        $collection = $connection->{$table};

        return $collection->insert($data);
    });
}/*}}}*/

function storage_find($table, array $query = [], $config_key = 'default')
{/*{{{*/
    return _mongodb_database_closure($config_key, function ($connection) use ($table, $query) {

        $collection = $connection->{$table};

        return $collection->find($query);
    });
}/*}}}*/

function storage_update($table, $query = [], array $new_data, $config_key = 'default')
{/*{{{*/
    return _mongodb_database_closure($config_key, function ($connection) use ($table, $query, $new_data) {

        $collection = $connection->{$table};

        return $collection->update($query, $new_data);
    });
}/*}}}*/

function storage_find_one($table, array $query = [], $config_key = 'default')
{/*{{{*/
    return _mongodb_database_closure($config_key, function ($connection) use ($table, $query) {

        $collection = $connection->{$table};

        return $collection->findOne($query);
    });
}/*}}}*/

function storage_remove($table, array $query = [], $config_key = 'default')
{/*{{{*/
    return _mongodb_database_closure($config_key, function ($connection) use ($table, $query) {

        $collection = $connection->{$table};

        return $collection->remove($query);
    });
}/*}}}*/
