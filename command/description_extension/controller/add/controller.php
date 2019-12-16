if_post('/{{ english_word_pluralize($entity_name) }}/add', function ()
{/*{^^{^^{*/
@php
$param_infos = [];
$setting_lines = [];
foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['relationship_type'] === 'belongs_to') {
        if ($relationship['association_type'] === 'composition') {
            $param_infos[] = "input_entity('$entity', null, '$attribute_name"."_id')";
        } else {
            $setting_lines[] = "$$entity_name->$attribute_name = dao('$entity')->find('{$attribute_name}_id')";
        }
    }
}
foreach ($entity_info['structs'] as $struct_name => $struct) {
    if ($struct['require']) {
        $param_infos[] = "input('$struct_name')";
    } else {
        $setting_lines[] = "$$entity_name->$struct_name = input('$struct_name')";
    }
}
@endphp
@if (empty($param_infos))
    ${{ $entity_name }} = {{ $entity_name }}::create();
@else
    ${{ $entity_name }} = {{ $entity_name }}::create(
        {{ implode(",\n        ", $param_infos)."\n" }}
    );
@endif
@if (! empty($setting_lines))

@foreach ($setting_lines as $setting_line)
    {{ $setting_line }};
@endforeach
@endif

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/
