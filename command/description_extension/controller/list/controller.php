if_get('/{{ english_word_pluralize($entity_name) }}', function ()
{
    list(
        {{ implode(', ', array_map(function($v) { return "\$inputs['".$v."']"; }, $entity_structs)) }}

    ) = input_list(
        {{ implode(', ', array_map(function($v) { return "'".$v."'"; }, $entity_structs)) }}

    );
    $inputs = array_filter($inputs, 'not_null');

    return render('{{ $entity_name }}/list', [
        '{{ english_word_pluralize($entity_name) }}' => dao('{{ $entity_name }}')->find_all_by_column($inputs),
    ]);
});
