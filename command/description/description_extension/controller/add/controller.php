if_post('/{{ english_word_pluralize($entity_name) }}/add', function ()
{/*{^^{^^{*/
@php
$input_infos = [];
$param_infos = [];
$setting_lines = [];
foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['relationship_type'] === 'belongs_to') {
        if ($relationship['require']) {
            $param_infos[] = "input_entity('$entity', '$attribute_name"."_id', true)";
        } else {
            $setting_lines[] = "\${$attribute_name} = input_entity('$entity', '$attribute_name"."_id');";
            $setting_lines[] = "if (\${$attribute_name}->is_not_null()) {";
            $setting_lines[] = "    \$new_$entity_name->$attribute_name = $$attribute_name;";
            $setting_lines[] = "}\n";
        }
    }
}
foreach ($entity_info['structs'] as $struct_name => $struct) {
    if ($struct['require']) {
        $input_infos[] = "$$struct_name = input_json('$struct_name');";
        $input_infos[] = "otherwise_error_code('".strtoupper($entity_name.'_REQUIRE_'.$struct_name)."', not_null($$struct_name));\n";
        $param_infos[] = "$$struct_name";
    } else {
        $input_infos[] = "$$struct_name = input_json('$struct_name');";
        $setting_lines[] = "if (not_null(\${$struct_name})) { \$new_{$entity_name}->$struct_name = \${$struct_name}; }";
    }
}
@endphp
@if ($input_infos)
@foreach ($input_infos as $input_info)
    {{ $input_info."\n" }}
@endforeach

@endif
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$dao_param_infos = [];
foreach ($repeat_check_structs as $struct_name) {
    $dao_param_infos[] = "$$struct_name";
}
@endphp
    $another_{{ $entity_name }} = dao('{{ $entity_name }}')->find_by_{{ implode('_and_', $repeat_check_structs) }}({{ implode(', ', $dao_param_infos) }});
    otherwise_error_code('{{ strtoupper($entity_name.'_DUPLICATED') }}', $another_{{ $entity_name }}->is_null(), [':{{ $entity_name }}_id' => $another_{{ $entity_name }}->id]);

@endif
@if (empty($param_infos))
    $new_{{ $entity_name }} = {{ $entity_name }}::create();

@else
    $new_{{ $entity_name }} = {{ $entity_name }}::create(
        {{ implode(",\n        ", $param_infos)."\n" }}
    );

@endif
@if (! empty($setting_lines))
@foreach ($setting_lines as $setting_line)
    {{ $setting_line."\n" }}
@endforeach

@endif
    return [
        'id' => $new_{{ $entity_name }}->id,
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_add($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_display' => $new_{{ $entity_name }}->{{ $attribute_name }}->display_for_{{ $relationship['self_attribute_name'] }}_{{ $attribute_name }}(),
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_add($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@endforeach
@endif
@endforeach
        'create_time' => $new_{{ $entity_name }}->create_time,
        'update_time' => $new_{{ $entity_name }}->update_time,
    ];
});/*}}}*/
