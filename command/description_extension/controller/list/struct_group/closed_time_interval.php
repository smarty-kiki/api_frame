    $between_{{ $struct_group_info['name'] }}_closed_time_interval = input('between_{{ $struct_group_info['name'] }}_closed_time_interval');
    $inputs['{{ $struct_name_map['$(name)_start_time'] }} <='] = $between_{{ $struct_group_info['name'] }}_closed_time_interval;
    $inputs['{{ $struct_name_map['$(name)_end_time'] }} >='] = $between_{{ $struct_group_info['name'] }}_closed_time_interval;
