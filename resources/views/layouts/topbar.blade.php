@props(['title' => 'Dashboard'])

<div class="topbar">
    <div class="topbar-title">
        {{ $title }}
    </div>

    <div class="topbar-meta">
        {{ auth()->user()->name ?? 'User' }}
    </div>
</div>