if_get('/{{ english_word_pluralize($entity_name) }}/update/*', function (${{ $entity_name }}_id)
{/*{^{^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} not found');

    return render('{{ $entity_name }}/update', [
        '{{ $entity_name }}' => ${{ $entity_name }},
    ]);
});/*}^}^}*/

if_post('/{{ english_word_pluralize($entity_name) }}/update/*', function (${{ $entity_name }}_id)
{/*{^{^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} not found');

@foreach ($entity_structs as $struct)
    ${{ $entity_name }}->{{ $struct }} = input('{{ $struct }}');
@endforeach

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}^}^}*/
