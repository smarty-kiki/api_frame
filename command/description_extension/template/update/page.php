<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>{{ $entity_name::$entity_display_name }}[^{^{ ${{ $entity_name }}->id ^}^}]修改</title>
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
<tbody>

    <form action='' method='POST'>
@foreach ($entity_name::$struct_types as $struct => $type)
    <tr>
        <td>{{ array_key_exists($struct, $entity_name::$struct_display_names)? $entity_name::$struct_display_names[$struct]: $struct }}</td>
        <td>
            {{ blade_eval(_generate_template_struct_update($type), ['entity_name' => $entity_name, 'struct' => $struct]) }}
        </td>
    </tr>
@endforeach
    <tr>
        <td>
            <a href='javascript:window.history.back(-1);'>取消</a>
        </td>
        <td>
            <input type='submit' value='保存'>
        </td>
    </tr>
    </form>

</tbody>
</table>
</body>
<script>
</script>
</html>
