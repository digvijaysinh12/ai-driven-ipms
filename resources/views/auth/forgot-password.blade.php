<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | AI Internship System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md bg-white border shadow-md rounded-lg p-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">
            Reset Password
        </h2>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 p-2 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <input type="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="Enter your registered email"
                   class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   required>

            @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-md font-medium hover:bg-indigo-700">
                Send Reset Link
            </button>

        </form>

    </div>
</div>

</body>
</html>