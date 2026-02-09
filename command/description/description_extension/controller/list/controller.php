if_get('/{{ english_word_pluralize($entity_name) }}', function ()
{/*{^^{^^{*/
@php
$inputs = [];

foreach ($entity_info['structs'] as $struct_name => $struct) {
    $inputs[] = $struct_name;
}

foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {

    if ($relationship['relationship_type'] === 'belongs_to') {
        $inputs[] = $attribute_name.'_id';
    }

    foreach ($relationship['snaps'] as $structs) {
        foreach ($structs as $struct_name => $struct) {
            $inputs[] = $struct_name;
        }
    }
}
@endphp
    list(
        {{ implode(', ', array_map(function($v) { return "\$inputs['".$v."']"; }, $inputs)) }}

    ) = input_list(
        {{ implode(', ', array_map(function($v) { return "'".$v."'"; }, $inputs)) }}

    );

@foreach ($entity_info['struct_groups'] as $struct_group)
{{ blade_eval(_generate_controller_struct_group_list($struct_group['type']), ['struct_group_info' => $struct_group['struct_group_info'], 'structs' => $struct_group['structs'], 'struct_name_map' => $struct_group['struct_name_maps']]) }}

@endforeach
    $inputs = array_filter($inputs, 'not_null');

    ${{ english_word_pluralize($entity_name) }} = dao('{{ $entity_name }}')->find_all_by_column($inputs);

    return [
        'count' => count(${{ english_word_pluralize($entity_name) }}),
        '{{ english_word_pluralize($entity_name) }}' => array_build(${{ english_word_pluralize($entity_name) }}, function ($id, ${{ $entity_name }}) {
            return [
                null,
                [
                    'id' => ${{ $entity_name }}->id,
@foreach ($entity_info['structs'] as $struct_name => $struct)
                    '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_list($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
                    '{{ $attribute_name }}_display' => ${{ $entity_name }}->{{ $attribute_name }}->display_for_{{ $relationship['self_attribute_name'] }}_{{ $attribute_name }}(),
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
                    '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_list($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@endforeach
@endif
@endforeach
                    'create_time' => ${{ $entity_name }}->create_time,
                    'update_time' => ${{ $entity_name }}->update_time,
                ]
            ];
        }),
    ];
});/*}^^}^^}*/
