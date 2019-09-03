<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>{{ $entity_name::$entity_display_name }}</title>
    <style>
     table {
         font-family: verdana,arial,sans-serif;
         font-size:11px;
         color:#333333;
         border-width: 1px;
         border-color: #666666;
         border-collapse: collapse;
         width: 100%;
     }
     table th {
         border-width: 1px;
         padding: 8px;
         border-style: solid;
         border-color: #666666;
         background-color: #dedede;
         text-align: center;
     }
     table td {
         border-width: 1px;
         padding: 8px;
         border-style: solid;
         border-color: #666666;
         background-color: #ffffff;
         text-align: center;
     }
    </style>
</head>
<body>
<table>
<thead>
    <tr>
        <th>ID</th>
@foreach ($entity_name::$struct_types as $struct => $type)
        <th>{{ array_key_exists($struct, $entity_name::$struct_display_names)? $entity_name::$struct_display_names[$struct]: $struct }}</th>
@endforeach
        <th>
            <a href='/{{ english_word_pluralize($entity_name) }}/add'>添加</a>
        </th>
    </tr>
</thead>
    @^foreach (${{ english_word_pluralize($entity_name) }} as $id => ${{ $entity_name }})
    <tr>
        <td>^{^{ $id ^}^}</td>
@foreach ($entity_name::$struct_types as $struct => $type)
        <td>
            {{ blade_eval(_generate_template_struct_list($type), ['entity_name' => $entity_name, 'struct' => $struct]) }}
        </td>
@endforeach
        <td>
            <a href='/{{ english_word_pluralize($entity_name) }}/update/^{^{ ${{ $entity_name }}->id ^}^}'>修改</a>
            <a href='javascript:delete_^{^{ ${{ $entity_name }}->id ^}^}.submit();'>删除</a>
            <form id='delete_^{^{ ${{ $entity_name }}->id ^}^}' action='/{{ english_word_pluralize($entity_name) }}/delete/^{^{ ${{ $entity_name }}->id ^}^}' method='POST'></form>
        </td>
    </tr>
    @^endforeach
<tbody>
</tbody>
</table>
</body>
</html>
