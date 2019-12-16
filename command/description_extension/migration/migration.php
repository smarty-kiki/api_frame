# up
CREATE TABLE IF NOT EXISTS `{{ $entity_name }}` (
    `id` bigint(20) UNSIGNED NOT NULL,
    `version` int(11) NOT NULL,
    `create_time` datetime DEFAULT NULL,
    `update_time` datetime DEFAULT NULL,
    `delete_time` datetime DEFAULT NULL,
@foreach ($entity_info['structs'] as $struct_name => $struct)
@php
$database_field = $struct['database_field'];
$null_str = '';
if (! $database_field['allow_null']) {
    $null_str = ' NOT NULL';
}

$default_str = '';
if (array_key_exists('default', $database_field)) {
    $default = $database_field['default'];

    if (is_string($default)) {
        $default_str = " DEFAULT '$default'";
    } elseif (is_null($default)) {
        if ($database_field['allow_null']) {
            $default_str = " DEFAULT NULL";
        }
    } else {
        $default_str = " DEFAULT $default";
    }
} 
@endphp
    `{{ $struct_name }}` {{ $database_field['type'] }}{{ isset($database_field['length'])?'('.$database_field['length'].')':'' }}{{ $null_str.$default_str }},
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
    `{{ $attribute_name }}_id` bigint(20) UNSIGNED NOT NULL,
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@php
$database_field = $struct['database_field'];
$null_str = '';
if (! $database_field['allow_null']) {
    $null_str = ' NOT NULL';
}

$default_str = '';
if (array_key_exists('default', $database_field)) {
    $default = $database_field['default'];

    if (is_string($default)) {
        $default_str = " DEFAULT '$default'";
    } elseif (is_null($default)) {
        if ($database_field['allow_null']) {
            $default_str = " DEFAULT NULL";
        }
    } else {
        $default_str = " DEFAULT $default";
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
    KEY `fk_{{ $attribute_name }}_idx` (`{{ $attribute_name }}_id`, `delete_time`),
@else
    KEY `fk_{{ $attribute_name }}_{{ $entity }}_idx` (`{{ $attribute_name }}_id`, `delete_time`),
@endif
@endif
@endforeach
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# down
drop table `{{ $entity_name }}`;
