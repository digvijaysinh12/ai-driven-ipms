<section>

    <header class="mb-4">
        <h2 class="text-lg font-semibold text-gray-900">
            Profile Information
        </h2>
        <p class="text-sm text-gray-600">
            Update your name and email.
        </p>
    </header>

    <!-- Email verification -->
    <form id="send-verification" method="POST" action="{{ route('password.email') }}">
        @csrf
    </form>

    <!-- Update form -->
    <form method="POST" action="{{ route('user.profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div>
            <label class="text-sm font-medium">Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $user->name ?? '') }}"
                   class="w-full mt-1 border rounded px-3 py-2"
                   required>

            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $user->email ?? '') }}"
                   class="w-full mt-1 border rounded px-3 py-2"
                   required>

            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Verification Notice -->
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div class="text-sm text-gray-600">
                Email not verified.

                <button form="send-verification"
                        class="underline text-indigo-600 ml-2">
                    Resend verification
                </button>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="text-green-600 text-sm">
                    Verification link sent!
                </div>
            @endif
        @endif

        <!-- Save -->
        <div class="flex items-center gap-3">
            <button class="bg-indigo-600 text-white px-4 py-2 text-sm rounded">
                Save
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-sm text-green-600">Saved!</span>
            @endif
        </div>

    </form>

</section>
