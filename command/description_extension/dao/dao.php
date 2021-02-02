class {{ $entity_name }}_dao extends dao
{
    protected $table_name = '{{ $entity_name }}';
    protected $db_config_key = '{{ unit_of_work_db_config_key() }}';

    /* generated code start */
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$param_infos = [];
$column_lines = [];
foreach ($repeat_check_structs as $struct_name) {
    $param_infos[] = "$$struct_name";
    $column_lines[] = "'$struct_name' => $$struct_name,";
}
@endphp
    public function find_by_{{ implode('_and_', $repeat_check_structs) }}({{ implode(', ', $param_infos) }})
    {/*^^{^^{^^{*/
        return $this->find_by_column([
@foreach ($column_lines as $column_line)
            {{ $column_line }}

@endforeach
        ]);
    }/*}}}*/
@endif
@if (not_empty($entity_info['struct_groups']))
@foreach ($entity_info['struct_groups'] as $struct_group)

{{ blade_eval(_generate_dao_struct_group_type($struct_group['type']), ['struct_group_info' => $struct_group['struct_group_info'], 'structs' => $struct_group['structs'], 'struct_name_map' => $struct_group['struct_name_maps']]) }}
@endforeach
@endif
    /* generated code end */
}
