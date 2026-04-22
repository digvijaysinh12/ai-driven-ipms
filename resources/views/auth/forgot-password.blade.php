<x-guest-layout>

    <div class="w-full">

        <!-- Back -->
        <a href="{{ route('login') }}"
           class="text-sm text-gray-500 hover:text-gray-700 mb-6 inline-block">
            ← Back to Sign In
        </a>

        <!-- Title -->
        <h2 class="text-xl font-bold text-gray-900 mb-2">
            Reset Password
        </h2>

        <p class="text-sm text-gray-600 mb-6">
            Enter your email and we'll send you a reset link.
        </p>

        <!-- Success Message -->
        @if(session('status'))
            <div class="mb-4 p-2 bg-green-100 text-green-700 text-sm rounded">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label class="text-sm font-medium text-gray-700">
                    Email Address
                </label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="you@company.com"
                       class="w-full mt-1 border rounded px-3 py-2 focus:ring focus:ring-indigo-200"
                       required>

                @error('email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Button -->
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">
                Send Reset Link
            </button>

        </form>

    </div>

</x-guest-layout>