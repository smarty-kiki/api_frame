if_post('/{{ english_word_pluralize($entity_name) }}/update/*', function (${{ $entity_name }}_id)
{/*{^^{^^{*/
@foreach ($entity_info['structs'] as $struct_name => $struct)
    ${{ $struct_name }} = input('{{ $struct_name }}');
@endforeach

    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise_error_code('{{ strtoupper($entity_name.'_NOT_FOUND') }}', ${{ $entity_name }}->is_not_null());
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$param_infos = [];
$msg_infos = [];
foreach ($repeat_check_structs as $struct_name) {
    $param_infos[] = "$$struct_name";
    $msg_infos[] = $entity_info['structs'][$struct_name]['display_name'];
}
@endphp

    $another_{{ $entity_name }} = dao('{{ $entity_name }}')->find_by_{{ implode('_and_', $repeat_check_structs) }}({{ implode(', ', $param_infos) }});
    otherwise_error_code('{{ strtoupper($entity_name.'_DUPLICATED') }}', $another_{{ $entity_name }}->is_null() || $another_{{ $entity_name }}->id === ${{ $entity_name }}->id, [':{{ $entity_name }}_id' => $another_{{ $entity_name }}->id]);
@endif

@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@php
$entity = $relationship['entity'];
@endphp
@if ($relationship['relationship_type'] === 'belongs_to')
@if ($relationship['require'])
    ${{ $entity_name }}->{{ $attribute_name }} = input_entity('{{ $entity }}', null, '{{ $attribute_name }}_id');
@else
    ${{ $entity_name }}->{{ $attribute_name }} = dao('{{ $entity }}')->find(input('{{ $attribute_name }}_id'));
@endif
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
    ${{ $entity_name }}->{{ $struct_name }} = ${{ $struct_name }};
@endforeach

    return true;
});/*}}}*/
