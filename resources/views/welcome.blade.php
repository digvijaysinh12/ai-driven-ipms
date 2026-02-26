<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Driven Internship Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Top Navigation -->
<header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold tracking-wide text-gray-900">
            AI Driven Internship Management System
        </h1>

        <div class="space-x-6 text-sm font-medium">

            @auth
                <a href="{{ route(strtolower(auth()->user()->role->name) . '.dashboard') }}"
                   class="text-gray-600 hover:text-indigo-600">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-gray-600 hover:text-indigo-600">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Register
                </a>
            @endauth

        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="grid md:grid-cols-2 gap-12 items-center">

        <div>
            <h2 class="text-4xl font-bold leading-tight mb-6">
                Intelligent Internship Lifecycle Management
            </h2>

            <p class="text-gray-600 text-lg mb-8">
                A secure, role-based enterprise platform designed to manage intern onboarding,
                HR approvals, mentor supervision, performance tracking, and AI-powered evaluations.
            </p>

            @guest
            <div class="flex space-x-4">
                <a href="{{ route('register') }}"
                   class="bg-indigo-600 text-white px-6 py-3 rounded-md text-sm font-medium hover:bg-indigo-700">
                    Request Access
                </a>

                <a href="{{ route('login') }}"
                   class="border border-indigo-600 text-indigo-600 px-6 py-3 rounded-md text-sm font-medium hover:bg-indigo-50">
                    Sign In
                </a>
            </div>
            @endguest
        </div>

        <div class="bg-white shadow-lg p-8 rounded-lg border">
            <h3 class="text-xl font-semibold mb-6 text-gray-900">
                System Highlights
            </h3>

            <ul class="space-y-4 text-gray-600 text-sm">
                <li>• Secure Role-Based Access Control (HR / Mentor / Intern)</li>
                <li>• HR Approval Workflow Before System Access</li>
                <li>• Email Verification & Account Status Management</li>
                <li>• Technology-Based Intern Assignment</li>
                <li>• AI-Driven Performance Monitoring</li>
                <li>• Controlled Topic & Submission Management</li>
                <li>• Rate Limiting & Secure Authentication Architecture</li>
            </ul>
        </div>

    </div>
</section>

<!-- Use Case Section -->
<section class="bg-white py-20 border-t">
    <div class="max-w-7xl mx-auto px-6">

        <h2 class="text-3xl font-bold text-center mb-16">
            User Role Capabilities
        </h2>

        <div class="grid md:grid-cols-3 gap-10 text-sm">

            <div class="border rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-indigo-600">HR</h3>
                <ul class="space-y-2 text-gray-600">
                    <li>• Approve / Reject Intern Registrations</li>
                    <li>• Assign Technologies & Mentors</li>
                    <li>• Monitor System Activity</li>
                    <li>• Manage Roles & Permissions</li>
                </ul>
            </div>

            <div class="border rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-indigo-600">Mentor / Team Lead</h3>
                <ul class="space-y-2 text-gray-600">
                    <li>• Create Internship Topics</li>
                    <li>• Review Intern Submissions</li>
                    <li>• Provide Performance Feedback</li>
                    <li>• Track Assigned Intern Progress</li>
                </ul>
            </div>

            <div class="border rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-indigo-600">Intern</h3>
                <ul class="space-y-2 text-gray-600">
                    <li>• Register with Technology Selection</li>
                    <li>• Submit Assigned Tasks</li>
                    <li>• View Performance Feedback</li>
                    <li>• Track Internship Progress</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- Security Section -->
<section class="bg-gray-100 py-16 border-t">
    <div class="max-w-4xl mx-auto text-center px-6">
        <h2 class="text-2xl font-semibold mb-6">
            Enterprise Security Architecture
        </h2>

        <p class="text-gray-600 text-sm leading-relaxed">
            The system enforces multi-layered security including authentication,
            status-based login restrictions, role-based middleware authorization,
            resource-level policies, and rate limiting to prevent abuse.
            Designed to meet enterprise-level access control standards.
        </p>
    </div>
</section>

<footer class="bg-white border-t py-6 text-center text-xs text-gray-500">
    © {{ date('Y') }} AI Driven Internship Management System. All rights reserved.
</footer>

</body>
</html>