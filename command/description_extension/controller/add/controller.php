if_get('/{{ english_word_pluralize($entity_name) }}/add', function ()
{
    return render('{{ $entity_name }}/add');
});

if_post('/{{ english_word_pluralize($entity_name) }}/add', function ()
{
    ${{ $entity_name }} = {{ $entity_name }}::create();

@foreach ($entity_structs as $struct)
    ${{ $entity_name }}->{{ $struct }} = input('{{ $struct }}');
@endforeach

    return redirect('/{{ english_word_pluralize($entity_name) }}');
});
