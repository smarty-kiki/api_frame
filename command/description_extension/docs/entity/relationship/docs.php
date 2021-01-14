这里展示整个项目的所有实体的 `E-R` 图:  

```mermaid
erDiagram
@foreach ($all_relationship_infos as $entity_name => $relationship_infos)
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] !== 'belongs_to')
    {{ $entity_name }} ||--{{ $relationship['association_type'] === 'composition'? '|': 'o' }}{{ $relationship['relationship_type'] === 'has_many'? '{':'|' }} {{ $relationship['entity'] }} : {{ english_word_camelize($relationship['association_type']) }}  
@endif
@endforeach
@endforeach
```
