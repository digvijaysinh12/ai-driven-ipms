<x-guest-layout>
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Create account</h2>
        <p class="text-slate-500 font-medium mt-2">Join our AI-driven internship platform</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div class="space-y-2">
            <x-input-label for="name" :value="__('Full Name')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <x-text-input id="name" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Jane Smith" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <x-text-input id="email" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@company.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password_confirmation" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password"
                                placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="space-y-2">
            <x-input-label for="role" :value="__('Platform Role')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                </div>
                <select id="role" name="role" required class="block w-full pl-11 pr-4 py-3 bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 focus:bg-white transition-all appearance-none cursor-pointer">
                    <option value="">Select your role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name === 'mentor' ? 'Team Lead / Mentor' : ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Technology Focus (Conditional) -->
        <div id="technology-wrapper" style="display:none;" class="space-y-2">
            <x-input-label for="technology_id" :value="__('Technology Focus')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="code-2" class="w-4 h-4"></i>
                </div>
                <select id="technology_id" name="technology_id" class="block w-full pl-11 pr-4 py-3 bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 focus:bg-white transition-all appearance-none cursor-pointer">
                    <option value="">Select technology</option>
                    @foreach ($technologies as $t)
                        <option value="{{ $t->id }}" {{ old('technology_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-input-error :messages="$errors->get('technology_id')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98]">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            {{ __('Already have an account?') }} 
            <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline underline-offset-4 decoration-2 transition-all">
                {{ __('Sign in') }}
            </a>
        </p>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect  = document.getElementById('role');
            const techWrapper = document.getElementById('technology-wrapper');
            function toggle() {
                if (roleSelect.value === 'intern') {
                    techWrapper.style.display = 'block';
                    document.getElementById('technology_id').setAttribute('required', 'required');
                } else {
                    techWrapper.style.display = 'none';
                    document.getElementById('technology_id').removeAttribute('required');
                }
            }
            toggle();
            roleSelect.addEventListener('change', toggle);
        });
    </script>
</x-guest-layout>