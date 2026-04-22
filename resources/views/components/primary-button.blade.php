<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-btn ui-btn--primary ui-btn--md']) }}>
    {{ $slot }}
</button>
