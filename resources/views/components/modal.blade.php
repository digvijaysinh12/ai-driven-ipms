@props([
    'id' => 'modal-' . uniqid(),
    'title' => null,
    'subtitle' => null
])

<div id="{{ $id }}"
     class="fixed inset-0 z-50 hidden items-center justify-center">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50"
         data-modal-close="{{ $id }}"></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6"
         onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-bold text-lg">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>

            <button data-modal-close="{{ $id }}">✕</button>
        </div>

        <!-- Content -->
        {{ $slot }}

    </div>
</div>