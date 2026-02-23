<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Login | AI Internship System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @vite(['resources/css/app.css'])
    </head>

    <body class="bg-gray-100">

        <div class="min-h-screen flex items-center justify-center px-6">
            <div class="w-full max-w-md bg-white border shadow-md rounded-lg p-8">

                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">
                    Sign In
                </h2>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500" required>

                    @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                    <input type="password" name="password" placeholder="Password"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500" required>

                    @error('password') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                    <div class="flex justify-between items-center text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300">
                            <span>Remember me</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md font-medium hover:bg-indigo-700">
                        Log In
                    </button>

                    <p class="text-sm text-center text-gray-600 mt-4">
                        Don’t have an account?
                        <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">
                            Create Account
                        </a>
                    </p>

                </form>

            </div>
        </div>

    </body>

</html>