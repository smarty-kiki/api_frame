return [
    /* generated {{ $entity_name }} start */
    '{{ strtoupper($entity_name.'_NOT_FOUND') }}' => '未找到 {{ $entity_name }} [id::id]',
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['require'])
    '{{ strtoupper($entity_name.'_REQUIRE_'.$struct_name) }}' => '未传入 {{ $struct_name }}',
@endif
@endforeach
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$msg_infos = [];
foreach ($repeat_check_structs as $struct_name) {
    $msg_infos[] = $entity_info['structs'][$struct_name]['display_name'];
}
@endphp
    '{{ strtoupper($entity_name.'_DUPLICATED') }}' => '已经存在相同{{ implode('和', $msg_infos) }}的{{ $entity_info['display_name'] }} [ID: :{{ $entity_name }}_id]',
@endif
    /* generated {{ $entity_name }} end */

    //more
];
