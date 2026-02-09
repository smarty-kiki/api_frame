if_get('/{{ english_word_pluralize($entity_name) }}/detail/*', function (${{ $entity_name }}_id)
{/*{^^{^^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise_error_code('{{ strtoupper($entity_name.'_NOT_FOUND') }}', ${{ $entity_name }}->is_not_null());

    return [
        'id' => ${{ $entity_name }}->id,
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_detail($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_display' => ${{ $entity_name }}->{{ $attribute_name }}->display_for_{{ $relationship['self_attribute_name'] }}_{{ $attribute_name }}(),
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => {{ blade_eval(_generate_controller_data_type_detail($struct['data_type']), ['entity_name' => $entity_name, 'struct_name' => $struct_name, 'struct' => $struct]) }},
@endforeach
@endforeach
@endif
@endforeach
        'create_time' => ${{ $entity_name }}->create_time,
        'update_time' => ${{ $entity_name }}->update_time,
    ];
});/*}}}*/
