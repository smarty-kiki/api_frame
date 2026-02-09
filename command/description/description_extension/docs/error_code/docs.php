[^_^]: {{ $entity_name }}_start

### {{ $entity_info['description'] }}

|错误码值|错误码描述|
|----|----|
|{{ strtoupper($entity_name.'_NOT_FOUND') }}|未找到 {{ $entity_name }} [id:]|
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['require'])
|{{ strtoupper($entity_name.'_REQUIRE_'.$struct_name) }}|未传入 {{ $struct_name }}|
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
|{{ strtoupper($entity_name.'_DUPLICATED') }}|已经存在相同{{ implode('和', $msg_infos) }}的{{ $entity_info['display_name'] }} [ID: :{{ $entity_name }}_id]|
@endif

[^_^]: {{ $entity_name }}_end

[^_^]: more
