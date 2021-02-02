    public function between_{{ $struct_group_info['name'] }}_closed_time_interval($datetime)
    {/*^^{^^{^^{*/
        return $this->{{ $struct_name_map['$(name)_start_time'] }} <= $datetime
            && $this->{{ $struct_name_map['$(name)_end_time'] }}   >= $datetime;
    }/*}}}*/
