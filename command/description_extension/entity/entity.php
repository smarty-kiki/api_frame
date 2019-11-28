class {{ $entity_name }} extends entity
{
    public $structs = [
@foreach ($entity_relationships as $relationship)
@php
$relationship_name = $relationship['relation_name'];
$relationship_struct_name = $relationship_name.'_id';
@endphp
        '{{ $relationship_struct_name }}' => '',
@endforeach
@foreach ($entity_structs as $struct)
@php
$struct_name = $struct['name'];
@endphp
@if (array_key_exists('default', $struct))
@php
$struct_default = $struct['default'];
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

    public static $entity_display_name = '{{ array_get($entity_options, 'display_name', $entity_name) }}';
    public static $entity_description = '{{ array_get($entity_options, 'description', $entity_name) }}';

    public static $struct_types = [
@foreach ($entity_relationships as $relationship)
@php
$relationship_name = $relationship['relation_name'];
$relationship_struct_name = $relationship_name.'_id';
@endphp
        '{{ $relationship_struct_name }}' => 'number',
@endforeach
@foreach ($entity_structs as $struct)
        '{{ $struct['name'] }}' => '{{ entity::convert_struct_format($struct['datatype'], $struct['format']) }}',
@endforeach
    ];

    public static $struct_display_names = [
@foreach ($entity_relationships as $relationship)
@php
$relationship_name = $relationship['relation_name'];
$relationship_struct_name = $relationship_name.'_id';
@endphp
        '{{ $relationship_struct_name }}' => '{{ $relationship['entity_display_name'] }}ID',
@endforeach
@foreach ($entity_structs as $struct)
        '{{ $struct['name'] }}' => '{{ $struct['display_name'] }}',
@endforeach
    ];

    public static $struct_descriptions = [
@foreach ($entity_relationships as $relationship)
@php
$relationship_name = $relationship['relation_name'];
$relationship_struct_name = $relationship_name.'_id';
@endphp
        '{{ $relationship_struct_name }}' => '{{ $relationship['entity_display_name'] }}ID',
@endforeach
@foreach ($entity_structs as $struct)
        '{{ $struct['name'] }}' => '{{ $struct['description'] }}',
@endforeach
    ];
@foreach ($entity_structs as $struct)
@php
$struct_format = $struct['format'];
$struct_name = $struct['name'];
@endphp
@if (! is_null($struct_format))
@if (is_array($struct_format))

@foreach ($struct_format as $value => $description)
    const {{ strtoupper($struct_name.'_'.$value) }} = '{{ strtoupper($struct_name) }}';
@endforeach

    const {{ strtoupper($struct_name) }}_MAPS = [
@foreach ($struct_format as $value => $description)
        self::{{ strtoupper($struct_name.'_'.$value) }} => '{{ $description }}',
@endforeach
    ];
@endif
@endif
@endforeach

    public static $struct_formats = [
@foreach ($entity_structs as $struct)
@php
$struct_format = $struct['format'];
$struct_name = $struct['name'];
@endphp
@if (! is_null($struct_format))
@if (is_array($struct_format))
        '{{ $struct_name }}' => self::{{ strtoupper($struct_name) }}_MAPS,
@else
        '{{ $struct_name }}' => '{{ $struct_format }}',
@endif
@endif
@endforeach
    ];

    public static $struct_format_descriptions = [
@foreach ($entity_structs as $struct)
@php
$struct_format = $struct['format'];
$struct_name = $struct['name'];
$format_description = $struct['format_description'];
@endphp
@if (! is_null($struct_format))
        '{{ $struct_name }}' => '{{ $format_description }}',
@endif
@endforeach
    ];

    public function __construct()
    {/*^{^{^{*/
@foreach ($entity_relationships as $relationship)
@php
$relationship_type = $relationship['type'];
$relationship_name = $relationship['relation_name'];
$relationship_relate_to = $relationship['relate_to'];
$relationship_struct_display_name = $relationship['entity_display_name'].'ID';
$relationship_struct_name = $relationship_name.'_id';
@endphp
@if ($relationship_name === $relationship_relate_to)
        $this->{{ $relationship_type }}('{{ $relationship_relate_to }}');
@else
@if ($relationship_type === 'belongs_to')
        $this->{{ $relationship_type }}('{{ $relationship_name }}', '{{ $relationship_relate_to }}', '{{ $relationship_struct_name }}');
@else
        $this->{{ $relationship_type }}('{{ $relationship_name }}', '{{ $relationship_relate_to }}');
@endif
@endif
@endforeach
    }/*}}}*/

    public static function create()
    {/*^{^{^{*/
        return parent::init();
    }/*}}}*/
@foreach ($entity_structs as $struct)
@php
$struct_format = $struct['format'];
$struct_name = $struct['name'];
@endphp
@if (! is_null($struct_format))
@if (is_array($struct_format))

    public function get_{{ $struct_name }}_description()
    {/*^{^{^{*/
        return self::{{ strtoupper($struct_name) }}_MAPS[$this->{{ $struct_name  }}];
    }/*}}}*/
@foreach ($struct_format as $value => $description)

    public function {{ $struct_name }}_is_{{ strtolower($value) }}()
    {/*^{^{^{*/
        return $this->{{ $struct_name }} === self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/

    public function set_{{ $struct_name }}_{{ strtolower($value) }}()
    {/*^{^{^{*/
        return $this->{{ $struct_name }} = self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/
@endforeach
@endif
@endif
@endforeach
}
