{{-- regular object attribute --}}
@php
	$value = data_get($entry, $column['name']);
    $realValue = $value;
    $value = formatNumber($value);
    $className = '';
    if(isset($column['color']) && $column['color']){
        $className = 'font-weight-bold';
        if($realValue < 0){
            $className .= ' text-danger';
        }
        else if($realValue > 0){
            $className .= ' text-success';
        } 
    }
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['prefix'].$value.$column['suffix'];
    if(isset($column['hide']) && $column['hide']){
        $column['text'] = '*****';
    }
    $column['wrapper'] = ['class' => $className];
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            {{ $column['text'] }}
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>