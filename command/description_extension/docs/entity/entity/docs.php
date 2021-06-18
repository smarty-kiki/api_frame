


### 关联关系  

@if ($relationship_infos['relationships'])
@php
$diagram_infos = [
    'id', 'create_time', 'update_time', 'delete_time',
];
@endphp

与{{ $entity_info['display_name'] }}相关的类图:  
```mermaid
classDiagram
entity ..> JsonSerializable
entity ..> Serializable
{{ $entity_name }} --> entity
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
{{ $entity_name }} "{{ $relationship['reverse_relationship_type'] === 'has_many'? '*':'1' }}" <--{{ $relationship['association_type'] === 'composition'? '*': 'o' }} "1" {{ $relationship['entity'] }} : {{ $attribute_name }}  
@php
$diagram_infos[] = $attribute_name.'_id';
@endphp
@else
{{ $entity_name }} "1" {{ $relationship['association_type'] === 'composition'? '*': 'o' }}--> "{{ $relationship['relationship_type'] === 'has_many'? '*':'1' }}" {{ $relationship['entity'] }} : {{ $attribute_name }}  
@endif
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@php
$diagram_infos[] = $struct_name;
@endphp
@endforeach
@endforeach
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
@php
$diagram_infos[] = $struct_name;
@endphp
@endforeach
@foreach ($diagram_infos as $diagram_info)
{{ $entity_name }} : +{{ $diagram_info }}  
@endforeach
```

@endif




@if ($relationship_infos['relationships'])
@php
$diagram_infos = [
    'id' => 'id',
    'create_time' => 'datetime',
    'update_time' => 'datetime',
    'delete_time' => 'datetime',
];
@endphp

相关的 `E-R` 图:  
```mermaid
erDiagram
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
    {{ $entity_name }} {{ $relationship['reverse_relationship_type'] === 'has_many'? '}': '|' }}{{ $relationship['association_type'] === 'composition'? '|': 'o' }}--|| {{ $relationship['entity'] }} : {{ $attribute_name }}  
@php
$diagram_infos[$attribute_name.'_id'] = 'id';
@endphp
@else
    {{ $entity_name }} ||--{{ $relationship['association_type'] === 'composition'? '|': 'o' }}{{ $relationship['relationship_type'] === 'has_many'? '{':'|' }} {{ $relationship['entity'] }} : {{ $attribute_name }}  
@endif
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@php
$diagram_infos[$struct_name] = $struct['data_type'];
@endphp
@endforeach
@endforeach
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
@php
$diagram_infos[$struct_name] = $struct['data_type'];
@endphp
@endforeach
    {{ $entity_name }} {
@foreach ($diagram_infos as $struct_name => $struct_type)
        {{ $struct_type }} {{ $struct_name }}  
@endforeach
    }
```

@endif



### 实体属性

这里是指{{ $entity_info['display_name'] }}在编码过程中可以被直接调用的属性，其中 `必要` 是指在{{ $entity_info['display_name'] }}创建时，是否必须要有的属性，可选属性可在创建{{ $entity_info['display_name'] }}后再赋值。  
**属性表:**   

|属性键名|数据类型|必要|名称|描述|
|----|----|----|----|----|
|id|id|无需|主键|主键会自动生成，无需赋值|
|create_time|datetime|无需|创建时间|会自动生成，无需赋值|
|update_time|datetime|无需|更新时间|会自动更新，无需赋值，创建时与 `create_time` 一致|
|delete_time|datetime|无需|删除时间|会自动维护，无需赋值|
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
|{{ $attribute_name }}|[{{ $relationship['entity'] }}](entity/{{ $relationship['entity'] }}.md)|{{ $relationship['association_type'] === 'composition'?'必传':'可选' }}|关联关系|{{ $entity_info['display_name'] }}所属的{{ $relationship['entity_display_name'] }}|
|{{ $attribute_name }}_id|id|无需|外键|{{ $entity_info['display_name'] }}所属的{{ $relationship['entity_display_name'] }}，此处为{{ $relationship['entity_display_name'] }}的`id`|
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
|{{ $struct_name }}|{{ $struct['data_type'] }}|无需|{{ $struct['display_name'] }}|会在 `{{ $attribute_name }}` 设置时自动维护|
@endforeach
@endforeach
@else
|{{ $attribute_name }}|[{{ $relationship['entity'] }}](entity/{{ $relationship['entity'] }}.md)|可选|关联关系|{{ $entity_info['display_name'] }}拥有的{{ $relationship['entity_display_name'] }}{{ $relationship['relationship_type'] === 'has_many'? ('，是包含 `'.$relationship['entity'].'` 的数组'):'' }}|
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
|{{ $struct_name }}|{{ $struct['data_type'] }}|无需|{{ $struct['display_name'] }}|会在 `{{ $attribute_name }}` 设置时自动维护|
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
|{{ $struct_name }}|{{ $struct['data_type'] }}|{{ $struct['require']?'必传':'可选' }}|{{ $struct['display_name'] }}|{{ $struct['description'] }}|
@endforeach




### 常量

@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['data_type'] === 'enum')
属性 `{{ $struct_name }}` 相关的常量：  

|常量键名|常量值|常量含义|
|----|----|----|
@foreach ($struct['formater'] as $value => $description)
|{{ $entity_name }}::{{ strtoupper($struct_name.'_'.$value) }}|{{ strtoupper($value) }}|{{ $entity_info['display_name'] }}{{ $struct['display_name'] }}{{ $description}}|
@endforeach

实体中提供的与这些变量相关的方法：  

|方法|作用|
|----|----|
|${{ $entity_name }}->get_{{ $struct_name }}_description()|获取{{ $entity_info['display_name'] }}的当前{{ $struct['display_name'] }}的文字描述|
@foreach ($struct['formater'] as $value => $description)
|${{ $entity_name }}->{{ $struct_name }}_is_{{ strtolower($value) }}()|判断当前{{ $struct['display_name'] }}是否是 `{{ $description }}`|
|${{ $entity_name }}->set_{{ $struct_name }}_{{ strtolower($value) }}()|设置当前{{ $struct['display_name'] }}为 `{{ $description }}`|
@endforeach


@endif
@endforeach



