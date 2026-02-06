@php
$param_infos = [];
$post_params = [];
foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['relationship_type'] === 'belongs_to') {
        if ($relationship['require']) {
            $param_infos[] = '$'.$attribute_name.'_id = null';
            $post_params[] = $attribute_name.'_id';
        }
    }
}
foreach ($entity_info['structs'] as $struct_name => $struct) {
    $param_infos[] = '$'.$struct_name.' = null';
    $post_params[] = $struct_name;
}
@endphp
function {{ $system_name }}_{{ english_word_pluralize($entity_name) }}_list({{ implode(', ', $param_infos) }})
{/*{^^{^^{*/
    $config = config('{{ $system_name }}');

    $res = http_json([
        'url' => $config['host'].'/{{ english_word_pluralize($entity_name) }}',
        'method' => 'GET',
        'data' =>  [
@foreach ($post_params as $post_param)
            '{{ $post_param }}' => ${{ $post_param }},
@endforeach
        ]
    ]);

    otherwise(
        isset($res['code']) && $res['code'] === 0, 
        $res['msg']
    );

    return $res['data'];
}/*}^^}^^}*/
