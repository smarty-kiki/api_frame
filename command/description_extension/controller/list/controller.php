if_get('/{{ english_word_pluralize($entity_name) }}', function ()
{/*{^{^{*/
    list(
        {{ implode(', ', array_map(function($v) { return "\$inputs['".$v."']"; }, $entity_structs)) }}

    ) = input_list(
        {{ implode(', ', array_map(function($v) { return "'".$v."'"; }, $entity_structs)) }}

    );
    $inputs = array_filter($inputs, 'not_null');

    ${{ english_word_pluralize($entity_name) }} = dao('{{ $entity_name }}')->find_all_by_column($inputs);

    return [
        'code' => 0,
        'msg'  => '',
        'count' => count(${{ english_word_pluralize($entity_name) }}),
        'data' => array_build(${{ english_word_pluralize($entity_name) }}, function ($id, ${{ $entity_name }}) {
            return [
                null,
                [
                    'id' => ${{ $entity_name }}->id,
@foreach ($entity_name::$struct_types as $struct => $type)
                    '{{ $struct }}' => {{ blade_eval(_generate_controller_struct_list($type), ['entity_name' => $entity_name, 'struct' => $struct]) }},
@endforeach
                    'create_time' => ${{ $entity_name }}->create_time,
                    'update_time' => ${{ $entity_name }}->update_time,
                ]
            ];
        }),
    ];
});/*}^}^}*/
