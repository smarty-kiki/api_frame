class {{ $entity_name }} extends entity
{
    /* generated code start */
    public $structs = [
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_id' => 0,
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@if (array_key_exists('default', $struct['database_field']))
@php
$struct_default = $struct['database_field']['default'];
@endphp
@if (is_string($struct_default))
        '{{ $struct_name }}' => '{{ $struct_default }}',
@elseif (is_null($struct_default))
        '{{ $struct_name }}' => '',
@else
        '{{ $struct_name }}' => {{ $struct_default }},
@endif
@else
        '{{ $struct_name }}' => '',
@endif
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if (array_key_exists('default', $struct['database_field']))
@php
$struct_default = $struct['database_field']['default'];
@endphp
@if (is_string($struct_default))
        '{{ $struct_name }}' => '{{ $struct_default }}',
@elseif (is_null($struct_default))
        '{{ $struct_name }}' => '',
@else
        '{{ $struct_name }}' => {{ $struct_default }},
@endif
@else
        '{{ $struct_name }}' => '',
@endif
@endforeach
    ];

    public static $struct_data_types = [
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_id' => 'number',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['data_type'] }}',
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['data_type'] }}',
@endforeach
    ];

    public static $struct_display_names = [
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_id' => '{{ $relationship['entity_display_name'] }}ID',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['display_name'] }}',
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['display_name'] }}',
@endforeach
    ];

@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['data_type'] === 'enum')

@foreach ($struct['validator'] as $value => $description)
    const {{ strtoupper($struct_name.'_'.$value) }} = '{{ strtoupper($value) }}';
@endforeach

    const {{ strtoupper($struct_name) }}_MAPS = [
@foreach ($struct['validator'] as $value => $description)
        self::{{ strtoupper($struct_name.'_'.$value) }} => '{{ $description }}',
@endforeach
    ];
@endif
@endforeach

    public static $struct_is_required = [
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attribute_name }}_id' => {{ $relationship['require']? 'true': 'false' }},
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => {{ $struct['require']? 'true': 'false' }},
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => {{ $struct['require']? 'true': 'false' }},
@endforeach
    ];

    public function __construct()
    {/*^^{^^{^^{*/
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@php
$entity = $relationship['entity'];
$self_entity_name = $relationship['self_attribute_name'];
$relationship_type = $relationship['relationship_type'];
@endphp
@if ($attribute_name === $entity)
        $this->{{ $relationship_type }}('{{ $attribute_name }}');
@else
@if ($relationship_type === 'belongs_to')
        $this->{{ $relationship_type }}('{{ $attribute_name }}', '{{ $entity }}', '{{ $attribute_name }}_id');
@else
@if ($self_entity_name === $entity_name)
        $this->{{ $relationship_type }}('{{ $attribute_name }}', '{{ $entity }}');
@else
        $this->{{ $relationship_type }}('{{ $attribute_name }}', '{{ $entity }}', '{{ $self_entity_name }}_id');
@endif
@endif
@endif
@endforeach
    }/*}}}*/

@php
$param_infos = [];
$setting_lines = [];
foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['relationship_type'] === 'belongs_to' && $relationship['require']) {
        $param_infos[] = "$entity $$attribute_name";
        $setting_lines[] = "$$entity_name->$attribute_name = $$attribute_name";
    }
}
foreach ($entity_info['structs'] as $struct_name => $struct) {
    if ($struct['require']) {
        $param_infos[] = "$$struct_name";
        $setting_lines[] = "$$entity_name->$struct_name = $$struct_name";
    }
}
@endphp
    public static function create({{ implode(', ', $param_infos) }}): {{ $entity_name }}
    {/*^^{^^{^^{*/
@if (empty($param_infos))
        return parent::init();
@else
        ${{ $entity_name }} = parent::init();

@foreach ($setting_lines as $setting_line)
        {{ $setting_line }};
@endforeach

        return ${{ $entity_name }};
@endif
    }/*}}}*/

    public static function struct_validators($property)
    {/*^^{^^{^^{*/
        $validators = [
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if (isset($struct['validator']))
@if ($struct['data_type'] === 'enum')
            '{{ $struct_name }}' => self::{{ strtoupper($struct_name) }}_MAPS,
@else
            '{{ $struct_name }}' => [
@foreach ($struct['validator'] as $validator)
                [
@if (isset($validator['reg']))
                    'reg' => '{{ $validator['reg'] }}',
                    'failed_message' => '{{ $validator['failed_message'] }}',
@elseif (isset($validator['function']))
                    'function' => function ($value) {
                        return {{ $validator['function'] }};
                    },
                    'failed_message' => '{{ $validator['failed_message'] }}',
@endif
                ],
@endforeach
            ],
@endif
@endif
@endforeach
        ];

        return $validators[$property] ?? false;
    }/*}}}*/
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['data_type'] === 'enum')

    public function get_{{ $struct_name }}_description(): string
    {/*^^{^^{^^{*/
@if (! $struct['require'])
        if ($this->{{ $struct_name }} === '') {
            return '';
        }

@endif
        return self::{{ strtoupper($struct_name) }}_MAPS[$this->{{ $struct_name }}];
    }/*}}}*/
