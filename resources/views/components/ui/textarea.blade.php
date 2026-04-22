@props([
    'label' => null,
    'value' => '',
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <textarea {{ $attributes->class(['form-textarea']) }}>{{ $value }}</textarea>
</label>
