<?php

function unit_of_work_system_code($system_code = null)
{
    static $container = null;

    if (!is_null($system_code)) {
        $container = $system_code;
    }

    return $container;
}

function unit_of_work(Closure $action)
{
    local_cache_clean_all();

    try {
        $res = $action();

        $entities = local_cache_get_all();
    } finally {
        local_cache_clean_all();
    }

    $sqls = [];

    foreach ($entities as $entity) {
        if ($entity->get_system_code() !== unit_of_work_system_code()) {
            continue;
        }

        $dao = $entity->get_dao();

        if ($entity->is_deleted()) {
            if (!$entity->is_new()) {
                $sqls[] = $dao->dump_delete_sql($entity);
            }
        } elseif ($entity->is_new()) {
            $sqls[] = $dao->dump_insert_sql($entity);
        } elseif ($entity->is_updated()) {
            $sqls[] = $dao->dump_update_sql($entity);
        }
    }

    if ($sqls) {
        if (count($sqls) > 1) {
            transaction(function () use ($sqls) {
                foreach ($sqls as $sql) {
                    db_execute($sql['sql_template'], $sql['binds']);
                }
            });
        } else {
            $sql = reset($sqls);
            db_execute($sql['sql_template'], $sql['binds']);
        }
    }

    return $res;
}

function generate_id($mark = '')
{
    static $step = 1;

    static $now_id;
    static $step_last_id;

    if ($now_id == $step_last_id) {
        $step_last_id = cache_increment($mark.'_last_id', $step, 0, 'idgenter');
        $now_id = $step_last_id - $step;
    }

    return $now_id += 1;
}
