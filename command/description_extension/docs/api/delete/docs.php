








### 删除{{ $entity_info['display_name'] }} 
----
**功能：**删除{{ $entity_info['display_name'] }}  
**请求方式：**`POST`  
**请求地址：**  
```
/{{ english_word_pluralize($entity_name) }}/delete/{^^{^^{{ $entity_name }}_id}^^}^^  
```
**`URL`中的变量：**  

|变量键名|类型|必传|描述|
|----|----|----|----|
|{{ $entity_name }}_id|id|必传|{{ $entity_info['display_name'] }}的主键，`id`|

**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "data": true
}

```



