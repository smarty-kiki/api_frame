@foreach ($entity_info['structs'] as $struct_name => $struct)
    ${{ $struct_name }} = input('{{ $struct_name }}');
@endforeach

    ${{ $entity_name }} = dao('{{ $entity_name }}')->find(${{ $entity_name }}_id);
    otherwise(${{ $entity_name }}->is_not_null(), '{{ $entity_name }} 不存在');

@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$param_infos = [];
$msg_infos = [];
foreach ($repeat_check_structs as $struct_name) {
    $param_infos[] = "$$struct_name";
    $msg_infos[] = $entity_info['structs'][$struct_name]['display_name'];
}
@endphp
    $another_{{ $entity_name }} = dao('{{ $entity_name }}')->find_by_{{ implode('_and_', $repeat_check_structs) }}({{ implode(', ', $param_infos) }});
    otherwise($another_{{ $entity_name }}->is_null() || $another_{{ $entity_name }}->id === ${{ $entity_name }}->id, '已经存在相同{{ implode('和', $msg_infos) }}的{{ $entity_info['display_name'] }} [ID: '.$another_{{ $entity_name }}->id.']');
@endif

@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@php
$entity = $relationship['entity'];
@endphp
@if ($relationship['relationship_type'] === 'belongs_to')
@if ($relationship['association_type'] === 'composition')
    ${{ $entity_name }}->{{ $attribute_name }} = input_entity('{{ $entity }}', null, '{{ $attribute_name }}_id');
@else
    ${{ $entity_name }}->{{ $attribute_name }} = dao('{{ $entity }}')->find(input('{{ $attribute_name }}_id'));
@endif
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
    ${{ $entity_name }}->{{ $struct_name }} = ${{ $struct_name }};
@endforeach

    return [
        'code' => 0,
        'msg' => '',
    ];
});/*}}}*/











### 修改{{ $entity_info['display_name'] }} 
----
**功能：**修改{{ $entity_info['display_name'] }}  
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
$msg_infos = [];
foreach ($repeat_check_structs as $struct_name) {
    $msg_infos[] = $entity_info['structs'][$struct_name]['display_name'];
}
@endphp
**注：**不能同时存在{{ implode('和', $msg_infos) }}相同的{{ $entity_info['display_name'] }}  
@endif
**请求方式：**`POST`  
**请求地址：**  
```
/{{ english_word_pluralize($entity_name) }}/update/{^^{^^{{ $entity_name }}_id}^^}^^  
```
**`URL`中的变量：**  

|变量键名|类型|必传|描述|
|----|----|----|----|
|{{ $entity_name }}_id|id|必传|{{ $entity_info['display_name'] }}的主键，`id`|

**参数：**  

|参数键名|类型|必传|描述|
|----|----|----|----|
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
@if ($relationship['association_type'] === 'composition')
|{{ $attribute_name.'_id' }}|id|必传|设置{{ $entity_info['display_name'] }}所属的{{ $relationship['entity_display_name'] }}，此处传{{ $relationship['entity_display_name'] }}的`id`|
@else
|{{ $attribute_name.'_id' }}|id|可选|设置{{ $entity_info['display_name'] }}所属的{{ $relationship['entity_display_name'] }}，此处传{{ $relationship['entity_display_name'] }}的`id`|
@endif
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
|{{ $struct_name }}|{{ $struct['data_type'] }}|{{ $struct['require']? '必传':'可选' }}|{{ $struct['display_name'] }}|
@endforeach

**返回值：**  
```json
{
    "code": 0,
    "msg": "",
}
```



