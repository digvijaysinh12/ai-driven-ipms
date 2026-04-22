@props([
    'label' => null,
    'value' => '',
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <input {{ $attributes->class(['form-input'])->merge(['value' => $value]) }}>
</label>
