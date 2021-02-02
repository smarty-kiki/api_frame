@php
$between_datetime_variable_name = '$between_'.$struct_group_info['name'].'_datetime';
@endphp
    {{ $between_datetime_variable_name }} = input('between_{{ $struct_group_info['name'] }}_datetime');
    if ($between_{{ $struct_group_info['name'] }}_datetime) {
        $inputs['{{ $struct_name_map['$(name)_start_time'] }} <='] = {{ $between_datetime_variable_name }};
        $inputs['{{ $struct_name_map['$(name)_end_time'] }} >='] = {{ $between_datetime_variable_name }};
    }
