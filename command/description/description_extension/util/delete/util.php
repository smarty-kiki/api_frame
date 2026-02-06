function {{ $system_name }}_{{ english_word_pluralize($entity_name) }}_delete(${{ $entity_name }}_id)
{/*{^^{^^{*/
    $config = config('{{ $system_name }}');

    $res = http_json([
        'url' => $config['host'].'/{{ english_word_pluralize($entity_name) }}/delete/'.${{ $entity_name }}_id,
        'method' => 'POST',
    ]);

    otherwise(
        isset($res['code']) && $res['code'] === 0, 
        $res['msg']
    );

    return $res['data'];
}/*}^^}^^}*/
