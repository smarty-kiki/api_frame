if_get('/{{ english_word_pluralize($entity_name) }}/update/*', function (${{ $entity_name }}_id)
{/*{^^{^^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} not found');

    return render('{{ $entity_name }}/update', [
        '{{ $entity_name }}' => ${{ $entity_name }},
    ]);
});/*}}}*/

if_post('/{{ english_word_pluralize($entity_name) }}/update/*', function (${{ $entity_name }}_id)
{/*{^^{^^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} not found');

@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@php
$entity = $relationship['entity'];
@endphp
@if ($relationship['relationship_type'] === 'belongs_to')
    ${{ $entity_name }}->{{ $attritube_name }} = input_entity('{{ $entity }}', null, '{{ $attritube_name }}_id');
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
    ${{ $entity_name }}->{{ $struct_name }} = input('{{ $struct_name }}');
@endforeach

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/
