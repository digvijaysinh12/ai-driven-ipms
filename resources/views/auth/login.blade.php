<x-guest-layout>
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Welcome back</h2>
        <p class="text-slate-500 font-medium mt-2">Enter your credentials to access your dashboard</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <x-text-input id="email" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@company.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-bold uppercase tracking-wider text-slate-500" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <x-text-input id="password" class="block w-full pl-11 py-3 bg-slate-50 border-slate-200 focus:bg-white transition-all"
                                type="password"
                                name="password"
                                required autocomplete="current-password" 
                                placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-all cursor-pointer" name="remember">
                <span class="ms-3 text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">{{ __('Keep me signed in') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98]">
                {{ __('Sign in to Platform') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            {{ __("Don't have an account?") }} 
            <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:underline underline-offset-4 decoration-2 transition-all">
                {{ __('Create an account') }}
            </a>
        </p>
    </form>
</x-guest-layout>