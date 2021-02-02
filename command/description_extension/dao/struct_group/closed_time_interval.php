    public function find_all_between_{{ $struct_group_info['name'] }}_closed_time_interval($datetime)
    {/*^^{^^{^^{*/
        return $this->find_all_by_column([
            '{{ $struct_name_map['$(name)_start_time'] }} <=' => $datetime,
            '{{ $struct_name_map['$(name)_end_time'] }} >='   => $datetime,
        ]);
    }/*}}}*/
