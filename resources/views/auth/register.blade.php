<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | AI Internship System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md bg-white border shadow-md rounded-lg p-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">
            Create Account
        </h2>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <input type="text" name="name"
                   value="{{ old('name') }}"
                   placeholder="Full Name"
                   class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   required>

            @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

            <input type="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="Email Address"
                   class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   required>

            @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

            <input type="password" name="password"
                   placeholder="Password"
                   class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   required>

            <input type="password" name="password_confirmation"
                   placeholder="Confirm Password"
                   class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   required>

            <select name="role" id="role"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name === 'mentor' ? 'Team Lead' : ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>

            <div id="technology-wrapper" style="display:none;">
                <select name="technology_id"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select Technology</option>
                    @foreach ($technologies as $t)
                        <option value="{{ $t->id }}"
                            {{ old('technology_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-md font-medium hover:bg-indigo-700">
                Register
            </button>

            <p class="text-sm text-center text-gray-600">
                Already registered?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">
                    Sign in
                </a>
            </p>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const roleSelect = document.getElementById('role');
    const techWrapper = document.getElementById('technology-wrapper');

    function toggleTechnology(){
        if(roleSelect.value === 'intern'){
            techWrapper.style.display = 'block';
        }else{
            techWrapper.style.display = 'none';
        }
    }

    toggleTechnology();
    roleSelect.addEventListener('change', toggleTechnology);
});
</script>

</body>
</html>