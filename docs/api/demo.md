# Demo 接口  
demo 示例相关接口




### 新增 Demo  
----
**功能：**新增 demo  
**请求方式：**`POST`  
**请求地址：**  
```
/demos/add
```
**请求参数：**  
```json
{
    "name": "demo name",
    "note": "备注，可选"
}
```
**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "data": {
        "id": 1
    }
}
```







### Demo 列表  
----
**功能：**查询 demo 列表  
**请求方式：**`GET`  
**请求地址：**  
```
/demos
```
**请求参数（Query）：**  
```
name: demo name (可选)
note: 备注 (可选)
```
**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "count": 1,
    "demos": [
        {
            "id": 1,
            "name": "demo name",
            "note": "备注",
            "create_time": "2020-01-01 00:00:00",
            "update_time": "2020-01-01 00:00:00"
        }
    ]
}
```







### Demo 详情  
----
**功能：**查询 demo 详情  
**请求方式：**`GET`  
**请求地址：**  
```
/demos/detail/{demo_id}
```
**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "data": {
        "id": 1,
        "name": "demo name",
        "note": "备注",
        "create_time": "2020-01-01 00:00:00",
        "update_time": "2020-01-01 00:00:00"
    }
}
```







### 更新 Demo  
----
**功能：**更新 demo  
**请求方式：**`POST`  
**请求地址：**  
```
/demos/update/{demo_id}
```
**请求参数：**  
```json
{
    "name": "demo name",
    "note": "备注"
}
```
**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "data": []
}
```







### 删除 Demo  
----
**功能：**删除 demo  
**请求方式：**`POST`  
**请求地址：**  
```
/demos/delete/{demo_id}
```
**返回值：**  
```json
{
    "code": 0,
    "msg": "",
    "data": []
}
```
