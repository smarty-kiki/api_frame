if_post('/{{ english_word_pluralize($entity_name) }}/delete/*', function (${{ $entity_name }}_id)
{/*{^^{^^{*/
    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise_error_code('{{ strtoupper($entity_name.'_NOT_FOUND') }}', ${{ $entity_name }}->is_not_null());

    ${{ $entity_name }}->delete();

    return [];
});/*}}}*/
