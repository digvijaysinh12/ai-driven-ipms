@props([
    'title' => null,
    'subtitle' => null
])

<div class="bg-white rounded-2xl shadow-md p-6">

    @if($title)
        <div class="mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
                {{ $title }}
            </h3>

            @if($subtitle)
                <p class="text-gray-500 text-sm mt-1">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}

</div>