@foreach ($struct['validator'] as $value => $description)

    public function {{ $struct_name }}_is_{{ strtolower($value) }}(): bool
    {/*^^{^^{^^{*/
        return $this->{{ $struct_name }} === self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/

    public function set_{{ $struct_name }}_{{ strtolower($value) }}()
    {/*^^{^^{^^{*/
        return $this->{{ $struct_name }} = self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/
@endforeach
@endif
@endforeach
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)
@php
$entity = $relationship['entity'];
@endphp
@if ($relationship['relationship_type'] === 'belongs_to')

    public function belongs_to_{{ $attribute_name }}({{ $entity }} ${{ $attribute_name }}): bool
    {/*^^{^^{^^{*/
        return $this->{{ $attribute_name }}_id == ${{ $attribute_name }}->id;
    }/*}}}*/
@foreach ($relationship['snaps'] as $snap_relation_to_with_dot => $structs)
@php
$relationship_attribute_names = explode('.', $snap_relation_to_with_dot);
@endphp

    protected function prepare_set_{{ $attribute_name }}(${{ $attribute_name }}): string
    {/*^^{^^{^^{*/
@if ($relationship['require'])
        otherwise(${{ $attribute_name }} instanceof {{ $entity }}, '{{ $attribute_name }} 类型必须为 {{ $entity }}');

@foreach ($structs as $struct_name => $struct)
        $this->{{ $struct_name }} = ${{ implode('->', $relationship_attribute_names) }}->{{ $struct['target_struct_name'] }};
@endforeach
@else
        otherwise(
            ${{ $attribute_name }} instanceof {{ $entity }} ||
            ${{ $attribute_name }} instanceof null_entity,
            '{{ $attribute_name }} 类型必须为 {{ $entity }} 或者 null_entity');

        if (${{ $attribute_name }} instanceof {{ $entity }}) {
@foreach ($structs as $struct_name => $struct)
            $this->{{ $struct_name }} = ${{ implode('->', $relationship_attribute_names) }}->{{ $struct['target_struct_name'] }};
@endforeach
        } else {
@foreach ($structs as $struct_name => $struct)
            $this->{{ $struct_name }} = null;
@endforeach
        }
@endif

        return ${{ $attribute_name }};
    }/*}}}*/
@endforeach
@endif
@endforeach
@php
$delete_relationship_lines = [];
foreach ($relationship_infos['relationships'] as $attribute_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['associate_delete']) {
        if ($relationship['relationship_type'] === 'has_many') {
            $delete_relationship_lines[] = 'foreach ($this->'.$attribute_name.' as $'.$entity.') {'."\n";
            $delete_relationship_lines[] = '    if ($'.$entity.'->'.$relationship['self_attribute_name'].'_id === $this->id) {'."\n";
            $delete_relationship_lines[] = '        $'.$entity.'->delete();'."\n";
            $delete_relationship_lines[] = "    }\n";
            $delete_relationship_lines[] = "}\n";
        } elseif ($relationship['relationship_type'] === 'has_one') {
            $delete_relationship_lines[] = '$this->'.$attribute_name.'->delete();'."\n";
        }
    } else {
        if ($relationship['relationship_type'] === 'has_many') {
            $delete_relationship_lines[] = 'foreach ($this->'.$attribute_name.' as $'.$entity.') {'."\n";
            $delete_relationship_lines[] = '    if ($'.$entity.'->'.$relationship['self_attribute_name'].'_id === $this->id) {'."\n";
            $delete_relationship_lines[] = '        $'.$entity.'->'.$relationship['self_attribute_name']."_id = 0;\n";
            $delete_relationship_lines[] = "    }\n";
            $delete_relationship_lines[] = "}\n";
        } elseif ($relationship['relationship_type'] === 'has_one') {
            $delete_relationship_lines[] = '$this->'.$attribute_name.'->delete();'."\n";
        }
    }
}
@endphp
@if ($delete_relationship_lines)

    public function delete()
    {/*^^{^^{^^{*/
@foreach ($delete_relationship_lines as $line)
        {{ $line }}
@endforeach

        parent::delete();
    }/*}}}*/
@endif
@foreach ($relationship_infos['relationships'] as $attribute_name => $relationship)

    public function display_for_{{ $attribute_name }}_{{ $relationship['self_attribute_name'] }}()
    {/*^^{^^{^^{*/
        return {{ $relationship['self_display']}};
    }/*}}}*/
@endforeach
@foreach ($entity_info['struct_groups'] as $struct_group)

{{ blade_eval(_generate_entity_struct_group_type($struct_group['type']), ['struct_group_info' => $struct_group['struct_group_info'], 'structs' => $struct_group['structs'], 'struct_name_map' => $struct_group['struct_name_maps']]) }}
@endforeach
    /* generated code end */
}
