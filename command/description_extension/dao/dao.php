class {{ $entity_name }}_dao extends dao
{
    protected $table_name = '{{ $entity_name }}';
    protected $db_config_key = '{{ unit_of_work_db_config_key() }}';
}
