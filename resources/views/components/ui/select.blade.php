@props([
    'label' => null,
    'options' => [],
    'selected' => null,
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <select {{ $attributes->class(['form-select']) }}>
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</label>
