if_post('/{{ english_word_pluralize($entity_name) }}/delete/*', function (${{ $entity_name }}_id)
{
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} not found');

    ${{ $entity_name }}->delete();

    return [
        'code' => 0,
        'msg' => '',
    ];
});
