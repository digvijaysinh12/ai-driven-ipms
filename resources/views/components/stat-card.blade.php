@props(['label', 'value', 'accent' => ''])

<div class="stat-cell">
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value {{ $accent }}">{{ $value }}</div>
</div>