<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-btn ui-btn--secondary ui-btn--md']) }}>
    {{ $slot }}
</button>
