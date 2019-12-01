# up
CREATE TABLE `{{ $entity_name }}` (
    `id` bigint(20) UNSIGNED NOT NULL,
    `version` int(11) NOT NULL,
    `create_time` datetime DEFAULT NULL,
    `update_time` datetime DEFAULT NULL,
    `delete_time` datetime DEFAULT NULL,
@foreach ($entity_structs as $struct)
@php
$null_str = '';
if (! $struct['allow_null']) {
    $null_str = ' NOT NULL';
}

$default_str = '';
if (array_key_exists('default', $struct)) {
    $default = $struct['default'];

    if (is_string($default)) {
        $default_str = " DEFAULT '$default'";
    } elseif (is_null($default)) {
        if ($struct['allow_null']) {
            $default_str = " DEFAULT NULL";
        }
    } else {
        $default_str = " DEFAULT $default";
    }
} 
@endphp
    `{{ $struct['name'] }}` {{ $struct['datatype'] }}{{ $null_str.$default_str }},
@endforeach
@foreach ($entity_relationships as $relationship)
@if ($relationship['type'] === 'belongs_to')
    `{{ $relationship['relation_name'] }}_id` bigint(20) UNSIGNED NOT NULL,
@endif
@endforeach
@foreach ($entity_relationships as $relationship)
@if ($relationship['type'] === 'belongs_to')
@if ($relationship['relation_name'] === $relationship['relate_to'])
    KEY `fk_{{ $relationship['relation_name'] }}_idx` (`{{ $relationship['relation_name'] }}_id`, `delete_time`),
@else
    KEY `fk_{{ $relationship['relation_name'] }}_{{ $relationship['relate_to'] }}_idx` (`{{ $relationship['relation_name'] }}_id`, `delete_time`),
@endif
@endif
@endforeach
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# down
drop table `{{ $entity_name }}`;
