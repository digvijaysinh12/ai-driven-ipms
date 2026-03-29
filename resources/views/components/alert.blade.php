@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif

@if(session('warning'))
    <div class="flash flash-warning">{{ session('warning') }}</div>
@endif