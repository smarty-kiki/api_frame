# up
create table if not exists `{{ $entity_name }}` (
    `id` bigint(20) unsigned not null,
    `version` int(11) not null,
    `create_time` datetime default null,
    `update_time` datetime default null,
    `delete_time` datetime default null,
@foreach ($entity_info['structs'] as $struct_name => $struct)
@php
$database_field = $struct['database_field'];
$null_str = '';
if (! $database_field['allow_null']) {
    $null_str = ' not null';
}

$default_str = '';
if (array_key_exists('default', $database_field)) {
    $default = $database_field['default'];

    if (is_string($default)) {
        $default_str = " default '$default'";
    } elseif (is_null($default)) {
        if ($database_field['allow_null']) {
            $default_str = " default null";
        }
    } else {
        $default_str = " default $default";
    }
} 
@endphp
    `{{ $struct_name }}` {{ $database_field['type'] }}{{ isset($database_field['length'])?'('.$database_field['length'].')':'' }}{{ $null_str.$default_str }},
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
    `{{ $attribute_name }}_id` bigint(20) unsigned not null,
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@php
$database_field = $struct['database_field'];
$null_str = '';
if (! $database_field['allow_null']) {
    $null_str = ' not null';
}

$default_str = '';
if (array_key_exists('default', $database_field)) {
    $default = $database_field['default'];

    if (is_string($default)) {
        $default_str = " default '$default'";
    } elseif (is_null($default)) {
        if ($database_field['allow_null']) {
            $default_str = " default null";
        }
    } else {
        $default_str = " default $default";
    }
} 
@endphp
    `{{ $struct_name }}` {{ $database_field['type'] }}{{ isset($database_field['length'])?'('.$database_field['length'].')':'' }}{{ $null_str.$default_str }},
@endforeach
@endforeach
@endif
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@php
$entity = $relationship['entity'];
$relationship_type = $relationship['relationship_type'];
@endphp
@if ($relationship_type === 'belongs_to')
@if ($attribute_name === $entity)
    key `fk_{{ $attribute_name }}_idx` (`{{ $attribute_name }}_id`, `delete_time`),
@else
    key `fk_{{ $attribute_name }}_{{ $entity }}_idx` (`{{ $attribute_name }}_id`, `delete_time`),
@endif
@endif
@endforeach
@if ($entity_info['repeat_check_structs'])
@php
$repeat_check_structs = $entity_info['repeat_check_structs'];
@endphp
    key `idx_{{ implode('_and_', $repeat_check_structs) }}` (`{{ implode('`, `', $repeat_check_structs) }}`, `delete_time`),
@endif
    primary key (`id`)
) engine=innodb default charset=utf8mb4;

# down
drop table `{{ $entity_name }}`;
