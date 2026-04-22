<x-app-layout>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Profile Settings
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Profile Info -->
            <div class="p-6 bg-white shadow rounded-lg">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Password -->
            <div class="p-6 bg-white shadow rounded-lg">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete -->
            <div class="p-6 bg-white shadow rounded-lg">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>

</x-app-layout>