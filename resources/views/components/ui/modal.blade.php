@props([
    'id',
    'title',
    'subtitle' => null,
])

<div id="{{ $id }}" class="ui-modal" hidden aria-hidden="true">
    <div class="ui-modal-backdrop" data-modal-close="{{ $id }}"></div>
    <div class="ui-modal-dialog ui-modal-dialog--lg" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="ui-modal-head">
            <div>
                <h2 id="{{ $id }}-title" class="ui-modal-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="ui-modal-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" class="ui-modal-close" data-modal-close="{{ $id }}">×</button>
        </div>
        <div class="ui-modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
