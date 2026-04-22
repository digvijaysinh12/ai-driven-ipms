# Project Code Export

Generated on: 2026-04-04 05:45:16

## File: .env.example
```text
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

OFFICE_ALLOWED_IPS=127.0.0.1,::1,192.168.1.*

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

```

## File: aggregate_project.php
```php
<?php

$root = __DIR__;
$outputFile = $root . '/project.md';

$includeExtensions = ['php', 'blade.php', 'css', 'js', 'json', 'env.example'];
$excludeDirs = ['vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache', 'public/build'];

$output = "# Project Code Export\n\n";
$output .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $path => $file) {
    if ($file->isDir()) continue;

    $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    
    // Check if in excluded directory
    $exclude = false;
    foreach ($excludeDirs as $dir) {
        if (strpos($relativePath, $dir . DIRECTORY_SEPARATOR) === 0 || $relativePath === $dir) {
            $exclude = true;
            break;
        }
    }
    if ($exclude) continue;

    // Check extension
    $ext = '';
    if (strpos($relativePath, '.') !== false) {
        $parts = explode('.', $relativePath);
        if (end($parts) === 'php' && count($parts) > 1 && $parts[count($parts)-2] === 'blade') {
            $ext = 'blade.php';
        } else {
            $ext = end($parts);
        }
    }

    $isIncluded = false;
    if (in_array($ext, $includeExtensions)) {
        $isIncluded = true;
    } elseif (in_array($relativePath, ['composer.json', 'package.json', 'tailwind.config.js', 'vite.config.js', '.env.example'])) {
        $isIncluded = true;
    }

    if ($isIncluded) {
        $language = 'text';
        if ($ext === 'php') $language = 'php';
        elseif ($ext === 'blade.php') $language = 'php'; // blade syntax usually highlighted well as php or html
        elseif ($ext === 'js') $language = 'javascript';
        elseif ($ext === 'css') $language = 'css';
        elseif ($ext === 'json') $language = 'json';

        $content = file_get_contents($path);
        
        $output .= "## File: $relativePath\n";
        $output .= "```$language\n";
        $output .= $content . "\n";
        $output .= "```\n\n";
    }
}

file_put_contents($outputFile, $output);
echo "Successfully generated project.md\n";

```

## File: app\Events\TopicPublished.php
```php
<?php

namespace App\Events;

use App\Models\Topic;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicPublished
{
    use Dispatchable, SerializesModels;

    public $topic;

    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }
}
```

## File: app\Exceptions\AIServiceException.php
```php
<?php

namespace App\Exceptions;

use Exception;

class AIServiceException extends Exception
{
    //
}
```

## File: app\Http\Controllers\ApprovalController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ApprovalController extends Controller
{
    public function index(){
        $pendingUsers = User::where('status','pending')->get();
        return view('hr.approvals', compact('pendingUsers'));
    }
}

```

## File: app\Http\Controllers\Auth\AuthenticatedSessionController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AttendanceService $attendanceService): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = auth()->user();

        // Check email verification
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // HR bypasses status check (seeded as approved)
        if ($user->role->name !== 'hr' && $user->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'rejected'
                ? 'Your account has been rejected. Please contact HR.'
                : 'Your account is pending HR approval. Please wait.';

            return redirect()->route('login')->with('error', $message);
        }

        $attendanceService->recordLogin($user);

        return match ($user->role->name) {
            'hr'     => redirect()->route('hr.dashboard'),
            'mentor' => redirect()->route('mentor.dashboard'),
            'intern' => redirect()->route('intern.dashboard'),
            default  => redirect()->route('dashboard'),
        };
    }

    public function destroy(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        if ($request->user()) {
            $attendanceService->recordLogout($request->user());
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

```

## File: app\Http\Controllers\Auth\ConfirmablePasswordController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended($this->homeRouteFor($request->user()));
    }

    private function homeRouteFor(User $user): string
    {
        return match ($user->role->name ?? null) {
            'hr' => route('hr.dashboard', absolute: false),
            'mentor' => route('mentor.dashboard', absolute: false),
            'intern' => route('intern.dashboard', absolute: false),
            default => url('/'),
        };
    }
}

```

## File: app\Http\Controllers\Auth\EmailVerificationNotificationController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}

```

## File: app\Http\Controllers\Auth\EmailVerificationPromptController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}

```

## File: app\Http\Controllers\Auth\NewPasswordController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ResetPasswordRequest $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request, $passwordResetService) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $passwordResetService->invalidateUserSessions($user);
                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}

```

## File: app\Http\Controllers\Auth\PasswordController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}

```

## File: app\Http\Controllers\Auth\PasswordResetLinkController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ForgotPasswordRequest $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $status = $passwordResetService->sendResetLink($request->string('email')->toString());

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}

```

## File: app\Http\Controllers\Auth\RegisteredUserController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Technology;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $roles = Role::where('name','!=','hr')->get();
        $technologies = Technology::all();
        return view('auth.register', compact('roles','technologies'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required','string','exists:roles,name'],
            'technology_id' => [
                'nullable',
                'required_if:role,intern',
                'exists:technologies,id'
            ]
        ]);
        $role = Role::where('name', $request->role)->firstOrFail();
        if($role->name === 'hr'){
            abort(403);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'technology_id'=> $role->name === 'intern' ? $request->technology_id : null,
        ]);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route('verification.notice');
    }
}

```

## File: app\Http\Controllers\Auth\VerifyEmailController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->homeRouteFor($request->user()).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($this->homeRouteFor($request->user()).'?verified=1');
    }

    private function homeRouteFor(User $user): string
    {
        return match ($user->role->name ?? null) {
            'hr' => route('hr.dashboard', absolute: false),
            'mentor' => route('mentor.dashboard', absolute: false),
            'intern' => route('intern.dashboard', absolute: false),
            default => url('/'),
        };
    }
}

```

## File: app\Http\Controllers\Controller.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}

```

## File: app\Http\Controllers\HR\DashboardController.php
```php
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorAssignment;
use App\Models\Topic;
use App\Models\Question;
use App\Models\InternTopicAssignment;

use App\Models\Attendance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * HR Dashboard â€” stats and overview.
     */
    public function index()
    {
        $totalInterns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))->count();
        $totalMentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))->count();

        $pendingUsers  = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();

        $topics          = Topic::count();
        $assignments     = InternTopicAssignment::count();
        $evaluated       = InternTopicAssignment::where('status', 'evaluated')->count();

        // Attendance stats
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $recentLogins = Attendance::with('user')->latest('login_time')->take(10)->get();

        return view('hr.dashboard', compact(
            'totalInterns', 'totalMentors',
            'pendingUsers', 'approvedUsers',
            'topics', 'assignments', 'evaluated',
            'todayAttendance', 'recentLogins'
        ));
    }

    /**
     * View full attendance logs for all interns.
     */
    public function attendance(Request $request)
    {
        $query = Attendance::with('user')->latest('login_time');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->paginate(30);
        $interns = User::whereHas('role', fn($q) => $q->where('name', 'intern'))->get();

        return view('hr.attendance', compact('attendances', 'interns'));
    }
}

```

## File: app\Http\Controllers\HR\InternProgressController.php
```php
<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorAssignment;
use App\Models\InternTopicAssignment;
use App\Models\Submission;

class InternProgressController extends Controller
{
    /**
     * Intern progress overview
     */
    public function index()
    {
        $interns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))
            ->where('status', 'approved')
            ->with('currentMentorAssignment.mentor')
            ->get();

        return view('hr.intern_progress', compact('interns'));
    }

    /**
     * Intern progress detail
     */
    public function show($id)
    {
        $intern = User::with('role')->findOrFail($id);
        abort_unless($intern->role->name === 'intern', 404);

        $mentorAssignment = MentorAssignment::with('mentor')
            ->where('intern_id', $id)
            ->where('is_active', 1)
            ->first();

        $topicAssignments = InternTopicAssignment::with('topic')
            ->where('intern_id', $id)
            ->latest('assigned_at')
            ->get();

        $totalSubmissions = Submission::where('intern_id', $id)->count();
        $reviewedCount    = Submission::where('intern_id', $id)
            ->where('status', 'reviewed')
            ->count();

        return view('hr.intern_progress_show', compact(
            'intern',
            'mentorAssignment',
            'topicAssignments',
            'totalSubmissions',
            'reviewedCount'
        ));
    }
}

```

## File: app\Http\Controllers\HR\MentorAssignmentController.php
```php
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{
    /**
     * Assign Mentor Page
     */
    public function index()
    {
        // Interns without active mentor
        $interns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))
            ->where('status', 'approved')
            ->whereDoesntHave('mentorAssignments', fn ($q) => $q->where('is_active', true))
            ->get();

        // All approved mentors
        $mentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))
            ->where('status', 'approved')
            ->get();

        return view('hr.mentor_assignments', compact('interns', 'mentors'));
    }

    /**
     * Assign Mentor Logic
     */
    public function assign(Request $request)
    {
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {
            // Deactivate old assignment if any
            MentorAssignment::where('intern_id', $request->intern_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // deactivate old assignment
            MentorAssignment::where('intern_id',$intern->id)
                ->where('is_active',true)
                ->update(['is_active'=>false]);


            // create new assignment
            MentorAssignment::create([
                'intern_id'   => $request->intern_id,
                'mentor_id'   => $request->mentor_id,
                'assigned_by' => auth()->id(),
                'is_active'   => true,
                'assigned_at' => now(),
            ]);

        });

        return back()->with('success', 'Mentor assigned successfully.');
    }

    /**
     * Intern-Mentor Mapping List
     */
    public function list()
    {
        $assignments = MentorAssignment::with(['intern', 'mentor'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('hr.intern_mentor_list', compact('assignments'));
    }
}

```

## File: app\Http\Controllers\HR\UserController.php
```php
<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Pending approvals page
     */
    public function index()
    {
        $users = User::with('role')
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['intern', 'mentor']))
            ->latest()
            ->get();

        return view('hr.approvals', compact('users'));
    }

    /**
     * Approve user
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
            return back()->with('error', 'Cannot approve HR account.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'User email not verified yet.');
        }

        $user->update(['status' => 'approved']);

        return back()->with('success', 'User approved successfully.');
    }

    /**
     * Reject user
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
            return back()->with('error', 'Cannot reject HR account.');
        }

        $user->update(['status' => 'rejected']);

        return back()->with('success', 'User rejected successfully.');
    }
}

```

## File: app\Http\Controllers\Intern\DashboardController.php
```php
<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\InternTopicAssignment;
use App\Models\MentorAssignment;
use App\Models\Submission;
use App\Models\Question;

class DashboardController extends Controller
{
    public function index()
    {
        $intern = Auth::user();

        // â”€â”€ Mentor info â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $mentorAssignment = MentorAssignment::where('intern_id', $intern->id)
            ->where('is_active', true)
            ->with('mentor')
            ->first();

        $mentor = $mentorAssignment?->mentor;

        // â”€â”€ Current (latest) topic assignment â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $currentAssignment = InternTopicAssignment::where('intern_id', $intern->id)
            ->with('topic')
            ->latest()
            ->first();

        // â”€â”€ Stats â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

        // Total topic assignments ever given to this intern
        $topicCount = InternTopicAssignment::where('intern_id', $intern->id)->count();

        // Total questions in the current topic
        $questionCount = 0;
        $submittedCount = 0;

        if ($currentAssignment?->topic) {
            $questionIds = $currentAssignment->topic->questions()->pluck('id');
            $questionCount = $questionIds->count();

            $submittedCount = Submission::where('intern_id', $intern->id)
                ->whereIn('question_id', $questionIds)
                ->count();
        }

        // Remaining questions in current topic
        $pendingCount = max(0, $questionCount - $submittedCount);

        // AI-evaluated submissions (scored but not yet reviewed by mentor)
        $evaluatedCount = Submission::where('intern_id', $intern->id)
            ->where('status', 'ai_evaluated')
            ->count();

        // Fully reviewed by mentor
        $reviewedCount = Submission::where('intern_id', $intern->id)
            ->where('status', 'reviewed')
            ->count();

        // Average final score across reviewed submissions
        $avgScore = Submission::where('intern_id', $intern->id)
            ->whereNotNull('final_score')
            ->avg('final_score');

        $avgScore = $avgScore ? round($avgScore, 1) : null;
        $completedAssignmentsCount = InternTopicAssignment::where('intern_id', $intern->id)
            ->whereIn('status', ['submitted', 'evaluated'])
            ->count();
        $performancePercent = $avgScore !== null
            ? min(100, (int) round(($avgScore / 30) * 100))
            : 0;

        return view('intern.dashboard', compact(
            'mentor',
            'currentAssignment',
            'topicCount',
            'questionCount',
            'submittedCount',
            'pendingCount',
            'evaluatedCount',
            'reviewedCount',
            'avgScore',
            'completedAssignmentsCount',
            'performancePercent'
        ));
    }

    public function attendance()
    {
        $internId = Auth::id();

        $mentorAssignment = MentorAssignment::where('intern_id', $internId)
            ->where('is_active', true)
            ->with('mentor')
            ->first();

        $currentAssignment = InternTopicAssignment::where('intern_id', $internId)
            ->with('topic')
            ->latest('assigned_at')
            ->first();

        $todayAttendance = Attendance::where('user_id', $internId)
            ->whereDate('date', today())
            ->latest('login_time')
            ->first();

        $recentAttendances = Attendance::where('user_id', $internId)
            ->latest('login_time')
            ->take(10)
            ->get();

        $totalTrackedSeconds = Attendance::where('user_id', $internId)->sum('total_seconds');

        return view('intern.attendance.index', compact(
            'mentorAssignment',
            'currentAssignment',
            'todayAttendance',
            'recentAttendances',
            'totalTrackedSeconds'
        ));
    }

    public function performance()
    {
        $internId = Auth::id();

        $assignments = InternTopicAssignment::where('intern_id', $internId)
            ->with('topic')
            ->latest('assigned_at')
            ->get();

        $submissions = Submission::where('intern_id', $internId)
            ->with('question')
            ->get();

        $submissionsByTopic = $submissions
            ->filter(fn (Submission $submission) => $submission->question !== null)
            ->groupBy(fn (Submission $submission) => $submission->question->topic_id);

        $assignmentCount = $assignments->count();
        $submittedAssignments = $assignments->whereIn('status', ['submitted', 'evaluated'])->count();
        $evaluatedAssignments = $assignments->where('status', 'evaluated')->count();
        $reviewedAnswers = $submissions->where('status', 'reviewed')->count();
        $averageFinalScore = $this->roundAverage($submissions->whereNotNull('final_score')->avg('final_score'));

        $latestEvaluatedAssignment = $assignments->firstWhere('status', 'evaluated');

        $topicPerformance = $assignments->map(function (InternTopicAssignment $assignment) use ($submissionsByTopic) {
            $topicSubmissions = $submissionsByTopic->get($assignment->topic_id, collect());

            return (object) [
                'topic' => $assignment->topic,
                'status' => $assignment->status,
                'grade' => $assignment->grade,
                'feedback' => $assignment->feedback,
                'deadline' => $assignment->deadline,
                'submitted_at' => $assignment->submitted_at,
                'ai_score' => $this->roundAverage($topicSubmissions->whereNotNull('ai_total_score')->avg('ai_total_score')),
                'final_score' => $this->roundAverage($topicSubmissions->whereNotNull('final_score')->avg('final_score')),
                'reviewed_answers' => $topicSubmissions->where('status', 'reviewed')->count(),
            ];
        });

        return view('intern.performance.index', compact(
            'assignmentCount',
            'submittedAssignments',
            'evaluatedAssignments',
            'reviewedAnswers',
            'averageFinalScore',
            'latestEvaluatedAssignment',
            'topicPerformance'
        ));
    }

    private function roundAverage($value): ?float
    {
        return $value === null ? null : round((float) $value, 1);
    }
}

```

## File: app\Http\Controllers\Intern\SubmissionController.php
```php
<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Models\Submission;
use App\Models\InternTopicAssignment;
use App\Services\GroqEvaluationService;
use App\Services\CodeExecutionService;
use App\Http\Requests\StoreSubmissionCodeRequest;
use App\Jobs\EvaluateExerciseJob;

class SubmissionController extends Controller
{
    /**
     * FINAL SUBMIT â€” sends all answers to AI in ONE prompt via Background Queue.
     */
    public function finalSubmit(Request $request, int $assignmentId, GroqEvaluationService $evaluator)
    {
        $internId = Auth::id();

        $assignment = InternTopicAssignment::where('id', $assignmentId)
            ->where('intern_id', $internId)
            ->with('topic')
            ->firstOrFail();

        // Guard: already submitted/evaluated
        if (in_array($assignment->status, ['submitted', 'evaluated'])) {
            return redirect()->route('intern.topic')
                ->with('error', 'This exercise has already been submitted.');
        }

        $questionIds   = Question::where('topic_id', $assignment->topic_id)->pluck('id');
        $answeredCount = Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->count();

        if ($answeredCount === 0) {
            return redirect()->route('intern.topic')
                ->with('error', 'Please answer at least one question before submitting.');
        }

        // Mark assignment as submitted first (locks further changes)
        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);
        });

        // Evaluation dispatched to background queue to prevent blocking HTTP requests
        EvaluateExerciseJob::dispatch($assignment);

        return redirect()->route('intern.topic')
            ->with('success', 'Exercise submitted! AI evaluation is in progress â€” check back shortly.');
    }

    /**
     * Submissions list â€” show grade when evaluated.
     */
    public function index()
    {
        $internId = Auth::id();

        $assignments = InternTopicAssignment::where('intern_id', $internId)
            ->with('topic')
            ->latest('assigned_at')
            ->get();

        $submissions = Submission::where('intern_id', $internId)
            ->with('question')
            ->latest()
            ->paginate(20);

        $totalSubmissions = $submissions->total();
        $reviewedCount    = Submission::where('intern_id', $internId)
            ->where('status', 'reviewed')
            ->count();

        return view('intern.submissions', compact(
            'assignments',
            'submissions',
            'totalSubmissions',
            'reviewedCount'
        ));
    }

    /**
     * PHP Code runner for coding questions.
     */
    public function runCode(StoreSubmissionCodeRequest $request, CodeExecutionService $executionService)
    {
        // Validation is handled by StoreSubmissionCodeRequest
        $result = $executionService->execute($request->code);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']]);
        }

        return response()->json(['output' => $result['output']]);
    }
}

```

## File: app\Http\Controllers\Intern\TopicController.php
```php
<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\InternTopicAssignment;
use App\Models\Submission;
use App\Models\Question;
use App\Http\Requests\StoreAnswerRequest;

class TopicController extends Controller
{
    /**
     * Topic overview page â€” module cards + final submit button.
     */
    public function index()
    {
        $assignment = InternTopicAssignment::where('intern_id', Auth::id())
            ->with('topic.questions')
            ->latest()
            ->first();

        $submissionCounts = [];

        if ($assignment?->topic) {
            $questions  = $assignment->topic->questions;
            $typeGroups = $questions->groupBy('type');

            foreach ($typeGroups as $type => $qs) {
                $submitted = Submission::where('intern_id', Auth::id())
                    ->whereIn('question_id', $qs->pluck('id'))
                    ->count();

                $submissionCounts[$type] = [
                    'total'     => $qs->count(),
                    'submitted' => $submitted,
                ];
            }
        }

        return view('intern.topic', compact('assignment', 'submissionCounts'));
    }

    /**
     * Exam mode â€” one question at a time for a given type module.
     */
    public function exam(int $assignmentId, string $type)
    {
        $validTypes = ['mcq', 'blank', 'theory', 'output', 'coding', 'description'];
        abort_unless(in_array($type, $validTypes), 404);

        $assignment = InternTopicAssignment::where('intern_id', Auth::id())
            ->where('id', $assignmentId)
            ->with('topic')
            ->firstOrFail();

        // Block access if already final-submitted
        if (in_array($assignment->status, ['submitted', 'evaluated'])) {
            return redirect()->route('intern.topic')
                ->with('error', 'This module has already been submitted for evaluation.');
        }

        $topic = $assignment->topic;

        $questions = $topic->questions()
            ->where('type', $type)
            ->get();

        abort_if($questions->isEmpty(), 404);

        // Build answered map: question_id => bool
        $submittedIds = Submission::where('intern_id', Auth::id())
            ->whereIn('question_id', $questions->pluck('id'))
            ->pluck('question_id')
            ->toArray();

        $answeredMap = $questions->pluck('id')->mapWithKeys(fn ($id) => [
            $id => in_array($id, $submittedIds),
        ])->toArray();

        // Saved answers map: question_id => submitted_code
        $savedAnswers = Submission::where('intern_id', Auth::id())
            ->whereIn('question_id', $questions->pluck('id'))
            ->pluck('submitted_code', 'question_id')
            ->toArray();

        return view('intern.exercise', compact(
            'assignment',
            'topic',
            'questions',
            'type',
            'answeredMap',
            'savedAnswers'
        ));
    }

    /**
     * AJAX â€” save a single answer during exam (no evaluation yet).
     */
    public function saveAnswer(StoreAnswerRequest $request)
    {
        // Validation is handled by StoreAnswerRequest

        $internId   = Auth::id();
        $questionId = $request->question_id;

        $assignment = InternTopicAssignment::where('intern_id', $internId)
            ->latest()
            ->first();

        if (! $assignment) {
            return response()->json(['ok' => false, 'msg' => 'No assignment found.'], 403);
        }

        $belongsToTopic = Question::where('id', $questionId)
            ->where('topic_id', $assignment->topic_id)
            ->exists();

        if (! $belongsToTopic) {
            return response()->json(['ok' => false, 'msg' => 'Question not in your topic.'], 403);
        }

        $data = [
            'status'         => 'submitted',
            'submitted_code' => $request->submitted_code,
            'github_link'    => $request->github_link,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('submissions/files', 'public');
        }

        // Upsert â€” create or update the submission row
        Submission::updateOrCreate(
            ['intern_id' => $internId, 'question_id' => $questionId],
            $data
        );

        return response()->json(['ok' => true]);
    }
}

```

## File: app\Http\Controllers\Intern\WaitingController.php
```php
<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\MentorAssignment;

class WaitingController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Show waiting page
    // Intern is approved by HR but no mentor has been assigned yet
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $intern = Auth::user();

        // Check if a mentor was actually assigned
        // (middleware should block this, but double check here)
        $mentorAssignment = MentorAssignment::where('intern_id', $intern->id)
            ->where('is_active', true)
            ->first();

        // If mentor is now assigned, redirect to dashboard
        if ($mentorAssignment) {
            return redirect()->route('intern.dashboard');
        }

        return view('intern.waiting', compact('intern'));
    }
}
```

## File: app\Http\Controllers\Mentor\DashboardController.php
```php
<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Topic;
use App\Models\Question;
use App\Models\Submission;
use App\Models\InternTopicAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $mentorId = Auth::id();

        // Total active interns assigned to this mentor
        $internCount = DB::table('mentor_assignments')
            ->where('mentor_id', $mentorId)
            ->where('is_active', 1)
            ->count();

        // All topics by this mentor
        $topics = Topic::where('mentor_id', $mentorId)->get();
        $topicIds = $topics->pluck('id');

        $topicCount = $topics->count();

        // Total questions across all topics
        $questionCount = Question::whereIn('topic_id', $topicIds)->count();

        // Topics that went through AI generation
        $aiTopics = $topics->where('status', 'ai_generated')->count();

        // Topic status breakdown
        $publishedTopics  = $topics->where('status', 'published')->count();
        $draftTopics      = $topics->where('status', 'draft')->count();

        // Submissions pending mentor review
        $pendingReview = Submission::whereIn(
            'question_id',
            Question::whereIn('topic_id', $topicIds)->pluck('id')
        )->where('status', 'ai_evaluated')->count();

        // Fully reviewed submissions
        $reviewedCount = Submission::whereIn(
            'question_id',
            Question::whereIn('topic_id', $topicIds)->pluck('id')
        )->where('status', 'reviewed')->count();

        // Recent topics for quick access
        $recentTopics = Topic::where('mentor_id', $mentorId)
            ->latest()
            ->take(5)
            ->get();

        // Recent intern assignments
        $recentAssignments = InternTopicAssignment::whereIn('topic_id', $topicIds)
            ->with(['intern', 'topic'])
            ->latest('assigned_at')
            ->take(5)
            ->get();

        // Stats for the view
        $assignedInternsCount = $internCount;
        $publishedTopicsCount = $publishedTopics;
        $pendingSubmissionsCount = $pendingReview;

        // Recent submissions (individual questions)
        $recentSubmissions = Submission::whereIn(
            'question_id',
            Question::whereIn('topic_id', $topicIds)->pluck('id')
        )
        ->with(['intern', 'question.topic'])
        ->latest()
        ->take(5)
        ->get();

        return view('mentor.dashboard', compact(
            'assignedInternsCount',
            'publishedTopicsCount',
            'pendingSubmissionsCount',
            'recentSubmissions',
            'topicCount',
            'questionCount',
            'aiTopics',
            'draftTopics',
            'reviewedCount',
            'recentTopics',
            'recentAssignments'
        ));
    }
}
```

## File: app\Http\Controllers\Mentor\InternController.php
```php
<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\InternTopicAssignment;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;

class InternController extends Controller
{
    // ─────────────────────────────────────────────
    // List all interns assigned to this mentor
    // ─────────────────────────────────────────────
    public function index()
    {
        $mentorId = Auth::id();

        $interns = DB::table('mentor_assignments')
            ->join('users', 'mentor_assignments.intern_id', '=', 'users.id')
            ->where('mentor_assignments.mentor_id', $mentorId)
            ->where('mentor_assignments.is_active', 1)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'mentor_assignments.assigned_at'
            )
            ->get();
            
        // Calculate stats for each intern
        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');
        $questionIds = Question::whereIn('topic_id', $topicIds)->pluck('id');

        foreach ($interns as $intern) {
            $internSubmissions = Submission::where('intern_id', $intern->id)
                ->whereIn('question_id', $questionIds)
                ->get();
                
            $intern->total_submissions = $internSubmissions->count();
            $intern->pending_reviews = $internSubmissions->where('status', 'ai_evaluated')->count();
        }

        return view('mentor.interns.index', compact('interns'));
    }

    // ─────────────────────────────────────────────
    // View a specific intern's full progress
    // Shows: assignment status, submissions, scores
    // ─────────────────────────────────────────────
    public function progress(int $internId)
    {
        $mentorId = Auth::id();

        // Confirm this intern belongs to this mentor
        $assigned = DB::table('mentor_assignments')
            ->where('mentor_id', $mentorId)
            ->where('intern_id', $internId)
            ->where('is_active', 1)
            ->exists();

        abort_unless($assigned, 403, 'This intern is not assigned to you.');

        $intern = User::findOrFail($internId);

        // Topic assignments for this intern (only topics owned by this mentor)
        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');

        $assignments = InternTopicAssignment::where('intern_id', $internId)
            ->whereIn('topic_id', $topicIds)
            ->with('topic')
            ->get();

        // All submissions by this intern for this mentor's questions
        $questionIds = Question::whereIn('topic_id', $topicIds)->pluck('id');

        $submissions = Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->with('question')
            ->latest()
            ->get();

        // Score summary
        $totalSubmissions  = $submissions->count();
        $evaluatedCount    = $submissions->whereIn('status', ['ai_evaluated', 'reviewed'])->count();
        $avgScore          = $evaluatedCount > 0
            ? round($submissions->whereIn('status', ['ai_evaluated', 'reviewed'])->avg('final_score'), 1)
            : null;

        return view('mentor.interns.submissions', compact(
            'intern',
            'assignments',
            'submissions',
            'totalSubmissions',
            'evaluatedCount',
            'avgScore'
        ));
    }
}
```

## File: app\Http\Controllers\Mentor\SubmissionReviewController.php
```php
<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Topic;
use App\Models\InternTopicAssignment;

class SubmissionReviewController extends Controller
{
    /**
     * Index — load all submissions and topic assignments for review.
     */
    public function index()
    {
        $mentorId    = Auth::id();
        $topicIds    = Topic::where('mentor_id', $mentorId)->pluck('id');
        $questionIds = Question::whereIn('topic_id', $topicIds)->pluck('id');

        $with = ['question.topic', 'question.referenceSolution', 'intern'];

        // Submissions
        $pendingSubmissions = Submission::whereIn('question_id', $questionIds)
            ->where('status', 'ai_evaluated')
            ->with($with)
            ->latest()
            ->get();

        // Topic Assignments (Holistic)
        $pendingAssignments = InternTopicAssignment::whereIn('topic_id', $topicIds)
            ->where('status', 'evaluated')
            ->with(['intern', 'topic'])
            ->latest()
            ->get();

        $reviewedSubmissions = Submission::whereIn('question_id', $questionIds)
            ->where('status', 'reviewed')
            ->with($with)
            ->latest()
            ->get();

        return view('mentor.submissions.index', compact(
            'pendingSubmissions',
            'pendingAssignments',
            'reviewedSubmissions'
        ));
    }

    /**
     * Review Topic Assignment — Holistic review of an entire assignment.
     */
    public function reviewAssignment(int $assignmentId)
    {
        $assignment = InternTopicAssignment::with(['intern', 'topic', 'topic.questions.submissions' => function($q) use ($assignmentId) {
            $q->where('intern_id', function($sub) use ($assignmentId) {
                $sub->select('intern_id')->from('intern_topic_assignments')->where('id', $assignmentId);
            });
        }])->findOrFail($assignmentId);

        // Authorization check
        abort_unless($assignment->topic->mentor_id === Auth::id(), 403);

        return view('mentor.submissions.review_assignment', compact('assignment'));
    }

    /**
     * Update Topic Assignment Review — Submit holistic override.
     */
    public function updateAssignmentReview(Request $request, int $assignmentId)
    {
        $request->validate([
            'score'     => 'required|integer|min:0|max:100',
            'grade'     => 'required|string|max:5',
            'feedback'  => 'required|string',
            'tone'      => 'required|string',
        ]);

        $assignment = InternTopicAssignment::findOrFail($assignmentId);
        abort_unless($assignment->topic->mentor_id === Auth::id(), 403);

        $assignment->update([
            'score'    => $request->score,
            'grade'    => $request->grade,
            'feedback' => $request->feedback,
            'tone'     => $request->tone,
            'status'   => 'evaluated', // Keep as evaluated or change to 'reviewed'? Let's use 'evaluated' as the finalized state for now but we can call it 'reviewed'.
        ]);

        return redirect()
            ->route('mentor.submissions.index')
            ->with('success', 'Holistic review finalized.');
    }

    /**
     * Show — review a specific submission within the context of its topic.
     */
    public function show(int $submissionId)
    {
        $submission = Submission::with([
            'intern',
            'question.topic',
            'question.referenceSolution',
        ])->findOrFail($submissionId);

        $this->authorizeSubmission($submission);

        // Load all submissions for this intern and topic to allow step-by-step navigation
        $allSubmissions = Submission::where('intern_id', $submission->intern_id)
            ->whereHas('question', function ($q) use ($submission) {
                $q->where('topic_id', $submission->question->topic_id);
            })
            ->with(['question.topic', 'question.referenceSolution'])
            ->get();

        $question = $submission->question;
        $reference = $question?->referenceSolution;

        return view('mentor.submissions.review', compact(
            'submission',
            'allSubmissions',
            'question',
            'reference'
        ));
    }


    /**
     * Review — AJAX + regular POST both supported.
     */
    public function review(Request $request, int $submissionId)
    {
        $request->validate([
            'mentor_override_score' => 'required|integer|min:0|max:100',
            'feedback'              => 'nullable|string|max:2000',
        ]);

        $submission = Submission::findOrFail($submissionId);
        $this->authorizeSubmission($submission);

        $submission->update([
            'mentor_override_score' => $request->mentor_override_score,
            'final_score'           => $request->mentor_override_score,
            'feedback'              => $request->feedback ?? $submission->feedback,
            'status'                => 'reviewed',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('mentor.interns.progress', $submission->intern_id)
            ->with('success', 'Review saved successfully.');
    }

    private function authorizeSubmission(Submission $submission): void
    {
        $mentorId = Auth::id();
        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');
        $allowed  = Question::whereIn('topic_id', $topicIds)
            ->where('id', $submission->question_id)
            ->exists();

        abort_unless($allowed, 403, 'Unauthorized: this submission does not belong to your topics.');
    }
}

```

## File: app\Http\Controllers\Mentor\TopicAssignController.php
```php
<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Topic;
use App\Models\InternTopicAssignment;
use App\Http\Requests\StoreTopicAssignRequest;

class TopicAssignController extends Controller
{
    // ─────────────────────────────────────────────
    // Show assignment form
    // Mentor selects: intern → topic → deadline
    // ─────────────────────────────────────────────
    public function create()
    {
        $mentorId = Auth::id();

        // Interns actively assigned to this mentor
        $interns = DB::table('mentor_assignments')
            ->join('users', 'mentor_assignments.intern_id', '=', 'users.id')
            ->where('mentor_assignments.mentor_id', $mentorId)
            ->where('mentor_assignments.is_active', 1)
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        // Only published topics by this mentor (ready to assign)
        $topics = Cache::remember("mentor.{$mentorId}.published_topics", 60*60, function () use ($mentorId) {
            return Topic::where('mentor_id', $mentorId)
                ->where('status', 'published')
                ->withCount('questions')
                ->orderBy('title')
                ->get();
        });

        return view('mentor.topics.assign', compact('interns', 'topics'));
    }

    // ─────────────────────────────────────────────
    // Store the assignment
    // ─────────────────────────────────────────────
    public function store(StoreTopicAssignRequest $request)
    {
        // Validation is now handled by StoreTopicAssignRequest

        $mentorId = Auth::id();

        // Security: confirm topic belongs to this mentor and is published
        $topic = Topic::where('id', $request->topic_id)
            ->where('mentor_id', $mentorId)
            ->where('status', 'published')
            ->firstOrFail();

        // Prevent same topic being assigned twice to same intern
        $alreadyAssigned = InternTopicAssignment::where('intern_id', $request->intern_id)
            ->where('topic_id', $request->topic_id)
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->withInput()
                ->withErrors(['topic_id' => 'This topic is already assigned to this intern.']);
        }

        DB::transaction(function () use ($request, $mentorId) {
            InternTopicAssignment::create([
                'intern_id'   => $request->intern_id,
                'topic_id'    => $request->topic_id,
                'assigned_by' => $mentorId,
                'deadline'    => $request->deadline,
                'status'      => 'assigned',
                'assigned_at' => now(),
            ]);
        });

        return redirect()
            ->route('mentor.topics.index')
            ->with('success', 'Topic assigned to intern successfully.');
    }

    // ─────────────────────────────────────────────
    // List all assignments made by this mentor
    // ─────────────────────────────────────────────
    public function index()
    {
        $mentorId = Auth::id();

        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');

        $assignments = InternTopicAssignment::whereIn('topic_id', $topicIds)
            ->with(['intern', 'topic'])
            ->latest('assigned_at')
            ->get();

        return view('mentor.assignments', compact('assignments'));
    }
}
```

## File: app\Http\Controllers\Mentor\TopicController.php
```php
<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Services\GroqQuestionService;
use App\Exceptions\AIServiceException;
use App\Events\TopicPublished;
use App\Jobs\GenerateQuestionsJob;
use App\Http\Requests\StoreTopicRequest;

class TopicController extends Controller
{
    /**
     * List all topics for this mentor.
     */
    public function index()
    {
        $baseQuery = Topic::where('mentor_id', Auth::id());

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'needs_attention' => (clone $baseQuery)->whereIn('status', ['draft', 'ai_generated'])->count(),
        ];

        $topics = $baseQuery
            ->withCount('questions')
            ->latest()
            ->paginate(10);

        return view('mentor.topics.index', compact('topics', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('mentor.topics.create');
    }

    /**
     * Store new topic.
     */
    public function store(StoreTopicRequest $request)
    {
        $counts = [
            'mcq_count'        => $request->input('mcq_count'),
            'blank_count'      => $request->input('blank_count'),
            'true_false_count' => $request->input('true_false_count'),
            'output_count'     => $request->input('output_count'),
            'coding_count'     => $request->input('coding_count'),
        ];

        $hasExplicitCounts = collect($counts)->contains(fn ($value) => $value !== null && $value !== '');

        if (! $hasExplicitCounts) {
            $counts = match ($request->input('difficulty', 'medium')) {
                'easy' => [
                    'mcq_count'        => 3,
                    'blank_count'      => 1,
                    'true_false_count' => 1,
                    'output_count'     => 0,
                    'coding_count'     => 0,
                ],
                'hard' => [
                    'mcq_count'        => 3,
                    'blank_count'      => 2,
                    'true_false_count' => 2,
                    'output_count'     => 1,
                    'coding_count'     => 2,
                ],
                default => [
                    'mcq_count'        => 3,
                    'blank_count'      => 2,
                    'true_false_count' => 1,
                    'output_count'     => 1,
                    'coding_count'     => 1,
                ],
            };
        }

        Topic::create([
            'mentor_id'        => Auth::id(),
            'title'            => $request->title,
            'description'      => $request->description,
            'status'           => 'draft',
            'mcq_count'        => $counts['mcq_count'] ?? 0,
            'blank_count'      => $counts['blank_count'] ?? 0,
            'true_false_count' => $counts['true_false_count'] ?? 0,
            'output_count'     => $counts['output_count'] ?? 0,
            'coding_count'     => $counts['coding_count'] ?? 0,
        ]);

        return redirect()
            ->route('mentor.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Show topic detail â€” question type cards.
     */
    public function show(Topic $topic)
    {
        $this->authorize('view', $topic);

        $typeCounts = $topic->questions()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('mentor.topics.show', compact('topic', 'typeCounts'));
    }

    /**
     * Show questions list for a specific type.
     */
    public function showQuestions(Topic $topic, string $type)
    {
        $this->authorize('view', $topic);

        $validTypes = ['mcq', 'blank', 'true_false', 'output', 'coding'];
        abort_unless(in_array($type, $validTypes), 404);

        $questions = $topic->questions()
            ->where('type', $type)
            ->with('referenceSolution')
            ->get();

        return view('mentor.topics.questions', compact('topic', 'questions', 'type'));
    }

    /**
     * Trigger AI question generation via Groq.
     */
    public function generateAI(Topic $topic, GroqQuestionService $aiService)
    {
        $this->authorize('update', $topic);

        if ($topic->status === 'published') {
            return back()->with('error', 'Cannot regenerate questions for a published topic.');
        }

        // Delete existing AI-generated questions before regenerating
        if ($topic->status === 'ai_generated') {
            $topic->questions()->delete();
        }

        $modules = [
            'mcq'        => $topic->mcq_count,
            'blank'      => $topic->blank_count,
            'true_false' => $topic->true_false_count,
            'output'     => $topic->output_count,
            'coding'     => $topic->coding_count,
        ];

        foreach ($modules as $type => $count) {
            if ($count <= 0) {
                continue;
            }
            GenerateQuestionsJob::dispatch($topic, $type, $count);
        }

        $topic->update(['status' => 'ai_generated']);

        return back()->with('success', 'AI question generation started. Check back shortly for results.');
    }

    /**
     * Publish a topic (makes it assignable to interns).
     */
    public function publish(Topic $topic)
    {
        $this->authorize('publish', $topic);

        if ($topic->questions()->count() === 0) {
            return back()->with('error', 'Cannot publish a topic with no questions.');
        }

        $topic->update(['status' => 'published']);

        event(new TopicPublished($topic));

        return back()->with('success', 'Topic published. It can now be assigned to interns.');
    }

    /**
     * Delete a topic (only draft or ai_generated).
     */
    public function destroy(Topic $topic)
    {
        $this->authorize('delete', $topic);

        if ($topic->status === 'published') {
            return back()->with('error', 'Published topics cannot be deleted.');
        }

        $topic->delete();

        return redirect()
            ->route('mentor.topics.index')
            ->with('success', 'Topic deleted.');
    }

    /**
     * Edit form (required for resource route).
     */
    public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);
        return view('mentor.topics.edit', compact('topic'));
    }

    /**
     * Update topic (only draft/ai_generated).
     */
    public function update(StoreTopicRequest $request, Topic $topic)
    {
        $this->authorize('update', $topic);

        $topic->update([
            'title'            => $request->title,
            'description'      => $request->description,
            'mcq_count'        => $request->mcq_count        ?? 0,
            'blank_count'      => $request->blank_count      ?? 0,
            'true_false_count' => $request->true_false_count ?? 0,
            'output_count'     => $request->output_count     ?? 0,
            'coding_count'     => $request->coding_count     ?? 0,
        ]);

        return redirect()
            ->route('mentor.topics.show', $topic)
            ->with('success', 'Topic updated.');
    }
}

```

## File: app\Http\Controllers\ProfileController.php
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

```

## File: app\Http\Middleware\CheckApproved.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if($user && $user->status !== 'approved'){
            Auth::logout();

            return redirect()->route('login')
                ->with('error','Your account is pending HR approval.');
        }
        return $next($request);
    }
}

```

## File: app\Http\Middleware\CheckAssigned.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\MentorAssignment;
class CheckAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only for intern
        if($user->role->name === 'intern'){
            $hasAssignement = MentorAssignment::where('intern_id',$user->id)
                                ->where('is_active',true)
                                ->exists();
            if(!$hasAssignement){
                return redirect()->route('intern.waiting');
            }
        }
        return $next($request);
    }
}

```

## File: app\Http\Middleware\CheckOfficeNetwork.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class CheckOfficeNetwork
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowedIp($request->ip())) {
            return $next($request);
        }

        $message = 'This action is only allowed from the office WiFi network.';

        if ($request->expectsJson()) {
            abort(403, $message);
        }

        return back()->with('error', $message);
    }

    private function isAllowedIp(?string $ip): bool
    {
        if ($ip === null) {
            return false;
        }

        foreach (config('attendance.allowed_ips', []) as $allowedIp) {
            if ($allowedIp === '') {
                continue;
            }

            if (Str::contains($allowedIp, '*') && Str::is($allowedIp, $ip)) {
                return true;
            }

            if (! Str::contains($allowedIp, '*') && IpUtils::checkIp($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }
}

```

## File: app\Http\Middleware\CheckRole.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,string $role): Response
    {
        $user = $request->user();

        if($user->role->name !== $role){
            return redirect()->route($user->role->name. '.dashboard');
        }
        return $next($request);
    }
}

```

## File: app\Http\Middleware\EnsureNotAssigned.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\MentorAssignment;

class EnsureNotAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $hasAssignment = MentorAssignment::where('intern_id',$user->id)
                        ->where('is_active',true)
                        ->exists();

        if($hasAssignment){
            return redirect()->route('intern.dashboard');
        }
        return $next($request);
    }
}

```

## File: app\Http\Middleware\LogAttendance.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LogAttendance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $service = app(\App\Services\AttendanceService::class);
            $service->logLogin(Auth::id(), $request->ip());
        }

        return $next($request);
    }
}

```

## File: app\Http\Requests\Auth\ForgotPasswordRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}

```

## File: app\Http\Requests\Auth\LoginRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::lower($this->string('email')).'|'.$this->ip();
    }
}
```

## File: app\Http\Requests\Auth\ResetPasswordRequest.php
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}

```

## File: app\Http\Requests\ProfileUpdateRequest.php
```php
<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}

```

## File: app\Http\Requests\StoreAnswerRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role->name === 'intern';
    }

    public function rules(): array
    {
        return [
            'question_id'    => 'required|exists:questions,id',
            'submitted_code' => 'nullable|string',
            'github_link'    => 'nullable|url',
            'file'           => 'nullable|file|mimes:pdf,doc,docx,zip|max:5120', // 5MB
        ];
    }
}

```

## File: app\Http\Requests\StoreSubmissionCodeRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSubmissionCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role->name === 'intern';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:5000',
        ];
    }
}

```

## File: app\Http\Requests\StoreTopicAssignRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTopicAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only mentors can assign topics
        return Auth::check() && Auth::user()->role->name === 'mentor';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'intern_id' => 'required|exists:users,id',
            'topic_id'  => 'required|exists:topics,id',
            'deadline'  => 'required|date|after:today',
        ];
    }
}

```

## File: app\Http\Requests\StoreTopicRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'mentor';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'mcq_count' => 'nullable|integer|min:0|max:50',
            'blank_count' => 'nullable|integer|min:0|max:50',
            'true_false_count' => 'nullable|integer|min:0|max:50',
            'output_count' => 'nullable|integer|min:0|max:50',
            'coding_count' => 'nullable|integer|min:0|max:50',
        ];
    }
}

```

## File: app\Jobs\EvaluateExerciseJob.php
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\InternTopicAssignment;
use App\Services\GroqEvaluationService;

class EvaluateExerciseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180]; // Retry after 1 min, then 3 mins

    public function __construct(public InternTopicAssignment $assignment) {}

    public function handle(GroqEvaluationService $evaluator)
    {
        if ($this->assignment->status === 'evaluated') {
            return; // Idempotency check 
        }

        $result = $evaluator->evaluateExercise($this->assignment);
        
        Log::info("Successfully evaluated assignment {$this->assignment->id} via Groq", [
            'grade' => $result['grade'] ?? 'N/A'
        ]);
    }

    public function failed(Throwable $exception)
    {
        Log::error("Failed to evaluate assignment {$this->assignment->id}", [
            'error' => $exception->getMessage()
        ]);

        // Assuming you might want to mark it as error or notify the user
        $this->assignment->update(['status' => 'evaluation_failed']);
    }
}

```

## File: app\Jobs\GenerateQuestionsJob.php
```php
<?php

namespace App\Jobs;

use App\Models\Topic;
use App\Services\GroqQuestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180]; // Retry after 1 min, then 3 mins

    public function __construct(
        public Topic $topic, 
        public string $type, 
        public int $count
    ) {}

    public function handle(GroqQuestionService $aiService)
    {
        $aiService->generateQuestions($this->topic, $this->type, $this->count);
    }

    public function failed(Throwable $exception)
    {
        Log::error("Failed to generate {$this->type} questions for topic {$this->topic->id}", [
            'error' => $exception->getMessage()
        ]);
        
        $this->topic->update(['status' => 'draft']); // Revert status on failure
    }
}
```

## File: app\Listeners\SendTopicPublishedNotification.php
```php
<?php

namespace App\Listeners;

use App\Events\TopicPublished;
use App\Notifications\TopicPublishedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Notification;

class SendTopicPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TopicPublished $event)
    {
        $mentors = User::whereHas('role', function($q) {
            $q->where('name', 'mentor');
        })->where('status', 'approved')->get();

        Notification::send($mentors, new TopicPublishedNotification($event->topic));
    }
}
```

## File: app\Models\Attendance.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'total_seconds',
        'date',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'login_time' => 'datetime',
            'logout_time' => 'datetime',
            'total_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalHoursAttribute(): float
    {
        return round(($this->total_seconds ?? 0) / 3600, 2);
    }
}

```

## File: app\Models\InternTopicAssignment.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternTopicAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intern_id',
        'topic_id',
        'assigned_by',
        'deadline',
        'status',
        'score',
        'grade',
        'feedback',
        'tone',
        'weak_areas',
        'strengths',
        'assigned_at',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'deadline'     => 'date',
        'weak_areas'   => 'array',
        'strengths'    => 'array',
        'score'        => 'integer',
    ];

    // â”€â”€ Relationships â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'A'     => 'Excellent',
            'B'     => 'Good',
            'C'     => 'Average',
            'D'     => 'Below Average',
            'E'     => 'Needs Improvement',
            default => 'Not graded',
        };
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['submitted', 'evaluated']);
    }
}

```

## File: app\Models\MentorAssignment.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorAssignment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'intern_id',
        'mentor_id',
        'assigned_by',
        'is_active',
        'assigned_at'
    ];

    public function intern(){
        return $this->belongsTo(User::class,'intern_id');
    }

    public function mentor(){
        return $this->belongsTo(User::class,'mentor_id');
    }

    public function assignedBy(){
        return $this->belongsTo(User::class,'assigned_by');
    }

    public function assignedInterns()
    {
        return $this->hasMany(MentorAssignment::class, 'mentor_id')
                    ->where('is_active', true);
    }   
}

```

## File: app\Models\Question.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'topic_id',
        'language',
        'type',
        'problem_statement',
        'code',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function referenceSolution()
    {
        return $this->hasOne(ReferenceSolution::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get the option text for a given letter key (A/B/C/D)
     */
    public function getOptionText(string $key): ?string
    {
        return match (strtoupper($key)) {
            'A' => $this->option_a,
            'B' => $this->option_b,
            'C' => $this->option_c,
            'D' => $this->option_d,
            default => null,
        };
    }
}

```

## File: app\Models\ReferenceSolution.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSolution extends Model
{
    protected $fillable = [
        'question_id',
        'solution_code',
        'explanation',
        'created_by',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

```

## File: app\Models\Role.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users(){
        return $this->hasMany(User::class);
    }


}

```

## File: app\Models\Submission.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'intern_id',
        'submitted_code',
        'github_link',
        'file_path',
        'syntax_score',
        'logic_score',
        'structure_score',
        'ai_total_score',
        'mentor_override_score',
        'final_score',
        'feedback',
        'status',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    /**
     * Resolve the effective final score
     */
    public function getEffectiveFinalScore(): int
    {
        return $this->final_score
            ?? $this->mentor_override_score
            ?? $this->ai_total_score
            ?? 0;
    }
}

```

## File: app\Models\Technology.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

```

## File: app\Models\Topic.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'status',
        'mcq_count',
        'blank_count',
        'true_false_count',
        'output_count',
        'coding_count',
    ];

    // â”€â”€ Relationships â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function internAssignments()
    {
        return $this->hasMany(InternTopicAssignment::class);
    }

    // â”€â”€ Scopes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function totalQuestionCount(): int
    {
        return $this->mcq_count
             + $this->blank_count
             + $this->true_false_count
             + $this->output_count
             + $this->coding_count;
    }

    public function getDifficultyLabelAttribute(): string
    {
        $totalQuestions = $this->totalQuestionCount();

        if ($this->coding_count > 0 || $this->output_count >= 2 || $totalQuestions >= 10) {
            return 'hard';
        }

        if ($this->blank_count >= 2 || $this->true_false_count >= 2 || $totalQuestions >= 5) {
            return 'medium';
        }

        return 'easy';
    }
}

```

## File: app\Models\User.php
```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Technology;
use App\Models\Role;
use App\Models\MentorAssignment;
use App\Models\Attendance;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'technology_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function technology(){
        return $this->belongsTo(Technology::class);
    }


    public function mentorAssignments(){
        return $this->hasMany(MentorAssignment::class,'intern_id');
    }

    public function currentMentorAssignment(){
        return $this->hasOne(MentorAssignment::class,'intern_id')->where('is_active', true);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

```

## File: app\Notifications\TopicPublishedNotification.php
```php
<?php

namespace App\Notifications;

use App\Models\Topic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopicPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $topic;

    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Topic Published')
                    ->line('A new topic has been published: ' . $this->topic->title)
                    ->action('View Topic', url('/mentor/topics/' . $this->topic->id))
                    ->line('You can now assign this topic to interns.');
    }
}
```

## File: app\Policies\TopicPolicy.php
```php
<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TopicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'mentor' || $user->role->name === 'hr';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Topic $topic): bool
    {
        return $user->role->name === 'hr' || ($user->role->name === 'mentor' && $user->id === $topic->mentor_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->name === 'mentor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && in_array($topic->status, ['draft', 'ai_generated']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && $topic->status === 'draft';
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Topic $topic): bool
    {
        return $user->role->name === 'mentor' && $user->id === $topic->mentor_id && in_array($topic->status, ['ai_generated', 'reviewed']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Topic $topic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Topic $topic): bool
    {
        return false;
    }
}

```

## File: app\Providers\AppServiceProvider.php
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use App\Events\TopicPublished;
use App\Listeners\SendTopicPublishedNotification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.ipms');
        Paginator::defaultSimpleView('vendor.pagination.ipms-simple');

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return rtrim(config('app.url'), '/').route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false);
        });

        // Authorization gates
        Gate::define('approve-users', fn (User $user) => $user->role->name === 'hr');
        Gate::define('assign-mentors', fn (User $user) => $user->role->name === 'hr');
        Gate::define('view-reports', fn (User $user) => in_array($user->role->name, ['hr', 'mentor']));

        // Rate limiters
        RateLimiter::for('ai-generations', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id);
        });

        RateLimiter::for('code-executions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id);
        });

        // Event listeners
        Event::listen(TopicPublished::class, SendTopicPublishedNotification::class);
    }
}

```

## File: app\Services\AttendanceService.php
```php
<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Log the login time and IP address.
     */
    public function logLogin(int $userId, string $ipAddress)
    {
        $today = Carbon::today();

        // Check if already logged in today
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['login_time' => now(), 'ip_address' => $ipAddress]
        );

        return $attendance;
    }

    /**
     * Compatibility wrapper for User model.
     */
    public function recordLogin($user)
    {
        return $this->logLogin($user->id, request()->ip());
    }

    /**
     * Log the logout time and calculate total seconds.
     */
    public function logLogout(int $userId)
    {
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', Carbon::today())
            ->first();

        if ($attendance && ! $attendance->logout_time) {
            $now = now();
            $attendance->logout_time = $now;
            
            // Calculate duration
            $diff = $attendance->login_time ? $attendance->login_time->diffInSeconds($now) : 0;
            $attendance->total_seconds = $diff;
            
            $attendance->save();
        }

        return $attendance;
    }

    /**
     * Compatibility wrapper for User model.
     */
    public function recordLogout($user)
    {
        return $this->logLogout($user->id);
    }
}

```

## File: app\Services\CodeExecutionService.php
```php
<?php
namespace App\Services;

class CodeExecutionService
{
    /**
     * Executes PHP code securely and returns output.
     * 
     * @param string $code
     * @return string|array
     */
    public function execute(string $code)
    {
        $blocked = [
            'exec', 'shell_exec', 'system', 'passthru', 'popen',
            'proc_open', 'pcntl_exec', 'file_put_contents', 'unlink',
            'rmdir', 'rename', 'copy', 'eval', 'assert', 'create_function',
        ];

        foreach ($blocked as $fn) {
            if (stripos($code, $fn) !== false) {
                return ['error' => "Function '{$fn}()' is not allowed in the exercise runner."];
            }
        }

        $tmpFile     = tempnam(sys_get_temp_dir(), 'ex_') . '.php';
        $wrappedCode = "<?php\n"
            . "set_time_limit(5);\n"
            . "ini_set('memory_limit','32M');\n"
            . "error_reporting(E_ALL);\n"
            . "ini_set('display_errors','1');\n"
            . preg_replace('/^<\?php\s*/i', '', $code);

        file_put_contents($tmpFile, $wrappedCode);

        exec('php ' . escapeshellarg($tmpFile) . ' 2>&1', $outputLines, $return);
        $output = implode("\n", $outputLines);
        @unlink($tmpFile);

        return ['output' => $output];
    }
}

```

## File: app\Services\GeminiQuestionService.php
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;

class GeminiQuestionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        $this->baseUrl =
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";
    }

    public function generateQuestions(Topic $topic, $type, $count)
    {
        $generatedQuestions = [];

        for ($i = 1; $i <= $count; $i++) {

            $prompt = "
Generate ONE {$type} programming question for topic: {$topic->title}
Language: PHP

Return strictly valid JSON:

{
  \"problem_statement\": \"...\",
  \"max_syntax_marks\": 5,
  \"max_logic_marks\": 10,
  \"max_structure_marks\": 5,
  \"solutions\": [
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    }
  ]
}
";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post(
                $this->baseUrl . "?key=" . $this->apiKey,
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            if (!$response->successful()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $result = $response->json();

            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                throw new \Exception("Invalid Gemini response.");
            }

            $text = preg_replace('/```json|```/', '', $text);

            $data = json_decode($text, true);

            if (!$data) {
                throw new \Exception("Gemini returned invalid JSON.");
            }

            $question = Question::create([
                'topic_id' => $topic->id,
                'language' => Question::LANG_PHP,
                'type' => $type,
                'problem_statement' => $data['problem_statement'],
                'max_syntax_marks' => $data['max_syntax_marks'],
                'max_logic_marks' => $data['max_logic_marks'],
                'max_structure_marks' => $data['max_structure_marks'],
                'total_marks' =>
                    $data['max_syntax_marks'] +
                    $data['max_logic_marks'] +
                    $data['max_structure_marks'],
            ]);

            if (isset($data['solutions'])) {

                foreach ($data['solutions'] as $solution) {

                    ReferenceSolution::create([
                        'question_id' => $question->id,
                        'solution_code' => $solution['code'],
                        'explanation' => $solution['explanation'],
                        'created_by' => ReferenceSolution::CREATED_BY_AI,
                    ]);
                }
            }

            $generatedQuestions[] = $question;
        }

        return $generatedQuestions;
    }
}
```

## File: app\Services\Groqevaluationservice.php
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\InternTopicAssignment;
use App\Models\Submission;
use App\Models\Question;

class GroqEvaluationService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key', '');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    /**
     * Evaluate the ENTIRE exercise for one assignment.
     * Acts as a Senior Technical Mentor.
     */
    public function evaluateExercise(InternTopicAssignment $assignment): array
    {
        $topic       = $assignment->topic;
        $internId    = $assignment->intern_id;
        $questionIds = Question::where('topic_id', $topic->id)->pluck('id');

        $submissions = Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->with('question.referenceSolution')
            ->get()
            ->keyBy('question_id');

        $questions = Question::where('topic_id', $topic->id)
            ->with('referenceSolution')
            ->get();

        $prompt = $this->buildExercisePrompt($topic->title, $questions, $submissions);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120)->post($this->baseUrl, [
            'model'    => 'llama-3.1-8b-instant',
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'You are a Senior Technical Mentor evaluating an intern\'s full assignment. '
                               . 'Do NOT give per-question marks. Analyze the entire submission holistically. '
                               . 'Evaluate logic, code quality, understanding of topic, correctness, and problem-solving approach. '
                               . 'Return ONLY valid JSON. No markdown, no extra text, no code blocks.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.2,
            'max_tokens'      => 1500,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Groq API Error: ' . $response->body());
        }

        $result = $response->json('choices.0.message.content');
        $result = json_decode($result, true);

        if (! isset($result['grade'])) {
            throw new \Exception('Invalid AI response structure — missing grade.');
        }

        $grade = strtoupper(trim($result['grade']));
        if (! in_array($grade, ['A', 'B', 'C', 'D', 'F'])) {
            $grade = 'D'; // Default to D if invalid
        }

        $assignment->update([
            'status'     => 'evaluated',
            'score'      => $result['score'] ?? 0,
            'grade'      => $grade,
            'feedback'   => $result['feedback'] ?? null,
            'tone'       => $result['tone'] ?? 'neutral',
            'weak_areas' => $result['weak_areas'] ?? [],
            'strengths'  => $result['strengths'] ?? [],
            'submitted_at' => $assignment->submitted_at ?? now(),
        ]);

        // Mark all submissions as ai_evaluated
        Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->update(['status' => 'ai_evaluated']);

        return [
            'score'      => $result['score'] ?? 0,
            'grade'      => $grade,
            'feedback'   => $result['feedback'] ?? '',
            'tone'       => $result['tone'] ?? 'neutral',
            'weak_areas' => $result['weak_areas'] ?? [],
            'strengths'  => $result['strengths'] ?? [],
        ];
    }

    /**
     * Build the holistic prompt for the Senior Mentor.
     */
    private function buildExercisePrompt(string $topicTitle, $questions, $submissions): string
    {
        $lines   = [];
        $lines[] = "### TOPIC: {$topicTitle}";
        $lines[] = '';
        $lines[] = "### INTERN SUBMISSION (All Questions):";
        $lines[] = str_repeat('-', 40);

        foreach ($questions as $i => $q) {
            $num        = $i + 1;
            $submission = $submissions->get($q->id);
            $answer     = $submission ? trim($submission->submitted_code) : '[NOT ANSWERED]';

            $lines[] = "Q{$num}: {$q->problem_statement}";
            
            if ($q->type === 'coding' || $q->type === 'output') {
                $lines[] = "Intern's Answer/Code:\n```\n{$answer}\n```";
                $ref = $q->referenceSolution?->solution_code ?? $q->correct_answer;
                if ($ref) {
                    $lines[] = "Reference/Correct solution for context:\n```\n{$ref}\n```";
                }
            } else {
                $lines[] = "Intern's Answer: {$answer}";
                if ($q->correct_answer) {
                    $lines[] = "Correct Answer: {$q->correct_answer}";
                }
            }
            $lines[] = '';
        }

        $lines[] = str_repeat('-', 40);
        $lines[] = "### YOUR TASK:";
        $lines[] = "1. Analyze the entire submission holistically as a Senior Technical Mentor.";
        $lines[] = "2. Evaluate logic, code quality, understanding of topic, correctness, and problem-solving approach.";
        $lines[] = "3. Determine a score from 0 to 100.";
        $lines[] = "4. Assign a Grade (A: 90-100, B: 75-89, C: 60-74, D: 40-59, F: <40).";
        $lines[] = "5. Identify the Tone of the intern's work (positive, negative, mixed).";
        $lines[] = "6. Highlight specific Strengths and Weak Areas.";
        $lines[] = '';
        $lines[] = "### IMPORTANT RULES:";
        $lines[] = "- DO NOT evaluate per question.";
        $lines[] = "- Focus on overall understanding.";
        $lines[] = "- Feedback must be honest, clear and actionable.";
        $lines[] = '';
        $lines[] = "### FORMAT (JSON ONLY):";
        $lines[] = "{";
        $lines[] = '  "score": 85,';
        $lines[] = '  "grade": "B",';
        $lines[] = '  "tone": "positive",';
        $lines[] = '  "feedback": "...",';
        $lines[] = '  "weak_areas": ["loops", "conditions"],';
        $lines[] = '  "strengths": ["modular code", "efficiency"]';
        $lines[] = "}";

        return implode("\n", $lines);
    }
}

```

## File: app\Services\GroqQuestionService.php
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;
use App\Exceptions\AIServiceException;

class GroqQuestionService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key', '');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function generateQuestions(Topic $topic, string $type, int $count): bool
    {
        Log::info("Starting AI question generation", [
            'topic_id' => $topic->id,
            'type'     => $type,
            'count'    => $count,
        ]);

        $prompt = $this->buildPrompt($type, $count, $topic->title);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(60)->post($this->baseUrl, [
            'model'           => 'llama-3.1-8b-instant',
            'messages'        => [
                [
                    'role'    => 'system',
                    'content' => 'You are a PHP exam question generator. Always return valid JSON only. No extra text.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.3,
        ]);

        if (! $response->successful()) {
            throw new AIServiceException('Groq API Error: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content');

        if (! $text) {
            throw new AIServiceException('Empty Groq response.');
        }

        $data = json_decode($text, true);

        if (! isset($data['questions'])) {
            throw new AIServiceException('Invalid AI JSON structure â€” missing "questions" key.');
        }

        foreach ($data['questions'] as $item) {

            $questionData = [
                'topic_id'          => $topic->id,
                'language'          => 'php',
                'type'              => $type,
                'problem_statement' => $item['question'] ?? '',
                'code'              => isset($item['code']) ? trim($item['code']) : null,
                'correct_answer'    => $item['correct_answer'] ?? null,
            ];

            // MCQ: store 4 options
            if ($type === 'mcq') {
                $opts = $item['options'] ?? [];
                $questionData['option_a']       = $opts[0] ?? null;
                $questionData['option_b']       = $opts[1] ?? null;
                $questionData['option_c']       = $opts[2] ?? null;
                $questionData['option_d']       = $opts[3] ?? null;
                $questionData['correct_answer'] = $item['correct_option'] ?? null;
            }

            $question = Question::create($questionData);

            // Store reference solution
            $solutionText = $item['reference_solution']
                ?? $item['correct_answer']
                ?? null;

            if ($solutionText) {
                ReferenceSolution::create([
                    'question_id'   => $question->id,
                    'solution_code' => $solutionText,
                    'explanation'   => $item['explanation'] ?? null,
                    'created_by'    => 'ai',
                ]);
            }
        }

        Log::info("Generated {$count} {$type} questions for topic {$topic->id}");

        return true;
    }

    private function buildPrompt(string $type, int $count, string $topic): string
    {
        return match ($type) {

            'mcq' => <<<PROMPT
Generate {$count} PHP MCQ questions about the topic: {$topic}

Rules:
- Each question must have exactly 4 options (A, B, C, D)
- correct_option must be exactly one of: A, B, C, D
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What does strlen() return in PHP?",
      "options": ["Length of string", "Array size", "Boolean value", "Object count"],
      "correct_option": "A",
      "explanation": "strlen() returns the number of characters in a string."
    }
  ]
}
PROMPT,

            'true_false' => <<<PROMPT
Generate {$count} True/False PHP questions about the topic: {$topic}

Rules:
- correct_answer must be exactly "True" or "False"
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "PHP is a server-side scripting language.",
      "correct_answer": "True",
      "explanation": "PHP runs on the server and generates HTML output."
    }
  ]
}
PROMPT,

            'blank' => <<<PROMPT
Generate {$count} fill-in-the-blank PHP questions about the topic: {$topic}

Rules:
- Use _____ as the blank in the question
- correct_answer is the word/phrase that fills the blank
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "The PHP function to get the length of a string is _____.",
      "correct_answer": "strlen()",
      "explanation": "strlen() counts the number of characters in a string."
    }
  ]
}
PROMPT,

            'output' => <<<PROMPT
Generate {$count} PHP output prediction questions about the topic: {$topic}

Rules:
- The question text must always be: "What will be the output of the following PHP code?"
- The code must be valid PHP
- correct_answer is the EXACT console output
- reference_solution is the same as correct_answer
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What will be the output of the following PHP code?",
      "code": "$x = 5;\necho $x * 2;",
      "correct_answer": "10",
      "reference_solution": "10",
      "explanation": "5 multiplied by 2 equals 10."
    }
  ]
}
PROMPT,

            'coding' => <<<PROMPT
Generate {$count} PHP coding challenge questions about the topic: {$topic}

Rules:
- Each question should ask the intern to write a PHP function or script
- reference_solution must be a complete working PHP solution
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "Write a PHP function that reverses a string without using strrev().",
      "reference_solution": "function reverseString($str) {\n  $result = '';\n  for ($i = strlen($str) - 1; $i >= 0; $i--) {\n    $result .= $str[$i];\n  }\n  return $result;\n}",
      "explanation": "We iterate from the last character to the first and build the reversed string."
    }
  ]
}
PROMPT,

            default => throw new AIServiceException("Unsupported question type: {$type}"),
        };
    }
}

```

## File: app\Services\OpenAIQuestionService.php
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;

class OpenAIQuestionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = "https://api.openai.com/v1/chat/completions";
    }

    public function generateQuestions(Topic $topic)
    {
        $prompt = "
Generate ONE coding question for topic: {$topic->title}
Language: PHP

Return strictly valid JSON in this format:

{
  \"problem_statement\": \"...\",
  \"max_syntax_marks\": 5,
  \"max_logic_marks\": 10,
  \"max_structure_marks\": 5,
  \"solutions\": [
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    },
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    }
  ]
}
";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            "model" => "gpt-4o-mini",  // cheaper model
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are an expert coding question generator."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.7
        ]);

        if (!$response->successful()) {
            dd($response->status(), $response->body());
        }

        $result = $response->json();

        $text = $result['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            throw new \Exception("Invalid OpenAI response.");
        }

        $text = preg_replace('/```json|```/', '', $text);

        $data = json_decode($text, true);

        if (!$data) {
            throw new \Exception("OpenAI returned invalid JSON.");
        }

        $question = Question::create([
            'topic_id' => $topic->id,
            'language' => Question::LANG_PHP,
            'problem_statement' => $data['problem_statement'],
            'max_syntax_marks' => $data['max_syntax_marks'],
            'max_logic_marks' => $data['max_logic_marks'],
            'max_structure_marks' => $data['max_structure_marks'],
            'total_marks' =>
                $data['max_syntax_marks'] +
                $data['max_logic_marks'] +
                $data['max_structure_marks'],
        ]);

        foreach ($data['solutions'] as $solution) {
            ReferenceSolution::create([
                'question_id' => $question->id,
                'solution_code' => $solution['code'],
                'explanation' => $solution['explanation'],
                'created_by' => ReferenceSolution::CREATED_BY_AI,
            ]);
        }

        return $question;
    }
}
```

## File: app\Services\PasswordResetService.php
```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function sendResetLink(string $email): string
    {
        $userExists = User::query()->where('email', $email)->exists();

        if (! $userExists) {
            return Password::RESET_LINK_SENT;
        }

        return Password::sendResetLink(['email' => $email]);
    }

    public function invalidateUserSessions(User $user): void
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}

```

## File: app\View\Components\AppLayout.php
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}

```

## File: app\View\Components\GuestLayout.php
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}

```

## File: bootstrap\app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware aliases
        $middleware->alias([
            'checkrole'  => \App\Http\Middleware\CheckRole::class,
            'approved'   => \App\Http\Middleware\CheckApproved::class,
            'assigned'   => \App\Http\Middleware\CheckAssigned::class,
            'notassigned'=> \App\Http\Middleware\EnsureNotAssigned::class,
            'office'     => \App\Http\Middleware\CheckOfficeNetwork::class,
            'log.attendance' => \App\Http\Middleware\LogAttendance::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LogAttendance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

```

## File: bootstrap\cache\packages.php
```php
<?php return array (
  'laravel/breeze' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Breeze\\BreezeServiceProvider',
    ),
  ),
  'laravel/pail' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Pail\\PailServiceProvider',
    ),
  ),
  'laravel/sail' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Sail\\SailServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
  'nunomaduro/termwind' => 
  array (
    'providers' => 
    array (
      0 => 'Termwind\\Laravel\\TermwindServiceProvider',
    ),
  ),
);
```

## File: bootstrap\cache\services.php
```php
<?php return array (
  'providers' => 
  array (
    0 => 'Illuminate\\Auth\\AuthServiceProvider',
    1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    2 => 'Illuminate\\Bus\\BusServiceProvider',
    3 => 'Illuminate\\Cache\\CacheServiceProvider',
    4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
    6 => 'Illuminate\\Cookie\\CookieServiceProvider',
    7 => 'Illuminate\\Database\\DatabaseServiceProvider',
    8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
    10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
    11 => 'Illuminate\\Hashing\\HashServiceProvider',
    12 => 'Illuminate\\Mail\\MailServiceProvider',
    13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
    14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
    15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    17 => 'Illuminate\\Queue\\QueueServiceProvider',
    18 => 'Illuminate\\Redis\\RedisServiceProvider',
    19 => 'Illuminate\\Session\\SessionServiceProvider',
    20 => 'Illuminate\\Translation\\TranslationServiceProvider',
    21 => 'Illuminate\\Validation\\ValidationServiceProvider',
    22 => 'Illuminate\\View\\ViewServiceProvider',
    23 => 'Laravel\\Breeze\\BreezeServiceProvider',
    24 => 'Laravel\\Pail\\PailServiceProvider',
    25 => 'Laravel\\Sail\\SailServiceProvider',
    26 => 'Laravel\\Tinker\\TinkerServiceProvider',
    27 => 'Carbon\\Laravel\\ServiceProvider',
    28 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    29 => 'Termwind\\Laravel\\TermwindServiceProvider',
    30 => 'App\\Providers\\AppServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Illuminate\\Auth\\AuthServiceProvider',
    1 => 'Illuminate\\Cookie\\CookieServiceProvider',
    2 => 'Illuminate\\Database\\DatabaseServiceProvider',
    3 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    4 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
    5 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
    6 => 'Illuminate\\Notifications\\NotificationServiceProvider',
    7 => 'Illuminate\\Pagination\\PaginationServiceProvider',
    8 => 'Illuminate\\Session\\SessionServiceProvider',
    9 => 'Illuminate\\View\\ViewServiceProvider',
    10 => 'Laravel\\Pail\\PailServiceProvider',
    11 => 'Carbon\\Laravel\\ServiceProvider',
    12 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    13 => 'Termwind\\Laravel\\TermwindServiceProvider',
    14 => 'App\\Providers\\AppServiceProvider',
  ),
  'deferred' => 
  array (
    'Illuminate\\Broadcasting\\BroadcastManager' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Contracts\\Broadcasting\\Factory' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Contracts\\Broadcasting\\Broadcaster' => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
    'Illuminate\\Bus\\Dispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Contracts\\Bus\\Dispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Contracts\\Bus\\QueueingDispatcher' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Bus\\BatchRepository' => 'Illuminate\\Bus\\BusServiceProvider',
    'Illuminate\\Bus\\DatabaseBatchRepository' => 'Illuminate\\Bus\\BusServiceProvider',
    'cache' => 'Illuminate\\Cache\\CacheServiceProvider',
    'cache.store' => 'Illuminate\\Cache\\CacheServiceProvider',
    'cache.psr6' => 'Illuminate\\Cache\\CacheServiceProvider',
    'memcached.connector' => 'Illuminate\\Cache\\CacheServiceProvider',
    'Illuminate\\Cache\\RateLimiter' => 'Illuminate\\Cache\\CacheServiceProvider',
    'Illuminate\\Foundation\\Console\\AboutCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Cache\\Console\\ClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Cache\\Console\\ForgetCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ClearCompiledCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Auth\\Console\\ClearResetsCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigCacheCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigShowCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\DbCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\MonitorCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\PruneCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\ShowCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\TableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\WipeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\DownCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EnvironmentCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EnvironmentDecryptCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EnvironmentEncryptCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventCacheCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventListCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Concurrency\\Console\\InvokeSerializedClosureCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\KeyGenerateCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\OptimizeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\OptimizeClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\PackageDiscoverCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Cache\\Console\\PruneStaleTagsCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ListFailedCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\FlushFailedCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ForgetFailedCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ListenCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\MonitorCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\PauseCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\PruneBatchesCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\PruneFailedJobsCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RestartCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\ResumeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RetryCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\RetryBatchCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\WorkCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ReloadCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RouteCacheCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RouteClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RouteListCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\DumpCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Seeds\\SeedCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleFinishCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleListCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleRunCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleClearCacheCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleTestCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleWorkCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Console\\Scheduling\\ScheduleInterruptCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\ShowModelCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\StorageLinkCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\StorageUnlinkCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\UpCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ViewCacheCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ViewClearCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ApiInstallCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\BroadcastingInstallCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Cache\\Console\\CacheTableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\CastMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ChannelListCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ChannelMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ClassMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ComponentMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConfigPublishCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ConsoleMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Routing\\Console\\ControllerMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\DocsCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EnumMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventGenerateCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\EventMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ExceptionMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Factories\\FactoryMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\InterfaceMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\JobMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\JobMiddlewareMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\LangPublishCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ListenerMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\MailMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Routing\\Console\\MiddlewareMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ModelMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\NotificationMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Notifications\\Console\\NotificationTableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ObserverMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\PolicyMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ProviderMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\FailedTableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\TableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Queue\\Console\\BatchesTableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RequestMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ResourceMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\RuleMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ScopeMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Seeds\\SeederMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Session\\Console\\SessionTableCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ServeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\StubPublishCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\TestMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\TraitMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\VendorPublishCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Foundation\\Console\\ViewMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migrator' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migration.repository' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'migration.creator' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Migrations\\Migrator' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\MigrateCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\FreshCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\InstallCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\RefreshCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\ResetCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\RollbackCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\StatusCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Database\\Console\\Migrations\\MigrateMakeCommand' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'composer' => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
    'Illuminate\\Concurrency\\ConcurrencyManager' => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
    'hash' => 'Illuminate\\Hashing\\HashServiceProvider',
    'hash.driver' => 'Illuminate\\Hashing\\HashServiceProvider',
    'mail.manager' => 'Illuminate\\Mail\\MailServiceProvider',
    'mailer' => 'Illuminate\\Mail\\MailServiceProvider',
    'Illuminate\\Mail\\Markdown' => 'Illuminate\\Mail\\MailServiceProvider',
    'auth.password' => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    'auth.password.broker' => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
    'Illuminate\\Contracts\\Pipeline\\Hub' => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    'pipeline' => 'Illuminate\\Pipeline\\PipelineServiceProvider',
    'queue' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.connection' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.failer' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.listener' => 'Illuminate\\Queue\\QueueServiceProvider',
    'queue.worker' => 'Illuminate\\Queue\\QueueServiceProvider',
    'redis' => 'Illuminate\\Redis\\RedisServiceProvider',
    'redis.connection' => 'Illuminate\\Redis\\RedisServiceProvider',
    'translator' => 'Illuminate\\Translation\\TranslationServiceProvider',
    'translation.loader' => 'Illuminate\\Translation\\TranslationServiceProvider',
    'validator' => 'Illuminate\\Validation\\ValidationServiceProvider',
    'validation.presence' => 'Illuminate\\Validation\\ValidationServiceProvider',
    'Illuminate\\Contracts\\Validation\\UncompromisedVerifier' => 'Illuminate\\Validation\\ValidationServiceProvider',
    'Laravel\\Breeze\\Console\\InstallCommand' => 'Laravel\\Breeze\\BreezeServiceProvider',
    'Laravel\\Sail\\Console\\InstallCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'Laravel\\Sail\\Console\\PublishCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
  ),
  'when' => 
  array (
    'Illuminate\\Broadcasting\\BroadcastServiceProvider' => 
    array (
    ),
    'Illuminate\\Bus\\BusServiceProvider' => 
    array (
    ),
    'Illuminate\\Cache\\CacheServiceProvider' => 
    array (
    ),
    'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider' => 
    array (
    ),
    'Illuminate\\Concurrency\\ConcurrencyServiceProvider' => 
    array (
    ),
    'Illuminate\\Hashing\\HashServiceProvider' => 
    array (
    ),
    'Illuminate\\Mail\\MailServiceProvider' => 
    array (
    ),
    'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider' => 
    array (
    ),
    'Illuminate\\Pipeline\\PipelineServiceProvider' => 
    array (
    ),
    'Illuminate\\Queue\\QueueServiceProvider' => 
    array (
    ),
    'Illuminate\\Redis\\RedisServiceProvider' => 
    array (
    ),
    'Illuminate\\Translation\\TranslationServiceProvider' => 
    array (
    ),
    'Illuminate\\Validation\\ValidationServiceProvider' => 
    array (
    ),
    'Laravel\\Breeze\\BreezeServiceProvider' => 
    array (
    ),
    'Laravel\\Sail\\SailServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
  ),
);
```

## File: bootstrap\providers.php
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
];

```

## File: composer.json
```json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1",
        "openai-php/client": "^0.19.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/breeze": "^2.3",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.24",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^11.5.3"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}

```

## File: config\app.php
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];

```

## File: config\attendance.php
```php
<?php

return [
    'allowed_ips' => array_values(array_filter(array_map(
        static fn (string $ip): string => trim($ip),
        explode(',', env('OFFICE_ALLOWED_IPS', '127.0.0.1,::1,192.168.1.*'))
    ))),
];

```

## File: config\auth.php
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

```

## File: config\cache.php
```php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "octane",
    |                    "failover", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

];

```

## File: config\database.php
```php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];

```

## File: config\filesystems.php
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

```

## File: config\logging.php
```php
<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],  
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];

```

## File: config\mail.php
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];

```

## File: config\queue.php
```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis",
    |          "deferred", "background", "failover", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];

```

## File: config\services.php
```php
<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // â”€â”€ Groq AI â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
    ],

    // â”€â”€ Gemini (optional fallback) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

];

```

## File: config\session.php
```php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that is utilized for
    | incoming requests. Laravel supports a variety of storage options to
    | persist session data. Database storage is a great default choice.
    |
    | Supported: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to expire immediately when the browser is closed then you may
    | indicate that via the expire_on_close configuration option.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your session data
    | should be encrypted before it's stored. All encryption is performed
    | automatically by Laravel and you may use the session like normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When utilizing the "file" session driver, the session files are placed
    | on disk. The default storage location is defined here; however, you
    | are free to provide another location where they should be stored.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table to
    | be used to store sessions. Of course, a sensible default is defined
    | for you; however, you're welcome to change this to another table.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | When using one of the framework's cache driven session backends, you may
    | define the cache store which should be used to store the session data
    | between requests. This must match one of your defined cache stores.
    |
    | Affects: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that it will
    | happen on a given request. By default, the odds are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Here you may change the name of the session cookie that is created by
    | the framework. Typically, you should not need to change this value
    | since doing so does not grant a meaningful security improvement.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as available. Typically, this will be the root path of
    | your application, but you're free to change this when necessary.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | This value determines the domain and subdomains the session cookie is
    | available to. By default, the cookie will be available to the root
    | domain without subdomains. Typically, this shouldn't be changed.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | By setting this option to true, session cookies will only be sent back
    | to the server if the browser has a HTTPS connection. This will keep
    | the cookie from being sent to you when it can't be done securely.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will prevent JavaScript from accessing the
    | value of the cookie and the cookie will only be accessible through
    | the HTTP protocol. It's unlikely you should disable this option.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this value to "lax" to permit secure cross-site requests.
    |
    | See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will tie the cookie to the top-level site for
    | a cross-site context. Partitioned cookies are accepted by the browser
    | when flagged "secure" and the Same-Site attribute is set to "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];

```

## File: database\factories\RoleFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            // Keep names simple and unique to satisfy the unique constraint.
            'name' => $this->faker->unique()->randomElement([
                'intern', 'mentor', 'admin', 'hr', 'manager',
            ]),
        ];
    }

    public function intern(): static
    {
        return $this->state(fn () => ['name' => 'intern']);
    }

    public function mentor(): static
    {
        return $this->state(fn () => ['name' => 'mentor']);
    }
}

```

## File: database\factories\UserFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure every factory user is attached to a valid role.
        $roleId = Role::firstOrCreate(['name' => 'intern'])->id;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => $roleId,
            'status' => 'approved',
            'technology_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

```

## File: database\migrations\0001_01_01_000000_create_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('users', function (Blueprint $table) {
    $table->engine = 'InnoDB';
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

```

## File: database\migrations\0001_01_01_000001_create_cache_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};

```

## File: database\migrations\0001_01_01_000002_create_jobs_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};

```

## File: database\migrations\2026_02_18_055442_create_roles_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

```

## File: database\migrations\2026_02_18_063040_add_role_id_and_status_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->enum('status',['pending','approved','rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id']);
            $table->dropColumn('status');
        });
    }
};

```

## File: database\migrations\2026_02_23_055110_create_technologies_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('technologies', function (Blueprint $table) {
    $table->engine = 'InnoDB';
    $table->id();
    $table->string('name')->unique();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};

```

## File: database\migrations\2026_02_23_055208_add_technology_id_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
    $table->foreignId('technology_id')
          ->nullable()
          ->constrained('technologies')
          ->nullOnDelete();        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
Schema::table('users', function (Blueprint $table) {
    $table->dropForeign(['technology_id']);
    $table->dropColumn('technology_id');
});        });
    }
};

```

## File: database\migrations\2026_02_25_050422_create_mentor_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mentor_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intern_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');

            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_assignments');
    }
};

```

## File: database\migrations\2026_03_02_102959_create_topics_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('topics', function (Blueprint $table) {
    $table->id();

    $table->foreignId('mentor_id')
          ->constrained('users')
          ->onDelete('cascade');

    $table->string('title');
    $table->text('description')->nullable();

    $table->enum('status', [
        'draft',
        'ai_generated',
        'reviewed',
        'published'
    ])->default('draft');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};

```

## File: database\migrations\2026_03_02_103059_create_questions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('topic_id')
                ->constrained()
                ->onDelete('cascade');

            $table->enum('language', ['php', 'sql', 'javascript']);

            $table->enum('type', [
                'mcq',
                'blank',
                'true_false',
                'output',
                'coding'
            ]);

            $table->longText('problem_statement');

            $table->integer('marks')->default(5);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

```

## File: database\migrations\2026_03_02_103153_create_reference_solutions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reference_solutions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained()
                ->onDelete('cascade');

            $table->longText('solution_code');
            $table->text('explanation')->nullable();

            $table->enum('created_by', ['ai', 'mentor']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_solutions');
    }
};

```

## File: database\migrations\2026_03_02_103227_create_submissions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('intern_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->longText('submitted_code');

            $table->integer('syntax_score')->default(0);
            $table->integer('logic_score')->default(0);
            $table->integer('structure_score')->default(0);

            $table->integer('ai_total_score')->default(0);

            $table->integer('mentor_override_score')->nullable();
            $table->integer('final_score')->nullable();

            $table->enum('status', [
                'submitted',
                'ai_evaluated',
                'reviewed'
            ])->default('submitted');

            $table->text('feedback')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

```

## File: database\migrations\2026_03_04_153646_remove_marks_from_questions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('marks');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('marks')->nullable();
        });
    }

};

```

## File: database\migrations\2026_03_04_153826_add_question_counts_to_topics_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {

            $table->integer('mcq_count')->default(0);
            $table->integer('blank_count')->default(0);
            $table->integer('true_false_count')->default(0);
            $table->integer('output_count')->default(0);
            $table->integer('coding_count')->default(0);

        });
    }

    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {

            $table->dropColumn([
                'mcq_count',
                'blank_count',
                'true_false_count',
                'output_count',
                'coding_count'
            ]);

        });
    }
};
```

## File: database\migrations\2026_03_04_174046_create_intern_topic_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intern_topic_assignments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('intern_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('topic_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('deadline');

            $table->enum('status',[
                'assigned',
                'in_progress',
                'submitted',
                'evaluated'
            ])->default('assigned');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_topic_assignments');
    }
};

```

## File: database\migrations\2026_03_07_061027_add_code_to_questions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up()
{
Schema::table('questions', function (Blueprint $table) {

$table->text('code')->nullable()->after('problem_statement');

});
}

public function down()
{
Schema::table('questions', function (Blueprint $table) {

$table->dropColumn('code');

});
}

};
```

## File: database\migrations\2026_03_10_000001_add_options_to_questions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {

            // MCQ: 4 answer options stored directly on question row
            $table->string('option_a')->nullable()->after('code');
            $table->string('option_b')->nullable()->after('option_a');
            $table->string('option_c')->nullable()->after('option_b');
            $table->string('option_d')->nullable()->after('option_c');

            // Correct answer key:
            // MCQ        → "A" | "B" | "C" | "D"
            // true_false → "True" | "False"
            // blank      → expected word/phrase
            // output     → exact expected output string
            // coding     → not used for auto-check (AI evaluates)
            $table->text('correct_answer')->nullable()->after('option_d');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'option_a', 'option_b', 'option_c', 'option_d',
                'correct_answer',
            ]);
        });
    }
};
```

## File: database\migrations\2026_03_10_000002_add_scores_to_submissions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;

// This migration is intentionally a no-op.
// The submissions table ALREADY HAS all scoring columns from the original
// 2026_03_02_103227_create_submissions_table migration:
//   syntax_score, logic_score, structure_score, ai_total_score,
//   mentor_override_score, final_score, feedback, status
// Delete this file — or just run it, it does nothing.

return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
```

## File: database\migrations\2026_03_16_123254_add_grade_feedback_to_intern_topic_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            // Grade A/B/C/D/E assigned by AI after exercise submission
            $table->char('grade', 1)->nullable()->after('status');

            // Overall AI feedback for the whole exercise
            $table->text('feedback')->nullable()->after('grade');
        });
    }

    public function down(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->dropColumn(['grade', 'feedback']);
        });
    }
};
```

## File: database\migrations\2026_03_23_121226_add_deleted_at_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_23_121242_add_deleted_at_to_topics_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_23_121306_add_deleted_at_to_questions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_23_121321_add_deleted_at_to_submissions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_23_121333_add_deleted_at_to_intern_topic_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_23_121356_add_deleted_at_to_mentor_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mentor_assignments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

```

## File: database\migrations\2026_03_29_000001_create_attendances_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'logout_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

```

## File: database\migrations\2026_03_31_182438_add_weak_areas_to_intern_topic_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->json('weak_areas')->nullable()->after('feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->dropColumn('weak_areas');
        });
    }
};

```

## File: database\migrations\2026_04_01_183314_enhance_intern_topic_assignments_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->nullable()->after('status');
            $table->string('tone')->nullable()->after('feedback');
            $table->json('strengths')->nullable()->after('weak_areas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->dropColumn(['score', 'tone', 'strengths']);
        });
    }
};

```

## File: database\migrations\2026_04_01_183321_enhance_submissions_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('github_link')->nullable()->after('submitted_code');
            $table->string('file_path')->nullable()->after('github_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['github_link', 'file_path']);
        });
    }
};

```

## File: database\migrations\2026_04_01_183408_enhance_attendances_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};

```

## File: database\seeders\DatabaseSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(TechnologySeeder::class );
        $this->call(HrUserSeeder::class);
    }
}

```

## File: database\seeders\HrUserSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 
use App\Models\Role;
use App\Models\User;

class HrUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Check if the role exists
        $hrRole = Role::where('name', 'hr')->first();

        if (!$hrRole) {
            // Throwing an exception is good for debugging!
            throw new \Exception('HR role not found. Please run RoleSeeder first.');
        }

        // 2. Create the user if they don't exist
        User::firstOrCreate(
            ['email' => 'hr@role.com'], // Unique identifier
            [
                'name' => 'HR Admin',
                'password' => Hash::make('hr@123'),
                'role_id' => $hrRole->id,
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        ); 
    }
}
```

## File: database\seeders\RoleSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'hr']);
        Role::firstOrCreate(['name' => 'mentor']);
        Role::firstOrCreate(['name' => 'intern']);
    }
        
}

```

## File: database\seeders\TechnologySeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Technology;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Technology::firstOrCreate(['name' => 'PHP']);
        Technology::firstOrCreate(['name' => 'Java']);
        Technology::firstOrCreate(['name' => 'QA']);
        Technology::firstOrCreate(['name' => 'AI']);
        Technology::firstOrCreate(['name' => 'MERN']);
    }
}

```

## File: database\seeders\UserAccountSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Technology;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hrRole = Role::where('name', 'hr')->first();
        $mentorRole = Role::where('name', 'mentor')->first();
        $internRole = Role::where('name', 'intern')->first();
        $techs = Technology::all();

        // 1 HR (Approved)
        User::updateOrCreate(
            ['email' => 'hr@system.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
                'role_id' => $hrRole->id,
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        // 3 Mentors (Pending)
        for ($i = 1; $i <= 3; $i++) {
            User::updateOrCreate(
                ['email' => "mentor{$i}@system.com"],
                [
                    'name' => "Mentor User {$i}",
                    'password' => Hash::make('password'),
                    'role_id' => $mentorRole->id,
                    'status' => 'pending',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 10 Interns (Pending)
        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['email' => "intern{$i}@system.com"],
                [
                    'name' => "Intern User {$i}",
                    'password' => Hash::make('password'),
                    'role_id' => $internRole->id,
                    'status' => 'pending',
                    'technology_id' => $techs->random()->id,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}

```

## File: package-lock.json
```json
{
    "name": "ai-driven-internship-management-system",
    "lockfileVersion": 3,
    "requires": true,
    "packages": {
        "": {
            "dependencies": {
                "sweetalert2": "^11.26.20"
            },
            "devDependencies": {
                "@tailwindcss/forms": "^0.5.2",
                "@tailwindcss/vite": "^4.0.0",
                "alpinejs": "^3.4.2",
                "autoprefixer": "^10.4.2",
                "axios": "^1.11.0",
                "concurrently": "^9.0.1",
                "laravel-vite-plugin": "^2.0.0",
                "postcss": "^8.4.31",
                "tailwindcss": "^3.1.0",
                "vite": "^7.0.7"
            }
        },
        "node_modules/@alloc/quick-lru": {
            "version": "5.2.0",
            "resolved": "https://registry.npmjs.org/@alloc/quick-lru/-/quick-lru-5.2.0.tgz",
            "integrity": "sha512-UrcABB+4bUrFABwbluTIBErXwvbsU/V7TZWfmbgJfbkwiBuziS9gxdODUyuiecfdGQ85jglMW6juS3+z5TsKLw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=10"
            },
            "funding": {
                "url": "https://github.com/sponsors/sindresorhus"
            }
        },
        "node_modules/@esbuild/aix-ppc64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/aix-ppc64/-/aix-ppc64-0.27.3.tgz",
            "integrity": "sha512-9fJMTNFTWZMh5qwrBItuziu834eOCUcEqymSH7pY+zoMVEZg3gcPuBNxH1EvfVYe9h0x/Ptw8KBzv7qxb7l8dg==",
            "cpu": [
                "ppc64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "aix"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/android-arm": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/android-arm/-/android-arm-0.27.3.tgz",
            "integrity": "sha512-i5D1hPY7GIQmXlXhs2w8AWHhenb00+GxjxRncS2ZM7YNVGNfaMxgzSGuO8o8SJzRc/oZwU2bcScvVERk03QhzA==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/android-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/android-arm64/-/android-arm64-0.27.3.tgz",
            "integrity": "sha512-YdghPYUmj/FX2SYKJ0OZxf+iaKgMsKHVPF1MAq/P8WirnSpCStzKJFjOjzsW0QQ7oIAiccHdcqjbHmJxRb/dmg==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/android-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/android-x64/-/android-x64-0.27.3.tgz",
            "integrity": "sha512-IN/0BNTkHtk8lkOM8JWAYFg4ORxBkZQf9zXiEOfERX/CzxW3Vg1ewAhU7QSWQpVIzTW+b8Xy+lGzdYXV6UZObQ==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/darwin-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/darwin-arm64/-/darwin-arm64-0.27.3.tgz",
            "integrity": "sha512-Re491k7ByTVRy0t3EKWajdLIr0gz2kKKfzafkth4Q8A5n1xTHrkqZgLLjFEHVD+AXdUGgQMq+Godfq45mGpCKg==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/darwin-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/darwin-x64/-/darwin-x64-0.27.3.tgz",
            "integrity": "sha512-vHk/hA7/1AckjGzRqi6wbo+jaShzRowYip6rt6q7VYEDX4LEy1pZfDpdxCBnGtl+A5zq8iXDcyuxwtv3hNtHFg==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/freebsd-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/freebsd-arm64/-/freebsd-arm64-0.27.3.tgz",
            "integrity": "sha512-ipTYM2fjt3kQAYOvo6vcxJx3nBYAzPjgTCk7QEgZG8AUO3ydUhvelmhrbOheMnGOlaSFUoHXB6un+A7q4ygY9w==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "freebsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/freebsd-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/freebsd-x64/-/freebsd-x64-0.27.3.tgz",
            "integrity": "sha512-dDk0X87T7mI6U3K9VjWtHOXqwAMJBNN2r7bejDsc+j03SEjtD9HrOl8gVFByeM0aJksoUuUVU9TBaZa2rgj0oA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "freebsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-arm": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-arm/-/linux-arm-0.27.3.tgz",
            "integrity": "sha512-s6nPv2QkSupJwLYyfS+gwdirm0ukyTFNl3KTgZEAiJDd+iHZcbTPPcWCcRYH+WlNbwChgH2QkE9NSlNrMT8Gfw==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-arm64/-/linux-arm64-0.27.3.tgz",
            "integrity": "sha512-sZOuFz/xWnZ4KH3YfFrKCf1WyPZHakVzTiqji3WDc0BCl2kBwiJLCXpzLzUBLgmp4veFZdvN5ChW4Eq/8Fc2Fg==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-ia32": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-ia32/-/linux-ia32-0.27.3.tgz",
            "integrity": "sha512-yGlQYjdxtLdh0a3jHjuwOrxQjOZYD/C9PfdbgJJF3TIZWnm/tMd/RcNiLngiu4iwcBAOezdnSLAwQDPqTmtTYg==",
            "cpu": [
                "ia32"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-loong64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-loong64/-/linux-loong64-0.27.3.tgz",
            "integrity": "sha512-WO60Sn8ly3gtzhyjATDgieJNet/KqsDlX5nRC5Y3oTFcS1l0KWba+SEa9Ja1GfDqSF1z6hif/SkpQJbL63cgOA==",
            "cpu": [
                "loong64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-mips64el": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-mips64el/-/linux-mips64el-0.27.3.tgz",
            "integrity": "sha512-APsymYA6sGcZ4pD6k+UxbDjOFSvPWyZhjaiPyl/f79xKxwTnrn5QUnXR5prvetuaSMsb4jgeHewIDCIWljrSxw==",
            "cpu": [
                "mips64el"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-ppc64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-ppc64/-/linux-ppc64-0.27.3.tgz",
            "integrity": "sha512-eizBnTeBefojtDb9nSh4vvVQ3V9Qf9Df01PfawPcRzJH4gFSgrObw+LveUyDoKU3kxi5+9RJTCWlj4FjYXVPEA==",
            "cpu": [
                "ppc64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-riscv64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-riscv64/-/linux-riscv64-0.27.3.tgz",
            "integrity": "sha512-3Emwh0r5wmfm3ssTWRQSyVhbOHvqegUDRd0WhmXKX2mkHJe1SFCMJhagUleMq+Uci34wLSipf8Lagt4LlpRFWQ==",
            "cpu": [
                "riscv64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-s390x": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-s390x/-/linux-s390x-0.27.3.tgz",
            "integrity": "sha512-pBHUx9LzXWBc7MFIEEL0yD/ZVtNgLytvx60gES28GcWMqil8ElCYR4kvbV2BDqsHOvVDRrOxGySBM9Fcv744hw==",
            "cpu": [
                "s390x"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/linux-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/linux-x64/-/linux-x64-0.27.3.tgz",
            "integrity": "sha512-Czi8yzXUWIQYAtL/2y6vogER8pvcsOsk5cpwL4Gk5nJqH5UZiVByIY8Eorm5R13gq+DQKYg0+JyQoytLQas4dA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/netbsd-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/netbsd-arm64/-/netbsd-arm64-0.27.3.tgz",
            "integrity": "sha512-sDpk0RgmTCR/5HguIZa9n9u+HVKf40fbEUt+iTzSnCaGvY9kFP0YKBWZtJaraonFnqef5SlJ8/TiPAxzyS+UoA==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "netbsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/netbsd-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/netbsd-x64/-/netbsd-x64-0.27.3.tgz",
            "integrity": "sha512-P14lFKJl/DdaE00LItAukUdZO5iqNH7+PjoBm+fLQjtxfcfFE20Xf5CrLsmZdq5LFFZzb5JMZ9grUwvtVYzjiA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "netbsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/openbsd-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/openbsd-arm64/-/openbsd-arm64-0.27.3.tgz",
            "integrity": "sha512-AIcMP77AvirGbRl/UZFTq5hjXK+2wC7qFRGoHSDrZ5v5b8DK/GYpXW3CPRL53NkvDqb9D+alBiC/dV0Fb7eJcw==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "openbsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/openbsd-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/openbsd-x64/-/openbsd-x64-0.27.3.tgz",
            "integrity": "sha512-DnW2sRrBzA+YnE70LKqnM3P+z8vehfJWHXECbwBmH/CU51z6FiqTQTHFenPlHmo3a8UgpLyH3PT+87OViOh1AQ==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "openbsd"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/openharmony-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/openharmony-arm64/-/openharmony-arm64-0.27.3.tgz",
            "integrity": "sha512-NinAEgr/etERPTsZJ7aEZQvvg/A6IsZG/LgZy+81wON2huV7SrK3e63dU0XhyZP4RKGyTm7aOgmQk0bGp0fy2g==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "openharmony"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/sunos-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/sunos-x64/-/sunos-x64-0.27.3.tgz",
            "integrity": "sha512-PanZ+nEz+eWoBJ8/f8HKxTTD172SKwdXebZ0ndd953gt1HRBbhMsaNqjTyYLGLPdoWHy4zLU7bDVJztF5f3BHA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "sunos"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/win32-arm64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/win32-arm64/-/win32-arm64-0.27.3.tgz",
            "integrity": "sha512-B2t59lWWYrbRDw/tjiWOuzSsFh1Y/E95ofKz7rIVYSQkUYBjfSgf6oeYPNWHToFRr2zx52JKApIcAS/D5TUBnA==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/win32-ia32": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/win32-ia32/-/win32-ia32-0.27.3.tgz",
            "integrity": "sha512-QLKSFeXNS8+tHW7tZpMtjlNb7HKau0QDpwm49u0vUp9y1WOF+PEzkU84y9GqYaAVW8aH8f3GcBck26jh54cX4Q==",
            "cpu": [
                "ia32"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@esbuild/win32-x64": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/@esbuild/win32-x64/-/win32-x64-0.27.3.tgz",
            "integrity": "sha512-4uJGhsxuptu3OcpVAzli+/gWusVGwZZHTlS63hh++ehExkVT8SgiEf7/uC/PclrPPkLhZqGgCTjd0VWLo6xMqA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">=18"
            }
        },
        "node_modules/@jridgewell/gen-mapping": {
            "version": "0.3.13",
            "resolved": "https://registry.npmjs.org/@jridgewell/gen-mapping/-/gen-mapping-0.3.13.tgz",
            "integrity": "sha512-2kkt/7niJ6MgEPxF0bYdQ6etZaA+fQvDcLKckhy1yIQOzaoKjBBjSj63/aLVjYE3qhRt5dvM+uUyfCg6UKCBbA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/sourcemap-codec": "^1.5.0",
                "@jridgewell/trace-mapping": "^0.3.24"
            }
        },
        "node_modules/@jridgewell/remapping": {
            "version": "2.3.5",
            "resolved": "https://registry.npmjs.org/@jridgewell/remapping/-/remapping-2.3.5.tgz",
            "integrity": "sha512-LI9u/+laYG4Ds1TDKSJW2YPrIlcVYOwi2fUC6xB43lueCjgxV4lffOCZCtYFiH6TNOX+tQKXx97T4IKHbhyHEQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/gen-mapping": "^0.3.5",
                "@jridgewell/trace-mapping": "^0.3.24"
            }
        },
        "node_modules/@jridgewell/resolve-uri": {
            "version": "3.1.2",
            "resolved": "https://registry.npmjs.org/@jridgewell/resolve-uri/-/resolve-uri-3.1.2.tgz",
            "integrity": "sha512-bRISgCIjP20/tbWSPWMEi54QVPRZExkuD9lJL+UIxUKtwVJA8wW1Trb1jMs1RFXo1CBTNZ/5hpC9QvmKWdopKw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=6.0.0"
            }
        },
        "node_modules/@jridgewell/sourcemap-codec": {
            "version": "1.5.5",
            "resolved": "https://registry.npmjs.org/@jridgewell/sourcemap-codec/-/sourcemap-codec-1.5.5.tgz",
            "integrity": "sha512-cYQ9310grqxueWbl+WuIUIaiUaDcj7WOq5fVhEljNVgRfOUhY9fy2zTvfoqWsnebh8Sl70VScFbICvJnLKB0Og==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/@jridgewell/trace-mapping": {
            "version": "0.3.31",
            "resolved": "https://registry.npmjs.org/@jridgewell/trace-mapping/-/trace-mapping-0.3.31.tgz",
            "integrity": "sha512-zzNR+SdQSDJzc8joaeP8QQoCQr8NuYx2dIIytl1QeBEZHJ9uW6hebsrYgbz8hJwUQao3TWCMtmfV8Nu1twOLAw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/resolve-uri": "^3.1.0",
                "@jridgewell/sourcemap-codec": "^1.4.14"
            }
        },
        "node_modules/@nodelib/fs.scandir": {
            "version": "2.1.5",
            "resolved": "https://registry.npmjs.org/@nodelib/fs.scandir/-/fs.scandir-2.1.5.tgz",
            "integrity": "sha512-vq24Bq3ym5HEQm2NKCr3yXDwjc7vTsEThRDnkp2DK9p1uqLR+DHurm/NOTo0KG7HYHU7eppKZj3MyqYuMBf62g==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@nodelib/fs.stat": "2.0.5",
                "run-parallel": "^1.1.9"
            },
            "engines": {
                "node": ">= 8"
            }
        },
        "node_modules/@nodelib/fs.stat": {
            "version": "2.0.5",
            "resolved": "https://registry.npmjs.org/@nodelib/fs.stat/-/fs.stat-2.0.5.tgz",
            "integrity": "sha512-RkhPPp2zrqDAQA/2jNhnztcPAlv64XdhIp7a7454A5ovI7Bukxgt7MX7udwAu3zg1DcpPU0rz3VV1SeaqvY4+A==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 8"
            }
        },
        "node_modules/@nodelib/fs.walk": {
            "version": "1.2.8",
            "resolved": "https://registry.npmjs.org/@nodelib/fs.walk/-/fs.walk-1.2.8.tgz",
            "integrity": "sha512-oGB+UxlgWcgQkgwo8GcEGwemoTFt3FIO9ababBmaGwXIoBKZ+GTy0pP185beGg7Llih/NSHSV2XAs1lnznocSg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@nodelib/fs.scandir": "2.1.5",
                "fastq": "^1.6.0"
            },
            "engines": {
                "node": ">= 8"
            }
        },
        "node_modules/@rollup/rollup-android-arm-eabi": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-android-arm-eabi/-/rollup-android-arm-eabi-4.57.1.tgz",
            "integrity": "sha512-A6ehUVSiSaaliTxai040ZpZ2zTevHYbvu/lDoeAteHI8QnaosIzm4qwtezfRg1jOYaUmnzLX1AOD6Z+UJjtifg==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ]
        },
        "node_modules/@rollup/rollup-android-arm64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-android-arm64/-/rollup-android-arm64-4.57.1.tgz",
            "integrity": "sha512-dQaAddCY9YgkFHZcFNS/606Exo8vcLHwArFZ7vxXq4rigo2bb494/xKMMwRRQW6ug7Js6yXmBZhSBRuBvCCQ3w==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ]
        },
        "node_modules/@rollup/rollup-darwin-arm64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-darwin-arm64/-/rollup-darwin-arm64-4.57.1.tgz",
            "integrity": "sha512-crNPrwJOrRxagUYeMn/DZwqN88SDmwaJ8Cvi/TN1HnWBU7GwknckyosC2gd0IqYRsHDEnXf328o9/HC6OkPgOg==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ]
        },
        "node_modules/@rollup/rollup-darwin-x64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-darwin-x64/-/rollup-darwin-x64-4.57.1.tgz",
            "integrity": "sha512-Ji8g8ChVbKrhFtig5QBV7iMaJrGtpHelkB3lsaKzadFBe58gmjfGXAOfI5FV0lYMH8wiqsxKQ1C9B0YTRXVy4w==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ]
        },
        "node_modules/@rollup/rollup-freebsd-arm64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-freebsd-arm64/-/rollup-freebsd-arm64-4.57.1.tgz",
            "integrity": "sha512-R+/WwhsjmwodAcz65guCGFRkMb4gKWTcIeLy60JJQbXrJ97BOXHxnkPFrP+YwFlaS0m+uWJTstrUA9o+UchFug==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "freebsd"
            ]
        },
        "node_modules/@rollup/rollup-freebsd-x64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-freebsd-x64/-/rollup-freebsd-x64-4.57.1.tgz",
            "integrity": "sha512-IEQTCHeiTOnAUC3IDQdzRAGj3jOAYNr9kBguI7MQAAZK3caezRrg0GxAb6Hchg4lxdZEI5Oq3iov/w/hnFWY9Q==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "freebsd"
            ]
        },
        "node_modules/@rollup/rollup-linux-arm-gnueabihf": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-arm-gnueabihf/-/rollup-linux-arm-gnueabihf-4.57.1.tgz",
            "integrity": "sha512-F8sWbhZ7tyuEfsmOxwc2giKDQzN3+kuBLPwwZGyVkLlKGdV1nvnNwYD0fKQ8+XS6hp9nY7B+ZeK01EBUE7aHaw==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-arm-musleabihf": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-arm-musleabihf/-/rollup-linux-arm-musleabihf-4.57.1.tgz",
            "integrity": "sha512-rGfNUfn0GIeXtBP1wL5MnzSj98+PZe/AXaGBCRmT0ts80lU5CATYGxXukeTX39XBKsxzFpEeK+Mrp9faXOlmrw==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-arm64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-arm64-gnu/-/rollup-linux-arm64-gnu-4.57.1.tgz",
            "integrity": "sha512-MMtej3YHWeg/0klK2Qodf3yrNzz6CGjo2UntLvk2RSPlhzgLvYEB3frRvbEF2wRKh1Z2fDIg9KRPe1fawv7C+g==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-arm64-musl": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-arm64-musl/-/rollup-linux-arm64-musl-4.57.1.tgz",
            "integrity": "sha512-1a/qhaaOXhqXGpMFMET9VqwZakkljWHLmZOX48R0I/YLbhdxr1m4gtG1Hq7++VhVUmf+L3sTAf9op4JlhQ5u1Q==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-loong64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-loong64-gnu/-/rollup-linux-loong64-gnu-4.57.1.tgz",
            "integrity": "sha512-QWO6RQTZ/cqYtJMtxhkRkidoNGXc7ERPbZN7dVW5SdURuLeVU7lwKMpo18XdcmpWYd0qsP1bwKPf7DNSUinhvA==",
            "cpu": [
                "loong64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-loong64-musl": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-loong64-musl/-/rollup-linux-loong64-musl-4.57.1.tgz",
            "integrity": "sha512-xpObYIf+8gprgWaPP32xiN5RVTi/s5FCR+XMXSKmhfoJjrpRAjCuuqQXyxUa/eJTdAE6eJ+KDKaoEqjZQxh3Gw==",
            "cpu": [
                "loong64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-ppc64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-ppc64-gnu/-/rollup-linux-ppc64-gnu-4.57.1.tgz",
            "integrity": "sha512-4BrCgrpZo4hvzMDKRqEaW1zeecScDCR+2nZ86ATLhAoJ5FQ+lbHVD3ttKe74/c7tNT9c6F2viwB3ufwp01Oh2w==",
            "cpu": [
                "ppc64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-ppc64-musl": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-ppc64-musl/-/rollup-linux-ppc64-musl-4.57.1.tgz",
            "integrity": "sha512-NOlUuzesGauESAyEYFSe3QTUguL+lvrN1HtwEEsU2rOwdUDeTMJdO5dUYl/2hKf9jWydJrO9OL/XSSf65R5+Xw==",
            "cpu": [
                "ppc64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-riscv64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-riscv64-gnu/-/rollup-linux-riscv64-gnu-4.57.1.tgz",
            "integrity": "sha512-ptA88htVp0AwUUqhVghwDIKlvJMD/fmL/wrQj99PRHFRAG6Z5nbWoWG4o81Nt9FT+IuqUQi+L31ZKAFeJ5Is+A==",
            "cpu": [
                "riscv64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-riscv64-musl": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-riscv64-musl/-/rollup-linux-riscv64-musl-4.57.1.tgz",
            "integrity": "sha512-S51t7aMMTNdmAMPpBg7OOsTdn4tySRQvklmL3RpDRyknk87+Sp3xaumlatU+ppQ+5raY7sSTcC2beGgvhENfuw==",
            "cpu": [
                "riscv64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-s390x-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-s390x-gnu/-/rollup-linux-s390x-gnu-4.57.1.tgz",
            "integrity": "sha512-Bl00OFnVFkL82FHbEqy3k5CUCKH6OEJL54KCyx2oqsmZnFTR8IoNqBF+mjQVcRCT5sB6yOvK8A37LNm/kPJiZg==",
            "cpu": [
                "s390x"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-x64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-x64-gnu/-/rollup-linux-x64-gnu-4.57.1.tgz",
            "integrity": "sha512-ABca4ceT4N+Tv/GtotnWAeXZUZuM/9AQyCyKYyKnpk4yoA7QIAuBt6Hkgpw8kActYlew2mvckXkvx0FfoInnLg==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-linux-x64-musl": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-linux-x64-musl/-/rollup-linux-x64-musl-4.57.1.tgz",
            "integrity": "sha512-HFps0JeGtuOR2convgRRkHCekD7j+gdAuXM+/i6kGzQtFhlCtQkpwtNzkNj6QhCDp7DRJ7+qC/1Vg2jt5iSOFw==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ]
        },
        "node_modules/@rollup/rollup-openbsd-x64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-openbsd-x64/-/rollup-openbsd-x64-4.57.1.tgz",
            "integrity": "sha512-H+hXEv9gdVQuDTgnqD+SQffoWoc0Of59AStSzTEj/feWTBAnSfSD3+Dql1ZruJQxmykT/JVY0dE8Ka7z0DH1hw==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "openbsd"
            ]
        },
        "node_modules/@rollup/rollup-openharmony-arm64": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-openharmony-arm64/-/rollup-openharmony-arm64-4.57.1.tgz",
            "integrity": "sha512-4wYoDpNg6o/oPximyc/NG+mYUejZrCU2q+2w6YZqrAs2UcNUChIZXjtafAiiZSUc7On8v5NyNj34Kzj/Ltk6dQ==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "openharmony"
            ]
        },
        "node_modules/@rollup/rollup-win32-arm64-msvc": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-win32-arm64-msvc/-/rollup-win32-arm64-msvc-4.57.1.tgz",
            "integrity": "sha512-O54mtsV/6LW3P8qdTcamQmuC990HDfR71lo44oZMZlXU4tzLrbvTii87Ni9opq60ds0YzuAlEr/GNwuNluZyMQ==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ]
        },
        "node_modules/@rollup/rollup-win32-ia32-msvc": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-win32-ia32-msvc/-/rollup-win32-ia32-msvc-4.57.1.tgz",
            "integrity": "sha512-P3dLS+IerxCT/7D2q2FYcRdWRl22dNbrbBEtxdWhXrfIMPP9lQhb5h4Du04mdl5Woq05jVCDPCMF7Ub0NAjIew==",
            "cpu": [
                "ia32"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ]
        },
        "node_modules/@rollup/rollup-win32-x64-gnu": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-win32-x64-gnu/-/rollup-win32-x64-gnu-4.57.1.tgz",
            "integrity": "sha512-VMBH2eOOaKGtIJYleXsi2B8CPVADrh+TyNxJ4mWPnKfLB/DBUmzW+5m1xUrcwWoMfSLagIRpjUFeW5CO5hyciQ==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ]
        },
        "node_modules/@rollup/rollup-win32-x64-msvc": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/@rollup/rollup-win32-x64-msvc/-/rollup-win32-x64-msvc-4.57.1.tgz",
            "integrity": "sha512-mxRFDdHIWRxg3UfIIAwCm6NzvxG0jDX/wBN6KsQFTvKFqqg9vTrWUE68qEjHt19A5wwx5X5aUi2zuZT7YR0jrA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ]
        },
        "node_modules/@tailwindcss/forms": {
            "version": "0.5.11",
            "resolved": "https://registry.npmjs.org/@tailwindcss/forms/-/forms-0.5.11.tgz",
            "integrity": "sha512-h9wegbZDPurxG22xZSoWtdzc41/OlNEUQERNqI/0fOwa2aVlWGu7C35E/x6LDyD3lgtztFSSjKZyuVM0hxhbgA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "mini-svg-data-uri": "^1.2.3"
            },
            "peerDependencies": {
                "tailwindcss": ">=3.0.0 || >= 3.0.0-alpha.1 || >= 4.0.0-alpha.20 || >= 4.0.0-beta.1"
            }
        },
        "node_modules/@tailwindcss/node": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/node/-/node-4.1.18.tgz",
            "integrity": "sha512-DoR7U1P7iYhw16qJ49fgXUlry1t4CpXeErJHnQ44JgTSKMaZUdf17cfn5mHchfJ4KRBZRFA/Coo+MUF5+gOaCQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/remapping": "^2.3.4",
                "enhanced-resolve": "^5.18.3",
                "jiti": "^2.6.1",
                "lightningcss": "1.30.2",
                "magic-string": "^0.30.21",
                "source-map-js": "^1.2.1",
                "tailwindcss": "4.1.18"
            }
        },
        "node_modules/@tailwindcss/node/node_modules/tailwindcss": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/tailwindcss/-/tailwindcss-4.1.18.tgz",
            "integrity": "sha512-4+Z+0yiYyEtUVCScyfHCxOYP06L5Ne+JiHhY2IjR2KWMIWhJOYZKLSGZaP5HkZ8+bY0cxfzwDE5uOmzFXyIwxw==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/@tailwindcss/oxide": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide/-/oxide-4.1.18.tgz",
            "integrity": "sha512-EgCR5tTS5bUSKQgzeMClT6iCY3ToqE1y+ZB0AKldj809QXk1Y+3jB0upOYZrn9aGIzPtUsP7sX4QQ4XtjBB95A==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 10"
            },
            "optionalDependencies": {
                "@tailwindcss/oxide-android-arm64": "4.1.18",
                "@tailwindcss/oxide-darwin-arm64": "4.1.18",
                "@tailwindcss/oxide-darwin-x64": "4.1.18",
                "@tailwindcss/oxide-freebsd-x64": "4.1.18",
                "@tailwindcss/oxide-linux-arm-gnueabihf": "4.1.18",
                "@tailwindcss/oxide-linux-arm64-gnu": "4.1.18",
                "@tailwindcss/oxide-linux-arm64-musl": "4.1.18",
                "@tailwindcss/oxide-linux-x64-gnu": "4.1.18",
                "@tailwindcss/oxide-linux-x64-musl": "4.1.18",
                "@tailwindcss/oxide-wasm32-wasi": "4.1.18",
                "@tailwindcss/oxide-win32-arm64-msvc": "4.1.18",
                "@tailwindcss/oxide-win32-x64-msvc": "4.1.18"
            }
        },
        "node_modules/@tailwindcss/oxide-android-arm64": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-android-arm64/-/oxide-android-arm64-4.1.18.tgz",
            "integrity": "sha512-dJHz7+Ugr9U/diKJA0W6N/6/cjI+ZTAoxPf9Iz9BFRF2GzEX8IvXxFIi/dZBloVJX/MZGvRuFA9rqwdiIEZQ0Q==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "android"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-darwin-arm64": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-darwin-arm64/-/oxide-darwin-arm64-4.1.18.tgz",
            "integrity": "sha512-Gc2q4Qhs660bhjyBSKgq6BYvwDz4G+BuyJ5H1xfhmDR3D8HnHCmT/BSkvSL0vQLy/nkMLY20PQ2OoYMO15Jd0A==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-darwin-x64": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-darwin-x64/-/oxide-darwin-x64-4.1.18.tgz",
            "integrity": "sha512-FL5oxr2xQsFrc3X9o1fjHKBYBMD1QZNyc1Xzw/h5Qu4XnEBi3dZn96HcHm41c/euGV+GRiXFfh2hUCyKi/e+yw==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-freebsd-x64": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-freebsd-x64/-/oxide-freebsd-x64-4.1.18.tgz",
            "integrity": "sha512-Fj+RHgu5bDodmV1dM9yAxlfJwkkWvLiRjbhuO2LEtwtlYlBgiAT4x/j5wQr1tC3SANAgD+0YcmWVrj8R9trVMA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "freebsd"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-linux-arm-gnueabihf": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-linux-arm-gnueabihf/-/oxide-linux-arm-gnueabihf-4.1.18.tgz",
            "integrity": "sha512-Fp+Wzk/Ws4dZn+LV2Nqx3IilnhH51YZoRaYHQsVq3RQvEl+71VGKFpkfHrLM/Li+kt5c0DJe/bHXK1eHgDmdiA==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-linux-arm64-gnu": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-linux-arm64-gnu/-/oxide-linux-arm64-gnu-4.1.18.tgz",
            "integrity": "sha512-S0n3jboLysNbh55Vrt7pk9wgpyTTPD0fdQeh7wQfMqLPM/Hrxi+dVsLsPrycQjGKEQk85Kgbx+6+QnYNiHalnw==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-linux-arm64-musl": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-linux-arm64-musl/-/oxide-linux-arm64-musl-4.1.18.tgz",
            "integrity": "sha512-1px92582HkPQlaaCkdRcio71p8bc8i/ap5807tPRDK/uw953cauQBT8c5tVGkOwrHMfc2Yh6UuxaH4vtTjGvHg==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-linux-x64-gnu": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-linux-x64-gnu/-/oxide-linux-x64-gnu-4.1.18.tgz",
            "integrity": "sha512-v3gyT0ivkfBLoZGF9LyHmts0Isc8jHZyVcbzio6Wpzifg/+5ZJpDiRiUhDLkcr7f/r38SWNe7ucxmGW3j3Kb/g==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-linux-x64-musl": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-linux-x64-musl/-/oxide-linux-x64-musl-4.1.18.tgz",
            "integrity": "sha512-bhJ2y2OQNlcRwwgOAGMY0xTFStt4/wyU6pvI6LSuZpRgKQwxTec0/3Scu91O8ir7qCR3AuepQKLU/kX99FouqQ==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-wasm32-wasi": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-wasm32-wasi/-/oxide-wasm32-wasi-4.1.18.tgz",
            "integrity": "sha512-LffYTvPjODiP6PT16oNeUQJzNVyJl1cjIebq/rWWBF+3eDst5JGEFSc5cWxyRCJ0Mxl+KyIkqRxk1XPEs9x8TA==",
            "bundleDependencies": [
                "@napi-rs/wasm-runtime",
                "@emnapi/core",
                "@emnapi/runtime",
                "@tybys/wasm-util",
                "@emnapi/wasi-threads",
                "tslib"
            ],
            "cpu": [
                "wasm32"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "dependencies": {
                "@emnapi/core": "^1.7.1",
                "@emnapi/runtime": "^1.7.1",
                "@emnapi/wasi-threads": "^1.1.0",
                "@napi-rs/wasm-runtime": "^1.1.0",
                "@tybys/wasm-util": "^0.10.1",
                "tslib": "^2.4.0"
            },
            "engines": {
                "node": ">=14.0.0"
            }
        },
        "node_modules/@tailwindcss/oxide-win32-arm64-msvc": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-win32-arm64-msvc/-/oxide-win32-arm64-msvc-4.1.18.tgz",
            "integrity": "sha512-HjSA7mr9HmC8fu6bdsZvZ+dhjyGCLdotjVOgLA2vEqxEBZaQo9YTX4kwgEvPCpRh8o4uWc4J/wEoFzhEmjvPbA==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/oxide-win32-x64-msvc": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/oxide-win32-x64-msvc/-/oxide-win32-x64-msvc-4.1.18.tgz",
            "integrity": "sha512-bJWbyYpUlqamC8dpR7pfjA0I7vdF6t5VpUGMWRkXVE3AXgIZjYUYAK7II1GNaxR8J1SSrSrppRar8G++JekE3Q==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">= 10"
            }
        },
        "node_modules/@tailwindcss/vite": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/@tailwindcss/vite/-/vite-4.1.18.tgz",
            "integrity": "sha512-jVA+/UpKL1vRLg6Hkao5jldawNmRo7mQYrZtNHMIVpLfLhDml5nMRUo/8MwoX2vNXvnaXNNMedrMfMugAVX1nA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@tailwindcss/node": "4.1.18",
                "@tailwindcss/oxide": "4.1.18",
                "tailwindcss": "4.1.18"
            },
            "peerDependencies": {
                "vite": "^5.2.0 || ^6 || ^7"
            }
        },
        "node_modules/@tailwindcss/vite/node_modules/tailwindcss": {
            "version": "4.1.18",
            "resolved": "https://registry.npmjs.org/tailwindcss/-/tailwindcss-4.1.18.tgz",
            "integrity": "sha512-4+Z+0yiYyEtUVCScyfHCxOYP06L5Ne+JiHhY2IjR2KWMIWhJOYZKLSGZaP5HkZ8+bY0cxfzwDE5uOmzFXyIwxw==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/@types/estree": {
            "version": "1.0.8",
            "resolved": "https://registry.npmjs.org/@types/estree/-/estree-1.0.8.tgz",
            "integrity": "sha512-dWHzHa2WqEXI/O1E9OjrocMTKJl2mSrEolh1Iomrv6U+JuNwaHXsXx9bLu5gG7BUWFIN0skIQJQ/L1rIex4X6w==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/@vue/reactivity": {
            "version": "3.1.5",
            "resolved": "https://registry.npmjs.org/@vue/reactivity/-/reactivity-3.1.5.tgz",
            "integrity": "sha512-1tdfLmNjWG6t/CsPldh+foumYFo3cpyCHgBYQ34ylaMsJ+SNHQ1kApMIa8jN+i593zQuaw3AdWH0nJTARzCFhg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@vue/shared": "3.1.5"
            }
        },
        "node_modules/@vue/shared": {
            "version": "3.1.5",
            "resolved": "https://registry.npmjs.org/@vue/shared/-/shared-3.1.5.tgz",
            "integrity": "sha512-oJ4F3TnvpXaQwZJNF3ZK+kLPHKarDmJjJ6jyzVNDKH9md1dptjC7lWR//jrGuLdek/U6iltWxqAnYOu8gCiOvA==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/alpinejs": {
            "version": "3.15.8",
            "resolved": "https://registry.npmjs.org/alpinejs/-/alpinejs-3.15.8.tgz",
            "integrity": "sha512-zxIfCRTBGvF1CCLIOMQOxAyBuqibxSEwS6Jm1a3HGA9rgrJVcjEWlwLcQTVGAWGS8YhAsTRLVrtQ5a5QT9bSSQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@vue/reactivity": "~3.1.1"
            }
        },
        "node_modules/ansi-regex": {
            "version": "5.0.1",
            "resolved": "https://registry.npmjs.org/ansi-regex/-/ansi-regex-5.0.1.tgz",
            "integrity": "sha512-quJQXlTSUGL2LH9SUXo8VwsY4soanhgo6LNSm84E1LBcE8s3O0wpdiRzyR9z/ZZJMlMWv37qOOb9pdJlMUEKFQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/ansi-styles": {
            "version": "4.3.0",
            "resolved": "https://registry.npmjs.org/ansi-styles/-/ansi-styles-4.3.0.tgz",
            "integrity": "sha512-zbB9rCJAT1rbjiVDb2hqKFHNYLxgtk8NURxZ3IZwD3F6NtxbXZQCnnSi1Lkx+IDohdPlFp222wVALIheZJQSEg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "color-convert": "^2.0.1"
            },
            "engines": {
                "node": ">=8"
            },
            "funding": {
                "url": "https://github.com/chalk/ansi-styles?sponsor=1"
            }
        },
        "node_modules/any-promise": {
            "version": "1.3.0",
            "resolved": "https://registry.npmjs.org/any-promise/-/any-promise-1.3.0.tgz",
            "integrity": "sha512-7UvmKalWRt1wgjL1RrGxoSJW/0QZFIegpeGvZG9kjp8vrRu55XTHbwnqq2GpXm9uLbcuhxm3IqX9OB4MZR1b2A==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/anymatch": {
            "version": "3.1.3",
            "resolved": "https://registry.npmjs.org/anymatch/-/anymatch-3.1.3.tgz",
            "integrity": "sha512-KMReFUr0B4t+D+OBkjR3KYqvocp2XaSzO55UcB6mgQMd3KbcE+mWTyvVV7D/zsdEbNnV6acZUutkiHQXvTr1Rw==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "normalize-path": "^3.0.0",
                "picomatch": "^2.0.4"
            },
            "engines": {
                "node": ">= 8"
            }
        },
        "node_modules/arg": {
            "version": "5.0.2",
            "resolved": "https://registry.npmjs.org/arg/-/arg-5.0.2.tgz",
            "integrity": "sha512-PYjyFOLKQ9y57JvQ6QLo8dAgNqswh8M1RMJYdQduT6xbWSgK36P/Z/v+p888pM69jMMfS8Xd8F6I1kQ/I9HUGg==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/asynckit": {
            "version": "0.4.0",
            "resolved": "https://registry.npmjs.org/asynckit/-/asynckit-0.4.0.tgz",
            "integrity": "sha512-Oei9OH4tRh0YqU3GxhX79dM/mwVgvbZJaSNaRk+bshkj0S5cfHcgYakreBjrHwatXKbz+IoIdYLxrKim2MjW0Q==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/autoprefixer": {
            "version": "10.4.24",
            "resolved": "https://registry.npmjs.org/autoprefixer/-/autoprefixer-10.4.24.tgz",
            "integrity": "sha512-uHZg7N9ULTVbutaIsDRoUkoS8/h3bdsmVJYZ5l3wv8Cp/6UIIoRDm90hZ+BwxUj/hGBEzLxdHNSKuFpn8WOyZw==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/postcss/"
                },
                {
                    "type": "tidelift",
                    "url": "https://tidelift.com/funding/github/npm/autoprefixer"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "browserslist": "^4.28.1",
                "caniuse-lite": "^1.0.30001766",
                "fraction.js": "^5.3.4",
                "picocolors": "^1.1.1",
                "postcss-value-parser": "^4.2.0"
            },
            "bin": {
                "autoprefixer": "bin/autoprefixer"
            },
            "engines": {
                "node": "^10 || ^12 || >=14"
            },
            "peerDependencies": {
                "postcss": "^8.1.0"
            }
        },
        "node_modules/axios": {
            "version": "1.13.5",
            "resolved": "https://registry.npmjs.org/axios/-/axios-1.13.5.tgz",
            "integrity": "sha512-cz4ur7Vb0xS4/KUN0tPWe44eqxrIu31me+fbang3ijiNscE129POzipJJA6zniq2C/Z6sJCjMimjS8Lc/GAs8Q==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "follow-redirects": "^1.15.11",
                "form-data": "^4.0.5",
                "proxy-from-env": "^1.1.0"
            }
        },
        "node_modules/baseline-browser-mapping": {
            "version": "2.9.19",
            "resolved": "https://registry.npmjs.org/baseline-browser-mapping/-/baseline-browser-mapping-2.9.19.tgz",
            "integrity": "sha512-ipDqC8FrAl/76p2SSWKSI+H9tFwm7vYqXQrItCuiVPt26Km0jS+NzSsBWAaBusvSbQcfJG+JitdMm+wZAgTYqg==",
            "dev": true,
            "license": "Apache-2.0",
            "bin": {
                "baseline-browser-mapping": "dist/cli.js"
            }
        },
        "node_modules/binary-extensions": {
            "version": "2.3.0",
            "resolved": "https://registry.npmjs.org/binary-extensions/-/binary-extensions-2.3.0.tgz",
            "integrity": "sha512-Ceh+7ox5qe7LJuLHoY0feh3pHuUDHAcRUeyL2VYghZwfpkNIy/+8Ocg0a3UuSoYzavmylwuLWQOf3hl0jjMMIw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=8"
            },
            "funding": {
                "url": "https://github.com/sponsors/sindresorhus"
            }
        },
        "node_modules/braces": {
            "version": "3.0.3",
            "resolved": "https://registry.npmjs.org/braces/-/braces-3.0.3.tgz",
            "integrity": "sha512-yQbXgO/OSZVD2IsiLlro+7Hf6Q18EJrKSEsdoMzKePKXct3gvD8oLcOQdIzGupr5Fj+EDe8gO/lxc1BzfMpxvA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "fill-range": "^7.1.1"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/browserslist": {
            "version": "4.28.1",
            "resolved": "https://registry.npmjs.org/browserslist/-/browserslist-4.28.1.tgz",
            "integrity": "sha512-ZC5Bd0LgJXgwGqUknZY/vkUQ04r8NXnJZ3yYi4vDmSiZmC/pdSN0NbNRPxZpbtO4uAfDUAFffO8IZoM3Gj8IkA==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/browserslist"
                },
                {
                    "type": "tidelift",
                    "url": "https://tidelift.com/funding/github/npm/browserslist"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "baseline-browser-mapping": "^2.9.0",
                "caniuse-lite": "^1.0.30001759",
                "electron-to-chromium": "^1.5.263",
                "node-releases": "^2.0.27",
                "update-browserslist-db": "^1.2.0"
            },
            "bin": {
                "browserslist": "cli.js"
            },
            "engines": {
                "node": "^6 || ^7 || ^8 || ^9 || ^10 || ^11 || ^12 || >=13.7"
            }
        },
        "node_modules/call-bind-apply-helpers": {
            "version": "1.0.2",
            "resolved": "https://registry.npmjs.org/call-bind-apply-helpers/-/call-bind-apply-helpers-1.0.2.tgz",
            "integrity": "sha512-Sp1ablJ0ivDkSzjcaJdxEunN5/XvksFJ2sMBFfq6x0ryhQV/2b/KwFe21cMpmHtPOSij8K99/wSfoEuTObmuMQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "es-errors": "^1.3.0",
                "function-bind": "^1.1.2"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/camelcase-css": {
            "version": "2.0.1",
            "resolved": "https://registry.npmjs.org/camelcase-css/-/camelcase-css-2.0.1.tgz",
            "integrity": "sha512-QOSvevhslijgYwRx6Rv7zKdMF8lbRmx+uQGx2+vDc+KI/eBnsy9kit5aj23AgGu3pa4t9AgwbnXWqS+iOY+2aA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/caniuse-lite": {
            "version": "1.0.30001770",
            "resolved": "https://registry.npmjs.org/caniuse-lite/-/caniuse-lite-1.0.30001770.tgz",
            "integrity": "sha512-x/2CLQ1jHENRbHg5PSId2sXq1CIO1CISvwWAj027ltMVG2UNgW+w9oH2+HzgEIRFembL8bUlXtfbBHR1fCg2xw==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/browserslist"
                },
                {
                    "type": "tidelift",
                    "url": "https://tidelift.com/funding/github/npm/caniuse-lite"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "CC-BY-4.0"
        },
        "node_modules/chalk": {
            "version": "4.1.2",
            "resolved": "https://registry.npmjs.org/chalk/-/chalk-4.1.2.tgz",
            "integrity": "sha512-oKnbhFyRIXpUuez8iBMmyEa4nbj4IOQyuhc/wy9kY7/WVPcwIO9VA668Pu8RkO7+0G76SLROeyw9CpQ061i4mA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "ansi-styles": "^4.1.0",
                "supports-color": "^7.1.0"
            },
            "engines": {
                "node": ">=10"
            },
            "funding": {
                "url": "https://github.com/chalk/chalk?sponsor=1"
            }
        },
        "node_modules/chalk/node_modules/supports-color": {
            "version": "7.2.0",
            "resolved": "https://registry.npmjs.org/supports-color/-/supports-color-7.2.0.tgz",
            "integrity": "sha512-qpCAvRl9stuOHveKsn7HncJRvv501qIacKzQlO/+Lwxc9+0q2wLyv4Dfvt80/DPn2pqOBsJdDiogXGR9+OvwRw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "has-flag": "^4.0.0"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/chokidar": {
            "version": "3.6.0",
            "resolved": "https://registry.npmjs.org/chokidar/-/chokidar-3.6.0.tgz",
            "integrity": "sha512-7VT13fmjotKpGipCW9JEQAusEPE+Ei8nl6/g4FBAmIm0GOOLMua9NDDo/DWp0ZAxCr3cPq5ZpBqmPAQgDda2Pw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "anymatch": "~3.1.2",
                "braces": "~3.0.2",
                "glob-parent": "~5.1.2",
                "is-binary-path": "~2.1.0",
                "is-glob": "~4.0.1",
                "normalize-path": "~3.0.0",
                "readdirp": "~3.6.0"
            },
            "engines": {
                "node": ">= 8.10.0"
            },
            "funding": {
                "url": "https://paulmillr.com/funding/"
            },
            "optionalDependencies": {
                "fsevents": "~2.3.2"
            }
        },
        "node_modules/chokidar/node_modules/glob-parent": {
            "version": "5.1.2",
            "resolved": "https://registry.npmjs.org/glob-parent/-/glob-parent-5.1.2.tgz",
            "integrity": "sha512-AOIgSQCepiJYwP3ARnGx+5VnTu2HBYdzbGP45eLw1vr3zB3vZLeyed1sC9hnbcOc9/SrMyM5RPQrkGz4aS9Zow==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "is-glob": "^4.0.1"
            },
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/cliui": {
            "version": "8.0.1",
            "resolved": "https://registry.npmjs.org/cliui/-/cliui-8.0.1.tgz",
            "integrity": "sha512-BSeNnyus75C4//NQ9gQt1/csTXyo/8Sb+afLAkzAptFuMsod9HFokGNudZpi/oQV73hnVK+sR+5PVRMd+Dr7YQ==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "string-width": "^4.2.0",
                "strip-ansi": "^6.0.1",
                "wrap-ansi": "^7.0.0"
            },
            "engines": {
                "node": ">=12"
            }
        },
        "node_modules/color-convert": {
            "version": "2.0.1",
            "resolved": "https://registry.npmjs.org/color-convert/-/color-convert-2.0.1.tgz",
            "integrity": "sha512-RRECPsj7iu/xb5oKYcsFHSppFNnsj/52OVTRKb4zP5onXwVF3zVmmToNcOfGC+CRDpfK/U584fMg38ZHCaElKQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "color-name": "~1.1.4"
            },
            "engines": {
                "node": ">=7.0.0"
            }
        },
        "node_modules/color-name": {
            "version": "1.1.4",
            "resolved": "https://registry.npmjs.org/color-name/-/color-name-1.1.4.tgz",
            "integrity": "sha512-dOy+3AuW3a2wNbZHIuMZpTcgjGuLU/uBL/ubcZF9OXbDo8ff4O8yVp5Bf0efS8uEoYo5q4Fx7dY9OgQGXgAsQA==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/combined-stream": {
            "version": "1.0.8",
            "resolved": "https://registry.npmjs.org/combined-stream/-/combined-stream-1.0.8.tgz",
            "integrity": "sha512-FQN4MRfuJeHf7cBbBMJFXhKSDq+2kAArBlmRBvcvFE5BB1HZKXtSFASDhdlz9zOYwxh8lDdnvmMOe/+5cdoEdg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "delayed-stream": "~1.0.0"
            },
            "engines": {
                "node": ">= 0.8"
            }
        },
        "node_modules/commander": {
            "version": "4.1.1",
            "resolved": "https://registry.npmjs.org/commander/-/commander-4.1.1.tgz",
            "integrity": "sha512-NOKm8xhkzAjzFx8B2v5OAHT+u5pRQc2UCa2Vq9jYL/31o2wi9mxBA7LIFs3sV5VSC49z6pEhfbMULvShKj26WA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/concurrently": {
            "version": "9.2.1",
            "resolved": "https://registry.npmjs.org/concurrently/-/concurrently-9.2.1.tgz",
            "integrity": "sha512-fsfrO0MxV64Znoy8/l1vVIjjHa29SZyyqPgQBwhiDcaW8wJc2W3XWVOGx4M3oJBnv/zdUZIIp1gDeS98GzP8Ng==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "chalk": "4.1.2",
                "rxjs": "7.8.2",
                "shell-quote": "1.8.3",
                "supports-color": "8.1.1",
                "tree-kill": "1.2.2",
                "yargs": "17.7.2"
            },
            "bin": {
                "conc": "dist/bin/concurrently.js",
                "concurrently": "dist/bin/concurrently.js"
            },
            "engines": {
                "node": ">=18"
            },
            "funding": {
                "url": "https://github.com/open-cli-tools/concurrently?sponsor=1"
            }
        },
        "node_modules/cssesc": {
            "version": "3.0.0",
            "resolved": "https://registry.npmjs.org/cssesc/-/cssesc-3.0.0.tgz",
            "integrity": "sha512-/Tb/JcjK111nNScGob5MNtsntNM1aCNUDipB/TkwZFhyDrrE47SOx/18wF2bbjgc3ZzCSKW1T5nt5EbFoAz/Vg==",
            "dev": true,
            "license": "MIT",
            "bin": {
                "cssesc": "bin/cssesc"
            },
            "engines": {
                "node": ">=4"
            }
        },
        "node_modules/delayed-stream": {
            "version": "1.0.0",
            "resolved": "https://registry.npmjs.org/delayed-stream/-/delayed-stream-1.0.0.tgz",
            "integrity": "sha512-ZySD7Nf91aLB0RxL4KGrKHBXl7Eds1DAmEdcoVawXnLD7SDhpNgtuII2aAkg7a7QS41jxPSZ17p4VdGnMHk3MQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.4.0"
            }
        },
        "node_modules/detect-libc": {
            "version": "2.1.2",
            "resolved": "https://registry.npmjs.org/detect-libc/-/detect-libc-2.1.2.tgz",
            "integrity": "sha512-Btj2BOOO83o3WyH59e8MgXsxEQVcarkUOpEYrubB0urwnN10yQ364rsiByU11nZlqWYZm05i/of7io4mzihBtQ==",
            "dev": true,
            "license": "Apache-2.0",
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/didyoumean": {
            "version": "1.2.2",
            "resolved": "https://registry.npmjs.org/didyoumean/-/didyoumean-1.2.2.tgz",
            "integrity": "sha512-gxtyfqMg7GKyhQmb056K7M3xszy/myH8w+B4RT+QXBQsvAOdc3XymqDDPHx1BgPgsdAA5SIifona89YtRATDzw==",
            "dev": true,
            "license": "Apache-2.0"
        },
        "node_modules/dlv": {
            "version": "1.1.3",
            "resolved": "https://registry.npmjs.org/dlv/-/dlv-1.1.3.tgz",
            "integrity": "sha512-+HlytyjlPKnIG8XuRG8WvmBP8xs8P71y+SKKS6ZXWoEgLuePxtDoUEiH7WkdePWrQ5JBpE6aoVqfZfJUQkjXwA==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/dunder-proto": {
            "version": "1.0.1",
            "resolved": "https://registry.npmjs.org/dunder-proto/-/dunder-proto-1.0.1.tgz",
            "integrity": "sha512-KIN/nDJBQRcXw0MLVhZE9iQHmG68qAVIBg9CqmUYjmQIhgij9U5MFvrqkUL5FbtyyzZuOeOt0zdeRe4UY7ct+A==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "call-bind-apply-helpers": "^1.0.1",
                "es-errors": "^1.3.0",
                "gopd": "^1.2.0"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/electron-to-chromium": {
            "version": "1.5.286",
            "resolved": "https://registry.npmjs.org/electron-to-chromium/-/electron-to-chromium-1.5.286.tgz",
            "integrity": "sha512-9tfDXhJ4RKFNerfjdCcZfufu49vg620741MNs26a9+bhLThdB+plgMeou98CAaHu/WATj2iHOOHTp1hWtABj2A==",
            "dev": true,
            "license": "ISC"
        },
        "node_modules/emoji-regex": {
            "version": "8.0.0",
            "resolved": "https://registry.npmjs.org/emoji-regex/-/emoji-regex-8.0.0.tgz",
            "integrity": "sha512-MSjYzcWNOA0ewAHpz0MxpYFvwg6yjy1NG3xteoqz644VCo/RPgnr1/GGt+ic3iJTzQ8Eu3TdM14SawnVUmGE6A==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/enhanced-resolve": {
            "version": "5.19.0",
            "resolved": "https://registry.npmjs.org/enhanced-resolve/-/enhanced-resolve-5.19.0.tgz",
            "integrity": "sha512-phv3E1Xl4tQOShqSte26C7Fl84EwUdZsyOuSSk9qtAGyyQs2s3jJzComh+Abf4g187lUUAvH+H26omrqia2aGg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "graceful-fs": "^4.2.4",
                "tapable": "^2.3.0"
            },
            "engines": {
                "node": ">=10.13.0"
            }
        },
        "node_modules/es-define-property": {
            "version": "1.0.1",
            "resolved": "https://registry.npmjs.org/es-define-property/-/es-define-property-1.0.1.tgz",
            "integrity": "sha512-e3nRfgfUZ4rNGL232gUgX06QNyyez04KdjFrF+LTRoOXmrOgFKDg4BCdsjW8EnT69eqdYGmRpJwiPVYNrCaW3g==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/es-errors": {
            "version": "1.3.0",
            "resolved": "https://registry.npmjs.org/es-errors/-/es-errors-1.3.0.tgz",
            "integrity": "sha512-Zf5H2Kxt2xjTvbJvP2ZWLEICxA6j+hAmMzIlypy4xcBg1vKVnx89Wy0GbS+kf5cwCVFFzdCFh2XSCFNULS6csw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/es-object-atoms": {
            "version": "1.1.1",
            "resolved": "https://registry.npmjs.org/es-object-atoms/-/es-object-atoms-1.1.1.tgz",
            "integrity": "sha512-FGgH2h8zKNim9ljj7dankFPcICIK9Cp5bm+c2gQSYePhpaG5+esrLODihIorn+Pe6FGJzWhXQotPv73jTaldXA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "es-errors": "^1.3.0"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/es-set-tostringtag": {
            "version": "2.1.0",
            "resolved": "https://registry.npmjs.org/es-set-tostringtag/-/es-set-tostringtag-2.1.0.tgz",
            "integrity": "sha512-j6vWzfrGVfyXxge+O0x5sh6cvxAog0a/4Rdd2K36zCMV5eJ+/+tOAngRO8cODMNWbVRdVlmGZQL2YS3yR8bIUA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "es-errors": "^1.3.0",
                "get-intrinsic": "^1.2.6",
                "has-tostringtag": "^1.0.2",
                "hasown": "^2.0.2"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/esbuild": {
            "version": "0.27.3",
            "resolved": "https://registry.npmjs.org/esbuild/-/esbuild-0.27.3.tgz",
            "integrity": "sha512-8VwMnyGCONIs6cWue2IdpHxHnAjzxnw2Zr7MkVxB2vjmQ2ivqGFb4LEG3SMnv0Gb2F/G/2yA8zUaiL1gywDCCg==",
            "dev": true,
            "hasInstallScript": true,
            "license": "MIT",
            "bin": {
                "esbuild": "bin/esbuild"
            },
            "engines": {
                "node": ">=18"
            },
            "optionalDependencies": {
                "@esbuild/aix-ppc64": "0.27.3",
                "@esbuild/android-arm": "0.27.3",
                "@esbuild/android-arm64": "0.27.3",
                "@esbuild/android-x64": "0.27.3",
                "@esbuild/darwin-arm64": "0.27.3",
                "@esbuild/darwin-x64": "0.27.3",
                "@esbuild/freebsd-arm64": "0.27.3",
                "@esbuild/freebsd-x64": "0.27.3",
                "@esbuild/linux-arm": "0.27.3",
                "@esbuild/linux-arm64": "0.27.3",
                "@esbuild/linux-ia32": "0.27.3",
                "@esbuild/linux-loong64": "0.27.3",
                "@esbuild/linux-mips64el": "0.27.3",
                "@esbuild/linux-ppc64": "0.27.3",
                "@esbuild/linux-riscv64": "0.27.3",
                "@esbuild/linux-s390x": "0.27.3",
                "@esbuild/linux-x64": "0.27.3",
                "@esbuild/netbsd-arm64": "0.27.3",
                "@esbuild/netbsd-x64": "0.27.3",
                "@esbuild/openbsd-arm64": "0.27.3",
                "@esbuild/openbsd-x64": "0.27.3",
                "@esbuild/openharmony-arm64": "0.27.3",
                "@esbuild/sunos-x64": "0.27.3",
                "@esbuild/win32-arm64": "0.27.3",
                "@esbuild/win32-ia32": "0.27.3",
                "@esbuild/win32-x64": "0.27.3"
            }
        },
        "node_modules/escalade": {
            "version": "3.2.0",
            "resolved": "https://registry.npmjs.org/escalade/-/escalade-3.2.0.tgz",
            "integrity": "sha512-WUj2qlxaQtO4g6Pq5c29GTcWGDyd8itL8zTlipgECz3JesAiiOKotd8JU6otB3PACgG6xkJUyVhboMS+bje/jA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=6"
            }
        },
        "node_modules/fast-glob": {
            "version": "3.3.3",
            "resolved": "https://registry.npmjs.org/fast-glob/-/fast-glob-3.3.3.tgz",
            "integrity": "sha512-7MptL8U0cqcFdzIzwOTHoilX9x5BrNqye7Z/LuC7kCMRio1EMSyqRK3BEAUD7sXRq4iT4AzTVuZdhgQ2TCvYLg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@nodelib/fs.stat": "^2.0.2",
                "@nodelib/fs.walk": "^1.2.3",
                "glob-parent": "^5.1.2",
                "merge2": "^1.3.0",
                "micromatch": "^4.0.8"
            },
            "engines": {
                "node": ">=8.6.0"
            }
        },
        "node_modules/fast-glob/node_modules/glob-parent": {
            "version": "5.1.2",
            "resolved": "https://registry.npmjs.org/glob-parent/-/glob-parent-5.1.2.tgz",
            "integrity": "sha512-AOIgSQCepiJYwP3ARnGx+5VnTu2HBYdzbGP45eLw1vr3zB3vZLeyed1sC9hnbcOc9/SrMyM5RPQrkGz4aS9Zow==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "is-glob": "^4.0.1"
            },
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/fastq": {
            "version": "1.20.1",
            "resolved": "https://registry.npmjs.org/fastq/-/fastq-1.20.1.tgz",
            "integrity": "sha512-GGToxJ/w1x32s/D2EKND7kTil4n8OVk/9mycTc4VDza13lOvpUZTGX3mFSCtV9ksdGBVzvsyAVLM6mHFThxXxw==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "reusify": "^1.0.4"
            }
        },
        "node_modules/fill-range": {
            "version": "7.1.1",
            "resolved": "https://registry.npmjs.org/fill-range/-/fill-range-7.1.1.tgz",
            "integrity": "sha512-YsGpe3WHLK8ZYi4tWDg2Jy3ebRz2rXowDxnld4bkQB00cc/1Zw9AWnC0i9ztDJitivtQvaI9KaLyKrc+hBW0yg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "to-regex-range": "^5.0.1"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/follow-redirects": {
            "version": "1.15.11",
            "resolved": "https://registry.npmjs.org/follow-redirects/-/follow-redirects-1.15.11.tgz",
            "integrity": "sha512-deG2P0JfjrTxl50XGCDyfI97ZGVCxIpfKYmfyrQ54n5FO/0gfIES8C/Psl6kWVDolizcaaxZJnTS0QSMxvnsBQ==",
            "dev": true,
            "funding": [
                {
                    "type": "individual",
                    "url": "https://github.com/sponsors/RubenVerborgh"
                }
            ],
            "license": "MIT",
            "engines": {
                "node": ">=4.0"
            },
            "peerDependenciesMeta": {
                "debug": {
                    "optional": true
                }
            }
        },
        "node_modules/form-data": {
            "version": "4.0.5",
            "resolved": "https://registry.npmjs.org/form-data/-/form-data-4.0.5.tgz",
            "integrity": "sha512-8RipRLol37bNs2bhoV67fiTEvdTrbMUYcFTiy3+wuuOnUog2QBHCZWXDRijWQfAkhBj2Uf5UnVaiWwA5vdd82w==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "asynckit": "^0.4.0",
                "combined-stream": "^1.0.8",
                "es-set-tostringtag": "^2.1.0",
                "hasown": "^2.0.2",
                "mime-types": "^2.1.12"
            },
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/fraction.js": {
            "version": "5.3.4",
            "resolved": "https://registry.npmjs.org/fraction.js/-/fraction.js-5.3.4.tgz",
            "integrity": "sha512-1X1NTtiJphryn/uLQz3whtY6jK3fTqoE3ohKs0tT+Ujr1W59oopxmoEh7Lu5p6vBaPbgoM0bzveAW4Qi5RyWDQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": "*"
            },
            "funding": {
                "type": "github",
                "url": "https://github.com/sponsors/rawify"
            }
        },
        "node_modules/fsevents": {
            "version": "2.3.3",
            "resolved": "https://registry.npmjs.org/fsevents/-/fsevents-2.3.3.tgz",
            "integrity": "sha512-5xoDfX+fL7faATnagmWPpbFtwh/R77WmMMqqHGS65C3vvB0YHrgF+B1YmZ3441tMj5n63k0212XNoJwzlhffQw==",
            "dev": true,
            "hasInstallScript": true,
            "license": "MIT",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": "^8.16.0 || ^10.6.0 || >=11.0.0"
            }
        },
        "node_modules/function-bind": {
            "version": "1.1.2",
            "resolved": "https://registry.npmjs.org/function-bind/-/function-bind-1.1.2.tgz",
            "integrity": "sha512-7XHNxH7qX9xG5mIwxkhumTox/MIRNcOgDrxWsMt2pAr23WHp6MrRlN7FBSFpCpr+oVO0F744iUgR82nJMfG2SA==",
            "dev": true,
            "license": "MIT",
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/get-caller-file": {
            "version": "2.0.5",
            "resolved": "https://registry.npmjs.org/get-caller-file/-/get-caller-file-2.0.5.tgz",
            "integrity": "sha512-DyFP3BM/3YHTQOCUL/w0OZHR0lpKeGrxotcHWcqNEdnltqFwXVfhEBQ94eIo34AfQpo0rGki4cyIiftY06h2Fg==",
            "dev": true,
            "license": "ISC",
            "engines": {
                "node": "6.* || 8.* || >= 10.*"
            }
        },
        "node_modules/get-intrinsic": {
            "version": "1.3.0",
            "resolved": "https://registry.npmjs.org/get-intrinsic/-/get-intrinsic-1.3.0.tgz",
            "integrity": "sha512-9fSjSaos/fRIVIp+xSJlE6lfwhES7LNtKaCBIamHsjr2na1BiABJPo0mOjjz8GJDURarmCPGqaiVg5mfjb98CQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "call-bind-apply-helpers": "^1.0.2",
                "es-define-property": "^1.0.1",
                "es-errors": "^1.3.0",
                "es-object-atoms": "^1.1.1",
                "function-bind": "^1.1.2",
                "get-proto": "^1.0.1",
                "gopd": "^1.2.0",
                "has-symbols": "^1.1.0",
                "hasown": "^2.0.2",
                "math-intrinsics": "^1.1.0"
            },
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/get-proto": {
            "version": "1.0.1",
            "resolved": "https://registry.npmjs.org/get-proto/-/get-proto-1.0.1.tgz",
            "integrity": "sha512-sTSfBjoXBp89JvIKIefqw7U2CCebsc74kiY6awiGogKtoSGbgjYE/G/+l9sF3MWFPNc9IcoOC4ODfKHfxFmp0g==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "dunder-proto": "^1.0.1",
                "es-object-atoms": "^1.0.0"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/glob-parent": {
            "version": "6.0.2",
            "resolved": "https://registry.npmjs.org/glob-parent/-/glob-parent-6.0.2.tgz",
            "integrity": "sha512-XxwI8EOhVQgWp6iDL+3b0r86f4d6AX6zSU55HfB4ydCEuXLXc5FcYeOu+nnGftS4TEju/11rt4KJPTMgbfmv4A==",
            "dev": true,
            "license": "ISC",
            "dependencies": {
                "is-glob": "^4.0.3"
            },
            "engines": {
                "node": ">=10.13.0"
            }
        },
        "node_modules/gopd": {
            "version": "1.2.0",
            "resolved": "https://registry.npmjs.org/gopd/-/gopd-1.2.0.tgz",
            "integrity": "sha512-ZUKRh6/kUFoAiTAtTYPZJ3hw9wNxx+BIBOijnlG9PnrJsCcSjs1wyyD6vJpaYtgnzDrKYRSqf3OO6Rfa93xsRg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/graceful-fs": {
            "version": "4.2.11",
            "resolved": "https://registry.npmjs.org/graceful-fs/-/graceful-fs-4.2.11.tgz",
            "integrity": "sha512-RbJ5/jmFcNNCcDV5o9eTnBLJ/HszWV0P73bc+Ff4nS/rJj+YaS6IGyiOL0VoBYX+l1Wrl3k63h/KrH+nhJ0XvQ==",
            "dev": true,
            "license": "ISC"
        },
        "node_modules/has-flag": {
            "version": "4.0.0",
            "resolved": "https://registry.npmjs.org/has-flag/-/has-flag-4.0.0.tgz",
            "integrity": "sha512-EykJT/Q1KjTWctppgIAgfSO0tKVuZUjhgMr17kqTumMl6Afv3EISleU7qZUzoXDFTAHTDC4NOoG/ZxU3EvlMPQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/has-symbols": {
            "version": "1.1.0",
            "resolved": "https://registry.npmjs.org/has-symbols/-/has-symbols-1.1.0.tgz",
            "integrity": "sha512-1cDNdwJ2Jaohmb3sg4OmKaMBwuC48sYni5HUw2DvsC8LjGTLK9h+eb1X6RyuOHe4hT0ULCW68iomhjUoKUqlPQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/has-tostringtag": {
            "version": "1.0.2",
            "resolved": "https://registry.npmjs.org/has-tostringtag/-/has-tostringtag-1.0.2.tgz",
            "integrity": "sha512-NqADB8VjPFLM2V0VvHUewwwsw0ZWBaIdgo+ieHtK3hasLz4qeCRjYcqfB6AQrBggRKppKF8L52/VqdVsO47Dlw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "has-symbols": "^1.0.3"
            },
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/hasown": {
            "version": "2.0.2",
            "resolved": "https://registry.npmjs.org/hasown/-/hasown-2.0.2.tgz",
            "integrity": "sha512-0hJU9SCPvmMzIBdZFqNPXWa6dqh7WdH0cII9y+CyS8rG3nL48Bclra9HmKhVVUHyPWNH5Y7xDwAB7bfgSjkUMQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "function-bind": "^1.1.2"
            },
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/is-binary-path": {
            "version": "2.1.0",
            "resolved": "https://registry.npmjs.org/is-binary-path/-/is-binary-path-2.1.0.tgz",
            "integrity": "sha512-ZMERYes6pDydyuGidse7OsHxtbI7WVeUEozgR/g7rd0xUimYNlvZRE/K2MgZTjWy725IfelLeVcEM97mmtRGXw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "binary-extensions": "^2.0.0"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/is-core-module": {
            "version": "2.16.1",
            "resolved": "https://registry.npmjs.org/is-core-module/-/is-core-module-2.16.1.tgz",
            "integrity": "sha512-UfoeMA6fIJ8wTYFEUjelnaGI67v6+N7qXJEvQuIGa99l4xsCruSYOVSQ0uPANn4dAzm8lkYPaKLrrijLq7x23w==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "hasown": "^2.0.2"
            },
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/is-extglob": {
            "version": "2.1.1",
            "resolved": "https://registry.npmjs.org/is-extglob/-/is-extglob-2.1.1.tgz",
            "integrity": "sha512-SbKbANkN603Vi4jEZv49LeVJMn4yGwsbzZworEoyEiutsN3nJYdbO36zfhGJ6QEDpOZIFkDtnq5JRxmvl3jsoQ==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/is-fullwidth-code-point": {
            "version": "3.0.0",
            "resolved": "https://registry.npmjs.org/is-fullwidth-code-point/-/is-fullwidth-code-point-3.0.0.tgz",
            "integrity": "sha512-zymm5+u+sCsSWyD9qNaejV3DFvhCKclKdizYaJUuHA83RLjb7nSuGnddCHGv0hk+KY7BMAlsWeK4Ueg6EV6XQg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/is-glob": {
            "version": "4.0.3",
            "resolved": "https://registry.npmjs.org/is-glob/-/is-glob-4.0.3.tgz",
            "integrity": "sha512-xelSayHH36ZgE7ZWhli7pW34hNbNl8Ojv5KVmkJD4hBdD3th8Tfk9vYasLM+mXWOZhFkgZfxhLSnrwRr4elSSg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "is-extglob": "^2.1.1"
            },
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/is-number": {
            "version": "7.0.0",
            "resolved": "https://registry.npmjs.org/is-number/-/is-number-7.0.0.tgz",
            "integrity": "sha512-41Cifkg6e8TylSpdtTpeLVMqvSBEVzTttHvERD741+pnZ8ANv0004MRL43QKPDlK9cGvNp6NZWZUBlbGXYxxng==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.12.0"
            }
        },
        "node_modules/jiti": {
            "version": "2.6.1",
            "resolved": "https://registry.npmjs.org/jiti/-/jiti-2.6.1.tgz",
            "integrity": "sha512-ekilCSN1jwRvIbgeg/57YFh8qQDNbwDb9xT/qu2DAHbFFZUicIl4ygVaAvzveMhMVr3LnpSKTNnwt8PoOfmKhQ==",
            "dev": true,
            "license": "MIT",
            "bin": {
                "jiti": "lib/jiti-cli.mjs"
            }
        },
        "node_modules/laravel-vite-plugin": {
            "version": "2.1.0",
            "resolved": "https://registry.npmjs.org/laravel-vite-plugin/-/laravel-vite-plugin-2.1.0.tgz",
            "integrity": "sha512-z+ck2BSV6KWtYcoIzk9Y5+p4NEjqM+Y4i8/H+VZRLq0OgNjW2DqyADquwYu5j8qRvaXwzNmfCWl1KrMlV1zpsg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "picocolors": "^1.0.0",
                "vite-plugin-full-reload": "^1.1.0"
            },
            "bin": {
                "clean-orphaned-assets": "bin/clean.js"
            },
            "engines": {
                "node": "^20.19.0 || >=22.12.0"
            },
            "peerDependencies": {
                "vite": "^7.0.0"
            }
        },
        "node_modules/lightningcss": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss/-/lightningcss-1.30.2.tgz",
            "integrity": "sha512-utfs7Pr5uJyyvDETitgsaqSyjCb2qNRAtuqUeWIAKztsOYdcACf2KtARYXg2pSvhkt+9NfoaNY7fxjl6nuMjIQ==",
            "dev": true,
            "license": "MPL-2.0",
            "dependencies": {
                "detect-libc": "^2.0.3"
            },
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            },
            "optionalDependencies": {
                "lightningcss-android-arm64": "1.30.2",
                "lightningcss-darwin-arm64": "1.30.2",
                "lightningcss-darwin-x64": "1.30.2",
                "lightningcss-freebsd-x64": "1.30.2",
                "lightningcss-linux-arm-gnueabihf": "1.30.2",
                "lightningcss-linux-arm64-gnu": "1.30.2",
                "lightningcss-linux-arm64-musl": "1.30.2",
                "lightningcss-linux-x64-gnu": "1.30.2",
                "lightningcss-linux-x64-musl": "1.30.2",
                "lightningcss-win32-arm64-msvc": "1.30.2",
                "lightningcss-win32-x64-msvc": "1.30.2"
            }
        },
        "node_modules/lightningcss-android-arm64": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-android-arm64/-/lightningcss-android-arm64-1.30.2.tgz",
            "integrity": "sha512-BH9sEdOCahSgmkVhBLeU7Hc9DWeZ1Eb6wNS6Da8igvUwAe0sqROHddIlvU06q3WyXVEOYDZ6ykBZQnjTbmo4+A==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "android"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-darwin-arm64": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-darwin-arm64/-/lightningcss-darwin-arm64-1.30.2.tgz",
            "integrity": "sha512-ylTcDJBN3Hp21TdhRT5zBOIi73P6/W0qwvlFEk22fkdXchtNTOU4Qc37SkzV+EKYxLouZ6M4LG9NfZ1qkhhBWA==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-darwin-x64": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-darwin-x64/-/lightningcss-darwin-x64-1.30.2.tgz",
            "integrity": "sha512-oBZgKchomuDYxr7ilwLcyms6BCyLn0z8J0+ZZmfpjwg9fRVZIR5/GMXd7r9RH94iDhld3UmSjBM6nXWM2TfZTQ==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "darwin"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-freebsd-x64": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-freebsd-x64/-/lightningcss-freebsd-x64-1.30.2.tgz",
            "integrity": "sha512-c2bH6xTrf4BDpK8MoGG4Bd6zAMZDAXS569UxCAGcA7IKbHNMlhGQ89eRmvpIUGfKWNVdbhSbkQaWhEoMGmGslA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "freebsd"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-linux-arm-gnueabihf": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-linux-arm-gnueabihf/-/lightningcss-linux-arm-gnueabihf-1.30.2.tgz",
            "integrity": "sha512-eVdpxh4wYcm0PofJIZVuYuLiqBIakQ9uFZmipf6LF/HRj5Bgm0eb3qL/mr1smyXIS1twwOxNWndd8z0E374hiA==",
            "cpu": [
                "arm"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-linux-arm64-gnu": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-linux-arm64-gnu/-/lightningcss-linux-arm64-gnu-1.30.2.tgz",
            "integrity": "sha512-UK65WJAbwIJbiBFXpxrbTNArtfuznvxAJw4Q2ZGlU8kPeDIWEX1dg3rn2veBVUylA2Ezg89ktszWbaQnxD/e3A==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-linux-arm64-musl": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-linux-arm64-musl/-/lightningcss-linux-arm64-musl-1.30.2.tgz",
            "integrity": "sha512-5Vh9dGeblpTxWHpOx8iauV02popZDsCYMPIgiuw97OJ5uaDsL86cnqSFs5LZkG3ghHoX5isLgWzMs+eD1YzrnA==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-linux-x64-gnu": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-linux-x64-gnu/-/lightningcss-linux-x64-gnu-1.30.2.tgz",
            "integrity": "sha512-Cfd46gdmj1vQ+lR6VRTTadNHu6ALuw2pKR9lYq4FnhvgBc4zWY1EtZcAc6EffShbb1MFrIPfLDXD6Xprbnni4w==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-linux-x64-musl": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-linux-x64-musl/-/lightningcss-linux-x64-musl-1.30.2.tgz",
            "integrity": "sha512-XJaLUUFXb6/QG2lGIW6aIk6jKdtjtcffUT0NKvIqhSBY3hh9Ch+1LCeH80dR9q9LBjG3ewbDjnumefsLsP6aiA==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "linux"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-win32-arm64-msvc": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-win32-arm64-msvc/-/lightningcss-win32-arm64-msvc-1.30.2.tgz",
            "integrity": "sha512-FZn+vaj7zLv//D/192WFFVA0RgHawIcHqLX9xuWiQt7P0PtdFEVaxgF9rjM/IRYHQXNnk61/H/gb2Ei+kUQ4xQ==",
            "cpu": [
                "arm64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lightningcss-win32-x64-msvc": {
            "version": "1.30.2",
            "resolved": "https://registry.npmjs.org/lightningcss-win32-x64-msvc/-/lightningcss-win32-x64-msvc-1.30.2.tgz",
            "integrity": "sha512-5g1yc73p+iAkid5phb4oVFMB45417DkRevRbt/El/gKXJk4jid+vPFF/AXbxn05Aky8PapwzZrdJShv5C0avjw==",
            "cpu": [
                "x64"
            ],
            "dev": true,
            "license": "MPL-2.0",
            "optional": true,
            "os": [
                "win32"
            ],
            "engines": {
                "node": ">= 12.0.0"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/parcel"
            }
        },
        "node_modules/lilconfig": {
            "version": "3.1.3",
            "resolved": "https://registry.npmjs.org/lilconfig/-/lilconfig-3.1.3.tgz",
            "integrity": "sha512-/vlFKAoH5Cgt3Ie+JLhRbwOsCQePABiU3tJ1egGvyQ+33R/vcwM2Zl2QR/LzjsBeItPt3oSVXapn+m4nQDvpzw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=14"
            },
            "funding": {
                "url": "https://github.com/sponsors/antonk52"
            }
        },
        "node_modules/lines-and-columns": {
            "version": "1.2.4",
            "resolved": "https://registry.npmjs.org/lines-and-columns/-/lines-and-columns-1.2.4.tgz",
            "integrity": "sha512-7ylylesZQ/PV29jhEDl3Ufjo6ZX7gCqJr5F7PKrqc93v7fzSymt1BpwEU8nAUXs8qzzvqhbjhK5QZg6Mt/HkBg==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/magic-string": {
            "version": "0.30.21",
            "resolved": "https://registry.npmjs.org/magic-string/-/magic-string-0.30.21.tgz",
            "integrity": "sha512-vd2F4YUyEXKGcLHoq+TEyCjxueSeHnFxyyjNp80yg0XV4vUhnDer/lvvlqM/arB5bXQN5K2/3oinyCRyx8T2CQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/sourcemap-codec": "^1.5.5"
            }
        },
        "node_modules/math-intrinsics": {
            "version": "1.1.0",
            "resolved": "https://registry.npmjs.org/math-intrinsics/-/math-intrinsics-1.1.0.tgz",
            "integrity": "sha512-/IXtbwEk5HTPyEwyKX6hGkYXxM9nbj64B+ilVJnC/R6B0pH5G4V3b0pVbL7DBj4tkhBAppbQUlf6F6Xl9LHu1g==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            }
        },
        "node_modules/merge2": {
            "version": "1.4.1",
            "resolved": "https://registry.npmjs.org/merge2/-/merge2-1.4.1.tgz",
            "integrity": "sha512-8q7VEgMJW4J8tcfVPy8g09NcQwZdbwFEqhe/WZkoIzjn/3TGDwtOCYtXGxA3O8tPzpczCCDgv+P2P5y00ZJOOg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 8"
            }
        },
        "node_modules/micromatch": {
            "version": "4.0.8",
            "resolved": "https://registry.npmjs.org/micromatch/-/micromatch-4.0.8.tgz",
            "integrity": "sha512-PXwfBhYu0hBCPw8Dn0E+WDYb7af3dSLVWKi3HGv84IdF4TyFoC0ysxFd0Goxw7nSv4T/PzEJQxsYsEiFCKo2BA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "braces": "^3.0.3",
                "picomatch": "^2.3.1"
            },
            "engines": {
                "node": ">=8.6"
            }
        },
        "node_modules/mime-db": {
            "version": "1.52.0",
            "resolved": "https://registry.npmjs.org/mime-db/-/mime-db-1.52.0.tgz",
            "integrity": "sha512-sPU4uV7dYlvtWJxwwxHD0PuihVNiE7TyAbQ5SWxDCB9mUYvOgroQOwYQQOKPJ8CIbE+1ETVlOoK1UC2nU3gYvg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.6"
            }
        },
        "node_modules/mime-types": {
            "version": "2.1.35",
            "resolved": "https://registry.npmjs.org/mime-types/-/mime-types-2.1.35.tgz",
            "integrity": "sha512-ZDY+bPm5zTTF+YpCrAU9nK0UgICYPT0QtT1NZWFv4s++TNkcgVaT0g6+4R2uI4MjQjzysHB1zxuWL50hzaeXiw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "mime-db": "1.52.0"
            },
            "engines": {
                "node": ">= 0.6"
            }
        },
        "node_modules/mini-svg-data-uri": {
            "version": "1.4.4",
            "resolved": "https://registry.npmjs.org/mini-svg-data-uri/-/mini-svg-data-uri-1.4.4.tgz",
            "integrity": "sha512-r9deDe9p5FJUPZAk3A59wGH7Ii9YrjjWw0jmw/liSbHl2CHiyXj6FcDXDu2K3TjVAXqiJdaw3xxwlZZr9E6nHg==",
            "dev": true,
            "license": "MIT",
            "bin": {
                "mini-svg-data-uri": "cli.js"
            }
        },
        "node_modules/mz": {
            "version": "2.7.0",
            "resolved": "https://registry.npmjs.org/mz/-/mz-2.7.0.tgz",
            "integrity": "sha512-z81GNO7nnYMEhrGh9LeymoE4+Yr0Wn5McHIZMK5cfQCl+NDX08sCZgUc9/6MHni9IWuFLm1Z3HTCXu2z9fN62Q==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "any-promise": "^1.0.0",
                "object-assign": "^4.0.1",
                "thenify-all": "^1.0.0"
            }
        },
        "node_modules/nanoid": {
            "version": "3.3.11",
            "resolved": "https://registry.npmjs.org/nanoid/-/nanoid-3.3.11.tgz",
            "integrity": "sha512-N8SpfPUnUp1bK+PMYW8qSWdl9U+wwNWI4QKxOYDy9JAro3WMX7p2OeVRF9v+347pnakNevPmiHhNmZ2HbFA76w==",
            "dev": true,
            "funding": [
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "bin": {
                "nanoid": "bin/nanoid.cjs"
            },
            "engines": {
                "node": "^10 || ^12 || ^13.7 || ^14 || >=15.0.1"
            }
        },
        "node_modules/node-releases": {
            "version": "2.0.27",
            "resolved": "https://registry.npmjs.org/node-releases/-/node-releases-2.0.27.tgz",
            "integrity": "sha512-nmh3lCkYZ3grZvqcCH+fjmQ7X+H0OeZgP40OierEaAptX4XofMh5kwNbWh7lBduUzCcV/8kZ+NDLCwm2iorIlA==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/normalize-path": {
            "version": "3.0.0",
            "resolved": "https://registry.npmjs.org/normalize-path/-/normalize-path-3.0.0.tgz",
            "integrity": "sha512-6eZs5Ls3WtCisHWp9S2GUy8dqkpGi4BVSz3GaqiE6ezub0512ESztXUwUB6C6IKbQkY2Pnb/mD4WYojCRwcwLA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/object-assign": {
            "version": "4.1.1",
            "resolved": "https://registry.npmjs.org/object-assign/-/object-assign-4.1.1.tgz",
            "integrity": "sha512-rJgTQnkUnH1sFw8yT6VSU3zD3sWmu6sZhIseY8VX+GRu3P6F7Fu+JNDoXfklElbLJSnc3FUQHVe4cU5hj+BcUg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/object-hash": {
            "version": "3.0.0",
            "resolved": "https://registry.npmjs.org/object-hash/-/object-hash-3.0.0.tgz",
            "integrity": "sha512-RSn9F68PjH9HqtltsSnqYC1XXoWe9Bju5+213R98cNGttag9q9yAOTzdbsqvIa7aNm5WffBZFpWYr2aWrklWAw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/path-parse": {
            "version": "1.0.7",
            "resolved": "https://registry.npmjs.org/path-parse/-/path-parse-1.0.7.tgz",
            "integrity": "sha512-LDJzPVEEEPR+y48z93A0Ed0yXb8pAByGWo/k5YYdYgpY2/2EsOsksJrq7lOHxryrVOn1ejG6oAp8ahvOIQD8sw==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/picocolors": {
            "version": "1.1.1",
            "resolved": "https://registry.npmjs.org/picocolors/-/picocolors-1.1.1.tgz",
            "integrity": "sha512-xceH2snhtb5M9liqDsmEw56le376mTZkEX/jEb/RxNFyegNul7eNslCXP9FDj/Lcu0X8KEyMceP2ntpaHrDEVA==",
            "dev": true,
            "license": "ISC"
        },
        "node_modules/picomatch": {
            "version": "2.3.1",
            "resolved": "https://registry.npmjs.org/picomatch/-/picomatch-2.3.1.tgz",
            "integrity": "sha512-JU3teHTNjmE2VCGFzuY8EXzCDVwEqB2a8fsIvwaStHhAWJEeVd1o1QD80CU6+ZdEXXSLbSsuLwJjkCBWqRQUVA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=8.6"
            },
            "funding": {
                "url": "https://github.com/sponsors/jonschlinkert"
            }
        },
        "node_modules/pify": {
            "version": "2.3.0",
            "resolved": "https://registry.npmjs.org/pify/-/pify-2.3.0.tgz",
            "integrity": "sha512-udgsAY+fTnvv7kI7aaxbqwWNb0AHiB0qBO89PZKPkoTmGOgdbrHDKD+0B2X4uTfJ/FT1R09r9gTsjUjNJotuog==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/pirates": {
            "version": "4.0.7",
            "resolved": "https://registry.npmjs.org/pirates/-/pirates-4.0.7.tgz",
            "integrity": "sha512-TfySrs/5nm8fQJDcBDuUng3VOUKsd7S+zqvbOTiGXHfxX4wK31ard+hoNuvkicM/2YFzlpDgABOevKSsB4G/FA==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 6"
            }
        },
        "node_modules/postcss": {
            "version": "8.5.6",
            "resolved": "https://registry.npmjs.org/postcss/-/postcss-8.5.6.tgz",
            "integrity": "sha512-3Ybi1tAuwAP9s0r1UQ2J4n5Y0G05bJkpUIO0/bI9MhwmD70S5aTWbXGBwxHrelT+XM1k6dM0pk+SwNkpTRN7Pg==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/postcss/"
                },
                {
                    "type": "tidelift",
                    "url": "https://tidelift.com/funding/github/npm/postcss"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "nanoid": "^3.3.11",
                "picocolors": "^1.1.1",
                "source-map-js": "^1.2.1"
            },
            "engines": {
                "node": "^10 || ^12 || >=14"
            }
        },
        "node_modules/postcss-import": {
            "version": "15.1.0",
            "resolved": "https://registry.npmjs.org/postcss-import/-/postcss-import-15.1.0.tgz",
            "integrity": "sha512-hpr+J05B2FVYUAXHeK1YyI267J/dDDhMU6B6civm8hSY1jYJnBXxzKDKDswzJmtLHryrjhnDjqqp/49t8FALew==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "postcss-value-parser": "^4.0.0",
                "read-cache": "^1.0.0",
                "resolve": "^1.1.7"
            },
            "engines": {
                "node": ">=14.0.0"
            },
            "peerDependencies": {
                "postcss": "^8.0.0"
            }
        },
        "node_modules/postcss-js": {
            "version": "4.1.0",
            "resolved": "https://registry.npmjs.org/postcss-js/-/postcss-js-4.1.0.tgz",
            "integrity": "sha512-oIAOTqgIo7q2EOwbhb8UalYePMvYoIeRY2YKntdpFQXNosSu3vLrniGgmH9OKs/qAkfoj5oB3le/7mINW1LCfw==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/postcss/"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "camelcase-css": "^2.0.1"
            },
            "engines": {
                "node": "^12 || ^14 || >= 16"
            },
            "peerDependencies": {
                "postcss": "^8.4.21"
            }
        },
        "node_modules/postcss-load-config": {
            "version": "6.0.1",
            "resolved": "https://registry.npmjs.org/postcss-load-config/-/postcss-load-config-6.0.1.tgz",
            "integrity": "sha512-oPtTM4oerL+UXmx+93ytZVN82RrlY/wPUV8IeDxFrzIjXOLF1pN+EmKPLbubvKHT2HC20xXsCAH2Z+CKV6Oz/g==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/postcss/"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "lilconfig": "^3.1.1"
            },
            "engines": {
                "node": ">= 18"
            },
            "peerDependencies": {
                "jiti": ">=1.21.0",
                "postcss": ">=8.0.9",
                "tsx": "^4.8.1",
                "yaml": "^2.4.2"
            },
            "peerDependenciesMeta": {
                "jiti": {
                    "optional": true
                },
                "postcss": {
                    "optional": true
                },
                "tsx": {
                    "optional": true
                },
                "yaml": {
                    "optional": true
                }
            }
        },
        "node_modules/postcss-nested": {
            "version": "6.2.0",
            "resolved": "https://registry.npmjs.org/postcss-nested/-/postcss-nested-6.2.0.tgz",
            "integrity": "sha512-HQbt28KulC5AJzG+cZtj9kvKB93CFCdLvog1WFLf1D+xmMvPGlBstkpTEZfK5+AN9hfJocyBFCNiqyS48bpgzQ==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/postcss/"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "postcss-selector-parser": "^6.1.1"
            },
            "engines": {
                "node": ">=12.0"
            },
            "peerDependencies": {
                "postcss": "^8.2.14"
            }
        },
        "node_modules/postcss-selector-parser": {
            "version": "6.1.2",
            "resolved": "https://registry.npmjs.org/postcss-selector-parser/-/postcss-selector-parser-6.1.2.tgz",
            "integrity": "sha512-Q8qQfPiZ+THO/3ZrOrO0cJJKfpYCagtMUkXbnEfmgUjwXg6z/WBeOyS9APBBPCTSiDV+s4SwQGu8yFsiMRIudg==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "cssesc": "^3.0.0",
                "util-deprecate": "^1.0.2"
            },
            "engines": {
                "node": ">=4"
            }
        },
        "node_modules/postcss-value-parser": {
            "version": "4.2.0",
            "resolved": "https://registry.npmjs.org/postcss-value-parser/-/postcss-value-parser-4.2.0.tgz",
            "integrity": "sha512-1NNCs6uurfkVbeXG4S8JFT9t19m45ICnif8zWLd5oPSZ50QnwMfK+H3jv408d4jw/7Bttv5axS5IiHoLaVNHeQ==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/proxy-from-env": {
            "version": "1.1.0",
            "resolved": "https://registry.npmjs.org/proxy-from-env/-/proxy-from-env-1.1.0.tgz",
            "integrity": "sha512-D+zkORCbA9f1tdWRK0RaCR3GPv50cMxcrz4X8k5LTSUD1Dkw47mKJEZQNunItRTkWwgtaUSo1RVFRIG9ZXiFYg==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/queue-microtask": {
            "version": "1.2.3",
            "resolved": "https://registry.npmjs.org/queue-microtask/-/queue-microtask-1.2.3.tgz",
            "integrity": "sha512-NuaNSa6flKT5JaSYQzJok04JzTL1CA6aGhv5rfLW3PgqA+M2ChpZQnAC8h8i4ZFkBS8X5RqkDBHA7r4hej3K9A==",
            "dev": true,
            "funding": [
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/feross"
                },
                {
                    "type": "patreon",
                    "url": "https://www.patreon.com/feross"
                },
                {
                    "type": "consulting",
                    "url": "https://feross.org/support"
                }
            ],
            "license": "MIT"
        },
        "node_modules/read-cache": {
            "version": "1.0.0",
            "resolved": "https://registry.npmjs.org/read-cache/-/read-cache-1.0.0.tgz",
            "integrity": "sha512-Owdv/Ft7IjOgm/i0xvNDZ1LrRANRfew4b2prF3OWMQLxLfu3bS8FVhCsrSCMK4lR56Y9ya+AThoTpDCTxCmpRA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "pify": "^2.3.0"
            }
        },
        "node_modules/readdirp": {
            "version": "3.6.0",
            "resolved": "https://registry.npmjs.org/readdirp/-/readdirp-3.6.0.tgz",
            "integrity": "sha512-hOS089on8RduqdbhvQ5Z37A0ESjsqz6qnRcffsMU3495FuTdqSm+7bhJ29JvIOsBDEEnan5DPu9t3To9VRlMzA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "picomatch": "^2.2.1"
            },
            "engines": {
                "node": ">=8.10.0"
            }
        },
        "node_modules/require-directory": {
            "version": "2.1.1",
            "resolved": "https://registry.npmjs.org/require-directory/-/require-directory-2.1.1.tgz",
            "integrity": "sha512-fGxEI7+wsG9xrvdjsrlmL22OMTTiHRwAMroiEeMgq8gzoLC/PQr7RsRDSTLUg/bZAZtF+TVIkHc6/4RIKrui+Q==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/resolve": {
            "version": "1.22.11",
            "resolved": "https://registry.npmjs.org/resolve/-/resolve-1.22.11.tgz",
            "integrity": "sha512-RfqAvLnMl313r7c9oclB1HhUEAezcpLjz95wFH4LVuhk9JF/r22qmVP9AMmOU4vMX7Q8pN8jwNg/CSpdFnMjTQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "is-core-module": "^2.16.1",
                "path-parse": "^1.0.7",
                "supports-preserve-symlinks-flag": "^1.0.0"
            },
            "bin": {
                "resolve": "bin/resolve"
            },
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/reusify": {
            "version": "1.1.0",
            "resolved": "https://registry.npmjs.org/reusify/-/reusify-1.1.0.tgz",
            "integrity": "sha512-g6QUff04oZpHs0eG5p83rFLhHeV00ug/Yf9nZM6fLeUrPguBTkTQOdpAWWspMh55TZfVQDPaN3NQJfbVRAxdIw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "iojs": ">=1.0.0",
                "node": ">=0.10.0"
            }
        },
        "node_modules/rollup": {
            "version": "4.57.1",
            "resolved": "https://registry.npmjs.org/rollup/-/rollup-4.57.1.tgz",
            "integrity": "sha512-oQL6lgK3e2QZeQ7gcgIkS2YZPg5slw37hYufJ3edKlfQSGGm8ICoxswK15ntSzF/a8+h7ekRy7k7oWc3BQ7y8A==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@types/estree": "1.0.8"
            },
            "bin": {
                "rollup": "dist/bin/rollup"
            },
            "engines": {
                "node": ">=18.0.0",
                "npm": ">=8.0.0"
            },
            "optionalDependencies": {
                "@rollup/rollup-android-arm-eabi": "4.57.1",
                "@rollup/rollup-android-arm64": "4.57.1",
                "@rollup/rollup-darwin-arm64": "4.57.1",
                "@rollup/rollup-darwin-x64": "4.57.1",
                "@rollup/rollup-freebsd-arm64": "4.57.1",
                "@rollup/rollup-freebsd-x64": "4.57.1",
                "@rollup/rollup-linux-arm-gnueabihf": "4.57.1",
                "@rollup/rollup-linux-arm-musleabihf": "4.57.1",
                "@rollup/rollup-linux-arm64-gnu": "4.57.1",
                "@rollup/rollup-linux-arm64-musl": "4.57.1",
                "@rollup/rollup-linux-loong64-gnu": "4.57.1",
                "@rollup/rollup-linux-loong64-musl": "4.57.1",
                "@rollup/rollup-linux-ppc64-gnu": "4.57.1",
                "@rollup/rollup-linux-ppc64-musl": "4.57.1",
                "@rollup/rollup-linux-riscv64-gnu": "4.57.1",
                "@rollup/rollup-linux-riscv64-musl": "4.57.1",
                "@rollup/rollup-linux-s390x-gnu": "4.57.1",
                "@rollup/rollup-linux-x64-gnu": "4.57.1",
                "@rollup/rollup-linux-x64-musl": "4.57.1",
                "@rollup/rollup-openbsd-x64": "4.57.1",
                "@rollup/rollup-openharmony-arm64": "4.57.1",
                "@rollup/rollup-win32-arm64-msvc": "4.57.1",
                "@rollup/rollup-win32-ia32-msvc": "4.57.1",
                "@rollup/rollup-win32-x64-gnu": "4.57.1",
                "@rollup/rollup-win32-x64-msvc": "4.57.1",
                "fsevents": "~2.3.2"
            }
        },
        "node_modules/run-parallel": {
            "version": "1.2.0",
            "resolved": "https://registry.npmjs.org/run-parallel/-/run-parallel-1.2.0.tgz",
            "integrity": "sha512-5l4VyZR86LZ/lDxZTR6jqL8AFE2S0IFLMP26AbjsLVADxHdhB/c0GUsH+y39UfCi3dzz8OlQuPmnaJOMoDHQBA==",
            "dev": true,
            "funding": [
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/feross"
                },
                {
                    "type": "patreon",
                    "url": "https://www.patreon.com/feross"
                },
                {
                    "type": "consulting",
                    "url": "https://feross.org/support"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "queue-microtask": "^1.2.2"
            }
        },
        "node_modules/rxjs": {
            "version": "7.8.2",
            "resolved": "https://registry.npmjs.org/rxjs/-/rxjs-7.8.2.tgz",
            "integrity": "sha512-dhKf903U/PQZY6boNNtAGdWbG85WAbjT/1xYoZIC7FAY0yWapOBQVsVrDl58W86//e1VpMNBtRV4MaXfdMySFA==",
            "dev": true,
            "license": "Apache-2.0",
            "dependencies": {
                "tslib": "^2.1.0"
            }
        },
        "node_modules/shell-quote": {
            "version": "1.8.3",
            "resolved": "https://registry.npmjs.org/shell-quote/-/shell-quote-1.8.3.tgz",
            "integrity": "sha512-ObmnIF4hXNg1BqhnHmgbDETF8dLPCggZWBjkQfhZpbszZnYur5DUljTcCHii5LC3J5E0yeO/1LIMyH+UvHQgyw==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/source-map-js": {
            "version": "1.2.1",
            "resolved": "https://registry.npmjs.org/source-map-js/-/source-map-js-1.2.1.tgz",
            "integrity": "sha512-UXWMKhLOwVKb728IUtQPXxfYU+usdybtUrK/8uGE8CQMvrhOpwvzDBwj0QhSL7MQc7vIsISBG8VQ8+IDQxpfQA==",
            "dev": true,
            "license": "BSD-3-Clause",
            "engines": {
                "node": ">=0.10.0"
            }
        },
        "node_modules/string-width": {
            "version": "4.2.3",
            "resolved": "https://registry.npmjs.org/string-width/-/string-width-4.2.3.tgz",
            "integrity": "sha512-wKyQRQpjJ0sIp62ErSZdGsjMJWsap5oRNihHhu6G7JVO/9jIB6UyevL+tXuOqrng8j/cxKTWyWUwvSTriiZz/g==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "emoji-regex": "^8.0.0",
                "is-fullwidth-code-point": "^3.0.0",
                "strip-ansi": "^6.0.1"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/strip-ansi": {
            "version": "6.0.1",
            "resolved": "https://registry.npmjs.org/strip-ansi/-/strip-ansi-6.0.1.tgz",
            "integrity": "sha512-Y38VPSHcqkFrCpFnQ9vuSXmquuv5oXOKpGeT6aGrr3o3Gc9AlVa6JBfUSOCnbxGGZF+/0ooI7KrPuUSztUdU5A==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "ansi-regex": "^5.0.1"
            },
            "engines": {
                "node": ">=8"
            }
        },
        "node_modules/sucrase": {
            "version": "3.35.1",
            "resolved": "https://registry.npmjs.org/sucrase/-/sucrase-3.35.1.tgz",
            "integrity": "sha512-DhuTmvZWux4H1UOnWMB3sk0sbaCVOoQZjv8u1rDoTV0HTdGem9hkAZtl4JZy8P2z4Bg0nT+YMeOFyVr4zcG5Tw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@jridgewell/gen-mapping": "^0.3.2",
                "commander": "^4.0.0",
                "lines-and-columns": "^1.1.6",
                "mz": "^2.7.0",
                "pirates": "^4.0.1",
                "tinyglobby": "^0.2.11",
                "ts-interface-checker": "^0.1.9"
            },
            "bin": {
                "sucrase": "bin/sucrase",
                "sucrase-node": "bin/sucrase-node"
            },
            "engines": {
                "node": ">=16 || 14 >=14.17"
            }
        },
        "node_modules/supports-color": {
            "version": "8.1.1",
            "resolved": "https://registry.npmjs.org/supports-color/-/supports-color-8.1.1.tgz",
            "integrity": "sha512-MpUEN2OodtUzxvKQl72cUF7RQ5EiHsGvSsVG0ia9c5RbWGL2CI4C7EpPS8UTBIplnlzZiNuV56w+FuNxy3ty2Q==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "has-flag": "^4.0.0"
            },
            "engines": {
                "node": ">=10"
            },
            "funding": {
                "url": "https://github.com/chalk/supports-color?sponsor=1"
            }
        },
        "node_modules/supports-preserve-symlinks-flag": {
            "version": "1.0.0",
            "resolved": "https://registry.npmjs.org/supports-preserve-symlinks-flag/-/supports-preserve-symlinks-flag-1.0.0.tgz",
            "integrity": "sha512-ot0WnXS9fgdkgIcePe6RHNk1WA8+muPa6cSjeR3V8K27q9BB1rTE3R1p7Hv0z1ZyAc8s6Vvv8DIyWf681MAt0w==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">= 0.4"
            },
            "funding": {
                "url": "https://github.com/sponsors/ljharb"
            }
        },
        "node_modules/sweetalert2": {
            "version": "11.26.20",
            "resolved": "https://registry.npmjs.org/sweetalert2/-/sweetalert2-11.26.20.tgz",
            "integrity": "sha512-mG3guPRzmpqGufTQ05Kr7WQIe1xfBEB9JwPIJbR2plL0ezVzS3qmazuzse0J8+/YYGCGrAL4Us2Sm5VCgHyuRg==",
            "license": "MIT",
            "funding": {
                "type": "individual",
                "url": "https://github.com/sponsors/limonte"
            }
        },
        "node_modules/tailwindcss": {
            "version": "3.4.19",
            "resolved": "https://registry.npmjs.org/tailwindcss/-/tailwindcss-3.4.19.tgz",
            "integrity": "sha512-3ofp+LL8E+pK/JuPLPggVAIaEuhvIz4qNcf3nA1Xn2o/7fb7s/TYpHhwGDv1ZU3PkBluUVaF8PyCHcm48cKLWQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "@alloc/quick-lru": "^5.2.0",
                "arg": "^5.0.2",
                "chokidar": "^3.6.0",
                "didyoumean": "^1.2.2",
                "dlv": "^1.1.3",
                "fast-glob": "^3.3.2",
                "glob-parent": "^6.0.2",
                "is-glob": "^4.0.3",
                "jiti": "^1.21.7",
                "lilconfig": "^3.1.3",
                "micromatch": "^4.0.8",
                "normalize-path": "^3.0.0",
                "object-hash": "^3.0.0",
                "picocolors": "^1.1.1",
                "postcss": "^8.4.47",
                "postcss-import": "^15.1.0",
                "postcss-js": "^4.0.1",
                "postcss-load-config": "^4.0.2 || ^5.0 || ^6.0",
                "postcss-nested": "^6.2.0",
                "postcss-selector-parser": "^6.1.2",
                "resolve": "^1.22.8",
                "sucrase": "^3.35.0"
            },
            "bin": {
                "tailwind": "lib/cli.js",
                "tailwindcss": "lib/cli.js"
            },
            "engines": {
                "node": ">=14.0.0"
            }
        },
        "node_modules/tailwindcss/node_modules/jiti": {
            "version": "1.21.7",
            "resolved": "https://registry.npmjs.org/jiti/-/jiti-1.21.7.tgz",
            "integrity": "sha512-/imKNG4EbWNrVjoNC/1H5/9GFy+tqjGBHCaSsN+P2RnPqjsLmv6UD3Ej+Kj8nBWaRAwyk7kK5ZUc+OEatnTR3A==",
            "dev": true,
            "license": "MIT",
            "bin": {
                "jiti": "bin/jiti.js"
            }
        },
        "node_modules/tapable": {
            "version": "2.3.0",
            "resolved": "https://registry.npmjs.org/tapable/-/tapable-2.3.0.tgz",
            "integrity": "sha512-g9ljZiwki/LfxmQADO3dEY1CbpmXT5Hm2fJ+QaGKwSXUylMybePR7/67YW7jOrrvjEgL1Fmz5kzyAjWVWLlucg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=6"
            },
            "funding": {
                "type": "opencollective",
                "url": "https://opencollective.com/webpack"
            }
        },
        "node_modules/thenify": {
            "version": "3.3.1",
            "resolved": "https://registry.npmjs.org/thenify/-/thenify-3.3.1.tgz",
            "integrity": "sha512-RVZSIV5IG10Hk3enotrhvz0T9em6cyHBLkH/YAZuKqd8hRkKhSfCGIcP2KUY0EPxndzANBmNllzWPwak+bheSw==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "any-promise": "^1.0.0"
            }
        },
        "node_modules/thenify-all": {
            "version": "1.6.0",
            "resolved": "https://registry.npmjs.org/thenify-all/-/thenify-all-1.6.0.tgz",
            "integrity": "sha512-RNxQH/qI8/t3thXJDwcstUO4zeqo64+Uy/+sNVRBx4Xn2OX+OZ9oP+iJnNFqplFra2ZUVeKCSa2oVWi3T4uVmA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "thenify": ">= 3.1.0 < 4"
            },
            "engines": {
                "node": ">=0.8"
            }
        },
        "node_modules/tinyglobby": {
            "version": "0.2.15",
            "resolved": "https://registry.npmjs.org/tinyglobby/-/tinyglobby-0.2.15.tgz",
            "integrity": "sha512-j2Zq4NyQYG5XMST4cbs02Ak8iJUdxRM0XI5QyxXuZOzKOINmWurp3smXu3y5wDcJrptwpSjgXHzIQxR0omXljQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "fdir": "^6.5.0",
                "picomatch": "^4.0.3"
            },
            "engines": {
                "node": ">=12.0.0"
            },
            "funding": {
                "url": "https://github.com/sponsors/SuperchupuDev"
            }
        },
        "node_modules/tinyglobby/node_modules/fdir": {
            "version": "6.5.0",
            "resolved": "https://registry.npmjs.org/fdir/-/fdir-6.5.0.tgz",
            "integrity": "sha512-tIbYtZbucOs0BRGqPJkshJUYdL+SDH7dVM8gjy+ERp3WAUjLEFJE+02kanyHtwjWOnwrKYBiwAmM0p4kLJAnXg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=12.0.0"
            },
            "peerDependencies": {
                "picomatch": "^3 || ^4"
            },
            "peerDependenciesMeta": {
                "picomatch": {
                    "optional": true
                }
            }
        },
        "node_modules/tinyglobby/node_modules/picomatch": {
            "version": "4.0.3",
            "resolved": "https://registry.npmjs.org/picomatch/-/picomatch-4.0.3.tgz",
            "integrity": "sha512-5gTmgEY/sqK6gFXLIsQNH19lWb4ebPDLA4SdLP7dsWkIXHWlG66oPuVvXSGFPppYZz8ZDZq0dYYrbHfBCVUb1Q==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=12"
            },
            "funding": {
                "url": "https://github.com/sponsors/jonschlinkert"
            }
        },
        "node_modules/to-regex-range": {
            "version": "5.0.1",
            "resolved": "https://registry.npmjs.org/to-regex-range/-/to-regex-range-5.0.1.tgz",
            "integrity": "sha512-65P7iz6X5yEr1cwcgvQxbbIw7Uk3gOy5dIdtZ4rDveLqhrdJP+Li/Hx6tyK0NEb+2GCyneCMJiGqrADCSNk8sQ==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "is-number": "^7.0.0"
            },
            "engines": {
                "node": ">=8.0"
            }
        },
        "node_modules/tree-kill": {
            "version": "1.2.2",
            "resolved": "https://registry.npmjs.org/tree-kill/-/tree-kill-1.2.2.tgz",
            "integrity": "sha512-L0Orpi8qGpRG//Nd+H90vFB+3iHnue1zSSGmNOOCh1GLJ7rUKVwV2HvijphGQS2UmhUZewS9VgvxYIdgr+fG1A==",
            "dev": true,
            "license": "MIT",
            "bin": {
                "tree-kill": "cli.js"
            }
        },
        "node_modules/ts-interface-checker": {
            "version": "0.1.13",
            "resolved": "https://registry.npmjs.org/ts-interface-checker/-/ts-interface-checker-0.1.13.tgz",
            "integrity": "sha512-Y/arvbn+rrz3JCKl9C4kVNfTfSm2/mEp5FSz5EsZSANGPSlQrpRI5M4PKF+mJnE52jOO90PnPSc3Ur3bTQw0gA==",
            "dev": true,
            "license": "Apache-2.0"
        },
        "node_modules/tslib": {
            "version": "2.8.1",
            "resolved": "https://registry.npmjs.org/tslib/-/tslib-2.8.1.tgz",
            "integrity": "sha512-oJFu94HQb+KVduSUQL7wnpmqnfmLsOA/nAh6b6EH0wCEoK0/mPeXU6c3wKDV83MkOuHPRHtSXKKU99IBazS/2w==",
            "dev": true,
            "license": "0BSD"
        },
        "node_modules/update-browserslist-db": {
            "version": "1.2.3",
            "resolved": "https://registry.npmjs.org/update-browserslist-db/-/update-browserslist-db-1.2.3.tgz",
            "integrity": "sha512-Js0m9cx+qOgDxo0eMiFGEueWztz+d4+M3rGlmKPT+T4IS/jP4ylw3Nwpu6cpTTP8R1MAC1kF4VbdLt3ARf209w==",
            "dev": true,
            "funding": [
                {
                    "type": "opencollective",
                    "url": "https://opencollective.com/browserslist"
                },
                {
                    "type": "tidelift",
                    "url": "https://tidelift.com/funding/github/npm/browserslist"
                },
                {
                    "type": "github",
                    "url": "https://github.com/sponsors/ai"
                }
            ],
            "license": "MIT",
            "dependencies": {
                "escalade": "^3.2.0",
                "picocolors": "^1.1.1"
            },
            "bin": {
                "update-browserslist-db": "cli.js"
            },
            "peerDependencies": {
                "browserslist": ">= 4.21.0"
            }
        },
        "node_modules/util-deprecate": {
            "version": "1.0.2",
            "resolved": "https://registry.npmjs.org/util-deprecate/-/util-deprecate-1.0.2.tgz",
            "integrity": "sha512-EPD5q1uXyFxJpCrLnCc1nHnq3gOa6DZBocAIiI2TaSCA7VCJ1UJDMagCzIkXNsUYfD1daK//LTEQ8xiIbrHtcw==",
            "dev": true,
            "license": "MIT"
        },
        "node_modules/vite": {
            "version": "7.3.1",
            "resolved": "https://registry.npmjs.org/vite/-/vite-7.3.1.tgz",
            "integrity": "sha512-w+N7Hifpc3gRjZ63vYBXA56dvvRlNWRczTdmCBBa+CotUzAPf5b7YMdMR/8CQoeYE5LX3W4wj6RYTgonm1b9DA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "esbuild": "^0.27.0",
                "fdir": "^6.5.0",
                "picomatch": "^4.0.3",
                "postcss": "^8.5.6",
                "rollup": "^4.43.0",
                "tinyglobby": "^0.2.15"
            },
            "bin": {
                "vite": "bin/vite.js"
            },
            "engines": {
                "node": "^20.19.0 || >=22.12.0"
            },
            "funding": {
                "url": "https://github.com/vitejs/vite?sponsor=1"
            },
            "optionalDependencies": {
                "fsevents": "~2.3.3"
            },
            "peerDependencies": {
                "@types/node": "^20.19.0 || >=22.12.0",
                "jiti": ">=1.21.0",
                "less": "^4.0.0",
                "lightningcss": "^1.21.0",
                "sass": "^1.70.0",
                "sass-embedded": "^1.70.0",
                "stylus": ">=0.54.8",
                "sugarss": "^5.0.0",
                "terser": "^5.16.0",
                "tsx": "^4.8.1",
                "yaml": "^2.4.2"
            },
            "peerDependenciesMeta": {
                "@types/node": {
                    "optional": true
                },
                "jiti": {
                    "optional": true
                },
                "less": {
                    "optional": true
                },
                "lightningcss": {
                    "optional": true
                },
                "sass": {
                    "optional": true
                },
                "sass-embedded": {
                    "optional": true
                },
                "stylus": {
                    "optional": true
                },
                "sugarss": {
                    "optional": true
                },
                "terser": {
                    "optional": true
                },
                "tsx": {
                    "optional": true
                },
                "yaml": {
                    "optional": true
                }
            }
        },
        "node_modules/vite-plugin-full-reload": {
            "version": "1.2.0",
            "resolved": "https://registry.npmjs.org/vite-plugin-full-reload/-/vite-plugin-full-reload-1.2.0.tgz",
            "integrity": "sha512-kz18NW79x0IHbxRSHm0jttP4zoO9P9gXh+n6UTwlNKnviTTEpOlum6oS9SmecrTtSr+muHEn5TUuC75UovQzcA==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "picocolors": "^1.0.0",
                "picomatch": "^2.3.1"
            }
        },
        "node_modules/vite/node_modules/fdir": {
            "version": "6.5.0",
            "resolved": "https://registry.npmjs.org/fdir/-/fdir-6.5.0.tgz",
            "integrity": "sha512-tIbYtZbucOs0BRGqPJkshJUYdL+SDH7dVM8gjy+ERp3WAUjLEFJE+02kanyHtwjWOnwrKYBiwAmM0p4kLJAnXg==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=12.0.0"
            },
            "peerDependencies": {
                "picomatch": "^3 || ^4"
            },
            "peerDependenciesMeta": {
                "picomatch": {
                    "optional": true
                }
            }
        },
        "node_modules/vite/node_modules/picomatch": {
            "version": "4.0.3",
            "resolved": "https://registry.npmjs.org/picomatch/-/picomatch-4.0.3.tgz",
            "integrity": "sha512-5gTmgEY/sqK6gFXLIsQNH19lWb4ebPDLA4SdLP7dsWkIXHWlG66oPuVvXSGFPppYZz8ZDZq0dYYrbHfBCVUb1Q==",
            "dev": true,
            "license": "MIT",
            "engines": {
                "node": ">=12"
            },
            "funding": {
                "url": "https://github.com/sponsors/jonschlinkert"
            }
        },
        "node_modules/wrap-ansi": {
            "version": "7.0.0",
            "resolved": "https://registry.npmjs.org/wrap-ansi/-/wrap-ansi-7.0.0.tgz",
            "integrity": "sha512-YVGIj2kamLSTxw6NsZjoBxfSwsn0ycdesmc4p+Q21c5zPuZ1pl+NfxVdxPtdHvmNVOQ6XSYG4AUtyt/Fi7D16Q==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "ansi-styles": "^4.0.0",
                "string-width": "^4.1.0",
                "strip-ansi": "^6.0.0"
            },
            "engines": {
                "node": ">=10"
            },
            "funding": {
                "url": "https://github.com/chalk/wrap-ansi?sponsor=1"
            }
        },
        "node_modules/y18n": {
            "version": "5.0.8",
            "resolved": "https://registry.npmjs.org/y18n/-/y18n-5.0.8.tgz",
            "integrity": "sha512-0pfFzegeDWJHJIAmTLRP2DwHjdF5s7jo9tuztdQxAhINCdvS+3nGINqPd00AphqJR/0LhANUS6/+7SCb98YOfA==",
            "dev": true,
            "license": "ISC",
            "engines": {
                "node": ">=10"
            }
        },
        "node_modules/yargs": {
            "version": "17.7.2",
            "resolved": "https://registry.npmjs.org/yargs/-/yargs-17.7.2.tgz",
            "integrity": "sha512-7dSzzRQ++CKnNI/krKnYRV7JKKPUXMEh61soaHKg9mrWEhzFWhFnxPxGl+69cD1Ou63C13NUPCnmIcrvqCuM6w==",
            "dev": true,
            "license": "MIT",
            "dependencies": {
                "cliui": "^8.0.1",
                "escalade": "^3.1.1",
                "get-caller-file": "^2.0.5",
                "require-directory": "^2.1.1",
                "string-width": "^4.2.3",
                "y18n": "^5.0.5",
                "yargs-parser": "^21.1.1"
            },
            "engines": {
                "node": ">=12"
            }
        },
        "node_modules/yargs-parser": {
            "version": "21.1.1",
            "resolved": "https://registry.npmjs.org/yargs-parser/-/yargs-parser-21.1.1.tgz",
            "integrity": "sha512-tVpsJW7DdjecAiFpbIB1e3qxIQsE6NoPc5/eTdrbbIC4h0LVsWhnoa3g+m2HclBIujHzsxZ4VJVA+GUuc2/LBw==",
            "dev": true,
            "license": "ISC",
            "engines": {
                "node": ">=12"
            }
        }
    }
}

```

## File: package.json
```json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.2",
        "@tailwindcss/vite": "^4.0.0",
        "alpinejs": "^3.4.2",
        "autoprefixer": "^10.4.2",
        "axios": "^1.11.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^2.0.0",
        "postcss": "^8.4.31",
        "tailwindcss": "^3.1.0",
        "vite": "^7.0.7"
    },
    "dependencies": {
        "sweetalert2": "^11.26.20"
    }
}

```

## File: postcss.config.js
```javascript
export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};

```

## File: public\build\assets\app-CBbTb_k3.js
```javascript
function Zn(e,t){return function(){return e.apply(t,arguments)}}const{toString:Ii}=Object.prototype,{getPrototypeOf:qt}=Object,{iterator:rt,toStringTag:Qn}=Symbol,it=(e=>t=>{const n=Ii.call(t);return e[n]||(e[n]=n.slice(8,-1).toLowerCase())})(Object.create(null)),B=e=>(e=e.toLowerCase(),t=>it(t)===e),st=e=>t=>typeof t===e,{isArray:_e}=Array,pe=st("undefined");function Fe(e){return e!==null&&!pe(e)&&e.constructor!==null&&!pe(e.constructor)&&M(e.constructor.isBuffer)&&e.constructor.isBuffer(e)}const er=B("ArrayBuffer");function Di(e){let t;return typeof ArrayBuffer<"u"&&ArrayBuffer.isView?t=ArrayBuffer.isView(e):t=e&&e.buffer&&er(e.buffer),t}const ji=st("string"),M=st("function"),tr=st("number"),Le=e=>e!==null&&typeof e=="object",Bi=e=>e===!0||e===!1,Xe=e=>{if(it(e)!=="object")return!1;const t=qt(e);return(t===null||t===Object.prototype||Object.getPrototypeOf(t)===null)&&!(Qn in e)&&!(rt in e)},$i=e=>{if(!Le(e)||Fe(e))return!1;try{return Object.keys(e).length===0&&Object.getPrototypeOf(e)===Object.prototype}catch{return!1}},Ui=B("Date"),ki=B("File"),qi=B("Blob"),Hi=B("FileList"),zi=e=>Le(e)&&M(e.pipe),Ki=e=>{let t;return e&&(typeof FormData=="function"&&e instanceof FormData||M(e.append)&&((t=it(e))==="formdata"||t==="object"&&M(e.toString)&&e.toString()==="[object FormData]"))},Wi=B("URLSearchParams"),[Ji,Vi,Xi,Gi]=["ReadableStream","Request","Response","Headers"].map(B),Yi=e=>e.trim?e.trim():e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"");function Ie(e,t,{allOwnKeys:n=!1}={}){if(e===null||typeof e>"u")return;let r,i;if(typeof e!="object"&&(e=[e]),_e(e))for(r=0,i=e.length;r<i;r++)t.call(null,e[r],r,e);else{if(Fe(e))return;const s=n?Object.getOwnPropertyNames(e):Object.keys(e),o=s.length;let a;for(r=0;r<o;r++)a=s[r],t.call(null,e[a],a,e)}}function nr(e,t){if(Fe(e))return null;t=t.toLowerCase();const n=Object.keys(e);let r=n.length,i;for(;r-- >0;)if(i=n[r],t===i.toLowerCase())return i;return null}const G=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:global,rr=e=>!pe(e)&&e!==G;function St(){const{caseless:e,skipUndefined:t}=rr(this)&&this||{},n={},r=(i,s)=>{if(s==="__proto__"||s==="constructor"||s==="prototype")return;const o=e&&nr(n,s)||s;Xe(n[o])&&Xe(i)?n[o]=St(n[o],i):Xe(i)?n[o]=St({},i):_e(i)?n[o]=i.slice():(!t||!pe(i))&&(n[o]=i)};for(let i=0,s=arguments.length;i<s;i++)arguments[i]&&Ie(arguments[i],r);return n}const Zi=(e,t,n,{allOwnKeys:r}={})=>(Ie(t,(i,s)=>{n&&M(i)?Object.defineProperty(e,s,{value:Zn(i,n),writable:!0,enumerable:!0,configurable:!0}):Object.defineProperty(e,s,{value:i,writable:!0,enumerable:!0,configurable:!0})},{allOwnKeys:r}),e),Qi=e=>(e.charCodeAt(0)===65279&&(e=e.slice(1)),e),es=(e,t,n,r)=>{e.prototype=Object.create(t.prototype,r),Object.defineProperty(e.prototype,"constructor",{value:e,writable:!0,enumerable:!1,configurable:!0}),Object.defineProperty(e,"super",{value:t.prototype}),n&&Object.assign(e.prototype,n)},ts=(e,t,n,r)=>{let i,s,o;const a={};if(t=t||{},e==null)return t;do{for(i=Object.getOwnPropertyNames(e),s=i.length;s-- >0;)o=i[s],(!r||r(o,e,t))&&!a[o]&&(t[o]=e[o],a[o]=!0);e=n!==!1&&qt(e)}while(e&&(!n||n(e,t))&&e!==Object.prototype);return t},ns=(e,t,n)=>{e=String(e),(n===void 0||n>e.length)&&(n=e.length),n-=t.length;const r=e.indexOf(t,n);return r!==-1&&r===n},rs=e=>{if(!e)return null;if(_e(e))return e;let t=e.length;if(!tr(t))return null;const n=new Array(t);for(;t-- >0;)n[t]=e[t];return n},is=(e=>t=>e&&t instanceof e)(typeof Uint8Array<"u"&&qt(Uint8Array)),ss=(e,t)=>{const r=(e&&e[rt]).call(e);let i;for(;(i=r.next())&&!i.done;){const s=i.value;t.call(e,s[0],s[1])}},os=(e,t)=>{let n;const r=[];for(;(n=e.exec(t))!==null;)r.push(n);return r},as=B("HTMLFormElement"),cs=e=>e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g,function(n,r,i){return r.toUpperCase()+i}),En=(({hasOwnProperty:e})=>(t,n)=>e.call(t,n))(Object.prototype),us=B("RegExp"),ir=(e,t)=>{const n=Object.getOwnPropertyDescriptors(e),r={};Ie(n,(i,s)=>{let o;(o=t(i,s,e))!==!1&&(r[s]=o||i)}),Object.defineProperties(e,r)},ls=e=>{ir(e,(t,n)=>{if(M(e)&&["arguments","caller","callee"].indexOf(n)!==-1)return!1;const r=e[n];if(M(r)){if(t.enumerable=!1,"writable"in t){t.writable=!1;return}t.set||(t.set=()=>{throw Error("Can not rewrite read-only method '"+n+"'")})}})},fs=(e,t)=>{const n={},r=i=>{i.forEach(s=>{n[s]=!0})};return _e(e)?r(e):r(String(e).split(t)),n},ds=()=>{},ps=(e,t)=>e!=null&&Number.isFinite(e=+e)?e:t;function hs(e){return!!(e&&M(e.append)&&e[Qn]==="FormData"&&e[rt])}const _s=e=>{const t=new Array(10),n=(r,i)=>{if(Le(r)){if(t.indexOf(r)>=0)return;if(Fe(r))return r;if(!("toJSON"in r)){t[i]=r;const s=_e(r)?[]:{};return Ie(r,(o,a)=>{const c=n(o,i+1);!pe(c)&&(s[a]=c)}),t[i]=void 0,s}}return r};return n(e,0)},ms=B("AsyncFunction"),gs=e=>e&&(Le(e)||M(e))&&M(e.then)&&M(e.catch),sr=((e,t)=>e?setImmediate:t?((n,r)=>(G.addEventListener("message",({source:i,data:s})=>{i===G&&s===n&&r.length&&r.shift()()},!1),i=>{r.push(i),G.postMessage(n,"*")}))(`axios@${Math.random()}`,[]):n=>setTimeout(n))(typeof setImmediate=="function",M(G.postMessage)),ys=typeof queueMicrotask<"u"?queueMicrotask.bind(G):typeof process<"u"&&process.nextTick||sr,bs=e=>e!=null&&M(e[rt]),f={isArray:_e,isArrayBuffer:er,isBuffer:Fe,isFormData:Ki,isArrayBufferView:Di,isString:ji,isNumber:tr,isBoolean:Bi,isObject:Le,isPlainObject:Xe,isEmptyObject:$i,isReadableStream:Ji,isRequest:Vi,isResponse:Xi,isHeaders:Gi,isUndefined:pe,isDate:Ui,isFile:ki,isBlob:qi,isRegExp:us,isFunction:M,isStream:zi,isURLSearchParams:Wi,isTypedArray:is,isFileList:Hi,forEach:Ie,merge:St,extend:Zi,trim:Yi,stripBOM:Qi,inherits:es,toFlatObject:ts,kindOf:it,kindOfTest:B,endsWith:ns,toArray:rs,forEachEntry:ss,matchAll:os,isHTMLForm:as,hasOwnProperty:En,hasOwnProp:En,reduceDescriptors:ir,freezeMethods:ls,toObjectSet:fs,toCamelCase:cs,noop:ds,toFiniteNumber:ps,findKey:nr,global:G,isContextDefined:rr,isSpecCompliantForm:hs,toJSONObject:_s,isAsyncFn:ms,isThenable:gs,setImmediate:sr,asap:ys,isIterable:bs};let g=class or extends Error{static from(t,n,r,i,s,o){const a=new or(t.message,n||t.code,r,i,s);return a.cause=t,a.name=t.name,o&&Object.assign(a,o),a}constructor(t,n,r,i,s){super(t),this.name="AxiosError",this.isAxiosError=!0,n&&(this.code=n),r&&(this.config=r),i&&(this.request=i),s&&(this.response=s,this.status=s.status)}toJSON(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:f.toJSONObject(this.config),code:this.code,status:this.status}}};g.ERR_BAD_OPTION_VALUE="ERR_BAD_OPTION_VALUE";g.ERR_BAD_OPTION="ERR_BAD_OPTION";g.ECONNABORTED="ECONNABORTED";g.ETIMEDOUT="ETIMEDOUT";g.ERR_NETWORK="ERR_NETWORK";g.ERR_FR_TOO_MANY_REDIRECTS="ERR_FR_TOO_MANY_REDIRECTS";g.ERR_DEPRECATED="ERR_DEPRECATED";g.ERR_BAD_RESPONSE="ERR_BAD_RESPONSE";g.ERR_BAD_REQUEST="ERR_BAD_REQUEST";g.ERR_CANCELED="ERR_CANCELED";g.ERR_NOT_SUPPORT="ERR_NOT_SUPPORT";g.ERR_INVALID_URL="ERR_INVALID_URL";const ws=null;function At(e){return f.isPlainObject(e)||f.isArray(e)}function ar(e){return f.endsWith(e,"[]")?e.slice(0,-2):e}function Sn(e,t,n){return e?e.concat(t).map(function(i,s){return i=ar(i),!n&&s?"["+i+"]":i}).join(n?".":""):t}function xs(e){return f.isArray(e)&&!e.some(At)}const Es=f.toFlatObject(f,{},null,function(t){return/^is[A-Z]/.test(t)});function ot(e,t,n){if(!f.isObject(e))throw new TypeError("target must be an object");t=t||new FormData,n=f.toFlatObject(n,{metaTokens:!0,dots:!1,indexes:!1},!1,function(_,d){return!f.isUndefined(d[_])});const r=n.metaTokens,i=n.visitor||u,s=n.dots,o=n.indexes,c=(n.Blob||typeof Blob<"u"&&Blob)&&f.isSpecCompliantForm(t);if(!f.isFunction(i))throw new TypeError("visitor must be a function");function l(p){if(p===null)return"";if(f.isDate(p))return p.toISOString();if(f.isBoolean(p))return p.toString();if(!c&&f.isBlob(p))throw new g("Blob is not supported. Use a Buffer instead.");return f.isArrayBuffer(p)||f.isTypedArray(p)?c&&typeof Blob=="function"?new Blob([p]):Buffer.from(p):p}function u(p,_,d){let m=p;if(p&&!d&&typeof p=="object"){if(f.endsWith(_,"{}"))_=r?_:_.slice(0,-2),p=JSON.stringify(p);else if(f.isArray(p)&&xs(p)||(f.isFileList(p)||f.endsWith(_,"[]"))&&(m=f.toArray(p)))return _=ar(_),m.forEach(function(b,E){!(f.isUndefined(b)||b===null)&&t.append(o===!0?Sn([_],E,s):o===null?_:_+"[]",l(b))}),!1}return At(p)?!0:(t.append(Sn(d,_,s),l(p)),!1)}const h=[],y=Object.assign(Es,{defaultVisitor:u,convertValue:l,isVisitable:At});function x(p,_){if(!f.isUndefined(p)){if(h.indexOf(p)!==-1)throw Error("Circular reference detected in "+_.join("."));h.push(p),f.forEach(p,function(m,w){(!(f.isUndefined(m)||m===null)&&i.call(t,m,f.isString(w)?w.trim():w,_,y))===!0&&x(m,_?_.concat(w):[w])}),h.pop()}}if(!f.isObject(e))throw new TypeError("data must be an object");return x(e),t}function An(e){const t={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g,function(r){return t[r]})}function Ht(e,t){this._pairs=[],e&&ot(e,this,t)}const cr=Ht.prototype;cr.append=function(t,n){this._pairs.push([t,n])};cr.toString=function(t){const n=t?function(r){return t.call(this,r,An)}:An;return this._pairs.map(function(i){return n(i[0])+"="+n(i[1])},"").join("&")};function Ss(e){return encodeURIComponent(e).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+")}function ur(e,t,n){if(!t)return e;const r=n&&n.encode||Ss,i=f.isFunction(n)?{serialize:n}:n,s=i&&i.serialize;let o;if(s?o=s(t,i):o=f.isURLSearchParams(t)?t.toString():new Ht(t,i).toString(r),o){const a=e.indexOf("#");a!==-1&&(e=e.slice(0,a)),e+=(e.indexOf("?")===-1?"?":"&")+o}return e}class On{constructor(){this.handlers=[]}use(t,n,r){return this.handlers.push({fulfilled:t,rejected:n,synchronous:r?r.synchronous:!1,runWhen:r?r.runWhen:null}),this.handlers.length-1}eject(t){this.handlers[t]&&(this.handlers[t]=null)}clear(){this.handlers&&(this.handlers=[])}forEach(t){f.forEach(this.handlers,function(r){r!==null&&t(r)})}}const zt={silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1,legacyInterceptorReqResOrdering:!0},As=typeof URLSearchParams<"u"?URLSearchParams:Ht,Os=typeof FormData<"u"?FormData:null,Rs=typeof Blob<"u"?Blob:null,vs={isBrowser:!0,classes:{URLSearchParams:As,FormData:Os,Blob:Rs},protocols:["http","https","file","blob","url","data"]},Kt=typeof window<"u"&&typeof document<"u",Ot=typeof navigator=="object"&&navigator||void 0,Ts=Kt&&(!Ot||["ReactNative","NativeScript","NS"].indexOf(Ot.product)<0),Cs=typeof WorkerGlobalScope<"u"&&self instanceof WorkerGlobalScope&&typeof self.importScripts=="function",Ps=Kt&&window.location.href||"http://localhost",Ns=Object.freeze(Object.defineProperty({__proto__:null,hasBrowserEnv:Kt,hasStandardBrowserEnv:Ts,hasStandardBrowserWebWorkerEnv:Cs,navigator:Ot,origin:Ps},Symbol.toStringTag,{value:"Module"})),T={...Ns,...vs};function Ms(e,t){return ot(e,new T.classes.URLSearchParams,{visitor:function(n,r,i,s){return T.isNode&&f.isBuffer(n)?(this.append(r,n.toString("base64")),!1):s.defaultVisitor.apply(this,arguments)},...t})}function Fs(e){return f.matchAll(/\w+|\[(\w*)]/g,e).map(t=>t[0]==="[]"?"":t[1]||t[0])}function Ls(e){const t={},n=Object.keys(e);let r;const i=n.length;let s;for(r=0;r<i;r++)s=n[r],t[s]=e[s];return t}function lr(e){function t(n,r,i,s){let o=n[s++];if(o==="__proto__")return!0;const a=Number.isFinite(+o),c=s>=n.length;return o=!o&&f.isArray(i)?i.length:o,c?(f.hasOwnProp(i,o)?i[o]=[i[o],r]:i[o]=r,!a):((!i[o]||!f.isObject(i[o]))&&(i[o]=[]),t(n,r,i[o],s)&&f.isArray(i[o])&&(i[o]=Ls(i[o])),!a)}if(f.isFormData(e)&&f.isFunction(e.entries)){const n={};return f.forEachEntry(e,(r,i)=>{t(Fs(r),i,n,0)}),n}return null}function Is(e,t,n){if(f.isString(e))try{return(t||JSON.parse)(e),f.trim(e)}catch(r){if(r.name!=="SyntaxError")throw r}return(n||JSON.stringify)(e)}const De={transitional:zt,adapter:["xhr","http","fetch"],transformRequest:[function(t,n){const r=n.getContentType()||"",i=r.indexOf("application/json")>-1,s=f.isObject(t);if(s&&f.isHTMLForm(t)&&(t=new FormData(t)),f.isFormData(t))return i?JSON.stringify(lr(t)):t;if(f.isArrayBuffer(t)||f.isBuffer(t)||f.isStream(t)||f.isFile(t)||f.isBlob(t)||f.isReadableStream(t))return t;if(f.isArrayBufferView(t))return t.buffer;if(f.isURLSearchParams(t))return n.setContentType("application/x-www-form-urlencoded;charset=utf-8",!1),t.toString();let a;if(s){if(r.indexOf("application/x-www-form-urlencoded")>-1)return Ms(t,this.formSerializer).toString();if((a=f.isFileList(t))||r.indexOf("multipart/form-data")>-1){const c=this.env&&this.env.FormData;return ot(a?{"files[]":t}:t,c&&new c,this.formSerializer)}}return s||i?(n.setContentType("application/json",!1),Is(t)):t}],transformResponse:[function(t){const n=this.transitional||De.transitional,r=n&&n.forcedJSONParsing,i=this.responseType==="json";if(f.isResponse(t)||f.isReadableStream(t))return t;if(t&&f.isString(t)&&(r&&!this.responseType||i)){const o=!(n&&n.silentJSONParsing)&&i;try{return JSON.parse(t,this.parseReviver)}catch(a){if(o)throw a.name==="SyntaxError"?g.from(a,g.ERR_BAD_RESPONSE,this,null,this.response):a}}return t}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,env:{FormData:T.classes.FormData,Blob:T.classes.Blob},validateStatus:function(t){return t>=200&&t<300},headers:{common:{Accept:"application/json, text/plain, */*","Content-Type":void 0}}};f.forEach(["delete","get","head","post","put","patch"],e=>{De.headers[e]={}});const Ds=f.toObjectSet(["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"]),js=e=>{const t={};let n,r,i;return e&&e.split(`
`).forEach(function(o){i=o.indexOf(":"),n=o.substring(0,i).trim().toLowerCase(),r=o.substring(i+1).trim(),!(!n||t[n]&&Ds[n])&&(n==="set-cookie"?t[n]?t[n].push(r):t[n]=[r]:t[n]=t[n]?t[n]+", "+r:r)}),t},Rn=Symbol("internals");function Se(e){return e&&String(e).trim().toLowerCase()}function Ge(e){return e===!1||e==null?e:f.isArray(e)?e.map(Ge):String(e)}function Bs(e){const t=Object.create(null),n=/([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;let r;for(;r=n.exec(e);)t[r[1]]=r[2];return t}const $s=e=>/^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());function mt(e,t,n,r,i){if(f.isFunction(r))return r.call(this,t,n);if(i&&(t=n),!!f.isString(t)){if(f.isString(r))return t.indexOf(r)!==-1;if(f.isRegExp(r))return r.test(t)}}function Us(e){return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g,(t,n,r)=>n.toUpperCase()+r)}function ks(e,t){const n=f.toCamelCase(" "+t);["get","set","has"].forEach(r=>{Object.defineProperty(e,r+n,{value:function(i,s,o){return this[r].call(this,t,i,s,o)},configurable:!0})})}let F=class{constructor(t){t&&this.set(t)}set(t,n,r){const i=this;function s(a,c,l){const u=Se(c);if(!u)throw new Error("header name must be a non-empty string");const h=f.findKey(i,u);(!h||i[h]===void 0||l===!0||l===void 0&&i[h]!==!1)&&(i[h||c]=Ge(a))}const o=(a,c)=>f.forEach(a,(l,u)=>s(l,u,c));if(f.isPlainObject(t)||t instanceof this.constructor)o(t,n);else if(f.isString(t)&&(t=t.trim())&&!$s(t))o(js(t),n);else if(f.isObject(t)&&f.isIterable(t)){let a={},c,l;for(const u of t){if(!f.isArray(u))throw TypeError("Object iterator must return a key-value pair");a[l=u[0]]=(c=a[l])?f.isArray(c)?[...c,u[1]]:[c,u[1]]:u[1]}o(a,n)}else t!=null&&s(n,t,r);return this}get(t,n){if(t=Se(t),t){const r=f.findKey(this,t);if(r){const i=this[r];if(!n)return i;if(n===!0)return Bs(i);if(f.isFunction(n))return n.call(this,i,r);if(f.isRegExp(n))return n.exec(i);throw new TypeError("parser must be boolean|regexp|function")}}}has(t,n){if(t=Se(t),t){const r=f.findKey(this,t);return!!(r&&this[r]!==void 0&&(!n||mt(this,this[r],r,n)))}return!1}delete(t,n){const r=this;let i=!1;function s(o){if(o=Se(o),o){const a=f.findKey(r,o);a&&(!n||mt(r,r[a],a,n))&&(delete r[a],i=!0)}}return f.isArray(t)?t.forEach(s):s(t),i}clear(t){const n=Object.keys(this);let r=n.length,i=!1;for(;r--;){const s=n[r];(!t||mt(this,this[s],s,t,!0))&&(delete this[s],i=!0)}return i}normalize(t){const n=this,r={};return f.forEach(this,(i,s)=>{const o=f.findKey(r,s);if(o){n[o]=Ge(i),delete n[s];return}const a=t?Us(s):String(s).trim();a!==s&&delete n[s],n[a]=Ge(i),r[a]=!0}),this}concat(...t){return this.constructor.concat(this,...t)}toJSON(t){const n=Object.create(null);return f.forEach(this,(r,i)=>{r!=null&&r!==!1&&(n[i]=t&&f.isArray(r)?r.join(", "):r)}),n}[Symbol.iterator](){return Object.entries(this.toJSON())[Symbol.iterator]()}toString(){return Object.entries(this.toJSON()).map(([t,n])=>t+": "+n).join(`
`)}getSetCookie(){return this.get("set-cookie")||[]}get[Symbol.toStringTag](){return"AxiosHeaders"}static from(t){return t instanceof this?t:new this(t)}static concat(t,...n){const r=new this(t);return n.forEach(i=>r.set(i)),r}static accessor(t){const r=(this[Rn]=this[Rn]={accessors:{}}).accessors,i=this.prototype;function s(o){const a=Se(o);r[a]||(ks(i,o),r[a]=!0)}return f.isArray(t)?t.forEach(s):s(t),this}};F.accessor(["Content-Type","Content-Length","Accept","Accept-Encoding","User-Agent","Authorization"]);f.reduceDescriptors(F.prototype,({value:e},t)=>{let n=t[0].toUpperCase()+t.slice(1);return{get:()=>e,set(r){this[n]=r}}});f.freezeMethods(F);function gt(e,t){const n=this||De,r=t||n,i=F.from(r.headers);let s=r.data;return f.forEach(e,function(a){s=a.call(n,s,i.normalize(),t?t.status:void 0)}),i.normalize(),s}function fr(e){return!!(e&&e.__CANCEL__)}let je=class extends g{constructor(t,n,r){super(t??"canceled",g.ERR_CANCELED,n,r),this.name="CanceledError",this.__CANCEL__=!0}};function dr(e,t,n){const r=n.config.validateStatus;!n.status||!r||r(n.status)?e(n):t(new g("Request failed with status code "+n.status,[g.ERR_BAD_REQUEST,g.ERR_BAD_RESPONSE][Math.floor(n.status/100)-4],n.config,n.request,n))}function qs(e){const t=/^([-+\w]{1,25})(:?\/\/|:)/.exec(e);return t&&t[1]||""}function Hs(e,t){e=e||10;const n=new Array(e),r=new Array(e);let i=0,s=0,o;return t=t!==void 0?t:1e3,function(c){const l=Date.now(),u=r[s];o||(o=l),n[i]=c,r[i]=l;let h=s,y=0;for(;h!==i;)y+=n[h++],h=h%e;if(i=(i+1)%e,i===s&&(s=(s+1)%e),l-o<t)return;const x=u&&l-u;return x?Math.round(y*1e3/x):void 0}}function zs(e,t){let n=0,r=1e3/t,i,s;const o=(l,u=Date.now())=>{n=u,i=null,s&&(clearTimeout(s),s=null),e(...l)};return[(...l)=>{const u=Date.now(),h=u-n;h>=r?o(l,u):(i=l,s||(s=setTimeout(()=>{s=null,o(i)},r-h)))},()=>i&&o(i)]}const Qe=(e,t,n=3)=>{let r=0;const i=Hs(50,250);return zs(s=>{const o=s.loaded,a=s.lengthComputable?s.total:void 0,c=o-r,l=i(c),u=o<=a;r=o;const h={loaded:o,total:a,progress:a?o/a:void 0,bytes:c,rate:l||void 0,estimated:l&&a&&u?(a-o)/l:void 0,event:s,lengthComputable:a!=null,[t?"download":"upload"]:!0};e(h)},n)},vn=(e,t)=>{const n=e!=null;return[r=>t[0]({lengthComputable:n,total:e,loaded:r}),t[1]]},Tn=e=>(...t)=>f.asap(()=>e(...t)),Ks=T.hasStandardBrowserEnv?((e,t)=>n=>(n=new URL(n,T.origin),e.protocol===n.protocol&&e.host===n.host&&(t||e.port===n.port)))(new URL(T.origin),T.navigator&&/(msie|trident)/i.test(T.navigator.userAgent)):()=>!0,Ws=T.hasStandardBrowserEnv?{write(e,t,n,r,i,s,o){if(typeof document>"u")return;const a=[`${e}=${encodeURIComponent(t)}`];f.isNumber(n)&&a.push(`expires=${new Date(n).toUTCString()}`),f.isString(r)&&a.push(`path=${r}`),f.isString(i)&&a.push(`domain=${i}`),s===!0&&a.push("secure"),f.isString(o)&&a.push(`SameSite=${o}`),document.cookie=a.join("; ")},read(e){if(typeof document>"u")return null;const t=document.cookie.match(new RegExp("(?:^|; )"+e+"=([^;]*)"));return t?decodeURIComponent(t[1]):null},remove(e){this.write(e,"",Date.now()-864e5,"/")}}:{write(){},read(){return null},remove(){}};function Js(e){return typeof e!="string"?!1:/^([a-z][a-z\d+\-.]*:)?\/\//i.test(e)}function Vs(e,t){return t?e.replace(/\/?\/$/,"")+"/"+t.replace(/^\/+/,""):e}function pr(e,t,n){let r=!Js(t);return e&&(r||n==!1)?Vs(e,t):t}const Cn=e=>e instanceof F?{...e}:e;function re(e,t){t=t||{};const n={};function r(l,u,h,y){return f.isPlainObject(l)&&f.isPlainObject(u)?f.merge.call({caseless:y},l,u):f.isPlainObject(u)?f.merge({},u):f.isArray(u)?u.slice():u}function i(l,u,h,y){if(f.isUndefined(u)){if(!f.isUndefined(l))return r(void 0,l,h,y)}else return r(l,u,h,y)}function s(l,u){if(!f.isUndefined(u))return r(void 0,u)}function o(l,u){if(f.isUndefined(u)){if(!f.isUndefined(l))return r(void 0,l)}else return r(void 0,u)}function a(l,u,h){if(h in t)return r(l,u);if(h in e)return r(void 0,l)}const c={url:s,method:s,data:s,baseURL:o,transformRequest:o,transformResponse:o,paramsSerializer:o,timeout:o,timeoutMessage:o,withCredentials:o,withXSRFToken:o,adapter:o,responseType:o,xsrfCookieName:o,xsrfHeaderName:o,onUploadProgress:o,onDownloadProgress:o,decompress:o,maxContentLength:o,maxBodyLength:o,beforeRedirect:o,transport:o,httpAgent:o,httpsAgent:o,cancelToken:o,socketPath:o,responseEncoding:o,validateStatus:a,headers:(l,u,h)=>i(Cn(l),Cn(u),h,!0)};return f.forEach(Object.keys({...e,...t}),function(u){if(u==="__proto__"||u==="constructor"||u==="prototype")return;const h=f.hasOwnProp(c,u)?c[u]:i,y=h(e[u],t[u],u);f.isUndefined(y)&&h!==a||(n[u]=y)}),n}const hr=e=>{const t=re({},e);let{data:n,withXSRFToken:r,xsrfHeaderName:i,xsrfCookieName:s,headers:o,auth:a}=t;if(t.headers=o=F.from(o),t.url=ur(pr(t.baseURL,t.url,t.allowAbsoluteUrls),e.params,e.paramsSerializer),a&&o.set("Authorization","Basic "+btoa((a.username||"")+":"+(a.password?unescape(encodeURIComponent(a.password)):""))),f.isFormData(n)){if(T.hasStandardBrowserEnv||T.hasStandardBrowserWebWorkerEnv)o.setContentType(void 0);else if(f.isFunction(n.getHeaders)){const c=n.getHeaders(),l=["content-type","content-length"];Object.entries(c).forEach(([u,h])=>{l.includes(u.toLowerCase())&&o.set(u,h)})}}if(T.hasStandardBrowserEnv&&(r&&f.isFunction(r)&&(r=r(t)),r||r!==!1&&Ks(t.url))){const c=i&&s&&Ws.read(s);c&&o.set(i,c)}return t},Xs=typeof XMLHttpRequest<"u",Gs=Xs&&function(e){return new Promise(function(n,r){const i=hr(e);let s=i.data;const o=F.from(i.headers).normalize();let{responseType:a,onUploadProgress:c,onDownloadProgress:l}=i,u,h,y,x,p;function _(){x&&x(),p&&p(),i.cancelToken&&i.cancelToken.unsubscribe(u),i.signal&&i.signal.removeEventListener("abort",u)}let d=new XMLHttpRequest;d.open(i.method.toUpperCase(),i.url,!0),d.timeout=i.timeout;function m(){if(!d)return;const b=F.from("getAllResponseHeaders"in d&&d.getAllResponseHeaders()),v={data:!a||a==="text"||a==="json"?d.responseText:d.response,status:d.status,statusText:d.statusText,headers:b,config:e,request:d};dr(function(C){n(C),_()},function(C){r(C),_()},v),d=null}"onloadend"in d?d.onloadend=m:d.onreadystatechange=function(){!d||d.readyState!==4||d.status===0&&!(d.responseURL&&d.responseURL.indexOf("file:")===0)||setTimeout(m)},d.onabort=function(){d&&(r(new g("Request aborted",g.ECONNABORTED,e,d)),d=null)},d.onerror=function(E){const v=E&&E.message?E.message:"Network Error",L=new g(v,g.ERR_NETWORK,e,d);L.event=E||null,r(L),d=null},d.ontimeout=function(){let E=i.timeout?"timeout of "+i.timeout+"ms exceeded":"timeout exceeded";const v=i.transitional||zt;i.timeoutErrorMessage&&(E=i.timeoutErrorMessage),r(new g(E,v.clarifyTimeoutError?g.ETIMEDOUT:g.ECONNABORTED,e,d)),d=null},s===void 0&&o.setContentType(null),"setRequestHeader"in d&&f.forEach(o.toJSON(),function(E,v){d.setRequestHeader(v,E)}),f.isUndefined(i.withCredentials)||(d.withCredentials=!!i.withCredentials),a&&a!=="json"&&(d.responseType=i.responseType),l&&([y,p]=Qe(l,!0),d.addEventListener("progress",y)),c&&d.upload&&([h,x]=Qe(c),d.upload.addEventListener("progress",h),d.upload.addEventListener("loadend",x)),(i.cancelToken||i.signal)&&(u=b=>{d&&(r(!b||b.type?new je(null,e,d):b),d.abort(),d=null)},i.cancelToken&&i.cancelToken.subscribe(u),i.signal&&(i.signal.aborted?u():i.signal.addEventListener("abort",u)));const w=qs(i.url);if(w&&T.protocols.indexOf(w)===-1){r(new g("Unsupported protocol "+w+":",g.ERR_BAD_REQUEST,e));return}d.send(s||null)})},Ys=(e,t)=>{const{length:n}=e=e?e.filter(Boolean):[];if(t||n){let r=new AbortController,i;const s=function(l){if(!i){i=!0,a();const u=l instanceof Error?l:this.reason;r.abort(u instanceof g?u:new je(u instanceof Error?u.message:u))}};let o=t&&setTimeout(()=>{o=null,s(new g(`timeout of ${t}ms exceeded`,g.ETIMEDOUT))},t);const a=()=>{e&&(o&&clearTimeout(o),o=null,e.forEach(l=>{l.unsubscribe?l.unsubscribe(s):l.removeEventListener("abort",s)}),e=null)};e.forEach(l=>l.addEventListener("abort",s));const{signal:c}=r;return c.unsubscribe=()=>f.asap(a),c}},Zs=function*(e,t){let n=e.byteLength;if(n<t){yield e;return}let r=0,i;for(;r<n;)i=r+t,yield e.slice(r,i),r=i},Qs=async function*(e,t){for await(const n of eo(e))yield*Zs(n,t)},eo=async function*(e){if(e[Symbol.asyncIterator]){yield*e;return}const t=e.getReader();try{for(;;){const{done:n,value:r}=await t.read();if(n)break;yield r}}finally{await t.cancel()}},Pn=(e,t,n,r)=>{const i=Qs(e,t);let s=0,o,a=c=>{o||(o=!0,r&&r(c))};return new ReadableStream({async pull(c){try{const{done:l,value:u}=await i.next();if(l){a(),c.close();return}let h=u.byteLength;if(n){let y=s+=h;n(y)}c.enqueue(new Uint8Array(u))}catch(l){throw a(l),l}},cancel(c){return a(c),i.return()}},{highWaterMark:2})},Nn=64*1024,{isFunction:qe}=f,to=(({Request:e,Response:t})=>({Request:e,Response:t}))(f.global),{ReadableStream:Mn,TextEncoder:Fn}=f.global,Ln=(e,...t)=>{try{return!!e(...t)}catch{return!1}},no=e=>{e=f.merge.call({skipUndefined:!0},to,e);const{fetch:t,Request:n,Response:r}=e,i=t?qe(t):typeof fetch=="function",s=qe(n),o=qe(r);if(!i)return!1;const a=i&&qe(Mn),c=i&&(typeof Fn=="function"?(p=>_=>p.encode(_))(new Fn):async p=>new Uint8Array(await new n(p).arrayBuffer())),l=s&&a&&Ln(()=>{let p=!1;const _=new n(T.origin,{body:new Mn,method:"POST",get duplex(){return p=!0,"half"}}).headers.has("Content-Type");return p&&!_}),u=o&&a&&Ln(()=>f.isReadableStream(new r("").body)),h={stream:u&&(p=>p.body)};i&&["text","arrayBuffer","blob","formData","stream"].forEach(p=>{!h[p]&&(h[p]=(_,d)=>{let m=_&&_[p];if(m)return m.call(_);throw new g(`Response type '${p}' is not supported`,g.ERR_NOT_SUPPORT,d)})});const y=async p=>{if(p==null)return 0;if(f.isBlob(p))return p.size;if(f.isSpecCompliantForm(p))return(await new n(T.origin,{method:"POST",body:p}).arrayBuffer()).byteLength;if(f.isArrayBufferView(p)||f.isArrayBuffer(p))return p.byteLength;if(f.isURLSearchParams(p)&&(p=p+""),f.isString(p))return(await c(p)).byteLength},x=async(p,_)=>{const d=f.toFiniteNumber(p.getContentLength());return d??y(_)};return async p=>{let{url:_,method:d,data:m,signal:w,cancelToken:b,timeout:E,onDownloadProgress:v,onUploadProgress:L,responseType:C,headers:xe,withCredentials:ue="same-origin",fetchOptions:$e}=hr(p),gn=t||fetch;C=C?(C+"").toLowerCase():"text";let Ue=Ys([w,b&&b.toAbortSignal()],E),Ee=null;const V=Ue&&Ue.unsubscribe&&(()=>{Ue.unsubscribe()});let yn;try{if(L&&l&&d!=="get"&&d!=="head"&&(yn=await x(xe,m))!==0){let H=new n(_,{method:"POST",body:m,duplex:"half"}),le;if(f.isFormData(m)&&(le=H.headers.get("content-type"))&&xe.setContentType(le),H.body){const[_t,ke]=vn(yn,Qe(Tn(L)));m=Pn(H.body,Nn,_t,ke)}}f.isString(ue)||(ue=ue?"include":"omit");const N=s&&"credentials"in n.prototype,bn={...$e,signal:Ue,method:d.toUpperCase(),headers:xe.normalize().toJSON(),body:m,duplex:"half",credentials:N?ue:void 0};Ee=s&&new n(_,bn);let q=await(s?gn(Ee,$e):gn(_,bn));const wn=u&&(C==="stream"||C==="response");if(u&&(v||wn&&V)){const H={};["status","statusText","headers"].forEach(xn=>{H[xn]=q[xn]});const le=f.toFiniteNumber(q.headers.get("content-length")),[_t,ke]=v&&vn(le,Qe(Tn(v),!0))||[];q=new r(Pn(q.body,Nn,_t,()=>{ke&&ke(),V&&V()}),H)}C=C||"text";let Li=await h[f.findKey(h,C)||"text"](q,p);return!wn&&V&&V(),await new Promise((H,le)=>{dr(H,le,{data:Li,headers:F.from(q.headers),status:q.status,statusText:q.statusText,config:p,request:Ee})})}catch(N){throw V&&V(),N&&N.name==="TypeError"&&/Load failed|fetch/i.test(N.message)?Object.assign(new g("Network Error",g.ERR_NETWORK,p,Ee,N&&N.response),{cause:N.cause||N}):g.from(N,N&&N.code,p,Ee,N&&N.response)}}},ro=new Map,_r=e=>{let t=e&&e.env||{};const{fetch:n,Request:r,Response:i}=t,s=[r,i,n];let o=s.length,a=o,c,l,u=ro;for(;a--;)c=s[a],l=u.get(c),l===void 0&&u.set(c,l=a?new Map:no(t)),u=l;return l};_r();const Wt={http:ws,xhr:Gs,fetch:{get:_r}};f.forEach(Wt,(e,t)=>{if(e){try{Object.defineProperty(e,"name",{value:t})}catch{}Object.defineProperty(e,"adapterName",{value:t})}});const In=e=>`- ${e}`,io=e=>f.isFunction(e)||e===null||e===!1;function so(e,t){e=f.isArray(e)?e:[e];const{length:n}=e;let r,i;const s={};for(let o=0;o<n;o++){r=e[o];let a;if(i=r,!io(r)&&(i=Wt[(a=String(r)).toLowerCase()],i===void 0))throw new g(`Unknown adapter '${a}'`);if(i&&(f.isFunction(i)||(i=i.get(t))))break;s[a||"#"+o]=i}if(!i){const o=Object.entries(s).map(([c,l])=>`adapter ${c} `+(l===!1?"is not supported by the environment":"is not available in the build"));let a=n?o.length>1?`since :
`+o.map(In).join(`
`):" "+In(o[0]):"as no adapter specified";throw new g("There is no suitable adapter to dispatch the request "+a,"ERR_NOT_SUPPORT")}return i}const mr={getAdapter:so,adapters:Wt};function yt(e){if(e.cancelToken&&e.cancelToken.throwIfRequested(),e.signal&&e.signal.aborted)throw new je(null,e)}function Dn(e){return yt(e),e.headers=F.from(e.headers),e.data=gt.call(e,e.transformRequest),["post","put","patch"].indexOf(e.method)!==-1&&e.headers.setContentType("application/x-www-form-urlencoded",!1),mr.getAdapter(e.adapter||De.adapter,e)(e).then(function(r){return yt(e),r.data=gt.call(e,e.transformResponse,r),r.headers=F.from(r.headers),r},function(r){return fr(r)||(yt(e),r&&r.response&&(r.response.data=gt.call(e,e.transformResponse,r.response),r.response.headers=F.from(r.response.headers))),Promise.reject(r)})}const gr="1.13.5",at={};["object","boolean","number","function","string","symbol"].forEach((e,t)=>{at[e]=function(r){return typeof r===e||"a"+(t<1?"n ":" ")+e}});const jn={};at.transitional=function(t,n,r){function i(s,o){return"[Axios v"+gr+"] Transitional option '"+s+"'"+o+(r?". "+r:"")}return(s,o,a)=>{if(t===!1)throw new g(i(o," has been removed"+(n?" in "+n:"")),g.ERR_DEPRECATED);return n&&!jn[o]&&(jn[o]=!0,console.warn(i(o," has been deprecated since v"+n+" and will be removed in the near future"))),t?t(s,o,a):!0}};at.spelling=function(t){return(n,r)=>(console.warn(`${r} is likely a misspelling of ${t}`),!0)};function oo(e,t,n){if(typeof e!="object")throw new g("options must be an object",g.ERR_BAD_OPTION_VALUE);const r=Object.keys(e);let i=r.length;for(;i-- >0;){const s=r[i],o=t[s];if(o){const a=e[s],c=a===void 0||o(a,s,e);if(c!==!0)throw new g("option "+s+" must be "+c,g.ERR_BAD_OPTION_VALUE);continue}if(n!==!0)throw new g("Unknown option "+s,g.ERR_BAD_OPTION)}}const Ye={assertOptions:oo,validators:at},I=Ye.validators;let Z=class{constructor(t){this.defaults=t||{},this.interceptors={request:new On,response:new On}}async request(t,n){try{return await this._request(t,n)}catch(r){if(r instanceof Error){let i={};Error.captureStackTrace?Error.captureStackTrace(i):i=new Error;const s=i.stack?i.stack.replace(/^.+\n/,""):"";try{r.stack?s&&!String(r.stack).endsWith(s.replace(/^.+\n.+\n/,""))&&(r.stack+=`
`+s):r.stack=s}catch{}}throw r}}_request(t,n){typeof t=="string"?(n=n||{},n.url=t):n=t||{},n=re(this.defaults,n);const{transitional:r,paramsSerializer:i,headers:s}=n;r!==void 0&&Ye.assertOptions(r,{silentJSONParsing:I.transitional(I.boolean),forcedJSONParsing:I.transitional(I.boolean),clarifyTimeoutError:I.transitional(I.boolean),legacyInterceptorReqResOrdering:I.transitional(I.boolean)},!1),i!=null&&(f.isFunction(i)?n.paramsSerializer={serialize:i}:Ye.assertOptions(i,{encode:I.function,serialize:I.function},!0)),n.allowAbsoluteUrls!==void 0||(this.defaults.allowAbsoluteUrls!==void 0?n.allowAbsoluteUrls=this.defaults.allowAbsoluteUrls:n.allowAbsoluteUrls=!0),Ye.assertOptions(n,{baseUrl:I.spelling("baseURL"),withXsrfToken:I.spelling("withXSRFToken")},!0),n.method=(n.method||this.defaults.method||"get").toLowerCase();let o=s&&f.merge(s.common,s[n.method]);s&&f.forEach(["delete","get","head","post","put","patch","common"],p=>{delete s[p]}),n.headers=F.concat(o,s);const a=[];let c=!0;this.interceptors.request.forEach(function(_){if(typeof _.runWhen=="function"&&_.runWhen(n)===!1)return;c=c&&_.synchronous;const d=n.transitional||zt;d&&d.legacyInterceptorReqResOrdering?a.unshift(_.fulfilled,_.rejected):a.push(_.fulfilled,_.rejected)});const l=[];this.interceptors.response.forEach(function(_){l.push(_.fulfilled,_.rejected)});let u,h=0,y;if(!c){const p=[Dn.bind(this),void 0];for(p.unshift(...a),p.push(...l),y=p.length,u=Promise.resolve(n);h<y;)u=u.then(p[h++],p[h++]);return u}y=a.length;let x=n;for(;h<y;){const p=a[h++],_=a[h++];try{x=p(x)}catch(d){_.call(this,d);break}}try{u=Dn.call(this,x)}catch(p){return Promise.reject(p)}for(h=0,y=l.length;h<y;)u=u.then(l[h++],l[h++]);return u}getUri(t){t=re(this.defaults,t);const n=pr(t.baseURL,t.url,t.allowAbsoluteUrls);return ur(n,t.params,t.paramsSerializer)}};f.forEach(["delete","get","head","options"],function(t){Z.prototype[t]=function(n,r){return this.request(re(r||{},{method:t,url:n,data:(r||{}).data}))}});f.forEach(["post","put","patch"],function(t){function n(r){return function(s,o,a){return this.request(re(a||{},{method:t,headers:r?{"Content-Type":"multipart/form-data"}:{},url:s,data:o}))}}Z.prototype[t]=n(),Z.prototype[t+"Form"]=n(!0)});let ao=class yr{constructor(t){if(typeof t!="function")throw new TypeError("executor must be a function.");let n;this.promise=new Promise(function(s){n=s});const r=this;this.promise.then(i=>{if(!r._listeners)return;let s=r._listeners.length;for(;s-- >0;)r._listeners[s](i);r._listeners=null}),this.promise.then=i=>{let s;const o=new Promise(a=>{r.subscribe(a),s=a}).then(i);return o.cancel=function(){r.unsubscribe(s)},o},t(function(s,o,a){r.reason||(r.reason=new je(s,o,a),n(r.reason))})}throwIfRequested(){if(this.reason)throw this.reason}subscribe(t){if(this.reason){t(this.reason);return}this._listeners?this._listeners.push(t):this._listeners=[t]}unsubscribe(t){if(!this._listeners)return;const n=this._listeners.indexOf(t);n!==-1&&this._listeners.splice(n,1)}toAbortSignal(){const t=new AbortController,n=r=>{t.abort(r)};return this.subscribe(n),t.signal.unsubscribe=()=>this.unsubscribe(n),t.signal}static source(){let t;return{token:new yr(function(i){t=i}),cancel:t}}};function co(e){return function(n){return e.apply(null,n)}}function uo(e){return f.isObject(e)&&e.isAxiosError===!0}const Rt={Continue:100,SwitchingProtocols:101,Processing:102,EarlyHints:103,Ok:200,Created:201,Accepted:202,NonAuthoritativeInformation:203,NoContent:204,ResetContent:205,PartialContent:206,MultiStatus:207,AlreadyReported:208,ImUsed:226,MultipleChoices:300,MovedPermanently:301,Found:302,SeeOther:303,NotModified:304,UseProxy:305,Unused:306,TemporaryRedirect:307,PermanentRedirect:308,BadRequest:400,Unauthorized:401,PaymentRequired:402,Forbidden:403,NotFound:404,MethodNotAllowed:405,NotAcceptable:406,ProxyAuthenticationRequired:407,RequestTimeout:408,Conflict:409,Gone:410,LengthRequired:411,PreconditionFailed:412,PayloadTooLarge:413,UriTooLong:414,UnsupportedMediaType:415,RangeNotSatisfiable:416,ExpectationFailed:417,ImATeapot:418,MisdirectedRequest:421,UnprocessableEntity:422,Locked:423,FailedDependency:424,TooEarly:425,UpgradeRequired:426,PreconditionRequired:428,TooManyRequests:429,RequestHeaderFieldsTooLarge:431,UnavailableForLegalReasons:451,InternalServerError:500,NotImplemented:501,BadGateway:502,ServiceUnavailable:503,GatewayTimeout:504,HttpVersionNotSupported:505,VariantAlsoNegotiates:506,InsufficientStorage:507,LoopDetected:508,NotExtended:510,NetworkAuthenticationRequired:511,WebServerIsDown:521,ConnectionTimedOut:522,OriginIsUnreachable:523,TimeoutOccurred:524,SslHandshakeFailed:525,InvalidSslCertificate:526};Object.entries(Rt).forEach(([e,t])=>{Rt[t]=e});function br(e){const t=new Z(e),n=Zn(Z.prototype.request,t);return f.extend(n,Z.prototype,t,{allOwnKeys:!0}),f.extend(n,t,null,{allOwnKeys:!0}),n.create=function(i){return br(re(e,i))},n}const O=br(De);O.Axios=Z;O.CanceledError=je;O.CancelToken=ao;O.isCancel=fr;O.VERSION=gr;O.toFormData=ot;O.AxiosError=g;O.Cancel=O.CanceledError;O.all=function(t){return Promise.all(t)};O.spread=co;O.isAxiosError=uo;O.mergeConfig=re;O.AxiosHeaders=F;O.formToJSON=e=>lr(f.isHTMLForm(e)?new FormData(e):e);O.getAdapter=mr.getAdapter;O.HttpStatusCode=Rt;O.default=O;const{Axios:Pc,AxiosError:Nc,CanceledError:Mc,isCancel:Fc,CancelToken:Lc,VERSION:Ic,all:Dc,Cancel:jc,isAxiosError:Bc,spread:$c,toFormData:Uc,AxiosHeaders:kc,HttpStatusCode:qc,formToJSON:Hc,getAdapter:zc,mergeConfig:Kc}=O;window.axios=O;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";var vt=!1,Tt=!1,Q=[],Ct=-1,Jt=!1;function lo(e){ho(e)}function fo(){Jt=!0}function po(){Jt=!1,wr()}function ho(e){Q.includes(e)||Q.push(e),wr()}function _o(e){let t=Q.indexOf(e);t!==-1&&t>Ct&&Q.splice(t,1)}function wr(){if(!Tt&&!vt){if(Jt)return;vt=!0,queueMicrotask(mo)}}function mo(){vt=!1,Tt=!0;for(let e=0;e<Q.length;e++)Q[e](),Ct=e;Q.length=0,Ct=-1,Tt=!1}var me,ce,ge,xr,Pt=!0;function go(e){Pt=!1,e(),Pt=!0}function yo(e){me=e.reactive,ge=e.release,ce=t=>e.effect(t,{scheduler:n=>{Pt?lo(n):n()}}),xr=e.raw}function Bn(e){ce=e}function bo(e){let t=()=>{};return[r=>{let i=ce(r);return e._x_effects||(e._x_effects=new Set,e._x_runEffects=()=>{e._x_effects.forEach(s=>s())}),e._x_effects.add(i),t=()=>{i!==void 0&&(e._x_effects.delete(i),ge(i))},i},()=>{t()}]}function Er(e,t){let n=!0,r,i=ce(()=>{let s=e();if(JSON.stringify(s),!n&&(typeof s=="object"||s!==r)){let o=r;queueMicrotask(()=>{t(s,o)})}r=s,n=!1});return()=>ge(i)}async function wo(e){fo();try{await e(),await Promise.resolve()}finally{po()}}var Sr=[],Ar=[],Or=[];function xo(e){Or.push(e)}function Vt(e,t){typeof t=="function"?(e._x_cleanups||(e._x_cleanups=[]),e._x_cleanups.push(t)):(t=e,Ar.push(t))}function Rr(e){Sr.push(e)}function vr(e,t,n){e._x_attributeCleanups||(e._x_attributeCleanups={}),e._x_attributeCleanups[t]||(e._x_attributeCleanups[t]=[]),e._x_attributeCleanups[t].push(n)}function Tr(e,t){e._x_attributeCleanups&&Object.entries(e._x_attributeCleanups).forEach(([n,r])=>{(t===void 0||t.includes(n))&&(r.forEach(i=>i()),delete e._x_attributeCleanups[n])})}function Eo(e){for(e._x_effects?.forEach(_o);e._x_cleanups?.length;)e._x_cleanups.pop()()}var Xt=new MutationObserver(Qt),Gt=!1;function Yt(){Xt.observe(document,{subtree:!0,childList:!0,attributes:!0,attributeOldValue:!0}),Gt=!0}function Cr(){So(),Xt.disconnect(),Gt=!1}var Ae=[];function So(){let e=Xt.takeRecords();Ae.push(()=>e.length>0&&Qt(e));let t=Ae.length;queueMicrotask(()=>{if(Ae.length===t)for(;Ae.length>0;)Ae.shift()()})}function A(e){if(!Gt)return e();Cr();let t=e();return Yt(),t}var Zt=!1,et=[];function Ao(){Zt=!0}function Oo(){Zt=!1,Qt(et),et=[]}function Qt(e){if(Zt){et=et.concat(e);return}let t=[],n=new Set,r=new Map,i=new Map;for(let s=0;s<e.length;s++)if(!e[s].target._x_ignoreMutationObserver&&(e[s].type==="childList"&&(e[s].removedNodes.forEach(o=>{o.nodeType===1&&o._x_marker&&n.add(o)}),e[s].addedNodes.forEach(o=>{if(o.nodeType===1){if(n.has(o)){n.delete(o);return}o._x_marker||t.push(o)}})),e[s].type==="attributes")){let o=e[s].target,a=e[s].attributeName,c=e[s].oldValue,l=()=>{r.has(o)||r.set(o,[]),r.get(o).push({name:a,value:o.getAttribute(a)})},u=()=>{i.has(o)||i.set(o,[]),i.get(o).push(a)};o.hasAttribute(a)&&c===null?l():o.hasAttribute(a)?(u(),l()):u()}i.forEach((s,o)=>{Tr(o,s)}),r.forEach((s,o)=>{Sr.forEach(a=>a(o,s))});for(let s of n)t.some(o=>o.contains(s))||Ar.forEach(o=>o(s));for(let s of t)s.isConnected&&Or.forEach(o=>o(s));t=null,n=null,r=null,i=null}function Pr(e){return se(ie(e))}function Be(e,t,n){return e._x_dataStack=[t,...ie(n||e)],()=>{e._x_dataStack=e._x_dataStack.filter(r=>r!==t)}}function ie(e){return e._x_dataStack?e._x_dataStack:typeof ShadowRoot=="function"&&e instanceof ShadowRoot?ie(e.host):e.parentNode?ie(e.parentNode):[]}function se(e){return new Proxy({objects:e},Ro)}var Ro={ownKeys({objects:e}){return Array.from(new Set(e.flatMap(t=>Object.keys(t))))},has({objects:e},t){return t==Symbol.unscopables?!1:e.some(n=>Object.prototype.hasOwnProperty.call(n,t)||Reflect.has(n,t))},get({objects:e},t,n){return t=="toJSON"?vo:Reflect.get(e.find(r=>Reflect.has(r,t))||{},t,n)},set({objects:e},t,n,r){const i=e.find(o=>Object.prototype.hasOwnProperty.call(o,t))||e[e.length-1],s=Object.getOwnPropertyDescriptor(i,t);return s?.set&&s?.get?s.set.call(r,n)||!0:Reflect.set(i,t,n)}};function vo(){return Reflect.ownKeys(this).reduce((t,n)=>(t[n]=Reflect.get(this,n),t),{})}function en(e){let t=r=>typeof r=="object"&&!Array.isArray(r)&&r!==null,n=(r,i="")=>{Object.entries(Object.getOwnPropertyDescriptors(r)).forEach(([s,{value:o,enumerable:a}])=>{if(a===!1||o===void 0||typeof o=="object"&&o!==null&&o.__v_skip)return;let c=i===""?s:`${i}.${s}`;typeof o=="object"&&o!==null&&o._x_interceptor?r[s]=o.initialize(e,c,s):t(o)&&o!==r&&!(o instanceof Element)&&n(o,c)})};return n(e)}function Nr(e,t=()=>{}){let n={initialValue:void 0,_x_interceptor:!0,initialize(r,i,s){return e(this.initialValue,()=>To(r,i),o=>Nt(r,i,o),i,s)}};return t(n),r=>{if(typeof r=="object"&&r!==null&&r._x_interceptor){let i=n.initialize.bind(n);n.initialize=(s,o,a)=>{let c=r.initialize(s,o,a);return n.initialValue=c,i(s,o,a)}}else n.initialValue=r;return n}}function To(e,t){return t.split(".").reduce((n,r)=>n[r],e)}function Nt(e,t,n){if(typeof t=="string"&&(t=t.split(".")),t.length===1)e[t[0]]=n;else{if(t.length===0)throw error;return e[t[0]]||(e[t[0]]={}),Nt(e[t[0]],t.slice(1),n)}}var Mr={};function $(e,t){Mr[e]=t}function Pe(e,t){let n=Co(t);return Object.entries(Mr).forEach(([r,i])=>{Object.defineProperty(e,`$${r}`,{get(){return i(t,n)},enumerable:!1})}),e}function Co(e){let[t,n]=Ur(e),r={interceptor:Nr,...t};return Vt(e,n),r}function Po(e,t,n,...r){try{return n(...r)}catch(i){Ne(i,e,t)}}function Ne(...e){return Fr(...e)}var Fr=Mo;function No(e){Fr=e}function Mo(e,t,n=void 0){e=Object.assign(e??{message:"No error message given."},{el:t,expression:n}),console.warn(`Alpine Expression Error: ${e.message}

${n?'Expression: "'+n+`"

`:""}`,t),setTimeout(()=>{throw e},0)}var de=!0;function Lr(e){let t=de;de=!1;let n=e();return de=t,n}function ee(e,t,n={}){let r;return P(e,t)(i=>r=i,n),r}function P(...e){return Ir(...e)}var Ir=jr;function Fo(e){Ir=e}var Dr;function Lo(e){Dr=e}function jr(e,t){let n={};Pe(n,e);let r=[n,...ie(e)],i=typeof t=="function"?Io(r,t):jo(r,t,e);return Po.bind(null,e,t,i)}function Io(e,t){return(n=()=>{},{scope:r={},params:i=[],context:s}={})=>{if(!de){Me(n,t,se([r,...e]),i);return}let o=t.apply(se([r,...e]),i);Me(n,o)}}var bt={};function Do(e,t){if(bt[e])return bt[e];let n=Object.getPrototypeOf(async function(){}).constructor,r=/^[\n\s]*if.*\(.*\)/.test(e.trim())||/^(let|const)\s/.test(e.trim())?`(async()=>{ ${e} })()`:e,s=(()=>{try{let o=new n(["__self","scope"],`with (scope) { __self.result = ${r} }; __self.finished = true; return __self.result;`);return Object.defineProperty(o,"name",{value:`[Alpine] ${e}`}),o}catch(o){return Ne(o,t,e),Promise.resolve()}})();return bt[e]=s,s}function jo(e,t,n){let r=Do(t,n);return(i=()=>{},{scope:s={},params:o=[],context:a}={})=>{r.result=void 0,r.finished=!1;let c=se([s,...e]);if(typeof r=="function"){let l=r.call(a,r,c).catch(u=>Ne(u,n,t));r.finished?(Me(i,r.result,c,o,n),r.result=void 0):l.then(u=>{Me(i,u,c,o,n)}).catch(u=>Ne(u,n,t)).finally(()=>r.result=void 0)}}}function Me(e,t,n,r,i){if(de&&typeof t=="function"){let s=t.apply(n,r);s instanceof Promise?s.then(o=>Me(e,o,n,r)).catch(o=>Ne(o,i,t)):e(s)}else typeof t=="object"&&t instanceof Promise?t.then(s=>e(s)):e(t)}function Bo(...e){return Dr(...e)}function $o(e,t,n={}){let r={};Pe(r,e);let i=[r,...ie(e)],s=se([n.scope??{},...i]),o=n.params??[];if(t.includes("await")){let a=Object.getPrototypeOf(async function(){}).constructor,c=/^[\n\s]*if.*\(.*\)/.test(t.trim())||/^(let|const)\s/.test(t.trim())?`(async()=>{ ${t} })()`:t;return new a(["scope"],`with (scope) { let __result = ${c}; return __result }`).call(n.context,s)}else{let a=/^[\n\s]*if.*\(.*\)/.test(t.trim())||/^(let|const)\s/.test(t.trim())?`(()=>{ ${t} })()`:t,l=new Function(["scope"],`with (scope) { let __result = ${a}; return __result }`).call(n.context,s);return typeof l=="function"&&de?l.apply(s,o):l}}var tn="x-";function ye(e=""){return tn+e}function Uo(e){tn=e}var tt={};function R(e,t){return tt[e]=t,{before(n){if(!tt[n]){console.warn(String.raw`Cannot find directive \`${n}\`. \`${e}\` will use the default order of execution`);return}const r=Y.indexOf(n);Y.splice(r>=0?r:Y.indexOf("DEFAULT"),0,e)}}}function ko(e){return Object.keys(tt).includes(e)}function nn(e,t,n){if(t=Array.from(t),e._x_virtualDirectives){let s=Object.entries(e._x_virtualDirectives).map(([a,c])=>({name:a,value:c})),o=Br(s);s=s.map(a=>o.find(c=>c.name===a.name)?{name:`x-bind:${a.name}`,value:`"${a.value}"`}:a),t=t.concat(s)}let r={};return t.map(Hr((s,o)=>r[s]=o)).filter(Kr).map(zo(r,n)).sort(Ko).map(s=>Ho(e,s))}function Br(e){return Array.from(e).map(Hr()).filter(t=>!Kr(t))}var Mt=!1,ve=new Map,$r=Symbol();function qo(e){Mt=!0;let t=Symbol();$r=t,ve.set(t,[]);let n=()=>{for(;ve.get(t).length;)ve.get(t).shift()();ve.delete(t)},r=()=>{Mt=!1,n()};e(n),r()}function Ur(e){let t=[],n=a=>t.push(a),[r,i]=bo(e);return t.push(i),[{Alpine:we,effect:r,cleanup:n,evaluateLater:P.bind(P,e),evaluate:ee.bind(ee,e)},()=>t.forEach(a=>a())]}function Ho(e,t){let n=()=>{},r=tt[t.type]||n,[i,s]=Ur(e);vr(e,t.original,s);let o=()=>{e._x_ignore||e._x_ignoreSelf||(r.inline&&r.inline(e,t,i),r=r.bind(r,e,t,i),Mt?ve.get($r).push(r):r())};return o.runCleanups=s,o}var kr=(e,t)=>({name:n,value:r})=>(n.startsWith(e)&&(n=n.replace(e,t)),{name:n,value:r}),qr=e=>e;function Hr(e=()=>{}){return({name:t,value:n})=>{let{name:r,value:i}=zr.reduce((s,o)=>o(s),{name:t,value:n});return r!==t&&e(r,t),{name:r,value:i}}}var zr=[];function rn(e){zr.push(e)}function Kr({name:e}){return Wr().test(e)}var Wr=()=>new RegExp(`^${tn}([^:^.]+)\\b`);function zo(e,t){return({name:n,value:r})=>{n===r&&(r="");let i=n.match(Wr()),s=n.match(/:([a-zA-Z0-9\-_:]+)/),o=n.match(/\.[^.\]]+(?=[^\]]*$)/g)||[],a=t||e[n]||n;return{type:i?i[1]:null,value:s?s[1]:null,modifiers:o.map(c=>c.replace(".","")),expression:r,original:a}}}var Ft="DEFAULT",Y=["ignore","ref","data","id","anchor","bind","init","for","model","modelable","transition","show","if",Ft,"teleport"];function Ko(e,t){let n=Y.indexOf(e.type)===-1?Ft:e.type,r=Y.indexOf(t.type)===-1?Ft:t.type;return Y.indexOf(n)-Y.indexOf(r)}function Te(e,t,n={}){e.dispatchEvent(new CustomEvent(t,{detail:n,bubbles:!0,composed:!0,cancelable:!0}))}function oe(e,t){if(typeof ShadowRoot=="function"&&e instanceof ShadowRoot){Array.from(e.children).forEach(i=>oe(i,t));return}let n=!1;if(t(e,()=>n=!0),n)return;let r=e.firstElementChild;for(;r;)oe(r,t),r=r.nextElementSibling}function D(e,...t){console.warn(`Alpine Warning: ${e}`,...t)}var $n=!1;function Wo(){$n&&D("Alpine has already been initialized on this page. Calling Alpine.start() more than once can cause problems."),$n=!0,document.body||D("Unable to initialize. Trying to load Alpine before `<body>` is available. Did you forget to add `defer` in Alpine's `<script>` tag?"),Te(document,"alpine:init"),Te(document,"alpine:initializing"),Yt(),xo(t=>k(t,oe)),Vt(t=>be(t)),Rr((t,n)=>{nn(t,n).forEach(r=>r())});let e=t=>!ct(t.parentElement,!0);Array.from(document.querySelectorAll(Xr().join(","))).filter(e).forEach(t=>{k(t)}),Te(document,"alpine:initialized"),setTimeout(()=>{Go()})}var sn=[],Jr=[];function Vr(){return sn.map(e=>e())}function Xr(){return sn.concat(Jr).map(e=>e())}function Gr(e){sn.push(e)}function Yr(e){Jr.push(e)}function ct(e,t=!1){return ae(e,n=>{if((t?Xr():Vr()).some(i=>n.matches(i)))return!0})}function ae(e,t){if(e){if(t(e))return e;if(e._x_teleportBack&&(e=e._x_teleportBack),e.parentNode instanceof ShadowRoot)return ae(e.parentNode.host,t);if(e.parentElement)return ae(e.parentElement,t)}}function Jo(e){return Vr().some(t=>e.matches(t))}var Zr=[];function Vo(e){Zr.push(e)}var Xo=1;function k(e,t=oe,n=()=>{}){ae(e,r=>r._x_ignore)||qo(()=>{t(e,(r,i)=>{r._x_marker||(n(r,i),Zr.forEach(s=>s(r,i)),nn(r,r.attributes).forEach(s=>s()),r._x_ignore||(r._x_marker=Xo++),r._x_ignore&&i())})})}function be(e,t=oe){t(e,n=>{Eo(n),Tr(n),delete n._x_marker})}function Go(){[["ui","dialog",["[x-dialog], [x-popover]"]],["anchor","anchor",["[x-anchor]"]],["sort","sort",["[x-sort]"]]].forEach(([t,n,r])=>{ko(n)||r.some(i=>{if(document.querySelector(i))return D(`found "${i}", but missing ${t} plugin`),!0})})}var Lt=[],on=!1;function an(e=()=>{}){return queueMicrotask(()=>{on||setTimeout(()=>{It()})}),new Promise(t=>{Lt.push(()=>{e(),t()})})}function It(){for(on=!1;Lt.length;)Lt.shift()()}function Yo(){on=!0}function cn(e,t){return Array.isArray(t)?Un(e,t.join(" ")):typeof t=="object"&&t!==null?Zo(e,t):typeof t=="function"?cn(e,t()):Un(e,t)}function Un(e,t){let n=i=>i.split(" ").filter(s=>!e.classList.contains(s)).filter(Boolean),r=i=>(e.classList.add(...i),()=>{e.classList.remove(...i)});return t=t===!0?t="":t||"",r(n(t))}function Zo(e,t){let n=a=>a.split(" ").filter(Boolean),r=Object.entries(t).flatMap(([a,c])=>c?n(a):!1).filter(Boolean),i=Object.entries(t).flatMap(([a,c])=>c?!1:n(a)).filter(Boolean),s=[],o=[];return i.forEach(a=>{e.classList.contains(a)&&(e.classList.remove(a),o.push(a))}),r.forEach(a=>{e.classList.contains(a)||(e.classList.add(a),s.push(a))}),()=>{o.forEach(a=>e.classList.add(a)),s.forEach(a=>e.classList.remove(a))}}function ut(e,t){return typeof t=="object"&&t!==null?Qo(e,t):ea(e,t)}function Qo(e,t){let n={};return Object.entries(t).forEach(([r,i])=>{n[r]=e.style[r],r.startsWith("--")||(r=ta(r)),e.style.setProperty(r,i)}),setTimeout(()=>{e.style.length===0&&e.removeAttribute("style")}),()=>{ut(e,n)}}function ea(e,t){let n=e.getAttribute("style",t);return e.setAttribute("style",t),()=>{e.setAttribute("style",n||"")}}function ta(e){return e.replace(/([a-z])([A-Z])/g,"$1-$2").toLowerCase()}function Dt(e,t=()=>{}){let n=!1;return function(){n?t.apply(this,arguments):(n=!0,e.apply(this,arguments))}}R("transition",(e,{value:t,modifiers:n,expression:r},{evaluate:i})=>{typeof r=="function"&&(r=i(r)),r!==!1&&(!r||typeof r=="boolean"?ra(e,n,t):na(e,r,t))});function na(e,t,n){Qr(e,cn,""),{enter:i=>{e._x_transition.enter.during=i},"enter-start":i=>{e._x_transition.enter.start=i},"enter-end":i=>{e._x_transition.enter.end=i},leave:i=>{e._x_transition.leave.during=i},"leave-start":i=>{e._x_transition.leave.start=i},"leave-end":i=>{e._x_transition.leave.end=i}}[n](t)}function ra(e,t,n){Qr(e,ut);let r=!t.includes("in")&&!t.includes("out")&&!n,i=r||t.includes("in")||["enter"].includes(n),s=r||t.includes("out")||["leave"].includes(n);t.includes("in")&&!r&&(t=t.filter((m,w)=>w<t.indexOf("out"))),t.includes("out")&&!r&&(t=t.filter((m,w)=>w>t.indexOf("out")));let o=!t.includes("opacity")&&!t.includes("scale"),a=o||t.includes("opacity"),c=o||t.includes("scale"),l=a?0:1,u=c?Oe(t,"scale",95)/100:1,h=Oe(t,"delay",0)/1e3,y=Oe(t,"origin","center"),x="opacity, transform",p=Oe(t,"duration",150)/1e3,_=Oe(t,"duration",75)/1e3,d="cubic-bezier(0.4, 0.0, 0.2, 1)";i&&(e._x_transition.enter.during={transformOrigin:y,transitionDelay:`${h}s`,transitionProperty:x,transitionDuration:`${p}s`,transitionTimingFunction:d},e._x_transition.enter.start={opacity:l,transform:`scale(${u})`},e._x_transition.enter.end={opacity:1,transform:"scale(1)"}),s&&(e._x_transition.leave.during={transformOrigin:y,transitionDelay:`${h}s`,transitionProperty:x,transitionDuration:`${_}s`,transitionTimingFunction:d},e._x_transition.leave.start={opacity:1,transform:"scale(1)"},e._x_transition.leave.end={opacity:l,transform:`scale(${u})`})}function Qr(e,t,n={}){e._x_transition||(e._x_transition={enter:{during:n,start:n,end:n},leave:{during:n,start:n,end:n},in(r=()=>{},i=()=>{}){jt(e,t,{during:this.enter.during,start:this.enter.start,end:this.enter.end},r,i)},out(r=()=>{},i=()=>{}){jt(e,t,{during:this.leave.during,start:this.leave.start,end:this.leave.end},r,i)}})}window.Element.prototype._x_toggleAndCascadeWithTransitions=function(e,t,n,r){const i=document.visibilityState==="visible"?requestAnimationFrame:setTimeout;let s=()=>i(n);if(t){e._x_transition&&(e._x_transition.enter||e._x_transition.leave)?e._x_transition.enter&&(Object.entries(e._x_transition.enter.during).length||Object.entries(e._x_transition.enter.start).length||Object.entries(e._x_transition.enter.end).length)?e._x_transition.in(n):s():e._x_transition?e._x_transition.in(n):s();return}e._x_hidePromise=e._x_transition?new Promise((o,a)=>{e._x_transition.out(()=>{},()=>o(r)),e._x_transitioning&&e._x_transitioning.beforeCancel(()=>a({isFromCancelledTransition:!0}))}):Promise.resolve(r),queueMicrotask(()=>{let o=ei(e);o?(o._x_hideChildren||(o._x_hideChildren=[]),o._x_hideChildren.push(e)):i(()=>{let a=c=>{let l=Promise.all([c._x_hidePromise,...(c._x_hideChildren||[]).map(a)]).then(([u])=>u?.());return delete c._x_hidePromise,delete c._x_hideChildren,l};a(e).catch(c=>{if(!c.isFromCancelledTransition)throw c})})})};function ei(e){let t=e.parentNode;if(t)return t._x_hidePromise?t:ei(t)}function jt(e,t,{during:n,start:r,end:i}={},s=()=>{},o=()=>{}){if(e._x_transitioning&&e._x_transitioning.cancel(),Object.keys(n).length===0&&Object.keys(r).length===0&&Object.keys(i).length===0){s(),o();return}let a,c,l;ia(e,{start(){a=t(e,r)},during(){c=t(e,n)},before:s,end(){a(),l=t(e,i)},after:o,cleanup(){c(),l()}})}function ia(e,t){let n,r,i,s=Dt(()=>{A(()=>{n=!0,r||t.before(),i||(t.end(),It()),t.after(),e.isConnected&&t.cleanup(),delete e._x_transitioning})});e._x_transitioning={beforeCancels:[],beforeCancel(o){this.beforeCancels.push(o)},cancel:Dt(function(){for(;this.beforeCancels.length;)this.beforeCancels.shift()();s()}),finish:s},A(()=>{t.start(),t.during()}),Yo(),requestAnimationFrame(()=>{if(n)return;let o=Number(getComputedStyle(e).transitionDuration.replace(/,.*/,"").replace("s",""))*1e3,a=Number(getComputedStyle(e).transitionDelay.replace(/,.*/,"").replace("s",""))*1e3;o===0&&(o=Number(getComputedStyle(e).animationDuration.replace("s",""))*1e3),A(()=>{t.before()}),r=!0,requestAnimationFrame(()=>{n||(A(()=>{t.end()}),It(),setTimeout(e._x_transitioning.finish,o+a),i=!0)})})}function Oe(e,t,n){if(e.indexOf(t)===-1)return n;const r=e[e.indexOf(t)+1];if(!r||t==="scale"&&isNaN(r))return n;if(t==="duration"||t==="delay"){let i=r.match(/([0-9]+)ms/);if(i)return i[1]}return t==="origin"&&["top","right","left","center","bottom"].includes(e[e.indexOf(t)+2])?[r,e[e.indexOf(t)+2]].join(" "):r}var K=!1;function J(e,t=()=>{}){return(...n)=>K?t(...n):e(...n)}function sa(e){return(...t)=>K&&e(...t)}var ti=[];function lt(e){ti.push(e)}function oa(e,t){ti.forEach(n=>n(e,t)),K=!0,ni(()=>{k(t,(n,r)=>{r(n,()=>{})})}),K=!1}var Bt=!1;function aa(e,t){t._x_dataStack||(t._x_dataStack=e._x_dataStack),K=!0,Bt=!0,ni(()=>{ca(t)}),K=!1,Bt=!1}function ca(e){let t=!1;k(e,(r,i)=>{oe(r,(s,o)=>{if(t&&Jo(s))return o();t=!0,i(s,o)})})}function ni(e){let t=ce;Bn((n,r)=>{let i=t(n);return ge(i),()=>{}}),e(),Bn(t)}function ri(e,t,n,r=[]){switch(e._x_bindings||(e._x_bindings=me({})),e._x_bindings[t]=n,t=r.includes("camel")?ma(t):t,t){case"value":ua(e,n);break;case"style":fa(e,n);break;case"class":la(e,n);break;case"selected":case"checked":da(e,t,n);break;default:ii(e,t,n);break}}function ua(e,t){if(ai(e))e.attributes.value===void 0&&(e.value=t),window.fromModel&&(typeof t=="boolean"?e.checked=Ze(e.value)===t:e.checked=kn(e.value,t));else if(un(e))Number.isInteger(t)?e.value=t:!Array.isArray(t)&&typeof t!="boolean"&&![null,void 0].includes(t)?e.value=String(t):Array.isArray(t)?e.checked=t.some(n=>kn(n,e.value)):e.checked=!!t;else if(e.tagName==="SELECT")_a(e,t);else{if(e.value===t)return;e.value=t===void 0?"":t}}function la(e,t){e._x_undoAddedClasses&&e._x_undoAddedClasses(),e._x_undoAddedClasses=cn(e,t)}function fa(e,t){e._x_undoAddedStyles&&e._x_undoAddedStyles(),e._x_undoAddedStyles=ut(e,t)}function da(e,t,n){ii(e,t,n),ha(e,t,n)}function ii(e,t,n){[null,void 0,!1].includes(n)&&ya(t)?e.removeAttribute(t):(si(t)&&(n=t),pa(e,t,n))}function pa(e,t,n){e.getAttribute(t)!=n&&e.setAttribute(t,n)}function ha(e,t,n){e[t]!==n&&(e[t]=n)}function _a(e,t){const n=[].concat(t).map(r=>r+"");Array.from(e.options).forEach(r=>{r.selected=n.includes(r.value)})}function ma(e){return e.toLowerCase().replace(/-(\w)/g,(t,n)=>n.toUpperCase())}function kn(e,t){return e==t}function Ze(e){return[1,"1","true","on","yes",!0].includes(e)?!0:[0,"0","false","off","no",!1].includes(e)?!1:e?!!e:null}var ga=new Set(["allowfullscreen","async","autofocus","autoplay","checked","controls","default","defer","disabled","formnovalidate","inert","ismap","itemscope","loop","multiple","muted","nomodule","novalidate","open","playsinline","readonly","required","reversed","selected","shadowrootclonable","shadowrootdelegatesfocus","shadowrootserializable"]);function si(e){return ga.has(e)}function ya(e){return!["aria-pressed","aria-checked","aria-expanded","aria-selected"].includes(e)}function ba(e,t,n){return e._x_bindings&&e._x_bindings[t]!==void 0?e._x_bindings[t]:oi(e,t,n)}function wa(e,t,n,r=!0){if(e._x_bindings&&e._x_bindings[t]!==void 0)return e._x_bindings[t];if(e._x_inlineBindings&&e._x_inlineBindings[t]!==void 0){let i=e._x_inlineBindings[t];return i.extract=r,Lr(()=>ee(e,i.expression))}return oi(e,t,n)}function oi(e,t,n){let r=e.getAttribute(t);return r===null?typeof n=="function"?n():n:r===""?!0:si(t)?!![t,"true"].includes(r):r}function un(e){return e.type==="checkbox"||e.localName==="ui-checkbox"||e.localName==="ui-switch"}function ai(e){return e.type==="radio"||e.localName==="ui-radio"}function ci(e,t){let n;return function(){const r=this,i=arguments,s=function(){n=null,e.apply(r,i)};clearTimeout(n),n=setTimeout(s,t)}}function ui(e,t){let n;return function(){let r=this,i=arguments;n||(e.apply(r,i),n=!0,setTimeout(()=>n=!1,t))}}function li({get:e,set:t},{get:n,set:r}){let i=!0,s,o=ce(()=>{let a=e(),c=n();if(i)r(wt(a)),i=!1;else{let l=JSON.stringify(a),u=JSON.stringify(c);l!==s?r(wt(a)):l!==u&&t(wt(c))}s=JSON.stringify(e()),JSON.stringify(n())});return()=>{ge(o)}}function wt(e){return typeof e=="object"?JSON.parse(JSON.stringify(e)):e}function xa(e){(Array.isArray(e)?e:[e]).forEach(n=>n(we))}var X={},qn=!1;function Ea(e,t){if(qn||(X=me(X),qn=!0),t===void 0)return X[e];X[e]=t,en(X[e]),typeof t=="object"&&t!==null&&t.hasOwnProperty("init")&&typeof t.init=="function"&&X[e].init()}function Sa(){return X}var fi={};function Aa(e,t){let n=typeof t!="function"?()=>t:t;return e instanceof Element?di(e,n()):(fi[e]=n,()=>{})}function Oa(e){return Object.entries(fi).forEach(([t,n])=>{Object.defineProperty(e,t,{get(){return(...r)=>n(...r)}})}),e}function di(e,t,n){let r=[];for(;r.length;)r.pop()();let i=Object.entries(t).map(([o,a])=>({name:o,value:a})),s=Br(i);return i=i.map(o=>s.find(a=>a.name===o.name)?{name:`x-bind:${o.name}`,value:`"${o.value}"`}:o),nn(e,i,n).map(o=>{r.push(o.runCleanups),o()}),()=>{for(;r.length;)r.pop()()}}var pi={};function Ra(e,t){pi[e]=t}function va(e,t){return Object.entries(pi).forEach(([n,r])=>{Object.defineProperty(e,n,{get(){return(...i)=>r.bind(t)(...i)},enumerable:!1})}),e}var Ta={get reactive(){return me},get release(){return ge},get effect(){return ce},get raw(){return xr},get transaction(){return wo},version:"3.15.8",flushAndStopDeferringMutations:Oo,dontAutoEvaluateFunctions:Lr,disableEffectScheduling:go,startObservingMutations:Yt,stopObservingMutations:Cr,setReactivityEngine:yo,onAttributeRemoved:vr,onAttributesAdded:Rr,closestDataStack:ie,skipDuringClone:J,onlyDuringClone:sa,addRootSelector:Gr,addInitSelector:Yr,setErrorHandler:No,interceptClone:lt,addScopeToNode:Be,deferMutations:Ao,mapAttributes:rn,evaluateLater:P,interceptInit:Vo,initInterceptors:en,injectMagics:Pe,setEvaluator:Fo,setRawEvaluator:Lo,mergeProxies:se,extractProp:wa,findClosest:ae,onElRemoved:Vt,closestRoot:ct,destroyTree:be,interceptor:Nr,transition:jt,setStyles:ut,mutateDom:A,directive:R,entangle:li,throttle:ui,debounce:ci,evaluate:ee,evaluateRaw:Bo,initTree:k,nextTick:an,prefixed:ye,prefix:Uo,plugin:xa,magic:$,store:Ea,start:Wo,clone:aa,cloneNode:oa,bound:ba,$data:Pr,watch:Er,walk:oe,data:Ra,bind:Aa},we=Ta;function Ca(e,t){const n=Object.create(null),r=e.split(",");for(let i=0;i<r.length;i++)n[r[i]]=!0;return i=>!!n[i]}var Pa=Object.freeze({}),Na=Object.prototype.hasOwnProperty,ft=(e,t)=>Na.call(e,t),te=Array.isArray,Ce=e=>hi(e)==="[object Map]",Ma=e=>typeof e=="string",ln=e=>typeof e=="symbol",dt=e=>e!==null&&typeof e=="object",Fa=Object.prototype.toString,hi=e=>Fa.call(e),_i=e=>hi(e).slice(8,-1),fn=e=>Ma(e)&&e!=="NaN"&&e[0]!=="-"&&""+parseInt(e,10)===e,La=e=>{const t=Object.create(null);return n=>t[n]||(t[n]=e(n))},Ia=La(e=>e.charAt(0).toUpperCase()+e.slice(1)),mi=(e,t)=>e!==t&&(e===e||t===t),$t=new WeakMap,Re=[],U,ne=Symbol("iterate"),Ut=Symbol("Map key iterate");function Da(e){return e&&e._isEffect===!0}function ja(e,t=Pa){Da(e)&&(e=e.raw);const n=Ua(e,t);return t.lazy||n(),n}function Ba(e){e.active&&(gi(e),e.options.onStop&&e.options.onStop(),e.active=!1)}var $a=0;function Ua(e,t){const n=function(){if(!n.active)return e();if(!Re.includes(n)){gi(n);try{return qa(),Re.push(n),U=n,e()}finally{Re.pop(),yi(),U=Re[Re.length-1]}}};return n.id=$a++,n.allowRecurse=!!t.allowRecurse,n._isEffect=!0,n.active=!0,n.raw=e,n.deps=[],n.options=t,n}function gi(e){const{deps:t}=e;if(t.length){for(let n=0;n<t.length;n++)t[n].delete(e);t.length=0}}var he=!0,dn=[];function ka(){dn.push(he),he=!1}function qa(){dn.push(he),he=!0}function yi(){const e=dn.pop();he=e===void 0?!0:e}function j(e,t,n){if(!he||U===void 0)return;let r=$t.get(e);r||$t.set(e,r=new Map);let i=r.get(n);i||r.set(n,i=new Set),i.has(U)||(i.add(U),U.deps.push(i),U.options.onTrack&&U.options.onTrack({effect:U,target:e,type:t,key:n}))}function W(e,t,n,r,i,s){const o=$t.get(e);if(!o)return;const a=new Set,c=u=>{u&&u.forEach(h=>{(h!==U||h.allowRecurse)&&a.add(h)})};if(t==="clear")o.forEach(c);else if(n==="length"&&te(e))o.forEach((u,h)=>{(h==="length"||h>=r)&&c(u)});else switch(n!==void 0&&c(o.get(n)),t){case"add":te(e)?fn(n)&&c(o.get("length")):(c(o.get(ne)),Ce(e)&&c(o.get(Ut)));break;case"delete":te(e)||(c(o.get(ne)),Ce(e)&&c(o.get(Ut)));break;case"set":Ce(e)&&c(o.get(ne));break}const l=u=>{u.options.onTrigger&&u.options.onTrigger({effect:u,target:e,key:n,type:t,newValue:r,oldValue:i,oldTarget:s}),u.options.scheduler?u.options.scheduler(u):u()};a.forEach(l)}var Ha=Ca("__proto__,__v_isRef,__isVue"),bi=new Set(Object.getOwnPropertyNames(Symbol).map(e=>Symbol[e]).filter(ln)),za=wi(),Ka=wi(!0),Hn=Wa();function Wa(){const e={};return["includes","indexOf","lastIndexOf"].forEach(t=>{e[t]=function(...n){const r=S(this);for(let s=0,o=this.length;s<o;s++)j(r,"get",s+"");const i=r[t](...n);return i===-1||i===!1?r[t](...n.map(S)):i}}),["push","pop","shift","unshift","splice"].forEach(t=>{e[t]=function(...n){ka();const r=S(this)[t].apply(this,n);return yi(),r}}),e}function wi(e=!1,t=!1){return function(r,i,s){if(i==="__v_isReactive")return!e;if(i==="__v_isReadonly")return e;if(i==="__v_raw"&&s===(e?t?oc:Ai:t?sc:Si).get(r))return r;const o=te(r);if(!e&&o&&ft(Hn,i))return Reflect.get(Hn,i,s);const a=Reflect.get(r,i,s);return(ln(i)?bi.has(i):Ha(i))||(e||j(r,"get",i),t)?a:kt(a)?!o||!fn(i)?a.value:a:dt(a)?e?Oi(a):mn(a):a}}var Ja=Va();function Va(e=!1){return function(n,r,i,s){let o=n[r];if(!e&&(i=S(i),o=S(o),!te(n)&&kt(o)&&!kt(i)))return o.value=i,!0;const a=te(n)&&fn(r)?Number(r)<n.length:ft(n,r),c=Reflect.set(n,r,i,s);return n===S(s)&&(a?mi(i,o)&&W(n,"set",r,i,o):W(n,"add",r,i)),c}}function Xa(e,t){const n=ft(e,t),r=e[t],i=Reflect.deleteProperty(e,t);return i&&n&&W(e,"delete",t,void 0,r),i}function Ga(e,t){const n=Reflect.has(e,t);return(!ln(t)||!bi.has(t))&&j(e,"has",t),n}function Ya(e){return j(e,"iterate",te(e)?"length":ne),Reflect.ownKeys(e)}var Za={get:za,set:Ja,deleteProperty:Xa,has:Ga,ownKeys:Ya},Qa={get:Ka,set(e,t){return console.warn(`Set operation on key "${String(t)}" failed: target is readonly.`,e),!0},deleteProperty(e,t){return console.warn(`Delete operation on key "${String(t)}" failed: target is readonly.`,e),!0}},pn=e=>dt(e)?mn(e):e,hn=e=>dt(e)?Oi(e):e,_n=e=>e,pt=e=>Reflect.getPrototypeOf(e);function He(e,t,n=!1,r=!1){e=e.__v_raw;const i=S(e),s=S(t);t!==s&&!n&&j(i,"get",t),!n&&j(i,"get",s);const{has:o}=pt(i),a=r?_n:n?hn:pn;if(o.call(i,t))return a(e.get(t));if(o.call(i,s))return a(e.get(s));e!==i&&e.get(t)}function ze(e,t=!1){const n=this.__v_raw,r=S(n),i=S(e);return e!==i&&!t&&j(r,"has",e),!t&&j(r,"has",i),e===i?n.has(e):n.has(e)||n.has(i)}function Ke(e,t=!1){return e=e.__v_raw,!t&&j(S(e),"iterate",ne),Reflect.get(e,"size",e)}function zn(e){e=S(e);const t=S(this);return pt(t).has.call(t,e)||(t.add(e),W(t,"add",e,e)),this}function Kn(e,t){t=S(t);const n=S(this),{has:r,get:i}=pt(n);let s=r.call(n,e);s?Ei(n,r,e):(e=S(e),s=r.call(n,e));const o=i.call(n,e);return n.set(e,t),s?mi(t,o)&&W(n,"set",e,t,o):W(n,"add",e,t),this}function Wn(e){const t=S(this),{has:n,get:r}=pt(t);let i=n.call(t,e);i?Ei(t,n,e):(e=S(e),i=n.call(t,e));const s=r?r.call(t,e):void 0,o=t.delete(e);return i&&W(t,"delete",e,void 0,s),o}function Jn(){const e=S(this),t=e.size!==0,n=Ce(e)?new Map(e):new Set(e),r=e.clear();return t&&W(e,"clear",void 0,void 0,n),r}function We(e,t){return function(r,i){const s=this,o=s.__v_raw,a=S(o),c=t?_n:e?hn:pn;return!e&&j(a,"iterate",ne),o.forEach((l,u)=>r.call(i,c(l),c(u),s))}}function Je(e,t,n){return function(...r){const i=this.__v_raw,s=S(i),o=Ce(s),a=e==="entries"||e===Symbol.iterator&&o,c=e==="keys"&&o,l=i[e](...r),u=n?_n:t?hn:pn;return!t&&j(s,"iterate",c?Ut:ne),{next(){const{value:h,done:y}=l.next();return y?{value:h,done:y}:{value:a?[u(h[0]),u(h[1])]:u(h),done:y}},[Symbol.iterator](){return this}}}}function z(e){return function(...t){{const n=t[0]?`on key "${t[0]}" `:"";console.warn(`${Ia(e)} operation ${n}failed: target is readonly.`,S(this))}return e==="delete"?!1:this}}function ec(){const e={get(s){return He(this,s)},get size(){return Ke(this)},has:ze,add:zn,set:Kn,delete:Wn,clear:Jn,forEach:We(!1,!1)},t={get(s){return He(this,s,!1,!0)},get size(){return Ke(this)},has:ze,add:zn,set:Kn,delete:Wn,clear:Jn,forEach:We(!1,!0)},n={get(s){return He(this,s,!0)},get size(){return Ke(this,!0)},has(s){return ze.call(this,s,!0)},add:z("add"),set:z("set"),delete:z("delete"),clear:z("clear"),forEach:We(!0,!1)},r={get(s){return He(this,s,!0,!0)},get size(){return Ke(this,!0)},has(s){return ze.call(this,s,!0)},add:z("add"),set:z("set"),delete:z("delete"),clear:z("clear"),forEach:We(!0,!0)};return["keys","values","entries",Symbol.iterator].forEach(s=>{e[s]=Je(s,!1,!1),n[s]=Je(s,!0,!1),t[s]=Je(s,!1,!0),r[s]=Je(s,!0,!0)}),[e,n,t,r]}var[tc,nc]=ec();function xi(e,t){const n=e?nc:tc;return(r,i,s)=>i==="__v_isReactive"?!e:i==="__v_isReadonly"?e:i==="__v_raw"?r:Reflect.get(ft(n,i)&&i in r?n:r,i,s)}var rc={get:xi(!1)},ic={get:xi(!0)};function Ei(e,t,n){const r=S(n);if(r!==n&&t.call(e,r)){const i=_i(e);console.warn(`Reactive ${i} contains both the raw and reactive versions of the same object${i==="Map"?" as keys":""}, which can lead to inconsistencies. Avoid differentiating between the raw and reactive versions of an object and only use the reactive version if possible.`)}}var Si=new WeakMap,sc=new WeakMap,Ai=new WeakMap,oc=new WeakMap;function ac(e){switch(e){case"Object":case"Array":return 1;case"Map":case"Set":case"WeakMap":case"WeakSet":return 2;default:return 0}}function cc(e){return e.__v_skip||!Object.isExtensible(e)?0:ac(_i(e))}function mn(e){return e&&e.__v_isReadonly?e:Ri(e,!1,Za,rc,Si)}function Oi(e){return Ri(e,!0,Qa,ic,Ai)}function Ri(e,t,n,r,i){if(!dt(e))return console.warn(`value cannot be made reactive: ${String(e)}`),e;if(e.__v_raw&&!(t&&e.__v_isReactive))return e;const s=i.get(e);if(s)return s;const o=cc(e);if(o===0)return e;const a=new Proxy(e,o===2?r:n);return i.set(e,a),a}function S(e){return e&&S(e.__v_raw)||e}function kt(e){return!!(e&&e.__v_isRef===!0)}$("nextTick",()=>an);$("dispatch",e=>Te.bind(Te,e));$("watch",(e,{evaluateLater:t,cleanup:n})=>(r,i)=>{let s=t(r),a=Er(()=>{let c;return s(l=>c=l),c},i);n(a)});$("store",Sa);$("data",e=>Pr(e));$("root",e=>ct(e));$("refs",e=>(e._x_refs_proxy||(e._x_refs_proxy=se(uc(e))),e._x_refs_proxy));function uc(e){let t=[];return ae(e,n=>{n._x_refs&&t.push(n._x_refs)}),t}var xt={};function vi(e){return xt[e]||(xt[e]=0),++xt[e]}function lc(e,t){return ae(e,n=>{if(n._x_ids&&n._x_ids[t])return!0})}function fc(e,t){e._x_ids||(e._x_ids={}),e._x_ids[t]||(e._x_ids[t]=vi(t))}$("id",(e,{cleanup:t})=>(n,r=null)=>{let i=`${n}${r?`-${r}`:""}`;return dc(e,i,t,()=>{let s=lc(e,n),o=s?s._x_ids[n]:vi(n);return r?`${n}-${o}-${r}`:`${n}-${o}`})});lt((e,t)=>{e._x_id&&(t._x_id=e._x_id)});function dc(e,t,n,r){if(e._x_id||(e._x_id={}),e._x_id[t])return e._x_id[t];let i=r();return e._x_id[t]=i,n(()=>{delete e._x_id[t]}),i}$("el",e=>e);Ti("Focus","focus","focus");Ti("Persist","persist","persist");function Ti(e,t,n){$(t,r=>D(`You can't use [$${t}] without first installing the "${e}" plugin here: https://alpinejs.dev/plugins/${n}`,r))}R("modelable",(e,{expression:t},{effect:n,evaluateLater:r,cleanup:i})=>{let s=r(t),o=()=>{let u;return s(h=>u=h),u},a=r(`${t} = __placeholder`),c=u=>a(()=>{},{scope:{__placeholder:u}}),l=o();c(l),queueMicrotask(()=>{if(!e._x_model)return;e._x_removeModelListeners.default();let u=e._x_model.get,h=e._x_model.set,y=li({get(){return u()},set(x){h(x)}},{get(){return o()},set(x){c(x)}});i(y)})});R("teleport",(e,{modifiers:t,expression:n},{cleanup:r})=>{e.tagName.toLowerCase()!=="template"&&D("x-teleport can only be used on a <template> tag",e);let i=Vn(n),s=e.content.cloneNode(!0).firstElementChild;e._x_teleport=s,s._x_teleportBack=e,e.setAttribute("data-teleport-template",!0),s.setAttribute("data-teleport-target",!0),e._x_forwardEvents&&e._x_forwardEvents.forEach(a=>{s.addEventListener(a,c=>{c.stopPropagation(),e.dispatchEvent(new c.constructor(c.type,c))})}),Be(s,{},e);let o=(a,c,l)=>{l.includes("prepend")?c.parentNode.insertBefore(a,c):l.includes("append")?c.parentNode.insertBefore(a,c.nextSibling):c.appendChild(a)};A(()=>{o(s,i,t),J(()=>{k(s)})()}),e._x_teleportPutBack=()=>{let a=Vn(n);A(()=>{o(e._x_teleport,a,t)})},r(()=>A(()=>{s.remove(),be(s)}))});var pc=document.createElement("div");function Vn(e){let t=J(()=>document.querySelector(e),()=>pc)();return t||D(`Cannot find x-teleport element for selector: "${e}"`),t}var Ci=()=>{};Ci.inline=(e,{modifiers:t},{cleanup:n})=>{t.includes("self")?e._x_ignoreSelf=!0:e._x_ignore=!0,n(()=>{t.includes("self")?delete e._x_ignoreSelf:delete e._x_ignore})};R("ignore",Ci);R("effect",J((e,{expression:t},{effect:n})=>{n(P(e,t))}));function fe(e,t,n,r){let i=e,s=c=>r(c),o={},a=(c,l)=>u=>l(c,u);if(n.includes("dot")&&(t=hc(t)),n.includes("camel")&&(t=_c(t)),n.includes("passive")&&(o.passive=!0),n.includes("capture")&&(o.capture=!0),n.includes("window")&&(i=window),n.includes("document")&&(i=document),n.includes("debounce")){let c=n[n.indexOf("debounce")+1]||"invalid-wait",l=nt(c.split("ms")[0])?Number(c.split("ms")[0]):250;s=ci(s,l)}if(n.includes("throttle")){let c=n[n.indexOf("throttle")+1]||"invalid-wait",l=nt(c.split("ms")[0])?Number(c.split("ms")[0]):250;s=ui(s,l)}return n.includes("prevent")&&(s=a(s,(c,l)=>{l.preventDefault(),c(l)})),n.includes("stop")&&(s=a(s,(c,l)=>{l.stopPropagation(),c(l)})),n.includes("once")&&(s=a(s,(c,l)=>{c(l),i.removeEventListener(t,s,o)})),(n.includes("away")||n.includes("outside"))&&(i=document,s=a(s,(c,l)=>{e.contains(l.target)||l.target.isConnected!==!1&&(e.offsetWidth<1&&e.offsetHeight<1||e._x_isShown!==!1&&c(l))})),n.includes("self")&&(s=a(s,(c,l)=>{l.target===e&&c(l)})),t==="submit"&&(s=a(s,(c,l)=>{l.target._x_pendingModelUpdates&&l.target._x_pendingModelUpdates.forEach(u=>u()),c(l)})),(gc(t)||Pi(t))&&(s=a(s,(c,l)=>{yc(l,n)||c(l)})),i.addEventListener(t,s,o),()=>{i.removeEventListener(t,s,o)}}function hc(e){return e.replace(/-/g,".")}function _c(e){return e.toLowerCase().replace(/-(\w)/g,(t,n)=>n.toUpperCase())}function nt(e){return!Array.isArray(e)&&!isNaN(e)}function mc(e){return[" ","_"].includes(e)?e:e.replace(/([a-z])([A-Z])/g,"$1-$2").replace(/[_\s]/,"-").toLowerCase()}function gc(e){return["keydown","keyup"].includes(e)}function Pi(e){return["contextmenu","click","mouse"].some(t=>e.includes(t))}function yc(e,t){let n=t.filter(s=>!["window","document","prevent","stop","once","capture","self","away","outside","passive","preserve-scroll","blur","change","lazy"].includes(s));if(n.includes("debounce")){let s=n.indexOf("debounce");n.splice(s,nt((n[s+1]||"invalid-wait").split("ms")[0])?2:1)}if(n.includes("throttle")){let s=n.indexOf("throttle");n.splice(s,nt((n[s+1]||"invalid-wait").split("ms")[0])?2:1)}if(n.length===0||n.length===1&&Xn(e.key).includes(n[0]))return!1;const i=["ctrl","shift","alt","meta","cmd","super"].filter(s=>n.includes(s));return n=n.filter(s=>!i.includes(s)),!(i.length>0&&i.filter(o=>((o==="cmd"||o==="super")&&(o="meta"),e[`${o}Key`])).length===i.length&&(Pi(e.type)||Xn(e.key).includes(n[0])))}function Xn(e){if(!e)return[];e=mc(e);let t={ctrl:"control",slash:"/",space:" ",spacebar:" ",cmd:"meta",esc:"escape",up:"arrow-up",down:"arrow-down",left:"arrow-left",right:"arrow-right",period:".",comma:",",equal:"=",minus:"-",underscore:"_"};return t[e]=e,Object.keys(t).map(n=>{if(t[n]===e)return n}).filter(n=>n)}R("model",(e,{modifiers:t,expression:n},{effect:r,cleanup:i})=>{let s=e;t.includes("parent")&&(s=e.parentNode);let o=P(s,n),a;typeof n=="string"?a=P(s,`${n} = __placeholder`):typeof n=="function"&&typeof n()=="string"?a=P(s,`${n()} = __placeholder`):a=()=>{};let c=()=>{let _;return o(d=>_=d),Gn(_)?_.get():_},l=_=>{let d;o(m=>d=m),Gn(d)?d.set(_):a(()=>{},{scope:{__placeholder:_}})};typeof n=="string"&&e.type==="radio"&&A(()=>{e.hasAttribute("name")||e.setAttribute("name",n)});let u=t.includes("change")||t.includes("lazy"),h=t.includes("blur"),y=t.includes("enter"),x=u||h||y,p;if(K)p=()=>{};else if(x){let _=[],d=m=>l(Ve(e,t,m,c()));if(u&&_.push(fe(e,"change",t,d)),h&&(_.push(fe(e,"blur",t,d)),e.form)){let m=()=>d({target:e});e.form._x_pendingModelUpdates||(e.form._x_pendingModelUpdates=[]),e.form._x_pendingModelUpdates.push(m),i(()=>e.form._x_pendingModelUpdates.splice(e.form._x_pendingModelUpdates.indexOf(m),1))}y&&_.push(fe(e,"keydown",t,m=>{m.key==="Enter"&&d(m)})),p=()=>_.forEach(m=>m())}else{let _=e.tagName.toLowerCase()==="select"||["checkbox","radio"].includes(e.type)?"change":"input";p=fe(e,_,t,d=>{l(Ve(e,t,d,c()))})}if(t.includes("fill")&&([void 0,null,""].includes(c())||un(e)&&Array.isArray(c())||e.tagName.toLowerCase()==="select"&&e.multiple)&&l(Ve(e,t,{target:e},c())),e._x_removeModelListeners||(e._x_removeModelListeners={}),e._x_removeModelListeners.default=p,i(()=>e._x_removeModelListeners.default()),e.form){let _=fe(e.form,"reset",[],d=>{an(()=>e._x_model&&e._x_model.set(Ve(e,t,{target:e},c())))});i(()=>_())}e._x_model={get(){return c()},set(_){l(_)}},e._x_forceModelUpdate=_=>{_===void 0&&typeof n=="string"&&n.match(/\./)&&(_=""),window.fromModel=!0,A(()=>ri(e,"value",_)),delete window.fromModel},r(()=>{let _=c();t.includes("unintrusive")&&document.activeElement.isSameNode(e)||e._x_forceModelUpdate(_)})});function Ve(e,t,n,r){return A(()=>{if(n instanceof CustomEvent&&n.detail!==void 0)return n.detail!==null&&n.detail!==void 0?n.detail:n.target.value;if(un(e))if(Array.isArray(r)){let i=null;return t.includes("number")?i=Et(n.target.value):t.includes("boolean")?i=Ze(n.target.value):i=n.target.value,n.target.checked?r.includes(i)?r:r.concat([i]):r.filter(s=>!bc(s,i))}else return n.target.checked;else{if(e.tagName.toLowerCase()==="select"&&e.multiple)return t.includes("number")?Array.from(n.target.selectedOptions).map(i=>{let s=i.value||i.text;return Et(s)}):t.includes("boolean")?Array.from(n.target.selectedOptions).map(i=>{let s=i.value||i.text;return Ze(s)}):Array.from(n.target.selectedOptions).map(i=>i.value||i.text);{let i;return ai(e)?n.target.checked?i=n.target.value:i=r:i=n.target.value,t.includes("number")?Et(i):t.includes("boolean")?Ze(i):t.includes("trim")?i.trim():i}}})}function Et(e){let t=e?parseFloat(e):null;return wc(t)?t:e}function bc(e,t){return e==t}function wc(e){return!Array.isArray(e)&&!isNaN(e)}function Gn(e){return e!==null&&typeof e=="object"&&typeof e.get=="function"&&typeof e.set=="function"}R("cloak",e=>queueMicrotask(()=>A(()=>e.removeAttribute(ye("cloak")))));Yr(()=>`[${ye("init")}]`);R("init",J((e,{expression:t},{evaluate:n})=>typeof t=="string"?!!t.trim()&&n(t,{},!1):n(t,{},!1)));R("text",(e,{expression:t},{effect:n,evaluateLater:r})=>{let i=r(t);n(()=>{i(s=>{A(()=>{e.textContent=s})})})});R("html",(e,{expression:t},{effect:n,evaluateLater:r})=>{let i=r(t);n(()=>{i(s=>{A(()=>{e.innerHTML=s,e._x_ignoreSelf=!0,k(e),delete e._x_ignoreSelf})})})});rn(kr(":",qr(ye("bind:"))));var Ni=(e,{value:t,modifiers:n,expression:r,original:i},{effect:s,cleanup:o})=>{if(!t){let c={};Oa(c),P(e,r)(u=>{di(e,u,i)},{scope:c});return}if(t==="key")return xc(e,r);if(e._x_inlineBindings&&e._x_inlineBindings[t]&&e._x_inlineBindings[t].extract)return;let a=P(e,r);s(()=>a(c=>{c===void 0&&typeof r=="string"&&r.match(/\./)&&(c=""),A(()=>ri(e,t,c,n))})),o(()=>{e._x_undoAddedClasses&&e._x_undoAddedClasses(),e._x_undoAddedStyles&&e._x_undoAddedStyles()})};Ni.inline=(e,{value:t,modifiers:n,expression:r})=>{t&&(e._x_inlineBindings||(e._x_inlineBindings={}),e._x_inlineBindings[t]={expression:r,extract:!1})};R("bind",Ni);function xc(e,t){e._x_keyExpression=t}Gr(()=>`[${ye("data")}]`);R("data",(e,{expression:t},{cleanup:n})=>{if(Ec(e))return;t=t===""?"{}":t;let r={};Pe(r,e);let i={};va(i,r);let s=ee(e,t,{scope:i});(s===void 0||s===!0)&&(s={}),Pe(s,e);let o=me(s);en(o);let a=Be(e,o);o.init&&ee(e,o.init),n(()=>{o.destroy&&ee(e,o.destroy),a()})});lt((e,t)=>{e._x_dataStack&&(t._x_dataStack=e._x_dataStack,t.setAttribute("data-has-alpine-state",!0))});function Ec(e){return K?Bt?!0:e.hasAttribute("data-has-alpine-state"):!1}R("show",(e,{modifiers:t,expression:n},{effect:r})=>{let i=P(e,n);e._x_doHide||(e._x_doHide=()=>{A(()=>{e.style.setProperty("display","none",t.includes("important")?"important":void 0)})}),e._x_doShow||(e._x_doShow=()=>{A(()=>{e.style.length===1&&e.style.display==="none"?e.removeAttribute("style"):e.style.removeProperty("display")})});let s=()=>{e._x_doHide(),e._x_isShown=!1},o=()=>{e._x_doShow(),e._x_isShown=!0},a=()=>setTimeout(o),c=Dt(h=>h?o():s(),h=>{typeof e._x_toggleAndCascadeWithTransitions=="function"?e._x_toggleAndCascadeWithTransitions(e,h,o,s):h?a():s()}),l,u=!0;r(()=>i(h=>{!u&&h===l||(t.includes("immediate")&&(h?a():s()),c(h),l=h,u=!1)}))});R("for",(e,{expression:t},{effect:n,cleanup:r})=>{let i=Ac(t),s=P(e,i.items),o=P(e,e._x_keyExpression||"index");e._x_prevKeys=[],e._x_lookup={},n(()=>Sc(e,i,s,o)),r(()=>{Object.values(e._x_lookup).forEach(a=>A(()=>{be(a),a.remove()})),delete e._x_prevKeys,delete e._x_lookup})});function Sc(e,t,n,r){let i=o=>typeof o=="object"&&!Array.isArray(o),s=e;n(o=>{Oc(o)&&o>=0&&(o=Array.from(Array(o).keys(),d=>d+1)),o===void 0&&(o=[]);let a=e._x_lookup,c=e._x_prevKeys,l=[],u=[];if(i(o))o=Object.entries(o).map(([d,m])=>{let w=Yn(t,m,d,o);r(b=>{u.includes(b)&&D("Duplicate key on x-for",e),u.push(b)},{scope:{index:d,...w}}),l.push(w)});else for(let d=0;d<o.length;d++){let m=Yn(t,o[d],d,o);r(w=>{u.includes(w)&&D("Duplicate key on x-for",e),u.push(w)},{scope:{index:d,...m}}),l.push(m)}let h=[],y=[],x=[],p=[];for(let d=0;d<c.length;d++){let m=c[d];u.indexOf(m)===-1&&x.push(m)}c=c.filter(d=>!x.includes(d));let _="template";for(let d=0;d<u.length;d++){let m=u[d],w=c.indexOf(m);if(w===-1)c.splice(d,0,m),h.push([_,d]);else if(w!==d){let b=c.splice(d,1)[0],E=c.splice(w-1,1)[0];c.splice(d,0,E),c.splice(w,0,b),y.push([b,E])}else p.push(m);_=m}for(let d=0;d<x.length;d++){let m=x[d];m in a&&(A(()=>{be(a[m]),a[m].remove()}),delete a[m])}for(let d=0;d<y.length;d++){let[m,w]=y[d],b=a[m],E=a[w],v=document.createElement("div");A(()=>{E||D('x-for ":key" is undefined or invalid',s,w,a),E.after(v),b.after(E),E._x_currentIfEl&&E.after(E._x_currentIfEl),v.before(b),b._x_currentIfEl&&b.after(b._x_currentIfEl),v.remove()}),E._x_refreshXForScope(l[u.indexOf(w)])}for(let d=0;d<h.length;d++){let[m,w]=h[d],b=m==="template"?s:a[m];b._x_currentIfEl&&(b=b._x_currentIfEl);let E=l[w],v=u[w],L=document.importNode(s.content,!0).firstElementChild,C=me(E);Be(L,C,s),L._x_refreshXForScope=xe=>{Object.entries(xe).forEach(([ue,$e])=>{C[ue]=$e})},A(()=>{b.after(L),J(()=>k(L))()}),typeof v=="object"&&D("x-for key cannot be an object, it must be a string or an integer",s),a[v]=L}for(let d=0;d<p.length;d++)a[p[d]]._x_refreshXForScope(l[u.indexOf(p[d])]);s._x_prevKeys=u})}function Ac(e){let t=/,([^,\}\]]*)(?:,([^,\}\]]*))?$/,n=/^\s*\(|\)\s*$/g,r=/([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/,i=e.match(r);if(!i)return;let s={};s.items=i[2].trim();let o=i[1].replace(n,"").trim(),a=o.match(t);return a?(s.item=o.replace(t,"").trim(),s.index=a[1].trim(),a[2]&&(s.collection=a[2].trim())):s.item=o,s}function Yn(e,t,n,r){let i={};return/^\[.*\]$/.test(e.item)&&Array.isArray(t)?e.item.replace("[","").replace("]","").split(",").map(o=>o.trim()).forEach((o,a)=>{i[o]=t[a]}):/^\{.*\}$/.test(e.item)&&!Array.isArray(t)&&typeof t=="object"?e.item.replace("{","").replace("}","").split(",").map(o=>o.trim()).forEach(o=>{i[o]=t[o]}):i[e.item]=t,e.index&&(i[e.index]=n),e.collection&&(i[e.collection]=r),i}function Oc(e){return!Array.isArray(e)&&!isNaN(e)}function Mi(){}Mi.inline=(e,{expression:t},{cleanup:n})=>{let r=ct(e);r._x_refs||(r._x_refs={}),r._x_refs[t]=e,n(()=>delete r._x_refs[t])};R("ref",Mi);R("if",(e,{expression:t},{effect:n,cleanup:r})=>{e.tagName.toLowerCase()!=="template"&&D("x-if can only be used on a <template> tag",e);let i=P(e,t),s=()=>{if(e._x_currentIfEl)return e._x_currentIfEl;let a=e.content.cloneNode(!0).firstElementChild;return Be(a,{},e),A(()=>{e.after(a),J(()=>k(a))()}),e._x_currentIfEl=a,e._x_undoIf=()=>{A(()=>{be(a),a.remove()}),delete e._x_currentIfEl},a},o=()=>{e._x_undoIf&&(e._x_undoIf(),delete e._x_undoIf)};n(()=>i(a=>{a?s():o()})),r(()=>e._x_undoIf&&e._x_undoIf())});R("id",(e,{expression:t},{evaluate:n})=>{n(t).forEach(i=>fc(e,i))});lt((e,t)=>{e._x_ids&&(t._x_ids=e._x_ids)});rn(kr("@",qr(ye("on:"))));R("on",J((e,{value:t,modifiers:n,expression:r},{cleanup:i})=>{let s=r?P(e,r):()=>{};e.tagName.toLowerCase()==="template"&&(e._x_forwardEvents||(e._x_forwardEvents=[]),e._x_forwardEvents.includes(t)||e._x_forwardEvents.push(t));let o=fe(e,t,n,a=>{s(()=>{},{scope:{$event:a},params:[a]})});i(()=>o())}));ht("Collapse","collapse","collapse");ht("Intersect","intersect","intersect");ht("Focus","trap","focus");ht("Mask","mask","mask");function ht(e,t,n){R(t,r=>D(`You can't use [x-${t}] without first installing the "${e}" plugin here: https://alpinejs.dev/plugins/${n}`,r))}we.setEvaluator(jr);we.setRawEvaluator($o);we.setReactivityEngine({reactive:mn,effect:ja,release:Ba,raw:S});var Rc=we,Fi=Rc;window.Alpine=Fi;Fi.start();

```

## File: public\build\assets\app-CEwZte8_.css
```css
*,:before,:after{--tw-border-spacing-x: 0;--tw-border-spacing-y: 0;--tw-translate-x: 0;--tw-translate-y: 0;--tw-rotate: 0;--tw-skew-x: 0;--tw-skew-y: 0;--tw-scale-x: 1;--tw-scale-y: 1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness: proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width: 0px;--tw-ring-offset-color: #fff;--tw-ring-color: rgb(59 130 246 / .5);--tw-ring-offset-shadow: 0 0 #0000;--tw-ring-shadow: 0 0 #0000;--tw-shadow: 0 0 #0000;--tw-shadow-colored: 0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }::backdrop{--tw-border-spacing-x: 0;--tw-border-spacing-y: 0;--tw-translate-x: 0;--tw-translate-y: 0;--tw-rotate: 0;--tw-skew-x: 0;--tw-skew-y: 0;--tw-scale-x: 1;--tw-scale-y: 1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness: proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width: 0px;--tw-ring-offset-color: #fff;--tw-ring-color: rgb(59 130 246 / .5);--tw-ring-offset-shadow: 0 0 #0000;--tw-ring-shadow: 0 0 #0000;--tw-shadow: 0 0 #0000;--tw-shadow-colored: 0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }*,:before,:after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}:before,:after{--tw-content: ""}html,:host{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;-o-tab-size:4;tab-size:4;font-family:Figtree,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji",Segoe UI Symbol,"Noto Color Emoji";font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace;font-feature-settings:normal;font-variation-settings:normal;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}button,input:where([type=button]),input:where([type=reset]),input:where([type=submit]){-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dl,dd,h1,h2,h3,h4,h5,h6,hr,figure,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}ol,ul,menu{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::-moz-placeholder,textarea::-moz-placeholder{opacity:1;color:#9ca3af}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}button,[role=button]{cursor:pointer}:disabled{cursor:default}img,svg,video,canvas,audio,iframe,embed,object{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]:where(:not([hidden=until-found])){display:none}input:where([type=text]),input:where(:not([type])),input:where([type=email]),input:where([type=url]),input:where([type=password]),input:where([type=number]),input:where([type=date]),input:where([type=datetime-local]),input:where([type=month]),input:where([type=search]),input:where([type=tel]),input:where([type=time]),input:where([type=week]),select:where([multiple]),textarea,select{-webkit-appearance:none;-moz-appearance:none;appearance:none;background-color:#fff;border-color:#6b7280;border-width:1px;border-radius:0;padding:.5rem .75rem;font-size:1rem;line-height:1.5rem;--tw-shadow: 0 0 #0000}input:where([type=text]):focus,input:where(:not([type])):focus,input:where([type=email]):focus,input:where([type=url]):focus,input:where([type=password]):focus,input:where([type=number]):focus,input:where([type=date]):focus,input:where([type=datetime-local]):focus,input:where([type=month]):focus,input:where([type=search]):focus,input:where([type=tel]):focus,input:where([type=time]):focus,input:where([type=week]):focus,select:where([multiple]):focus,textarea:focus,select:focus{outline:2px solid transparent;outline-offset:2px;--tw-ring-inset: var(--tw-empty, );--tw-ring-offset-width: 0px;--tw-ring-offset-color: #fff;--tw-ring-color: #2563eb;--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow);border-color:#2563eb}input::-moz-placeholder,textarea::-moz-placeholder{color:#6b7280;opacity:1}input::placeholder,textarea::placeholder{color:#6b7280;opacity:1}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-date-and-time-value{min-height:1.5em;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit,::-webkit-datetime-edit-year-field,::-webkit-datetime-edit-month-field,::-webkit-datetime-edit-day-field,::-webkit-datetime-edit-hour-field,::-webkit-datetime-edit-minute-field,::-webkit-datetime-edit-second-field,::-webkit-datetime-edit-millisecond-field,::-webkit-datetime-edit-meridiem-field{padding-top:0;padding-bottom:0}select{background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .5rem center;background-repeat:no-repeat;background-size:1.5em 1.5em;padding-right:2.5rem;-webkit-print-color-adjust:exact;print-color-adjust:exact}select:where([multiple]),select:where([size]:not([size="1"])){background-image:initial;background-position:initial;background-repeat:unset;background-size:initial;padding-right:.75rem;-webkit-print-color-adjust:unset;print-color-adjust:unset}input:where([type=checkbox]),input:where([type=radio]){-webkit-appearance:none;-moz-appearance:none;appearance:none;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact;display:inline-block;vertical-align:middle;background-origin:border-box;-webkit-user-select:none;-moz-user-select:none;user-select:none;flex-shrink:0;height:1rem;width:1rem;color:#2563eb;background-color:#fff;border-color:#6b7280;border-width:1px;--tw-shadow: 0 0 #0000}input:where([type=checkbox]){border-radius:0}input:where([type=radio]){border-radius:100%}input:where([type=checkbox]):focus,input:where([type=radio]):focus{outline:2px solid transparent;outline-offset:2px;--tw-ring-inset: var(--tw-empty, );--tw-ring-offset-width: 2px;--tw-ring-offset-color: #fff;--tw-ring-color: #2563eb;--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}input:where([type=checkbox]):checked,input:where([type=radio]):checked{border-color:transparent;background-color:currentColor;background-size:100% 100%;background-position:center;background-repeat:no-repeat}input:where([type=checkbox]):checked{background-image:url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e")}@media(forced-colors:active){input:where([type=checkbox]):checked{-webkit-appearance:auto;-moz-appearance:auto;appearance:auto}}input:where([type=radio]):checked{background-image:url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e")}@media(forced-colors:active){input:where([type=radio]):checked{-webkit-appearance:auto;-moz-appearance:auto;appearance:auto}}input:where([type=checkbox]):checked:hover,input:where([type=checkbox]):checked:focus,input:where([type=radio]):checked:hover,input:where([type=radio]):checked:focus{border-color:transparent;background-color:currentColor}input:where([type=checkbox]):indeterminate{background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");border-color:transparent;background-color:currentColor;background-size:100% 100%;background-position:center;background-repeat:no-repeat}@media(forced-colors:active){input:where([type=checkbox]):indeterminate{-webkit-appearance:auto;-moz-appearance:auto;appearance:auto}}input:where([type=checkbox]):indeterminate:hover,input:where([type=checkbox]):indeterminate:focus{border-color:transparent;background-color:currentColor}input:where([type=file]){background:unset;border-color:inherit;border-width:0;border-radius:0;padding:0;font-size:unset;line-height:inherit}input:where([type=file]):focus{outline:1px solid ButtonText;outline:1px auto -webkit-focus-ring-color}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}.static{position:static}.fixed{position:fixed}.absolute{position:absolute}.relative{position:relative}.inset-0{inset:0}.bottom-1{bottom:.25rem}.end-0{inset-inline-end:0px}.start-0{inset-inline-start:0px}.top-1{top:.25rem}.z-50{z-index:50}.mx-auto{margin-left:auto;margin-right:auto}.-mb-px{margin-bottom:-1px}.-me-2{margin-inline-end:-.5rem}.-ml-8{margin-left:-2rem}.-ml-px{margin-left:-1px}.-mt-\[4\.9rem\]{margin-top:-4.9rem}.mb-1{margin-bottom:.25rem}.mb-2{margin-bottom:.5rem}.mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}.ml-1{margin-left:.25rem}.ms-1{margin-inline-start:.25rem}.ms-2{margin-inline-start:.5rem}.ms-3{margin-inline-start:.75rem}.ms-4{margin-inline-start:1rem}.mt-1{margin-top:.25rem}.mt-2{margin-top:.5rem}.mt-3{margin-top:.75rem}.mt-4{margin-top:1rem}.mt-6{margin-top:1.5rem}.block{display:block}.inline-block{display:inline-block}.flex{display:flex}.inline-flex{display:inline-flex}.table{display:table}.hidden{display:none}.aspect-\[335\/376\]{aspect-ratio:335/376}.h-1{height:.25rem}.h-1\.5{height:.375rem}.h-14{height:3.5rem}.h-16{height:4rem}.h-2{height:.5rem}.h-2\.5{height:.625rem}.h-20{height:5rem}.h-3{height:.75rem}.h-3\.5{height:.875rem}.h-4{height:1rem}.h-5{height:1.25rem}.h-6{height:1.5rem}.h-9{height:2.25rem}.min-h-screen{min-height:100vh}.w-1{width:.25rem}.w-1\.5{width:.375rem}.w-2{width:.5rem}.w-2\.5{width:.625rem}.w-20{width:5rem}.w-3{width:.75rem}.w-3\.5{width:.875rem}.w-3\/4{width:75%}.w-4{width:1rem}.w-48{width:12rem}.w-5{width:1.25rem}.w-6{width:1.5rem}.w-\[448px\]{width:448px}.w-auto{width:auto}.w-full{width:100%}.max-w-7xl{max-width:80rem}.max-w-\[335px\]{max-width:335px}.max-w-none{max-width:none}.max-w-xl{max-width:36rem}.flex-1{flex:1 1 0%}.shrink-0{flex-shrink:0}.origin-top{transform-origin:top}.translate-y-0{--tw-translate-y: 0px;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.translate-y-4{--tw-translate-y: 1rem;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.scale-100{--tw-scale-x: 1;--tw-scale-y: 1;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.scale-95{--tw-scale-x: .95;--tw-scale-y: .95;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.transform{transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.cursor-default{cursor:default}.cursor-not-allowed{cursor:not-allowed}.flex-col{flex-direction:column}.flex-col-reverse{flex-direction:column-reverse}.items-center{align-items:center}.justify-end{justify-content:flex-end}.justify-center{justify-content:center}.justify-between{justify-content:space-between}.justify-items-center{justify-items:center}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.space-x-1>:not([hidden])~:not([hidden]){--tw-space-x-reverse: 0;margin-right:calc(.25rem * var(--tw-space-x-reverse));margin-left:calc(.25rem * calc(1 - var(--tw-space-x-reverse)))}.space-x-8>:not([hidden])~:not([hidden]){--tw-space-x-reverse: 0;margin-right:calc(2rem * var(--tw-space-x-reverse));margin-left:calc(2rem * calc(1 - var(--tw-space-x-reverse)))}.space-y-1>:not([hidden])~:not([hidden]){--tw-space-y-reverse: 0;margin-top:calc(.25rem * calc(1 - var(--tw-space-y-reverse)));margin-bottom:calc(.25rem * var(--tw-space-y-reverse))}.space-y-6>:not([hidden])~:not([hidden]){--tw-space-y-reverse: 0;margin-top:calc(1.5rem * calc(1 - var(--tw-space-y-reverse)));margin-bottom:calc(1.5rem * var(--tw-space-y-reverse))}.overflow-hidden{overflow:hidden}.overflow-y-auto{overflow-y:auto}.overflow-y-hidden{overflow-y:hidden}.rounded{border-radius:.25rem}.rounded-full{border-radius:9999px}.rounded-lg{border-radius:.5rem}.rounded-md{border-radius:.375rem}.rounded-sm{border-radius:.125rem}.rounded-l-md{border-top-left-radius:.375rem;border-bottom-left-radius:.375rem}.rounded-r-md{border-top-right-radius:.375rem;border-bottom-right-radius:.375rem}.rounded-t-lg{border-top-left-radius:.5rem;border-top-right-radius:.5rem}.rounded-bl-lg{border-bottom-left-radius:.5rem}.rounded-br-lg{border-bottom-right-radius:.5rem}.border{border-width:1px}.border-b{border-bottom-width:1px}.border-b-2{border-bottom-width:2px}.border-l-4{border-left-width:4px}.border-t{border-top-width:1px}.border-\[\#19140035\]{border-color:#19140035}.border-\[\#e3e3e0\]{--tw-border-opacity: 1;border-color:rgb(227 227 224 / var(--tw-border-opacity, 1))}.border-black{--tw-border-opacity: 1;border-color:rgb(0 0 0 / var(--tw-border-opacity, 1))}.border-gray-100{--tw-border-opacity: 1;border-color:rgb(243 244 246 / var(--tw-border-opacity, 1))}.border-gray-200{--tw-border-opacity: 1;border-color:rgb(229 231 235 / var(--tw-border-opacity, 1))}.border-gray-300{--tw-border-opacity: 1;border-color:rgb(209 213 219 / var(--tw-border-opacity, 1))}.border-indigo-400{--tw-border-opacity: 1;border-color:rgb(129 140 248 / var(--tw-border-opacity, 1))}.border-transparent{border-color:transparent}.bg-\[\#1b1b18\]{--tw-bg-opacity: 1;background-color:rgb(27 27 24 / var(--tw-bg-opacity, 1))}.bg-\[\#FDFDFC\]{--tw-bg-opacity: 1;background-color:rgb(253 253 252 / var(--tw-bg-opacity, 1))}.bg-\[\#dbdbd7\]{--tw-bg-opacity: 1;background-color:rgb(219 219 215 / var(--tw-bg-opacity, 1))}.bg-\[\#fff2f2\]{--tw-bg-opacity: 1;background-color:rgb(255 242 242 / var(--tw-bg-opacity, 1))}.bg-gray-100{--tw-bg-opacity: 1;background-color:rgb(243 244 246 / var(--tw-bg-opacity, 1))}.bg-gray-200{--tw-bg-opacity: 1;background-color:rgb(229 231 235 / var(--tw-bg-opacity, 1))}.bg-gray-500{--tw-bg-opacity: 1;background-color:rgb(107 114 128 / var(--tw-bg-opacity, 1))}.bg-gray-800{--tw-bg-opacity: 1;background-color:rgb(31 41 55 / var(--tw-bg-opacity, 1))}.bg-indigo-50{--tw-bg-opacity: 1;background-color:rgb(238 242 255 / var(--tw-bg-opacity, 1))}.bg-red-600{--tw-bg-opacity: 1;background-color:rgb(220 38 38 / var(--tw-bg-opacity, 1))}.bg-white{--tw-bg-opacity: 1;background-color:rgb(255 255 255 / var(--tw-bg-opacity, 1))}.fill-current{fill:currentColor}.p-2{padding:.5rem}.p-4{padding:1rem}.p-6{padding:1.5rem}.px-1{padding-left:.25rem;padding-right:.25rem}.px-2{padding-left:.5rem;padding-right:.5rem}.px-3{padding-left:.75rem;padding-right:.75rem}.px-4{padding-left:1rem;padding-right:1rem}.px-5{padding-left:1.25rem;padding-right:1.25rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-1{padding-top:.25rem;padding-bottom:.25rem}.py-1\.5{padding-top:.375rem;padding-bottom:.375rem}.py-12{padding-top:3rem;padding-bottom:3rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.py-6{padding-top:1.5rem;padding-bottom:1.5rem}.pb-1{padding-bottom:.25rem}.pb-12{padding-bottom:3rem}.pb-3{padding-bottom:.75rem}.pe-4{padding-inline-end:1rem}.ps-3{padding-inline-start:.75rem}.pt-1{padding-top:.25rem}.pt-2{padding-top:.5rem}.pt-4{padding-top:1rem}.pt-6{padding-top:1.5rem}.text-start{text-align:start}.font-sans{font-family:Figtree,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji",Segoe UI Symbol,"Noto Color Emoji"}.text-\[13px\]{font-size:13px}.text-base{font-size:1rem;line-height:1.5rem}.text-lg{font-size:1.125rem;line-height:1.75rem}.text-sm{font-size:.875rem;line-height:1.25rem}.text-xl{font-size:1.25rem;line-height:1.75rem}.text-xs{font-size:.75rem;line-height:1rem}.font-medium{font-weight:500}.font-semibold{font-weight:600}.uppercase{text-transform:uppercase}.leading-4{line-height:1rem}.leading-5{line-height:1.25rem}.leading-\[20px\]{line-height:20px}.leading-normal{line-height:1.5}.leading-tight{line-height:1.25}.tracking-widest{letter-spacing:.1em}.text-\[\#1b1b18\]{--tw-text-opacity: 1;color:rgb(27 27 24 / var(--tw-text-opacity, 1))}.text-\[\#706f6c\]{--tw-text-opacity: 1;color:rgb(112 111 108 / var(--tw-text-opacity, 1))}.text-\[\#F53003\],.text-\[\#f53003\]{--tw-text-opacity: 1;color:rgb(245 48 3 / var(--tw-text-opacity, 1))}.text-gray-400{--tw-text-opacity: 1;color:rgb(156 163 175 / var(--tw-text-opacity, 1))}.text-gray-500{--tw-text-opacity: 1;color:rgb(107 114 128 / var(--tw-text-opacity, 1))}.text-gray-600{--tw-text-opacity: 1;color:rgb(75 85 99 / var(--tw-text-opacity, 1))}.text-gray-700{--tw-text-opacity: 1;color:rgb(55 65 81 / var(--tw-text-opacity, 1))}.text-gray-800{--tw-text-opacity: 1;color:rgb(31 41 55 / var(--tw-text-opacity, 1))}.text-gray-900{--tw-text-opacity: 1;color:rgb(17 24 39 / var(--tw-text-opacity, 1))}.text-green-600{--tw-text-opacity: 1;color:rgb(22 163 74 / var(--tw-text-opacity, 1))}.text-indigo-600{--tw-text-opacity: 1;color:rgb(79 70 229 / var(--tw-text-opacity, 1))}.text-indigo-700{--tw-text-opacity: 1;color:rgb(67 56 202 / var(--tw-text-opacity, 1))}.text-red-600{--tw-text-opacity: 1;color:rgb(220 38 38 / var(--tw-text-opacity, 1))}.text-white{--tw-text-opacity: 1;color:rgb(255 255 255 / var(--tw-text-opacity, 1))}.underline{text-decoration-line:underline}.underline-offset-4{text-underline-offset:4px}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.opacity-0{opacity:0}.opacity-100{opacity:1}.opacity-75{opacity:.75}.shadow{--tw-shadow: 0 1px 3px 0 rgb(0 0 0 / .1), 0 1px 2px -1px rgb(0 0 0 / .1);--tw-shadow-colored: 0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-\[0px_0px_1px_0px_rgba\(0\,0\,0\,0\.03\)\,0px_1px_2px_0px_rgba\(0\,0\,0\,0\.06\)\]{--tw-shadow: 0px 0px 1px 0px rgba(0,0,0,.03),0px 1px 2px 0px rgba(0,0,0,.06);--tw-shadow-colored: 0px 0px 1px 0px var(--tw-shadow-color), 0px 1px 2px 0px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-\[inset_0px_0px_0px_1px_rgba\(26\,26\,0\,0\.16\)\]{--tw-shadow: inset 0px 0px 0px 1px rgba(26,26,0,.16);--tw-shadow-colored: inset 0px 0px 0px 1px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-lg{--tw-shadow: 0 10px 15px -3px rgb(0 0 0 / .1), 0 4px 6px -4px rgb(0 0 0 / .1);--tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-md{--tw-shadow: 0 4px 6px -1px rgb(0 0 0 / .1), 0 2px 4px -2px rgb(0 0 0 / .1);--tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-sm{--tw-shadow: 0 1px 2px 0 rgb(0 0 0 / .05);--tw-shadow-colored: 0 1px 2px 0 var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.shadow-xl{--tw-shadow: 0 20px 25px -5px rgb(0 0 0 / .1), 0 8px 10px -6px rgb(0 0 0 / .1);--tw-shadow-colored: 0 20px 25px -5px var(--tw-shadow-color), 0 8px 10px -6px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.ring-1{--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow, 0 0 #0000)}.ring-black{--tw-ring-opacity: 1;--tw-ring-color: rgb(0 0 0 / var(--tw-ring-opacity, 1))}.ring-gray-300{--tw-ring-opacity: 1;--tw-ring-color: rgb(209 213 219 / var(--tw-ring-opacity, 1))}.ring-opacity-5{--tw-ring-opacity: .05}.\!filter{filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)!important}.filter{filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.transition{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke,opacity,box-shadow,transform,filter,backdrop-filter;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.15s}.transition-all{transition-property:all;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.15s}.transition-opacity{transition-property:opacity;transition-timing-function:cubic-bezier(.4,0,.2,1);transition-duration:.15s}.delay-300{transition-delay:.3s}.duration-150{transition-duration:.15s}.duration-200{transition-duration:.2s}.duration-300{transition-duration:.3s}.duration-75{transition-duration:75ms}.ease-in{transition-timing-function:cubic-bezier(.4,0,1,1)}.ease-in-out{transition-timing-function:cubic-bezier(.4,0,.2,1)}.ease-out{transition-timing-function:cubic-bezier(0,0,.2,1)}.before\:absolute:before{content:var(--tw-content);position:absolute}.before\:bottom-0:before{content:var(--tw-content);bottom:0}.before\:bottom-1\/2:before{content:var(--tw-content);bottom:50%}.before\:left-\[0\.4rem\]:before{content:var(--tw-content);left:.4rem}.before\:top-0:before{content:var(--tw-content);top:0}.before\:top-1\/2:before{content:var(--tw-content);top:50%}.before\:border-l:before{content:var(--tw-content);border-left-width:1px}.before\:border-\[\#e3e3e0\]:before{content:var(--tw-content);--tw-border-opacity: 1;border-color:rgb(227 227 224 / var(--tw-border-opacity, 1))}.hover\:border-\[\#19140035\]:hover{border-color:#19140035}.hover\:border-\[\#1915014a\]:hover{border-color:#1915014a}.hover\:border-black:hover{--tw-border-opacity: 1;border-color:rgb(0 0 0 / var(--tw-border-opacity, 1))}.hover\:border-gray-300:hover{--tw-border-opacity: 1;border-color:rgb(209 213 219 / var(--tw-border-opacity, 1))}.hover\:bg-black:hover{--tw-bg-opacity: 1;background-color:rgb(0 0 0 / var(--tw-bg-opacity, 1))}.hover\:bg-gray-100:hover{--tw-bg-opacity: 1;background-color:rgb(243 244 246 / var(--tw-bg-opacity, 1))}.hover\:bg-gray-50:hover{--tw-bg-opacity: 1;background-color:rgb(249 250 251 / var(--tw-bg-opacity, 1))}.hover\:bg-gray-700:hover{--tw-bg-opacity: 1;background-color:rgb(55 65 81 / var(--tw-bg-opacity, 1))}.hover\:bg-red-500:hover{--tw-bg-opacity: 1;background-color:rgb(239 68 68 / var(--tw-bg-opacity, 1))}.hover\:text-gray-400:hover{--tw-text-opacity: 1;color:rgb(156 163 175 / var(--tw-text-opacity, 1))}.hover\:text-gray-500:hover{--tw-text-opacity: 1;color:rgb(107 114 128 / var(--tw-text-opacity, 1))}.hover\:text-gray-700:hover{--tw-text-opacity: 1;color:rgb(55 65 81 / var(--tw-text-opacity, 1))}.hover\:text-gray-800:hover{--tw-text-opacity: 1;color:rgb(31 41 55 / var(--tw-text-opacity, 1))}.hover\:text-gray-900:hover{--tw-text-opacity: 1;color:rgb(17 24 39 / var(--tw-text-opacity, 1))}.focus\:border-blue-300:focus{--tw-border-opacity: 1;border-color:rgb(147 197 253 / var(--tw-border-opacity, 1))}.focus\:border-gray-300:focus{--tw-border-opacity: 1;border-color:rgb(209 213 219 / var(--tw-border-opacity, 1))}.focus\:border-indigo-500:focus{--tw-border-opacity: 1;border-color:rgb(99 102 241 / var(--tw-border-opacity, 1))}.focus\:border-indigo-700:focus{--tw-border-opacity: 1;border-color:rgb(67 56 202 / var(--tw-border-opacity, 1))}.focus\:bg-gray-100:focus{--tw-bg-opacity: 1;background-color:rgb(243 244 246 / var(--tw-bg-opacity, 1))}.focus\:bg-gray-50:focus{--tw-bg-opacity: 1;background-color:rgb(249 250 251 / var(--tw-bg-opacity, 1))}.focus\:bg-gray-700:focus{--tw-bg-opacity: 1;background-color:rgb(55 65 81 / var(--tw-bg-opacity, 1))}.focus\:bg-indigo-100:focus{--tw-bg-opacity: 1;background-color:rgb(224 231 255 / var(--tw-bg-opacity, 1))}.focus\:text-gray-500:focus{--tw-text-opacity: 1;color:rgb(107 114 128 / var(--tw-text-opacity, 1))}.focus\:text-gray-700:focus{--tw-text-opacity: 1;color:rgb(55 65 81 / var(--tw-text-opacity, 1))}.focus\:text-gray-800:focus{--tw-text-opacity: 1;color:rgb(31 41 55 / var(--tw-text-opacity, 1))}.focus\:text-indigo-800:focus{--tw-text-opacity: 1;color:rgb(55 48 163 / var(--tw-text-opacity, 1))}.focus\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}.focus\:ring:focus{--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow, 0 0 #0000)}.focus\:ring-2:focus{--tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow, 0 0 #0000)}.focus\:ring-indigo-500:focus{--tw-ring-opacity: 1;--tw-ring-color: rgb(99 102 241 / var(--tw-ring-opacity, 1))}.focus\:ring-red-500:focus{--tw-ring-opacity: 1;--tw-ring-color: rgb(239 68 68 / var(--tw-ring-opacity, 1))}.focus\:ring-offset-2:focus{--tw-ring-offset-width: 2px}.active\:bg-gray-100:active{--tw-bg-opacity: 1;background-color:rgb(243 244 246 / var(--tw-bg-opacity, 1))}.active\:bg-gray-900:active{--tw-bg-opacity: 1;background-color:rgb(17 24 39 / var(--tw-bg-opacity, 1))}.active\:bg-red-700:active{--tw-bg-opacity: 1;background-color:rgb(185 28 28 / var(--tw-bg-opacity, 1))}.active\:text-gray-500:active{--tw-text-opacity: 1;color:rgb(107 114 128 / var(--tw-text-opacity, 1))}.active\:text-gray-700:active{--tw-text-opacity: 1;color:rgb(55 65 81 / var(--tw-text-opacity, 1))}.active\:text-gray-800:active{--tw-text-opacity: 1;color:rgb(31 41 55 / var(--tw-text-opacity, 1))}.disabled\:opacity-25:disabled{opacity:.25}@media(min-width:640px){.sm\:-my-px{margin-top:-1px;margin-bottom:-1px}.sm\:mx-auto{margin-left:auto;margin-right:auto}.sm\:ms-10{margin-inline-start:2.5rem}.sm\:ms-6{margin-inline-start:1.5rem}.sm\:flex{display:flex}.sm\:hidden{display:none}.sm\:w-full{width:100%}.sm\:max-w-2xl{max-width:42rem}.sm\:max-w-lg{max-width:32rem}.sm\:max-w-md{max-width:28rem}.sm\:max-w-sm{max-width:24rem}.sm\:max-w-xl{max-width:36rem}.sm\:flex-1{flex:1 1 0%}.sm\:translate-y-0{--tw-translate-y: 0px;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.sm\:scale-100{--tw-scale-x: 1;--tw-scale-y: 1;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.sm\:scale-95{--tw-scale-x: .95;--tw-scale-y: .95;transform:translate(var(--tw-translate-x),var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.sm\:items-center{align-items:center}.sm\:justify-center{justify-content:center}.sm\:justify-between{justify-content:space-between}.sm\:gap-2{gap:.5rem}.sm\:rounded-lg{border-radius:.5rem}.sm\:p-8{padding:2rem}.sm\:px-0{padding-left:0;padding-right:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}}@media(min-width:1024px){.lg\:-ml-px{margin-left:-1px}.lg\:-mt-\[6\.6rem\]{margin-top:-6.6rem}.lg\:mb-0{margin-bottom:0}.lg\:mb-6{margin-bottom:1.5rem}.lg\:ml-0{margin-left:0}.lg\:block{display:block}.lg\:aspect-auto{aspect-ratio:auto}.lg\:w-\[438px\]{width:438px}.lg\:max-w-4xl{max-width:56rem}.lg\:grow{flex-grow:1}.lg\:flex-row{flex-direction:row}.lg\:justify-center{justify-content:center}.lg\:rounded-r-lg{border-top-right-radius:.5rem;border-bottom-right-radius:.5rem}.lg\:rounded-t-none{border-top-left-radius:0;border-top-right-radius:0}.lg\:rounded-br-none{border-bottom-right-radius:0}.lg\:rounded-tl-lg{border-top-left-radius:.5rem}.lg\:p-20{padding:5rem}.lg\:p-8{padding:2rem}.lg\:px-8{padding-left:2rem;padding-right:2rem}}.ltr\:origin-top-left:where([dir=ltr],[dir=ltr] *){transform-origin:top left}.ltr\:origin-top-right:where([dir=ltr],[dir=ltr] *){transform-origin:top right}.rtl\:origin-top-left:where([dir=rtl],[dir=rtl] *){transform-origin:top left}.rtl\:origin-top-right:where([dir=rtl],[dir=rtl] *){transform-origin:top right}.rtl\:flex-row-reverse:where([dir=rtl],[dir=rtl] *){flex-direction:row-reverse}@media(prefers-color-scheme:dark){.dark\:block{display:block}.dark\:hidden{display:none}.dark\:border-\[\#3E3E3A\]{--tw-border-opacity: 1;border-color:rgb(62 62 58 / var(--tw-border-opacity, 1))}.dark\:border-\[\#eeeeec\]{--tw-border-opacity: 1;border-color:rgb(238 238 236 / var(--tw-border-opacity, 1))}.dark\:border-gray-600{--tw-border-opacity: 1;border-color:rgb(75 85 99 / var(--tw-border-opacity, 1))}.dark\:bg-\[\#0a0a0a\]{--tw-bg-opacity: 1;background-color:rgb(10 10 10 / var(--tw-bg-opacity, 1))}.dark\:bg-\[\#161615\]{--tw-bg-opacity: 1;background-color:rgb(22 22 21 / var(--tw-bg-opacity, 1))}.dark\:bg-\[\#1D0002\]{--tw-bg-opacity: 1;background-color:rgb(29 0 2 / var(--tw-bg-opacity, 1))}.dark\:bg-\[\#3E3E3A\]{--tw-bg-opacity: 1;background-color:rgb(62 62 58 / var(--tw-bg-opacity, 1))}.dark\:bg-\[\#eeeeec\]{--tw-bg-opacity: 1;background-color:rgb(238 238 236 / var(--tw-bg-opacity, 1))}.dark\:bg-gray-700{--tw-bg-opacity: 1;background-color:rgb(55 65 81 / var(--tw-bg-opacity, 1))}.dark\:bg-gray-800{--tw-bg-opacity: 1;background-color:rgb(31 41 55 / var(--tw-bg-opacity, 1))}.dark\:text-\[\#1C1C1A\]{--tw-text-opacity: 1;color:rgb(28 28 26 / var(--tw-text-opacity, 1))}.dark\:text-\[\#A1A09A\]{--tw-text-opacity: 1;color:rgb(161 160 154 / var(--tw-text-opacity, 1))}.dark\:text-\[\#EDEDEC\]{--tw-text-opacity: 1;color:rgb(237 237 236 / var(--tw-text-opacity, 1))}.dark\:text-\[\#F61500\]{--tw-text-opacity: 1;color:rgb(246 21 0 / var(--tw-text-opacity, 1))}.dark\:text-\[\#FF4433\]{--tw-text-opacity: 1;color:rgb(255 68 51 / var(--tw-text-opacity, 1))}.dark\:text-gray-200{--tw-text-opacity: 1;color:rgb(229 231 235 / var(--tw-text-opacity, 1))}.dark\:text-gray-300{--tw-text-opacity: 1;color:rgb(209 213 219 / var(--tw-text-opacity, 1))}.dark\:text-gray-400{--tw-text-opacity: 1;color:rgb(156 163 175 / var(--tw-text-opacity, 1))}.dark\:text-gray-600{--tw-text-opacity: 1;color:rgb(75 85 99 / var(--tw-text-opacity, 1))}.dark\:shadow-\[inset_0px_0px_0px_1px_\#fffaed2d\]{--tw-shadow: inset 0px 0px 0px 1px #fffaed2d;--tw-shadow-colored: inset 0px 0px 0px 1px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)}.dark\:before\:border-\[\#3E3E3A\]:before{content:var(--tw-content);--tw-border-opacity: 1;border-color:rgb(62 62 58 / var(--tw-border-opacity, 1))}.dark\:hover\:border-\[\#3E3E3A\]:hover{--tw-border-opacity: 1;border-color:rgb(62 62 58 / var(--tw-border-opacity, 1))}.dark\:hover\:border-\[\#62605b\]:hover{--tw-border-opacity: 1;border-color:rgb(98 96 91 / var(--tw-border-opacity, 1))}.dark\:hover\:border-white:hover{--tw-border-opacity: 1;border-color:rgb(255 255 255 / var(--tw-border-opacity, 1))}.dark\:hover\:bg-gray-900:hover{--tw-bg-opacity: 1;background-color:rgb(17 24 39 / var(--tw-bg-opacity, 1))}.dark\:hover\:bg-white:hover{--tw-bg-opacity: 1;background-color:rgb(255 255 255 / var(--tw-bg-opacity, 1))}.dark\:hover\:text-gray-200:hover{--tw-text-opacity: 1;color:rgb(229 231 235 / var(--tw-text-opacity, 1))}.dark\:hover\:text-gray-300:hover{--tw-text-opacity: 1;color:rgb(209 213 219 / var(--tw-text-opacity, 1))}.dark\:focus\:border-blue-700:focus{--tw-border-opacity: 1;border-color:rgb(29 78 216 / var(--tw-border-opacity, 1))}.dark\:focus\:border-blue-800:focus{--tw-border-opacity: 1;border-color:rgb(30 64 175 / var(--tw-border-opacity, 1))}.dark\:active\:bg-gray-700:active{--tw-bg-opacity: 1;background-color:rgb(55 65 81 / var(--tw-bg-opacity, 1))}.dark\:active\:text-gray-300:active{--tw-text-opacity: 1;color:rgb(209 213 219 / var(--tw-text-opacity, 1))}}

```

## File: public\build\manifest.json
```json
{
  "resources/css/app.css": {
    "file": "assets/app-CEwZte8_.css",
    "src": "resources/css/app.css",
    "isEntry": true,
    "name": "app",
    "names": [
      "app.css"
    ]
  },
  "resources/js/app.js": {
    "file": "assets/app-CBbTb_k3.js",
    "name": "app",
    "src": "resources/js/app.js",
    "isEntry": true
  }
}
```

## File: public\index.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

## File: resources\css\app.css
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');

@layer base {
  :root {
    --primary: #0a0a0a;
    --primary-muted: #525252;
    --accent: #0066ff;
    --accent-bg: #eff6ff;
  }

  body {
    @apply antialiased bg-zinc-50 text-zinc-900 selection:bg-blue-100 selection:text-blue-900 font-sans;
  }
  
  h1, h2, h3, h4, h5, h6 {
    @apply tracking-tight font-semibold;
  }
}

@layer components {
  .btn {
    @apply inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-all duration-200 rounded-md disabled:opacity-50 disabled:pointer-events-none;
  }
  
  .btn-primary {
    @apply bg-zinc-950 text-white hover:bg-zinc-800 shadow-sm border border-zinc-900;
  }
  
  .btn-secondary {
    @apply bg-white text-zinc-900 border border-zinc-200 hover:bg-zinc-50 shadow-sm;
  }
  
  .btn-ghost {
    @apply bg-transparent text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900;
  }
  
  .card {
    @apply bg-white border border-zinc-200 rounded-xl shadow-[0_1px_3px_rgb(0,0,0,0.02)] overflow-hidden;
  }
}

/* Custom Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

```

## File: resources\js\app.js
```javascript
import './bootstrap';

const toggleModal = (id, shouldShow) => {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.hidden = !shouldShow;
    modal.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
    document.body.classList.toggle('modal-open', shouldShow);
};

document.addEventListener('click', (event) => {
    const openTrigger = event.target.closest('[data-modal-open]');
    const closeTrigger = event.target.closest('[data-modal-close]');

    if (openTrigger) {
        event.preventDefault();
        toggleModal(openTrigger.getAttribute('data-modal-open'), true);
    }

    if (closeTrigger) {
        event.preventDefault();
        toggleModal(closeTrigger.getAttribute('data-modal-close'), false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('.ui-modal').forEach((modal) => {
        if (!modal.hidden) {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    document.body.classList.remove('modal-open');
});

```

## File: resources\js\bootstrap.js
```javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

```

## File: resources\views\auth\confirm-password.blade.php
```php
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

```

## File: resources\views\auth\forgot-password.blade.php
```php
@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
<div class="auth-body" style="justify-content:center;">
    <div style="width:100%;max-width:420px;background:#fff;border:1px solid #e2e2e2;border-radius:2px;padding:48px 44px;">
        <a href="{{ route('login') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:32px;">← Back to Sign In</a>

        <div class="auth-title" style="margin-bottom:8px;">Reset Password</div>
        <p style="font-size:13px;color:#666;margin-bottom:28px;line-height:1.6;">Enter your email and we'll send you a reset link.</p>

        @if(session('status'))
            <div class="flash flash-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input" placeholder="you@company.com" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Send Reset Link</button>
        </form>
    </div>
</div>
@endsection
```

## File: resources\views\auth\login.blade.php
```php
@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div class="mb-10">
    <h2 class="text-3xl font-black text-zinc-950 tracking-tighter">Welcome back.</h2>
    <p class="text-zinc-500 font-medium mt-1">Please enter your details to sign in.</p>
</div>

<form class="space-y-6" action="{{ route('login') }}" method="POST">
    @csrf
    <div>
        <label for="email" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">
            Email Address
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input id="email" name="email" type="email" autocomplete="email" required 
                   value="{{ old('email') }}"
                   class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                   placeholder="name@company.com">
        </div>
        @error('email') <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <label for="password" class="block text-xs font-black text-zinc-500 uppercase tracking-widest">
                Password
            </label>
            <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                Forgot password?
            </a>
        </div>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required 
                   class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                   placeholder="••••••••">
        </div>
        @error('password') <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center">
        <input id="remember_me" name="remember" type="checkbox" 
               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-zinc-300 rounded-md">
        <label for="remember_me" class="ml-3 block text-sm font-medium text-zinc-600">
            Keep me signed in
        </label>
    </div>

    <button type="submit" class="w-full bg-zinc-950 text-white font-black py-4 rounded-xl hover:bg-zinc-800 focus:ring-4 focus:ring-zinc-900/20 transition-all shadow-xl shadow-zinc-900/10">
        Sign in to Platform
    </button>

    <p class="text-center text-sm font-medium text-zinc-500 mt-8">
        Don't have an account? 
        <a href="{{ route('register') }}" class="text-blue-600 font-black hover:underline underline-offset-4 decoration-2">
            Create an account
        </a>
    </p>
</form>
@endsection
```

## File: resources\views\auth\register.blade.php
```php
@extends('layouts.guest')
@section('title', 'Create Account')

@@section('content')
<div class="mb-10">
    <h2 class="text-3xl font-black text-zinc-950 tracking-tighter">Get started.</h2>
    <p class="text-zinc-500 font-medium mt-1">Create your career-ready profile today.</p>
</div>

<form class="space-y-5" action="{{ route('register') }}" method="POST">
    @csrf
    
    <div>
        <label for="name" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Full Name</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                   class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                   placeholder="Jane Smith">
        </div>
        @error('name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Email address</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                   class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                   placeholder="you@example.com">
        </div>
        @error('email') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="password" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" name="password" type="password" required
                       class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                       placeholder="••••••••">
            </div>
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Confirm</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                       placeholder="••••••••">
            </div>
        </div>
        @error('password') <p class="mt-1 col-span-2 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Platform Role</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="briefcase" class="w-4 h-4"></i>
            </div>
            <select id="role" name="role" required
                    class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                <option value="">Select your role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name === 'mentor' ? 'Team Lead / Mentor' : ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('role') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <div id="technology-wrapper" style="display:none;">
        <label for="technology_id" class="block text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Technology Focus</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400">
                <i data-lucide="code-2" class="w-4 h-4"></i>
            </div>
            <select id="technology_id" name="technology_id"
                    class="block w-full pl-11 pr-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                <option value="">Select technology</option>
                @foreach ($technologies as $t)
                    <option value="{{ $t->id }}" {{ old('technology_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('technology_id') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="w-full bg-zinc-950 text-white font-black py-4 rounded-xl hover:bg-zinc-800 focus:ring-4 focus:ring-zinc-900/20 transition-all shadow-xl shadow-zinc-900/10 mt-4">
        Create Account
    </button>

    <p class="text-center text-sm font-medium text-zinc-500 mt-8">
        Already have an account? 
        <a href="{{ route('login') }}" class="text-blue-600 font-black hover:underline underline-offset-4 decoration-2">
            Sign in
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
        } else {
            techWrapper.style.display = 'none';
        }
    }
    toggle();
    roleSelect.addEventListener('change', toggle);
    
    // Refresh icons for dynamic content
    if (window.lucide) lucide.createIcons();
});
</script>
@endsection
ggle);
});
</script>
@endsection
```

## File: resources\views\auth\reset-password.blade.php
```php
@extends('layouts.guest')
@section('title', 'Set New Password')

@section('content')
<div class="auth-body" style="justify-content:center;">
    <div style="width:100%;max-width:420px;background:#fff;border:1px solid #e2e2e2;border-radius:2px;padding:48px 44px;">
        <a href="{{ route('login') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:32px;">&larr; Back to Sign In</a>

        <div class="auth-title" style="margin-bottom:8px;">Create New Password</div>
        <p style="font-size:13px;color:#666;margin-bottom:28px;line-height:1.6;">
            Choose a new password for your account and confirm it below.
        </p>

        @if(session('status'))
            <div class="flash flash-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    class="form-input"
                    placeholder="you@company.com"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                >
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                >
                @error('password_confirmation') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Reset Password</button>
        </form>
    </div>
</div>
@endsection

```

## File: resources\views\auth\verify-email.blade.php
```php
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

```

## File: resources\views\components\application-logo.blade.php
```php
<svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <path d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.285 305.37 80.075C305.29 79.935 305.17 79.815 305.07 79.685C304.94 79.515 304.83 79.325 304.68 79.175C304.55 79.045 304.39 78.955 304.25 78.845C304.09 78.715 303.95 78.575 303.77 78.475L251.32 48.275C249.97 47.495 248.31 47.495 246.96 48.275L194.51 78.475C194.33 78.575 194.19 78.725 194.03 78.845C193.89 78.955 193.73 79.045 193.6 79.175C193.45 79.325 193.34 79.515 193.21 79.685C193.11 79.815 192.99 79.935 192.91 80.075C192.79 80.285 192.71 80.525 192.63 80.755C192.58 80.875 192.51 80.995 192.48 81.125C192.38 81.495 192.33 81.875 192.33 82.265V139.625L148.62 164.795V52.575C148.62 52.185 148.57 51.805 148.47 51.435C148.44 51.305 148.36 51.195 148.32 51.065C148.23 50.835 148.16 50.595 148.04 50.385C147.96 50.245 147.84 50.125 147.74 49.995C147.61 49.825 147.5 49.635 147.35 49.485C147.22 49.355 147.06 49.265 146.92 49.155C146.76 49.025 146.62 48.885 146.44 48.785L93.99 18.585C92.64 17.805 90.98 17.805 89.63 18.585L37.18 48.785C37 48.885 36.86 49.035 36.7 49.155C36.56 49.265 36.4 49.355 36.27 49.485C36.12 49.635 36.01 49.825 35.88 49.995C35.78 50.125 35.66 50.245 35.58 50.385C35.46 50.595 35.38 50.835 35.3 51.065C35.25 51.185 35.18 51.305 35.15 51.435C35.05 51.805 35 52.185 35 52.575V232.235C35 233.795 35.84 235.245 37.19 236.025L142.1 296.425C142.33 296.555 142.58 296.635 142.82 296.725C142.93 296.765 143.04 296.835 143.16 296.865C143.53 296.965 143.9 297.015 144.28 297.015C144.66 297.015 145.03 296.965 145.4 296.865C145.5 296.835 145.59 296.775 145.69 296.745C145.95 296.655 146.21 296.565 146.45 296.435L251.36 236.035C252.72 235.255 253.55 233.815 253.55 232.245V174.885L303.81 145.945C305.17 145.165 306 143.725 306 142.155V82.265C305.95 81.875 305.89 81.495 305.8 81.125ZM144.2 227.205L100.57 202.515L146.39 176.135L196.66 147.195L240.33 172.335L208.29 190.625L144.2 227.205ZM244.75 114.995V164.795L226.39 154.225L201.03 139.625V89.825L219.39 100.395L244.75 114.995ZM249.12 57.105L292.81 82.265L249.12 107.425L205.43 82.265L249.12 57.105ZM114.49 184.425L96.13 194.995V85.305L121.49 70.705L139.85 60.135V169.815L114.49 184.425ZM91.76 27.425L135.45 52.585L91.76 77.745L48.07 52.585L91.76 27.425ZM43.67 60.135L62.03 70.705L87.39 85.305V202.545V202.555V202.565C87.39 202.735 87.44 202.895 87.46 203.055C87.49 203.265 87.49 203.485 87.55 203.695V203.705C87.6 203.875 87.69 204.035 87.76 204.195C87.84 204.375 87.89 204.575 87.99 204.745C87.99 204.745 87.99 204.755 88 204.755C88.09 204.905 88.22 205.035 88.33 205.175C88.45 205.335 88.55 205.495 88.69 205.635L88.7 205.645C88.82 205.765 88.98 205.855 89.12 205.965C89.28 206.085 89.42 206.225 89.59 206.325C89.6 206.325 89.6 206.325 89.61 206.335C89.62 206.335 89.62 206.345 89.63 206.345L139.87 234.775V285.065L43.67 229.705V60.135ZM244.75 229.705L148.58 285.075V234.775L219.8 194.115L244.75 179.875V229.705ZM297.2 139.625L253.49 164.795V114.995L278.85 100.395L297.21 89.825V139.625H297.2Z"/>
</svg>

```

## File: resources\views\components\auth-session-status.blade.php
```php
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif

```

## File: resources\views\components\badge.blade.php
```php
@props(['status'])
<span class="badge badge-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>

```

## File: resources\views\components\card.blade.php
```php
@props([
    'title' => null,
    'subtitle' => null
])

<section class="ui-card ui-card--md">

    @if($title)
        <div class="ui-card-header">
            <h3 class="ui-card-title">
                {{ $title }}
            </h3>

            @if($subtitle)
                <p class="ui-card-subtitle">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}

</section>

```

## File: resources\views\components\danger-button.blade.php
```php
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

```

## File: resources\views\components\dropdown-link.blade.php
```php
<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out']) }}>{{ $slot }}</a>

```

## File: resources\views\components\dropdown.blade.php
```php
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

```

## File: resources\views\components\icons\academic-cap.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813c-.999-.248-1.123-1.395-.123-1.637l8.546-2.071c.907-.22 1.847-.22 2.755 0l8.546 2.071c1.001.242.877 1.389-.123 1.637a50.638 50.638 0 0 0-2.658.813m-15.482 0A50.71 50.71 0 0 1 12 11.233c2.43 0 4.812.172 7.142.505m-15.482 0C4.198 13.074 4 14.545 4 16.035c0 1.388.169 2.736.49 4.026m15.02-10.06c.212 1.474.41 2.945.41 4.435 0 1.388-.169 2.736-.49 4.026m-14.53-4.026A18.865 18.865 0 0 0 12 15c2.327 0 4.54-.42 6.577-1.187m-13.077 0V21m13.077-7.5V21" />
</svg>

```

## File: resources\views\components\icons\book-open.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25V20.25" />
</svg>

```

## File: resources\views\components\icons\check-circle.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
</svg>

```

## File: resources\views\components\icons\clipboard-list.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18 4.5h-2.25a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 15.75 18Zm-3.75 0h.008v.008H12V18Z" />
</svg>

```

## File: resources\views\components\icons\envelope.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
</svg>

```

## File: resources\views\components\icons\home.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
</svg>

```

## File: resources\views\components\icons\map.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-1.5V21m3.75-13.5V15a2.25 2.25 0 0 1-2.25 2.25H16.5a2.25 2.25 0 0 1-2.25-2.25V6.75a2.25 2.25 0 0 1 2.25-2.25h1.5a2.25 2.25 0 0 1 2.25 2.25Zm-12 0V15a2.25 2.25 0 0 0 2.25 2.25H5.25A2.25 2.25 0 0 0 7.5 15V6.75a2.25 2.25 0 0 0-2.25-2.25h-1.5a2.25 2.25 0 0 0-2.25 2.25Z" />
</svg>

```

## File: resources\views\components\icons\sparkles.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 22.25l-.394-1.683a2.25 2.25 0 0 0-1.423-1.423L13 18.75l1.683-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.683.394 1.683a2.25 2.25 0 0 0 1.423 1.423l1.683.394-1.683.394a2.25 2.25 0 0 0-1.423 1.423Z" />
</svg>

```

## File: resources\views\components\icons\user-plus.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
</svg>

```

## File: resources\views\components\icons\users.blade.php
```php
@props(['size' => 20, 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: {{ $size }}px; height: {{ $size }}px;" class="{{ $class }}">
    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
</svg>

```

## File: resources\views\components\input-error.blade.php
```php
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif

```

## File: resources\views\components\input-label.blade.php
```php
@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>

```

## File: resources\views\components\layout\sidebar.blade.php
```php
@props([
    'role' => 'intern',
])

@php
    $nav = match($role) {
        'hr' => [
            ['label' => 'Dashboard', 'route' => 'hr.dashboard', 'icon' => 'home'],
            ['label' => 'User Approvals', 'route' => 'hr.users', 'icon' => 'user-plus'],
            ['label' => 'Assignment Map', 'route' => 'hr.intern.mentor.list', 'icon' => 'map'],
            ['label' => 'Progress tracking', 'route' => 'hr.intern.progress', 'icon' => 'chart-bar'],
        ],
        'mentor' => [
            ['label' => 'Dashboard', 'route' => 'mentor.dashboard', 'icon' => 'home'],
            ['label' => 'My Interns', 'route' => 'mentor.interns.index', 'icon' => 'users'],
            ['label' => 'Task Management', 'route' => 'mentor.topics.index', 'icon' => 'clipboard-list'],
            ['label' => 'Review Queue', 'route' => 'mentor.reviews.index', 'icon' => 'check-circle'],
        ],
        default => [
            ['label' => 'Overview', 'route' => 'intern.dashboard', 'icon' => 'home'],
            ['label' => 'Coursework', 'route' => 'intern.tasks', 'icon' => 'book-open'],
            ['label' => 'Performance', 'route' => 'intern.performance', 'icon' => 'academic-cap'],
        ],
    };
@endphp

<aside class="w-64 bg-white border-r border-zinc-200 flex flex-col fixed inset-y-0 left-0 z-50 transition-transform lg:translate-x-0 -translate-x-full" id="sidebar">
    <div class="p-6 flex items-center gap-3">
        <div class="h-8 w-8 bg-zinc-900 rounded-lg flex items-center justify-center text-white font-bold">A</div>
        <span class="font-semibold text-zinc-900 tracking-tight">AI-IPMS</span>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto pt-4">
        @foreach($nav as $item)
            @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
            <a href="{{ route($item['route']) }}" 
               @class([
                   'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors group',
                   'bg-zinc-100 text-zinc-900' => $isActive,
                   'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' => !$isActive,
               ])>
                <div @class(['text-zinc-400 group-hover:text-zinc-900', 'text-zinc-900' => $isActive])>
                    @include('components.icons.' . $item['icon'], ['size' => 18])
                </div>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="p-4 border-t border-zinc-100">
        <div class="flex items-center gap-3 px-3 py-3 rounded-lg bg-zinc-50 border border-zinc-200/50">
            <div class="h-9 w-9 rounded-full bg-zinc-900 flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-zinc-900 truncate">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider">{{ $role }}</div>
            </div>
        </div>
    </div>
</aside>

```

## File: resources\views\components\layout\topbar.blade.php
```php
@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

<header class="h-16 border-b border-zinc-200 bg-white/80 backdrop-blur-md sticky top-0 z-40 flex items-center justify-between px-6 lg:px-10">
    <div class="flex flex-col">
        <h1 class="text-lg font-bold text-zinc-900 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-zinc-100 rounded-full text-zinc-400 group cursor-pointer transition-colors hover:bg-zinc-200 border border-transparent hover:border-zinc-300">
            @include('components.icons.search', ['size' => 14])
            <span class="text-xs font-medium text-zinc-500">Search anything...</span>
            <span class="ml-4 text-[10px] font-bold text-zinc-400 bg-zinc-200 px-1.5 py-0.5 rounded border border-zinc-300">⌘K</span>
        </div>

        <div class="flex items-center gap-2">
            <button class="p-2 text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors relative">
                @include('components.icons.bell', ['size' => 20])
                <span class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-blue-500 border-2 border-white"></span>
            </button>

            <div class="h-8 w-px bg-zinc-200 mx-1"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors group" title="Sign Out">
                    <span class="group-hover:translate-x-0.5 transition-transform inline-block">
                        @include('components.icons.logout', ['size' => 20])
                    </span>
                </button>
            </form>
        </div>
    </div>
</header>

```

## File: resources\views\components\modal.blade.php
```php
@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$sizeClass = [
    'sm' => 'ui-modal-dialog--sm',
    'md' => 'ui-modal-dialog--md',
    'lg' => 'ui-modal-dialog--lg',
    'xl' => 'ui-modal-dialog--xl',
    '2xl' => 'ui-modal-dialog--2xl',
][$maxWidth] ?? 'ui-modal-dialog--lg';
@endphp

<div id="{{ $name }}" class="ui-modal" @unless($show) hidden @endunless aria-hidden="{{ $show ? 'false' : 'true' }}">
    <div class="ui-modal-backdrop" data-modal-close="{{ $name }}"></div>

    <div class="ui-modal-dialog {{ $sizeClass }}" role="dialog" aria-modal="true">
        {{ $slot }}
    </div>
</div>

```

## File: resources\views\components\nav-link.blade.php
```php
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

```

## File: resources\views\components\primary-button.blade.php
```php
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-btn ui-btn--primary ui-btn--md']) }}>
    {{ $slot }}
</button>

```

## File: resources\views\components\responsive-nav-link.blade.php
```php
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

```

## File: resources\views\components\secondary-button.blade.php
```php
<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-btn ui-btn--secondary ui-btn--md']) }}>
    {{ $slot }}
</button>

```

## File: resources\views\components\sidebar.blade.php
```php
@php $role = auth()->user()->role->name ?? ''; @endphp

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">AI Internship</div>
        <div class="sidebar-brand-sub">{{ ucfirst($role) }} Panel</div>
    </div>

    <nav class="sidebar-nav">
        @if($role === 'hr')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('hr.dashboard') }}"
               class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('hr.users') }}"
               class="nav-link {{ request()->routeIs('hr.users') ? 'active' : '' }}">
                <span class="nav-dot"></span> Approvals
            </a>
            <a href="{{ route('hr.mentor.assignments') }}"
               class="nav-link {{ request()->routeIs('hr.mentor.assignments') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assign Mentors
            </a>
            <a href="{{ route('hr.intern.mentor.list') }}"
               class="nav-link {{ request()->routeIs('hr.intern.mentor.list') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern-Mentor Map
            </a>
            <a href="{{ route('hr.intern.progress') }}"
               class="nav-link {{ request()->routeIs('hr.intern.progress*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Intern Progress
            </a>
        @elseif($role === 'mentor')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('mentor.dashboard') }}"
               class="nav-link {{ request()->routeIs('mentor.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Manage</div>
            <a href="{{ route('mentor.interns') }}"
               class="nav-link {{ request()->routeIs('mentor.interns*') ? 'active' : '' }}">
                <span class="nav-dot"></span> My Interns
            </a>
            <a href="{{ route('mentor.tasks.index') }}"
               class="nav-link {{ request()->routeIs('mentor.tasks.*', 'mentor.topics.*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Tasks
            </a>
            <a href="{{ route('mentor.topics.assign') }}"
               class="nav-link {{ request()->routeIs('mentor.topics.assign*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assign Task
            </a>

            <div class="nav-section-label">Review</div>
            <a href="{{ route('mentor.assignments') }}"
               class="nav-link {{ request()->routeIs('mentor.assignments') ? 'active' : '' }}">
                <span class="nav-dot"></span> Assignments
            </a>
            <a href="{{ route('mentor.reviews.index') }}"
               class="nav-link {{ request()->routeIs('mentor.reviews.*', 'mentor.submissions.*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Reviews
            </a>
        @elseif($role === 'intern')
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('intern.dashboard') }}"
               class="nav-link {{ request()->routeIs('intern.dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span> Dashboard
            </a>

            <div class="nav-section-label">Work</div>
            <a href="{{ route('intern.tasks') }}"
               class="nav-link {{ request()->routeIs('intern.tasks', 'intern.topic', 'intern.exam', 'intern.exercise') ? 'active' : '' }}">
                <span class="nav-dot"></span> My Tasks
            </a>
            <a href="{{ route('intern.submissions') }}"
               class="nav-link {{ request()->routeIs('intern.submissions*') ? 'active' : '' }}">
                <span class="nav-dot"></span> Reviews
            </a>
            <a href="{{ route('intern.attendance') }}"
               class="nav-link {{ request()->routeIs('intern.attendance') ? 'active' : '' }}">
                <span class="nav-dot"></span> Attendance
            </a>
            <a href="{{ route('intern.performance') }}"
               class="nav-link {{ request()->routeIs('intern.performance') ? 'active' : '' }}">
                <span class="nav-dot"></span> Performance
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user-label">Signed in as</div>
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <a href="{{ route('profile.edit') }}" class="sidebar-profile-link">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Sign out</button>
        </form>
    </div>
</aside>

```

## File: resources\views\components\stat-card.blade.php
```php
@props(['label', 'value', 'accent' => ''])

<div class="stat-cell">
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value {{ $accent }}">{{ $value }}</div>
</div>
```

## File: resources\views\components\text-input.blade.php
```php
@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>

```

## File: resources\views\components\toast.blade.php
```php
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showToast(icon, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
</script>
@if(session('success'))
    <script>showToast('success', "{{ addslashes(session('success')) }}");</script>
@endif
@if(session('error'))
    <script>showToast('error', "{{ addslashes(session('error')) }}");</script>
@endif
@if($errors->any())
    <script>showToast('error', "{{ addslashes($errors->first()) }}");</script>
@endif
```

## File: resources\views\components\ui\badge.blade.php
```php
@props([
    'type' => 'neutral',
    'dot' => false,
])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'error', 'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        default => 'bg-zinc-50 text-zinc-600 ring-zinc-500/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset $classes"]) }}>
    @if($dot)
        <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3" />
        </svg>
    @endif
    {{ $slot }}
</span>

```

## File: resources\views\components\ui\button.blade.php
```php
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = 'ui-btn ui-btn--' . $variant . ' ui-btn--' . $size;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </button>
@endif

```

## File: resources\views\components\ui\card.blade.php
```php
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden group']) }}>
    @if($title || isset($header))
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($headerAction))
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif

    <div @class(['p-6' => $padding])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-3 bg-zinc-50 border-t border-zinc-100">
            {{ $footer }}
        </div>
    @endif
</div>

```

## File: resources\views\components\ui\flash.blade.php
```php
@if (session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif

@if (session('warning'))
    <div class="flash flash-warning">{{ session('warning') }}</div>
@endif

@if ($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

```

## File: resources\views\components\ui\input.blade.php
```php
@props([
    'label' => null,
    'value' => '',
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <input {{ $attributes->class(['form-input'])->merge(['value' => $value]) }}>
</label>

```

## File: resources\views\components\ui\modal.blade.php
```php
@props([
    'id',
    'title',
    'subtitle' => null,
])

<div id="{{ $id }}" class="ui-modal" hidden aria-hidden="true">
    <div class="ui-modal-backdrop" data-modal-close="{{ $id }}"></div>
    <div class="ui-modal-dialog ui-modal-dialog--lg" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="ui-modal-head">
            <div>
                <h2 id="{{ $id }}-title" class="ui-modal-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="ui-modal-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" class="ui-modal-close" data-modal-close="{{ $id }}">�</button>
        </div>
        <div class="ui-modal-body">
            {{ $slot }}
        </div>
    </div>
</div>

```

## File: resources\views\components\ui\select.blade.php
```php
@props([
    'label' => null,
    'options' => [],
    'selected' => null,
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <select {{ $attributes->class(['form-select']) }}>
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</label>

```

## File: resources\views\components\ui\stat-card.blade.php
```php
@props([
    'label' => '',
    'value' => '',
    'trend' => null,
    'trendUp' => true,
    'icon' => null,
])

<div class="p-6 bg-white border border-zinc-200 rounded-xl shadow-[0_1px_2px_rgba(0,0,0,0.02)] flex flex-col group hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider">{{ $label }}</span>
        @if($icon)
            <div class="p-2 bg-zinc-50 rounded-lg text-zinc-400 group-hover:text-zinc-900 transition-colors">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-4 flex flex-col">
        <span class="text-3xl font-bold tracking-tight text-zinc-900">{{ $value }}</span>
        @if($trend)
            <div @class([
                'flex items-center gap-1.5 mt-2 text-xs font-semibold',
                'text-emerald-600' => $trendUp,
                'text-red-600' => !$trendUp,
            ])>
                @if($trendUp)
                    @include('components.icons.arrow-up-right', ['size' => 12])
                @else
                    @include('components.icons.arrow-down-right', ['size' => 12])
                @endif
                {{ $trend }}
            </div>
        @endif
    </div>
</div>

```

## File: resources\views\components\ui\table.blade.php
```php
<div {{ $attributes->class(['table-card']) }}>
    <table class="data-table">
        {{ $slot }}
    </table>
</div>

```

## File: resources\views\components\ui\textarea.blade.php
```php
@props([
    'label' => null,
    'value' => '',
])

<label class="form-group">
    @if($label)
        <span class="form-label">{{ $label }}</span>
    @endif
    <textarea {{ $attributes->class(['form-textarea']) }}>{{ $value }}</textarea>
</label>

```

## File: resources\views\dashboard.blade.php
```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

```

## File: resources\views\hr\approvals.blade.php
```php
@extends('layouts.hr')
@section('title', 'User Approvals')

@section('content')
<div class="mb-10">
    <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">Access Control</h2>
    <p class="text-sm text-zinc-500 mt-1">Review and approve new user accounts to grant platform access.</p>
</div>

<x-ui.card>
    <div class="overflow-x-auto -mx-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-100">
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase">User Detail</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase">Role</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase text-center">Status</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($pendingUsers as $user)
                    <tr class="hover:bg-zinc-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 font-bold text-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-zinc-900 tracking-tight">{{ $user->name }}</div>
                                    <div class="text-[11px] text-zinc-500 font-medium">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold text-zinc-600 uppercase tracking-wider px-2 py-1 rounded bg-zinc-100">
                                {{ $user->roles->first()?->name ?? 'None' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-ui.badge type="warning" dot="true">Pending Review</x-ui.badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <form method="POST" action="{{ route('hr.users.approve', $user->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('hr.users.reject', $user->id) }}" onsubmit="return confirm('Reject this account request?')">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="text-zinc-300 mb-2">
                                @include('components.icons.check-circle', ['size' => 48, 'class' => 'mx-auto'])
                            </div>
                            <p class="text-sm text-zinc-600 font-medium">All accounts have been reviewed.</p>
                            <p class="text-xs text-zinc-400 mt-1">New registration requests will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pendingUsers->hasPages())
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 mt-0">
            {{ $pendingUsers->links() }}
        </div>
    @endif
</x-ui.card>
@endsection

```

## File: resources\views\hr\attendance.blade.php
```php
@extends('layouts.app')
@section('title', 'Attendance Monitoring')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <h1 class="page-shell-title">Attendance Monitoring</h1>
        <p class="page-shell-subtitle">Track intern activity, login periods, and network access points.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('hr.dashboard')" variant="secondary">Back to Dashboard</x-ui.button>
    </div>
</div>

<div class="mt-8 space-y-8">
    {{-- Filters --}}
    <div class="card p-6 bg-white border border-slate-200">
        <form action="{{ route('hr.attendance') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Filter by Intern</label>
                <select name="user_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm p-2.5 focus:ring-blue-500 outline-none">
                    <option value="">All Interns</option>
                    @foreach($interns as $intern)
                        <option value="{{ $intern->id }}" {{ request('user_id') == $intern->id ? 'selected' : '' }}>{{ $intern->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Select Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg text-sm p-2.5 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">Filter Logs</x-ui.button>
                <x-ui.button :href="route('hr.attendance')" variant="secondary">Reset</x-ui.button>
            </div>
        </form>
    </div>

    {{-- Attendance Table --}}
    <div class="table-card ui-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Intern</th>
                    <th>Date</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Duration</th>
                    <th>IP Address</th>
                    <th style="text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>
                            <div class="cell-name font-bold">{{ $attendance->user->name }}</div>
                            <div class="text-[10px] opacity-50">{{ $attendance->user->email }}</div>
                        </td>
                        <td class="text-xs font-semibold">{{ $attendance->date->format('M d, Y') }}</td>
                        <td class="text-xs">{{ $attendance->login_time->format('h:i A') }}</td>
                        <td class="text-xs">{{ $attendance->logout_time ? $attendance->logout_time->format('h:i A') : '--:--' }}</td>
                        <td class="cell-mono text-[10px] font-bold">
                            @if($attendance->total_seconds)
                                {{ floor($attendance->total_seconds / 3600) }}h {{ floor(($attendance->total_seconds % 3600) / 60) }}m
                            @else
                                Active Now
                            @endif
                        </td>
                        <td class="cell-mono text-[10px]">
                            <span class="px-2 py-0.5 rounded {{ $attendance->ip_address === env('OFFICE_IP') ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $attendance->ip_address }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            @if($attendance->logout_time)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded uppercase">Logged Out</span>
                            @else
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded uppercase animate-pulse">Session Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state py-12">No attendance records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $attendances->links() }}
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\hr\dashboard.blade.php
```php
@extends('layouts.app')
@section('title', 'HR Command Center')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
    <div>
        <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight">HR Command Center</h2>
        <p class="text-sm text-zinc-500 mt-1 font-medium">Oversee internship operations, monitor attendance, and manage recruitment.</p>
    </div>
    <div class="flex items-center gap-3">
        <x-ui.button :href="route('hr.users')" variant="primary" class="shadow-lg shadow-blue-100">
            <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> Review Applications
        </x-ui.button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Interns</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $totalInterns }}</div>
            <div class="text-xs text-zinc-500 font-medium">Active in System</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mentors</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $totalMentors }}</div>
            <div class="text-xs text-zinc-500 font-medium">Assigned Guides</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Attendance</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $todayAttendance }}</div>
            <div class="text-xs text-zinc-500 font-medium">Clocked in Today</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pending</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $pendingUsers }}</div>
            <div class="text-xs text-zinc-500 font-medium">Awaiting Approval</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        {{-- Recent Attendance --}}
        <x-ui.card title="Real-time Attendance" subtitle="Latest login activity across the office network.">
            <div class="table-card border-none shadow-none mt-4">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Intern</th>
                            <th>Login Time</th>
                            <th>IP Address</th>
                            <th style="text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogins as $login)
                            <tr>
                                <td>
                                    <div class="cell-name font-bold">{{ $login->user->name }}</div>
                                    <div class="text-[10px] opacity-50">{{ $login->user->email }}</div>
                                </td>
                                <td class="text-xs font-medium">{{ $login->login_time->format('h:i A') }}</td>
                                <td class="cell-mono text-[10px]">{{ $login->ip_address }}</td>
                                <td style="text-align: right;">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded uppercase">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">No login activity recorded today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                <x-ui.button :href="route('hr.attendance')" variant="secondary" size="sm">
                    View Full Logs <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-2"></i>
                </x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <aside class="space-y-8">
        {{-- Quick Stats --}}
        <x-ui.card title="Platform Health" subtitle="AI & Curriculum overview">
            <div class="space-y-6">
                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-400 mb-2">
                        <span>Topics Created</span>
                        <span class="text-slate-900">{{ $topics }}</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500" style="width: 60%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-400 mb-2">
                        <span>AI Evaluations</span>
                        <span class="text-slate-900">{{ $evaluated }}</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Promotion Box --}}
        <div class="card p-6 bg-zinc-900 border-none relative overflow-hidden text-white">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i data-lucide="brain-circuit" class="w-24 h-24"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-3">AI Engine Status</h3>
                <p class="text-xs text-zinc-400 leading-relaxed mb-6">Evaluation engine is processing submissions using llama-3.1-8b-instant. Average latency: 1.2s</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-500">Operational</span>
                </div>
            </div>
        </div>
    </aside>
</div>

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\hr\intern_mentor_list.blade.php
```php
@extends('layouts.hr')
@section('title', 'Assignment Map')

@section('content')
<div class="mb-10 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">Assignment Map</h2>
        <p class="text-sm text-zinc-500 mt-1">Review active intern-mentor relationships and platform distribution.</p>
    </div>
    <a href="{{ route('hr.mentor.assignments') }}" class="btn btn-primary">
        Assign Mentors
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <x-ui.stat-card 
        label="Total Assignments" 
        :value="$mappings->total()" 
        trend="Active pairs"
        :trendUp="true"
        :icon="view('components.icons.map', ['size' => 18])"
    />
    <x-ui.stat-card 
        label="Mentorship Load" 
        :value="round($mappings->unique('mentor_id')->count() > 0 ? $mappings->total() / $mappings->unique('mentor_id')->count() : 0, 1)" 
        trend="Avg Interns per Mentor"
        :trendUp="false"
        :icon="view('components.icons.users', ['size' => 18])"
    />
</div>

<x-ui.card>
    <div class="overflow-x-auto -mx-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-100">
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase">Intern</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase">Assigned Mentor</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase text-center">Duration</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-zinc-400 uppercase tracking-widest uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($mappings as $map)
                    <tr class="hover:bg-zinc-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 font-bold text-sm">
                                    {{ substr($map->intern->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-zinc-900 tracking-tight">{{ $map->intern->name }}</div>
                                    <div class="text-[11px] text-zinc-500 font-medium lowercase italic">{{ $map->intern->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-lg bg-zinc-900 text-white flex items-center justify-center text-[10px] font-bold uppercase">
                                    {{ substr($map->mentor->name, 0, 1) }}
                                </div>
                                <span class="text-xs font-semibold text-zinc-700 tracking-tight">
                                    {{ $map->mentor->name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-xs font-bold text-zinc-600 tabular-nums">
                                {{ $map->created_at->diffInDays() }} days
                            </div>
                            <div class="text-[10px] text-zinc-400 font-medium uppercase tracking-tighter">Active since {{ $map->created_at->format('M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('hr.intern.progress.show', $map->intern_id) }}" class="btn btn-secondary btn-sm">
                                    View Progress
                                </a>
                                {{-- Optionally add un-map/reassign action here --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="text-zinc-300 mb-2">
                                @include('components.icons.map', ['size' => 48, 'class' => 'mx-auto'])
                            </div>
                            <p class="text-sm text-zinc-600 font-medium">No intern-mentor relationships mapped yet.</p>
                            <p class="text-xs text-zinc-400 mt-1">Start by assigning mentors to your interns.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($mappings->hasPages())
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 mt-0">
            {{ $mappings->links() }}
        </div>
    @endif
</x-ui.card>
@endsection
```

## File: resources\views\hr\intern_progress.blade.php
```php
@extends('layouts.hr')
@section('title', 'Intern Progress')

@section('content')
<div class="mb-10 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">Progress Tracking</h2>
        <p class="text-sm text-zinc-500 mt-1">Detailed assignment status and performance monitoring for all interns.</p>
    </div>
    <a href="{{ route('hr.intern.mentor.list') }}" class="btn btn-secondary">
        <div class="mr-2">
            @include('components.icons.map', ['size' => 14])
        </div>
        View Assignment Map
    </a>
</div>

<x-ui.card>
    <div class="flex items-start gap-6">
        <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            @include('components.icons.sparkles', ['size' => 24])
        </div>
        <div class="flex-1">
            <h3 class="text-base font-bold text-zinc-900 tracking-tight">Accessing Detailed Reports</h3>
            <p class="text-sm text-zinc-600 leading-relaxed mt-2">
                Individual progress reports are linked directly to active mentorship pairings. To view a detailed status report for an intern, please:
            </p>
            <ol class="mt-4 space-y-3">
                <li class="flex items-center gap-3 text-sm text-zinc-600">
                    <span class="h-5 w-5 rounded-full bg-zinc-900 text-white text-[10px] font-bold flex items-center justify-center">1</span>
                    Navigate to the <a href="{{ route('hr.intern.mentor.list') }}" class="font-bold text-blue-600 hover:text-blue-500">Assignment Map</a>.
                </li>
                <li class="flex items-center gap-3 text-sm text-zinc-600">
                    <span class="h-5 w-5 rounded-full bg-zinc-900 text-white text-[10px] font-bold flex items-center justify-center">2</span>
                    Locate the intern in the management table.
                </li>
                <li class="flex items-center gap-3 text-sm text-zinc-600">
                    <span class="h-5 w-5 rounded-full bg-zinc-900 text-white text-[10px] font-bold flex items-center justify-center">3</span>
                    Click the <strong>View Progress</strong> action button to open their dashboard.
                </li>
            </ol>
            <div class="mt-6 pt-6 border-t border-zinc-100 flex items-center gap-2">
                <div class="text-emerald-500">
                    @include('components.icons.check-circle', ['size' => 16])
                </div>
                <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-widest">Monitor submissions, evaluations, and mentor feedback in one place.</p>
            </div>
        </div>
    </div>
</x-ui.card>
@endsection
```

## File: resources\views\hr\intern_progress_show.blade.php
```php
@extends('layouts.hr')
@section('title', 'Intern Progress — ' . $intern->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">{{ $intern->name }}</h1>
        <p class="page-subtitle">{{ $intern->email }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('hr.intern.mentor.list') }}" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;" class="mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Map
        </a>
    </div>
</header>

<div class="stats-grid mb-8">
    <x-ui.stat-card 
        label="Assigned Mentor" 
        :value="$mentorAssignment?->mentor?->name ?? 'None'" 
        trend="Primary Support"
        :trendUp="true"
    />
    <x-ui.stat-card 
        label="Topics" 
        :value="$topicAssignments->count()" 
        trend="Total Assignments"
        :trendUp="true"
    />
    <x-ui.stat-card 
        label="Submissions" 
        :value="$totalSubmissions" 
        trend="Evaluated: $reviewedCount"
        :trendUp="true"
    />
</div>

<x-ui.card title="Topic Assignments" subtitle="Historical breakdown of all issued tasks and their results." padding="false">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Result</th>
                    <th>Deadline</th>
                    <th class="text-right">Assigned On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topicAssignments as $asgn)
                    @php
                        $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast() 
                            && !in_array($asgn->status, ['submitted', 'evaluated']);
                    @endphp
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900">{{ $asgn->topic->title ?? 'Untitled Topic' }}</div>
                        </td>
                        <td>
                            <x-ui.badge :type="$asgn->status === 'evaluated' ? 'success' : ($asgn->status === 'submitted' ? 'info' : 'warning')" dot="true">
                                {{ ucfirst(str_replace('_', ' ', $asgn->status)) }}
                            </x-ui.badge>
                        </td>
                        <td>
                            @if($asgn->grade)
                                <div @class([
                                    'inline-flex items-center justify-center w-7 h-7 rounded text-xs font-bold font-mono border',
                                    'bg-success-bg text-success border-emerald-100' => in_array($asgn->grade, ['A', 'B']),
                                    'bg-warning-bg text-warning border-amber-100' => in_array($asgn->grade, ['C', 'D']),
                                    'bg-error-bg text-error border-red-100' => $asgn->grade === 'F',
                                ])>
                                    {{ $asgn->grade }}
                                </div>
                            @else
                                <span class="text-gray-300 font-mono">—</span>
                            @endif
                        </td>
                        <td>
                            <div @class(['text-xs font-mono', 'text-error font-semibold' => $isOverdue, 'text-gray-500' => !$isOverdue])>
                                {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M, Y') }}
                                @if($isOverdue)
                                    <span class="block text-[10px] uppercase tracking-wider mt-0.5">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="text-xs text-gray-400 font-mono">
                                {{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M, Y') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state py-12 text-center text-gray-400">
                                No topics have been assigned to this intern yet.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection

```

## File: resources\views\hr\mentor_assignments.blade.php
```php
@extends('layouts.app')
@section('title', 'Assign Mentors')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Assign Mentors</div>
        <div class="page-meta">Assign a mentor to each approved intern</div>
    </div>
</div>

<div class="table-card">
    @forelse($interns as $intern)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f0f0f0;gap:16px;">
            <div>
                <div class="cell-name" style="font-size:14px;">{{ $intern->name }}</div>
                <div class="cell-mono" style="margin-top:2px;">{{ $intern->email }}</div>
            </div>
            <form method="POST" action="{{ route('hr.assigned.mentor') }}"
                  style="display:flex;gap:8px;align-items:center;">
                @csrf
                <input type="hidden" name="intern_id" value="{{ $intern->id }}">
                <select name="mentor_id" class="form-select" style="min-width:180px;" required>
                    <option value="">Select mentor</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                    @endforeach
                </select>
                <button class="btn-primary btn-sm">Assign</button>
            </form>
        </div>
    @empty
        <div class="empty-state">No unassigned interns.</div>
    @endforelse
</div>
@endsection
```

## File: resources\views\intern\attendance\index.blade.php
```php
@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Attendance</div>
        <div class="page-meta">Login and logout are tracked automatically from the approved office network.</div>
    </div>
</div>

<div class="info-grid" style="margin-bottom: 28px;">
    <div class="info-card">
        <div class="info-card-label">Today's Status</div>
        <div class="info-card-value">
            @if($todayAttendance?->logout_time)
                Checked out
            @elseif($todayAttendance?->login_time)
                Checked in
            @else
                No record yet
            @endif
        </div>
        <div class="info-card-sub">
            @if($todayAttendance?->login_time)
                Login: {{ $todayAttendance->login_time->format('d M Y h:i A') }}
            @else
                Attendance starts automatically when you log in from office WiFi.
            @endif
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-label">Tracked Time</div>
        <div class="info-card-value">{{ gmdate('H:i', $totalTrackedSeconds) }} hrs</div>
        <div class="info-card-sub">Total completed office time across all recorded sessions.</div>
    </div>

    <div class="info-card">
        <div class="info-card-label">Current Mentor</div>
        @if($mentorAssignment?->mentor)
            <div class="info-card-value">{{ $mentorAssignment->mentor->name }}</div>
            <div class="info-card-sub">{{ $mentorAssignment->mentor->email }}</div>
        @else
            <div class="info-card-value">Not assigned</div>
            <div class="info-card-sub">A mentor assignment is required before attendance can be tied to a supervisor.</div>
        @endif
    </div>
</div>

<div class="table-card" style="padding: 28px;">
    <div class="section-label">Attendance Policy</div>
    <p style="font-size: 14px; color: #555; line-height: 1.8; margin-bottom: 20px;">
        Login and logout are only accepted from the configured office network IPs. Each login creates an attendance
        session, and each logout closes the latest open session by saving the logout time and total worked seconds.
    </p>

    @if($currentAssignment?->topic)
        <div class="section-label">Current Work Context</div>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-label">Topic</div>
                <div class="info-card-value">{{ $currentAssignment->topic->title }}</div>
                <div class="info-card-sub">{{ $currentAssignment->topic->description }}</div>
            </div>
            <div class="info-card">
                <div class="info-card-label">Deadline</div>
                <div class="info-card-value">{{ optional($currentAssignment->deadline)->format('d M Y') ?? 'Not set' }}</div>
                <div class="info-card-sub">Status: {{ ucfirst(str_replace('_', ' ', $currentAssignment->status)) }}</div>
            </div>
        </div>
    @endif
</div>

<div class="table-card" style="padding: 0; margin-top: 24px;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Total Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAttendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('d M Y') }}</td>
                    <td>{{ $attendance->login_time->format('h:i A') }}</td>
                    <td>{{ $attendance->logout_time?->format('h:i A') ?? 'Open session' }}</td>
                    <td>
                        @if($attendance->total_seconds > 0)
                            {{ gmdate('H:i', $attendance->total_seconds) }} hrs
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $attendance->logout_time ? 'badge-success' : 'badge-warning' }}">
                            {{ $attendance->logout_time ? 'Completed' : 'Active' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No attendance sessions have been recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

```

## File: resources\views\intern\dashboard.blade.php
```php
@extends('layouts.app')
@section('title', 'My Overview')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
    <div>
        <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Welcome back, {{ Auth::user()->name }} 👋</h2>
        <p class="text-sm text-zinc-500 mt-1 font-medium">Here's your internship performance at a glance.</p>
    </div>
    <div class="flex items-center gap-3">
        <x-ui.button :href="route('intern.tasks')" variant="primary" class="shadow-lg shadow-blue-100">
            <i data-lucide="play-circle" class="w-4 h-4 mr-2"></i> Continue Learning
        </x-ui.button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tasks</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $topicCount }}</div>
            <div class="text-xs text-zinc-500 font-medium">Assigned Curriculum</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Completed</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $completedAssignmentsCount }}</div>
            <div class="text-xs text-zinc-500 font-medium">Submissions Passed</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="star" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grade</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $currentAssignment->grade ?? 'N/A' }}</div>
            <div class="text-xs text-zinc-500 font-medium">Latest Evaluation</div>
        </div>
    </div>

    <div class="card p-6 bg-white flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Performance</span>
        </div>
        <div>
            <div class="text-2xl font-bold text-zinc-900">{{ $performancePercent }}%</div>
            <div class="text-xs text-zinc-500 font-medium">Skill Alignment</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        <!-- AI Evaluation Insights -->
        @if($currentAssignment && $currentAssignment->status === 'evaluated')
            <div class="card bg-zinc-900 text-white overflow-hidden relative border-none">
                <div class="absolute right-0 top-0 p-8 opacity-10">
                    <i data-lucide="sparkles" class="w-32 h-32"></i>
                </div>
                <div class="p-8 relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="px-2 py-1 bg-blue-500 text-[10px] font-bold uppercase rounded">AI Insights</div>
                        <span class="text-xs text-zinc-400 font-medium tracking-wide">Synthesized assessment for {{ $currentAssignment->topic->title }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-bold mb-4 tracking-tight">AI Overall Feedback</h3>
                            <p class="text-zinc-400 text-sm leading-relaxed mb-6">{{ $currentAssignment->feedback }}</p>
                            
                            <div class="flex items-center gap-4">
                                <div>
                                    <div class="text-zinc-500 text-[10px] font-bold uppercase mb-1">Score</div>
                                    <div class="text-xl font-bold text-blue-400">{{ $currentAssignment->score }}/100</div>
                                </div>
                                <div class="w-px h-8 bg-zinc-800"></div>
                                <div>
                                    <div class="text-zinc-500 text-[10px] font-bold uppercase mb-1">Tone</div>
                                    <div class="text-xl font-bold capitalize text-amber-400">{{ $currentAssignment->tone }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @if(!empty($currentAssignment->strengths))
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-emerald-500 mb-3">Key Strengths</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($currentAssignment->strengths as $strength)
                                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-full border border-emerald-500/20">{{ $strength }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($currentAssignment->weak_areas))
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-rose-500 mb-3">Growth Areas</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($currentAssignment->weak_areas as $area)
                                            <span class="px-3 py-1 bg-rose-500/10 text-rose-400 text-xs font-medium rounded-full border border-rose-500/20">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <x-ui.card title="Current Focus" subtitle="Your active assignment and upcoming deadline.">
            @if($currentAssignment)
                <div class="p-6 rounded-2xl border border-zinc-100 bg-zinc-50/50">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="font-bold text-xl text-zinc-900 tracking-tight">{{ $currentAssignment->topic->title }}</div>
                            <div class="text-xs text-zinc-500 font-medium mt-1">Topic assigned on {{ $currentAssignment->assigned_at->format('M d, Y') }}</div>
                        </div>
                        <x-ui.badge :type="$currentAssignment->status === 'evaluated' ? 'success' : 'info'" dot="true" class="px-3 py-1 text-xs">
                            {{ ucfirst(str_replace('_', ' ', $currentAssignment->status)) }}
                        </x-ui.badge>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                                <span>Exercise Progress</span>
                                <span class="text-zinc-900 font-black">{{ $submittedCount }}/{{ max($questionCount, 1) }} Sections</span>
                            </div>
                            <div class="h-3 w-full bg-zinc-100 rounded-full overflow-hidden p-0.5">
                                <div class="h-full bg-blue-600 rounded-full transition-all duration-700 shadow-sm" style="width: {{ $questionCount > 0 ? round(($submittedCount / $questionCount) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-between pt-6 border-t border-zinc-100">
                        <div class="flex items-center gap-2 text-[11px] font-bold text-zinc-400 uppercase">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-400"></i>
                            <span>Due {{ optional($currentAssignment->deadline)->format('d M Y') ?? 'Flexible' }}</span>
                        </div>
                        <x-ui.button :href="route('intern.topic')" variant="secondary" size="sm" class="font-bold">
                            Open Workspace <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-2"></i>
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    <aside class="space-y-8">
        <x-ui.card title="Assigned Mentor" subtitle="Guidance provided by">
            <div class="flex flex-col items-center text-center p-4">
                <div class="h-24 w-24 rounded-2xl bg-zinc-100 flex items-center justify-center text-zinc-900 font-bold text-3xl overflow-hidden shadow-sm mb-4 border-4 border-white rotate-3 group-hover:rotate-0 transition-transform duration-300">
                    {{ $mentor ? substr($mentor->name, 0, 1) : '?' }}
                </div>
                <div class="text-lg font-black text-zinc-900 tracking-tight">{{ $mentor?->name ?? 'Awaiting Assignment' }}</div>
                <div class="text-xs text-zinc-500 font-medium mb-6">{{ $mentor?->email ?? 'HR will assign soon' }}</div>
                
                @if($mentor)
                    <x-ui.button :href="'mailto:' . $mentor->email" variant="secondary" class="w-full font-bold">
                        <i data-lucide="mail" class="w-4 h-4 mr-2"></i> Send Message
                    </x-ui.button>
                @endif
            </div>
        </x-ui.card>

        <div class="card p-6 bg-gradient-to-br from-indigo-600 to-blue-700 text-white relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <i data-lucide="medal" class="w-24 h-24"></i>
            </div>
            <h3 class="text-sm font-bold opacity-80 uppercase tracking-widest mb-2">Weekly Goal</h3>
            <p class="text-lg font-medium leading-snug mb-6">Complete at least <span class="font-black underline decoration-blue-300 decoration-2">3 more tasks</span> to hit your milestone.</p>
            <div class="w-full bg-white/20 h-1.5 rounded-full overflow-hidden">
                <div class="h-full bg-white w-1/3"></div>
            </div>
        </div>
    </aside>
</div>

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\intern\editor.blade.php
```php
@extends('layouts.app')

@section('title', 'Code Editor')

@section('content')
<div class="row">
    <!-- Question Description -->
    <div class="col-md-5 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0 fw-bold">Question Detail</h5>
                    <span class="badge bg-info text-dark">PHP</span>
                </div>
                <hr>
            </div>
            <div class="card-body pt-0">
                <h6 class="fw-bold mb-3">Implement a Stack</h6>
                <p class="text-muted">
                    Write a PHP class <code>MyStack</code> that implements a basic stack data structure with the following methods:
                </p>
                <ul class="text-muted">
                    <li><code>push($item)</code>: Adds an item to the top of the stack.</li>
                    <li><code>pop()</code>: Removes and returns the item from the top of the stack.</li>
                    <li><code>peek()</code>: Returns the item from the top without removing it.</li>
                    <li><code>isEmpty()</code>: Returns true if the stack is empty, false otherwise.</li>
                </ul>
                
                <h6 class="fw-bold mt-4">Example:</h6>
                <div class="bg-light p-3 rounded rounded-3">
                    <code>
                        $stack = new MyStack();<br>
                        $stack->push(1);<br>
                        $stack->push(2);<br>
                        $stack->peek(); // Returns 2
                    </code>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Editor -->
    <div class="col-md-7 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-code-slash me-2"></i>Code Editor</span>
                <div>
                    <button class="btn btn-sm btn-outline-light me-2"><i class="bi bi-play-fill"></i> Run Code</button>
                    <button class="btn btn-sm btn-primary"><i class="bi bi-cloud-arrow-up"></i> Submit</button>
                </div>
            </div>
            <div class="card-body p-0" style="background-color: #1e1e1e;">
                <!-- Placeholder for actual editor like Monaco or CodeMirror -->
                <textarea class="form-control border-0 text-white" style="background-color: transparent; font-family: monospace; height: 100%; min-height: 400px; resize: none;" spellcheck="false">
<?php

class MyStack {
    private $stack;

    public function __construct() {
        $this->stack = [];
    }

    public function push($item) {
        // Your code here
    }

    public function pop() {
        // Your code here
    }

    public function peek() {
        // Your code here
    }

    public function isEmpty() {
        // Your code here
    }
}
                </textarea>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="alert alert-secondary mb-0 py-2 d-none" role="alert" id="outputArea">
                    <span class="fw-bold">Console Output:</span><br>
                    <samp>Execution results will appear here...</samp>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

```

## File: resources\views\intern\exercise.blade.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst(str_replace('_', ' ', $type)) }} Workspace | IPMS</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- UI Core -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Monaco Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/loader/2.0.2/loader.min.js"></script>

    <style>
        :root {
            --panel-bg: #ffffff;
            --workspace-bg: #f8fafc;
            --border-color: #e2e8f0;
            --accent: #2563eb;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--workspace-bg);
            color: var(--text-main);
            overflow: hidden;
        }

        .layout {
            display: grid;
            grid-template-rows: 64px 1fr;
            height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 50;
        }

        .workspace {
            display: grid;
            grid-template-columns: 400px 1fr;
            overflow: hidden;
        }

        .sidebar {
            background: #fff;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .main-editor {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #fff;
        }

        .panel-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-content {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .question-nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .nav-step {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border-color);
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .nav-step.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .nav-step.completed {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .nav-step:hover:not(.active) {
            border-color: var(--accent);
            color: var(--accent);
        }

        .problem-statement {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            color: var(--text-main);
        }

        .code-snippet {
            background: #0f172a;
            color: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            margin-bottom: 24px;
            white-space: pre-wrap;
        }

        .choice-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .choice-btn {
            padding: 14px 18px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            text-align: left;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            background: #fff;
        }

        .choice-btn:hover {
            border-color: var(--accent);
            background: #f8faff;
        }

        .choice-btn.selected {
            border-color: var(--accent);
            background: #eff6ff;
            box-shadow: 0 0 0 1px var(--accent);
        }

        .choice-letter {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .selected .choice-letter {
            background: var(--accent);
            color: #fff;
        }

        #monaco-container {
            flex: 1;
            width: 100%;
        }

        .editor-toolbar {
            height: 48px;
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 16px;
            justify-content: space-between;
        }

        .output-console {
            height: 200px;
            background: #0f172a;
            color: #e2e8f0;
            padding: 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            border-top: 4px solid #1e293b;
            overflow-y: auto;
        }

        .console-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
        }

        .footer-actions {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 20px;
            border-radius: 8px;
            background: #1e293b;
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            transform: translateY(100px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        .toast.show { transform: translateY(0); }

        .hidden { display: none; }

        @media (max-width: 1024px) {
            .workspace { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>

<div class="layout">
    <header class="topbar">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-bold tracking-tight">{{ $topic->title }}</h1>
            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold uppercase">{{ $type }}</span>
        </div>
        <div class="flex items-center gap-3">
            <span id="save-indicator" class="text-xs text-slate-400 font-medium italic opacity-0 transition-opacity duration-300">Auto-saving...</span>
            <x-ui.button variant="secondary" size="sm" :href="route('intern.topic')">
                <i data-lucide="layout-grid" class="w-4 h-4 mr-2"></i> Dashboard
            </x-ui.button>
        </div>
    </header>

    <div class="workspace">
        <!-- Question Side -->
        <aside class="sidebar">
            <div class="panel-header">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Task Overview</span>
                <span id="progress-text" class="text-xs font-medium text-blue-600">1 / {{ count($questions) }}</span>
            </div>
            
            <div class="panel-content">
                <div class="question-nav">
                    @foreach($questions as $idx => $q)
                        <div class="nav-step {{ $idx === 0 ? 'active' : '' }} {{ !empty($answeredMap[$q->id]) ? 'completed' : '' }}" 
                             onclick="showQuestion({{ $idx }})" 
                             data-idx="{{ $idx }}">
                            {{ $idx + 1 }}
                        </div>
                    @endforeach
                </div>

                <div id="question-body">
                    <!-- Dynamic Question Content -->
                </div>
            </div>

            <div class="footer-actions">
                <button type="button" class="btn-secondary px-3 py-1.5" onclick="prevQuestion()" id="prev-btn">Prev</button>
                <div class="flex gap-2">
                    <button type="button" class="btn-primary px-4 py-1.5" onclick="saveAnswer()" id="save-btn">Save</button>
                    <button type="button" class="btn-secondary px-3 py-1.5" onclick="nextQuestion()" id="next-btn">Next</button>
                </div>
            </div>
        </aside>

        <!-- Editor/Response Side -->
        <main class="main-editor">
            @if($type === 'coding')
                <div class="editor-toolbar">
                    <div class="flex items-center gap-4">
                        <select id="language-selector" class="text-xs font-semibold bg-slate-50 border-none rounded focus:ring-0 cursor-pointer">
                            <option value="php">PHP</option>
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                        </select>
                        <div class="h-4 w-px bg-slate-200"></div>
                        <span class="text-[11px] font-medium text-slate-400 uppercase">Monaco Editor v0.34.0</span>
                    </div>
                    <button type="button" onclick="runCode()" class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-green-50 text-green-600 hover:bg-green-100 text-xs font-bold transition-colors">
                        <i data-lucide="play" class="w-3.5 h-3.5"></i> Run Code
                    </button>
                </div>
                <div id="monaco-container"></div>
                <div class="output-console" id="console">
                    <div class="console-header">
                        <span>Terminal Output</span>
                        <button onclick="clearConsole()" class="hover:text-white uppercase">Clear</button>
                    </div>
                    <div id="console-output" class="opacity-70 italic">Code output will appear here...</div>
                </div>
            @elseif($type === 'description')
                <div class="panel-header">
                    <span class="text-xs font-bold uppercase text-slate-500">Submission Details</span>
                </div>
                <div class="panel-content bg-slate-50">
                    <div class="max-w-2xl mx-auto space-y-8">
                        <div class="card p-6">
                            <h3 class="text-sm font-bold mb-4">GitHub Repository</h3>
                            <input type="url" id="github_link" class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all" 
                                   placeholder="https://github.com/username/project">
                            <p class="mt-2 text-[11px] text-slate-400 italic">Provide the public repository link for your project.</p>
                        </div>
                        
                        <div class="text-center">
                            <span class="px-4 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-full uppercase">OR</span>
                        </div>

                        <div class="card p-6 border-dashed border-2 border-slate-200 bg-slate-50/50 hover:bg-slate-100/50 transition-colors cursor-pointer relative" id="drop-zone">
                            <input type="file" id="file-upload" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf,.doc,.docx,.zip">
                            <div class="flex flex-col items-center py-4">
                                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 mb-4">
                                    <i data-lucide="upload-cloud"></i>
                                </div>
                                <h3 class="text-sm font-bold" id="file-name">Upload Project Files</h3>
                                <p class="text-xs text-slate-400 mt-1">PDF, DOC, or ZIP (Max 5MB)</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="panel-header">
                    <span class="text-xs font-bold uppercase text-slate-500">Write your logic</span>
                    <span class="text-[10px] text-rose-500 font-bold bg-rose-50 px-2 py-0.5 rounded uppercase">Restricted Area</span>
                </div>
                <div class="panel-content bg-slate-50 flex flex-col h-full">
                    <textarea id="theory-input" 
                              class="flex-1 w-full p-8 border-none focus:ring-0 outline-none text-lg font-medium tracking-tight text-slate-700 bg-transparent resize-none leading-relaxed" 
                              placeholder="Type your response here..."
                              spellcheck="false"
                              onpaste="handlePaste(event)"></textarea>
                    <div class="mt-4 p-4 rounded-lg bg-slate-100 border border-slate-200 flex items-center gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-amber-500"></i>
                        <span class="text-[11px] font-medium text-slate-500">Copy-Paste is disabled to ensure honest evaluation. If you try to paste, a warning will be logged.</span>
                    </div>
                </div>
            @endif
        </main>
    </div>
</div>

<div id="toast" class="toast">
    <i data-lucide="alert-circle" class="w-5 h-5 text-amber-400"></i>
    <span id="toast-message"></span>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Data from Laravel
    const QUESTIONS = @json($questions);
    const EXAM_TYPE = @json($type);
    const SAVE_URL = "{{ route('intern.exam.save') }}";
    const RUN_URL = "{{ route('intern.run.code') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    const REDIRECT_URL = "{{ route('intern.topic') }}";

    let currentIndex = 0;
    let answers = @json($savedAnswers);
    let editor = null;
    let monacoLoaded = false;

    // Monaco Setup
    if (EXAM_TYPE === 'coding') {
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.0/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            monaco.editor.defineTheme('ipms-dark', {
                base: 'vs-dark',
                inherit: true,
                rules: [],
                colors: {
                    'editor.background': '#0f172a',
                    'editor.lineHighlightBackground': '#1e293b'
                }
            });

            const initialCode = (QUESTIONS.length > 0 && answers[QUESTIONS[0].id]) || '<' + '?php\n\n';
            
            editor = monaco.editor.create(document.getElementById('monaco-container'), {
                value: initialCode,
                language: 'php',
                theme: 'ipms-dark',
                fontSize: 14,
                fontFamily: "'JetBrains Mono', monospace",
                minimap: { enabled: false },
                scrollBeyondLastLine: false,
                automaticLayout: true,
                padding: { top: 20 }
            });

            monacoLoaded = true;
        });

        document.getElementById('language-selector').addEventListener('change', (e) => {
            if (editor) {
                monaco.editor.setModelLanguage(editor.getModel(), e.target.value);
            }
        });
    }

    // Question Rendering
    function showQuestion(idx) {
        currentIndex = idx;
        const q = QUESTIONS[idx];
        
        // Update nav
        document.querySelectorAll('.nav-step').forEach(el => {
            el.classList.toggle('active', parseInt(el.dataset.idx) === idx);
        });

        document.getElementById('progress-text').innerText = `${idx + 1} / ${QUESTIONS.length}`;
        document.getElementById('prev-btn').disabled = idx === 0;
        document.getElementById('next-btn').disabled = idx === QUESTIONS.length - 1;

        let html = `<div class="problem-statement">${q.problem_statement}</div>`;
        
        if (q.code) {
            html += `<pre class="code-snippet">${escapeHtml(q.code)}</pre>`;
        }

        if (q.type === 'mcq') {
            const currentAnswer = answers[q.id];
            html += `<div class="choice-list">`;
            ['a', 'b', 'c', 'd'].forEach(key => {
                const text = q['option_' + key];
                if (text) {
                    html += `
                        <button class="choice-btn ${currentAnswer === key.toUpperCase() ? 'selected' : ''}" onclick="pickChoice('${key.toUpperCase()}')">
                            <span class="choice-letter">${key.toUpperCase()}</span>
                            <span class="text-sm font-medium">${escapeHtml(text)}</span>
                        </button>
                    `;
                }
            });
            html += `</div>`;
        }

        document.getElementById('question-body').innerHTML = html;

        // Load specific answer for other types
        if (EXAM_TYPE === 'coding' && editor) {
            editor.setValue(answers[q.id] || '<' + '?php\n\n');
        } else if (['blank', 'theory', 'output'].includes(EXAM_TYPE)) {
            document.getElementById('theory-input').value = answers[q.id] || '';
        } else if (EXAM_TYPE === 'description') {
            // Special handling for description if needed
        }
    }

    function pickChoice(letter) {
        answers[QUESTIONS[currentIndex].id] = letter;
        showQuestion(currentIndex);
    }

    function prevQuestion() { if(currentIndex > 0) showQuestion(currentIndex - 1); }
    function nextQuestion() { if(currentIndex < QUESTIONS.length - 1) showQuestion(currentIndex + 1); }

    // Paste restriction
    function handlePaste(e) {
        e.preventDefault();
        showToast("⛔ PASTE RESTRICTED! Please type your answer manually.");
    }

    function showToast(msg) {
        const t = document.getElementById('toast');
        document.getElementById('toast-message').innerText = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // Save Logic
    async function saveAnswer() {
        const q = QUESTIONS[currentIndex];
        const saveBtn = document.getElementById('save-btn');
        const indicator = document.getElementById('save-indicator');

        let val = '';
        const formData = new FormData();
        formData.append('question_id', q.id);
        formData.append('_token', CSRF_TOKEN);

        if (EXAM_TYPE === 'coding' && editor) {
            val = editor.getValue();
            formData.append('submitted_code', val);
        } else if (['theory', 'blank', 'output'].includes(EXAM_TYPE)) {
            val = document.getElementById('theory-input').value;
            formData.append('submitted_code', val);
        } else if (EXAM_TYPE === 'description') {
            const github = document.getElementById('github_link').value;
            const file = document.getElementById('file-upload').files[0];
            if (github) formData.append('github_link', github);
            if (file) formData.append('file', file);
            val = github || 'File Uploaded';
        } else if (EXAM_TYPE === 'mcq') {
            val = answers[q.id];
            formData.append('submitted_code', val);
        }

        if (!val) return showToast("Please write something first!");

        saveBtn.disabled = true;
        indicator.style.opacity = 1;

        try {
            const res = await fetch(SAVE_URL, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            
            if (data.ok) {
                answers[q.id] = val;
                document.querySelector(`.nav-step[data-idx="${currentIndex}"]`).classList.add('completed');
                showToast("✅ Progress Saved");
            } else {
                showToast("❌ Error saving: " + data.msg);
            }
        } catch (e) {
            showToast("❌ Server communication failed");
        } finally {
            saveBtn.disabled = false;
            indicator.style.opacity = 0;
        }
    }

    // Code Execution
    async function runCode() {
        if (!editor) return;
        const out = document.getElementById('console-output');
        out.innerText = "Executing on remote container...";
        out.classList.remove('opacity-70', 'italic');

        try {
            const res = await fetch(RUN_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ code: editor.getValue() })
            });
            const data = await res.json();
            out.innerText = data.output || data.error || "No output returned.";
        } catch (e) {
            out.innerText = "Failed to reach execution engine.";
        }
    }

    function clearConsole() {
        document.getElementById('console-output').innerText = "Terminal cleared.";
    }

    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // File Upload Preview
    if (EXAM_TYPE === 'description') {
        const fileIn = document.getElementById('file-upload');
        const fileName = document.getElementById('file-name');
        fileIn.addEventListener('change', () => {
            if (fileIn.files.length) fileName.innerText = "Selected: " + fileIn.files[0].name;
        });
    }

    // Start
    setTimeout(() => showQuestion(0), 100);

</script>
</body>
</html>


```

## File: resources\views\intern\performance\index.blade.php
```php
@extends('layouts.app')
@section('title', 'Performance')

@section('content')
@php
    $completionPercent = $assignmentCount > 0 ? round(($submittedAssignments / $assignmentCount) * 100) : 0;
    $reviewCoveragePercent = $submittedAssignments > 0 ? round(($evaluatedAssignments / $submittedAssignments) * 100) : 0;
    $scorePercent = $averageFinalScore !== null ? min(100, round(($averageFinalScore / 30) * 100)) : 0;
    $latestGrade = $latestEvaluatedAssignment->grade ?? 'N/A';
@endphp

<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Intern analytics</div>
        <h1 class="page-shell-title">Performance</h1>
        <p class="page-shell-subtitle">
            Track how much work you have completed, how much has been reviewed, and how your latest grading is trending.
        </p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('intern.tasks')" variant="secondary">Back to Tasks</x-ui.button>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Assignments</div>
        <div class="summary-value">{{ $assignmentCount }}</div>
        <div class="summary-note">{{ $submittedAssignments }} already submitted.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Reviewed Answers</div>
        <div class="summary-value">{{ $reviewedAnswers }}</div>
        <div class="summary-note">Answers finalized after mentor review.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Average Score</div>
        <div class="summary-value">{{ $averageFinalScore ?? 'N/A' }}</div>
        <div class="summary-note">Average final score across all graded submissions.</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-stack">
        <x-ui.card title="Progress Bars" subtitle="A quick read on submission, evaluation, and score progress.">
            <div class="metric-stack">
                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Task Completion</div>
                        <div class="metric-value">{{ $completionPercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill" style="width: {{ $completionPercent }}%"></div>
                    </div>
                </div>

                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Evaluation Coverage</div>
                        <div class="metric-value">{{ $reviewCoveragePercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill is-warning" style="width: {{ $reviewCoveragePercent }}%"></div>
                    </div>
                </div>

                <div class="metric-row">
                    <div class="metric-head">
                        <div class="metric-title">Score Health</div>
                        <div class="metric-value">{{ $scorePercent }}%</div>
                    </div>
                    <div class="metric-bar">
                        <div class="metric-bar-fill is-success" style="width: {{ $scorePercent }}%"></div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        @if($topicPerformance->isNotEmpty())
            <x-ui.card title="Topic Breakdown" subtitle="Performance by task or topic assignment.">
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>AI Avg</th>
                            <th>Final Avg</th>
                            <th>Reviewed</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topicPerformance as $topicRow)
                            <tr>
                                <td>
                                    <div class="table-title">{{ $topicRow->topic->title ?? 'Untitled topic' }}</div>
                                    <div class="table-subtitle">{{ optional($topicRow->deadline)->format('d M Y') ?? 'No deadline' }}</div>
                                </td>
                                <td class="cell-mono">{{ $topicRow->ai_score ?? 'N/A' }}</td>
                                <td class="cell-mono">{{ $topicRow->final_score ?? 'N/A' }}</td>
                                <td class="cell-mono">{{ $topicRow->reviewed_answers }}</td>
                                <td><span class="role-pill">{{ $topicRow->grade ?? 'N/A' }}</span></td>
                                <td><x-badge :status="$topicRow->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </x-ui.card>
        @endif
    </div>

    <div class="dashboard-stack">
        <x-ui.card title="Charts" subtitle="Simple chart-style indicators for your current standing.">
            <div class="donut-chart" style="--value: {{ $completionPercent }};" data-label="{{ $completionPercent }}%"></div>
            <div class="chart-caption">Completion chart showing how much of your assigned work has been submitted.</div>
        </x-ui.card>

        <div class="grade-display-card">
            <div class="grade-display-letter">{{ $latestGrade }}</div>
            <div class="grade-display-copy">
                <div class="grade-display-title">Latest Grade</div>
                <div class="grade-display-subtitle">
                    @if($latestEvaluatedAssignment)
                        {{ $latestEvaluatedAssignment->topic->title ?? 'Latest assignment' }} was your most recently evaluated task.
                    @else
                        Your latest grade will appear here once an assignment is evaluated.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

```

## File: resources\views\intern\result.blade.php
```php
@extends('layouts.app')

@section('title', 'Submission Result')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow border-0">
            <div class="card-header bg-white pb-0 border-bottom-0 text-center pt-4">
                <div class="mb-3">
                    <!-- Status Icon -->
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1">Submission Successful</h4>
                <p class="text-muted">Implement a Stack</p>
            </div>
            <div class="card-body p-4 pt-2">
                <hr>
                
                <div class="row text-center mb-4">
                    <div class="col-md-6 border-end">
                        <div class="text-muted mb-1">Overall Score</div>
                        <h2 class="fw-bold text-success mb-0">85/100</h2>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted mb-1">Status</div>
                        <h4 class="mb-0"><span class="badge bg-success">Passed</span></h4>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">AI Feedback</h6>
                    <div class="p-3 bg-light rounded bg-opacity-50">
                        <p class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i><strong>Strengths:</strong></p>
                        <p class="text-muted ms-4 mb-3">Your implementation correctly utilizes PHP's empty arrays to mimic stack behavior via `array_push` and `array_pop`. The logic is sound and handles standard cases well.</p>

                        <p class="mb-2"><i class="bi bi-info-circle text-info me-2"></i><strong>Areas for Improvement:</strong></p>
                        <p class="text-muted ms-4 mb-0">Consider adding error handling or throwing an exception in `pop()` or `peek()` if the stack is completely empty, to prevent undefined index warnings or unexpected returns.</p>
                    </div>
                </div>

                <div>
                    <h6 class="fw-bold border-bottom pb-2">Test Cases</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Push single item
                            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Passed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Pop single item
                            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Passed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Peek empty stack
                            <span class="badge bg-danger rounded-pill"><i class="bi bi-x"></i> Failed</span>
                        </li>
                    </ul>
                </div>
                
            </div>
            <div class="card-footer bg-white border-top-0 pb-4 text-center">
                <a href="{{ url('/intern/questions') }}" class="btn btn-outline-secondary me-2">Back to Questions</a>
                <a href="{{ url('/intern/editor') }}" class="btn btn-primary">Try Again</a>
            </div>
        </div>
    </div>
</div>
@endsection

```

## File: resources\views\intern\submissions.blade.php
```php
@extends('layouts.app')
@section('title', 'Reviews')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Submission history</div>
        <h1 class="page-shell-title">Reviews</h1>
        <p class="page-shell-subtitle">
            View your submitted answers, grading progress, and mentor-reviewed results.
        </p>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Assignments</div>
        <div class="summary-value">{{ $assignments->count() }}</div>
        <div class="summary-note">Total task assignments on record.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Submissions</div>
        <div class="summary-value">{{ $totalSubmissions }}</div>
        <div class="summary-note">Answers saved or submitted across all tasks.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Reviewed</div>
        <div class="summary-value">{{ $reviewedCount }}</div>
        <div class="summary-note">Answers already reviewed by your mentor.</div>
    </div>
</div>

<div class="page-shell-header mt-5">
    <div class="page-shell-copy">
        <h2 class="page-shell-title" style="font-size: 20px;">Task Evaluations</h2>
        <p class="page-shell-subtitle">Holistic feedback from our AI Senior Mentor on your full assignments.</p>
    </div>
</div>

<div class="assignment-grid mt-3 mb-5" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
    @forelse($assignments as $assignment)
        @if($assignment->status === 'evaluated' || $assignment->status === 'submitted')
            <div class="ui-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span class="table-chip">{{ $assignment->topic->title }}</span>
                    <x-badge :status="$assignment->status" />
                </div>
                
                @if($assignment->status === 'evaluated')
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-top: 16px;">
                        <div class="score-circle" style="background: var(--ui-primary); color: #fff; width: 48px; height: 48px; font-size: 18px; flex-shrink: 0;">
                            {{ $assignment->grade }}
                        </div>
                        <div>
                            <div class="form-label" style="font-size: 11px; margin-bottom: 4px;">Mentor Feedback</div>
                            <p style="font-size: 13px; line-height: 1.5; color: var(--ui-text-soft);">{{ $assignment->feedback }}</p>
                            
                            @if(!empty($assignment->weak_areas))
                                <div style="margin-top: 12px;">
                                    <div class="form-label" style="font-size: 10px; margin-bottom: 4px;">Weak Areas</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        @foreach($assignment->weak_areas as $area)
                                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">
                                                {{ $area }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="empty-state" style="padding: 20px 0;">
                        Wait a moment! Your assignment is being evaluated by our Senior Mentor.
                    </div>
                @endif
            </div>
        @endif
    @empty
        <div class="ui-card text-center p-4">No assignments submitted for evaluation yet.</div>
    @endforelse
</div>

<div class="page-shell-header">
    <div class="page-shell-copy">
        <h2 class="page-shell-title" style="font-size: 20px;">Answer History</h2>
        <p class="page-shell-subtitle">Individual submission records for all tasks.</p>
    </div>
</div>

<x-ui.table>
    @if($submissions->isEmpty())
        <tbody>
            <tr>
                <td colspan="5" class="empty-state">No answers saved yet. Open a task to start solving questions.</td>
            </tr>
        </tbody>
    @else
        <thead>
            <tr>
                <th>Task</th>
                <th>Question Type</th>
                <th>Status</th>
                <th>Final Score</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr>
                    <td>
                        <div class="table-title">{{ $submission->question?->topic?->title ?? 'Untitled task' }}</div>
                        <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($submission->question?->problem_statement ?? 'No question text available.', 80) }}</div>
                    </td>
                    <td><span class="table-chip">{{ str_replace('_', ' ', $submission->question?->type ?? 'unknown') }}</span></td>
                    <td><x-badge :status="$submission->status" /></td>
                    <td class="cell-mono">{{ $submission->final_score ?? 'Pending' }}</td>
                    <td class="cell-mono">{{ $submission->updated_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</x-ui.table>

{{ $submissions->links() }}
@endsection

```

## File: resources\views\intern\topic.blade.php
```php
@extends('layouts.app')
@section('title', 'My Tasks')

@section('content')
@if(!$assignment)
    <div class="page-shell-header">
        <div class="page-shell-copy">
            <div class="page-shell-eyebrow">Intern workspace</div>
            <h1 class="page-shell-title">My Tasks</h1>
            <p class="page-shell-subtitle">
                Your task board will appear here after your mentor assigns a topic.
            </p>
        </div>
    </div>

    <div class="empty-state">
        No tasks have been assigned yet. Once a mentor assigns your first task, you will be able to open it from this page.
    </div>
@else
    @php
        $topic = $assignment->topic;
        $grouped = $topic->questions->groupBy('type');
        $totalQ = $topic->questions->count();
        $totalDone = collect($submissionCounts)->sum('submitted');
        $alreadyFinalSubmitted = in_array($assignment->status, ['submitted', 'evaluated']);
        $isDeadlineOver = $assignment->deadline?->isPast();
    @endphp

    <div class="page-shell-header">
        <div class="page-shell-copy">
            <div class="page-shell-eyebrow">Assigned task</div>
            <h1 class="page-shell-title">My Tasks</h1>
            <p class="page-shell-subtitle">
                {{ $topic->title }}{{ $topic->description ? ' - ' . $topic->description : '' }}
            </p>
        </div>

        <div class="page-shell-actions">
            <span class="table-chip">{{ $assignment->deadline?->format('d M Y') ?? 'No deadline' }}</span>
            <x-badge :status="$topic->difficulty_label" />
            <x-badge :status="$assignment->status" />
        </div>
    </div>

    <div class="summary-strip">
        <div class="summary-card">
            <div class="summary-label">Task Progress</div>
            <div class="summary-value">{{ $totalDone }}/{{ $totalQ }}</div>
            <div class="summary-note">Questions saved across all sections.</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Task Difficulty</div>
            <div class="summary-value">{{ ucfirst($topic->difficulty_label) }}</div>
            <div class="summary-note">Derived from the question mix in this assignment.</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Due Date</div>
            <div class="summary-value">{{ $assignment->deadline?->format('d M') ?? '--' }}</div>
            <div class="summary-note">{{ $isDeadlineOver ? 'Deadline has passed.' : 'Complete and submit before the deadline.' }}</div>
        </div>
    </div>

    <div class="task-grid">
        @foreach($grouped as $type => $questions)
            @php
                $counts = $submissionCounts[$type] ?? ['total' => $questions->count(), 'submitted' => 0];
                $total = $counts['total'];
                $done = $counts['submitted'];
                $isDone = $done >= $total && $total > 0;
                $hasStarted = $done > 0 && ! $isDone;
                $status = $alreadyFinalSubmitted ? 'submitted' : ($isDone ? 'done' : ($hasStarted ? 'in_progress' : 'pending'));
            @endphp

            <article class="task-card">
                <div class="task-card-header">
                    <div>
                        <div class="task-card-title">{{ ucwords(str_replace('_', ' ', $type)) }}</div>
                        <div class="task-card-meta">{{ $total }} questions in this section</div>
                    </div>

                    <x-badge :status="$status" />
                </div>

                <div class="task-card-copy">
                    Save answers question by question, then submit the full task once you are ready for AI evaluation.
                </div>

                <div class="task-card-tags">
                    <x-badge :status="$topic->difficulty_label" />
                    <span class="table-chip">{{ $done }}/{{ $total }} saved</span>
                </div>

                <div class="task-card-footer">
                    @if($alreadyFinalSubmitted)
                        <x-ui.button variant="secondary" disabled>Locked</x-ui.button>
                    @else
                        <x-ui.button :href="route('intern.exercise', [$assignment->id, $type])">
                            {{ $hasStarted ? 'Continue' : 'Start' }}
                        </x-ui.button>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    @if($alreadyFinalSubmitted && $assignment->grade)
        <div class="final-banner mt-24">
            <div>
                <div class="final-banner-text">Grade {{ $assignment->grade }} received</div>
                <div class="final-banner-sub">{{ $assignment->feedback ?: 'Your mentor review or AI summary will appear here once available.' }}</div>
            </div>
            <div class="task-card-tags">
                <x-badge :status="$assignment->status" />
                <x-ui.button :href="route('intern.submissions')" variant="secondary">View Reviews</x-ui.button>
            </div>
        </div>
    @elseif($alreadyFinalSubmitted)
        <div class="final-banner mt-24">
            <div>
                <div class="final-banner-text">Submission received</div>
                <div class="final-banner-sub">Your task is locked and waiting for AI processing or mentor review.</div>
            </div>
            <x-badge :status="$assignment->status" />
        </div>
    @elseif(session('exercise_result'))
        @php($result = session('exercise_result'))
        <div class="final-banner mt-24">
            <div>
                <div class="final-banner-text">Grade {{ $result['grade'] }} generated</div>
                <div class="final-banner-sub">{{ $result['feedback'] ?? $result['summary'] ?? 'Your submission has been evaluated.' }}</div>
            </div>
        </div>
    @else
        <div class="final-banner mt-24">
            <div>
                <div class="final-banner-text">{{ $totalDone }}/{{ $totalQ }} questions saved</div>
                <div class="final-banner-sub">Submit the full task when you are ready. You will not be able to edit answers after final submission.</div>
            </div>

            <form method="POST" action="{{ route('intern.final.submit', $assignment->id) }}" onsubmit="return confirm('Submit this task for AI evaluation? You will not be able to edit answers afterwards.')">
                @csrf
                @if($totalDone === 0)
                    <x-ui.button type="submit" variant="secondary" disabled>Submit Task</x-ui.button>
                @else
                    <x-ui.button type="submit" variant="secondary">Submit Task</x-ui.button>
                @endif
            </form>
        </div>
    @endif
@endif
@endsection

```

## File: resources\views\intern\waiting.blade.php
```php
@extends('layouts.app')

@section('title', 'Waiting for Assignment')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .waiting-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        padding: 48px;
        max-width: 520px;
    }

    .waiting-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 14px;
    }

    .waiting-title {
        font-size: 18px;
        font-weight: 500;
        letter-spacing: -0.01em;
        margin-bottom: 12px;
    }

    .waiting-desc {
        font-size: 13.5px;
        color: #666;
        line-height: 1.7;
        font-weight: 300;
    }

    .waiting-steps {
        margin-top: 28px;
        border-top: 1px solid #ebebeb;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
        color: #555;
    }

    .step-num {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        min-width: 18px;
    }

    .step.done .step-num { color: #1a1a1a; }
    .step.done { color: #1a1a1a; }
</style>

<div class="waiting-card">
    <div class="waiting-label">Account Status</div>
    <div class="waiting-title">Pending Mentor Assignment</div>
    <div class="waiting-desc">
        Your account has been approved by HR. You will gain access to your topics and tasks
        once a mentor (team lead) is assigned to you.
    </div>

    <div class="waiting-steps">
        <div class="step done">
            <span class="step-num">01</span>
            <span>Registration submitted</span>
        </div>
        <div class="step done">
            <span class="step-num">02</span>
            <span>Account approved by HR</span>
        </div>
        <div class="step">
            <span class="step-num">03</span>
            <span>Mentor assignment â€” pending</span>
        </div>
        <div class="step">
            <span class="step-num">04</span>
            <span>Topic assigned and work begins</span>
        </div>
    </div>
</div>

@endsection

```

## File: resources\views\layouts\app.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'AI-Driven IPMS') | Dashboard</title>

    <!-- Google Fonts: DM Sans for base, DM Mono for code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap" rel="stylesheet">

    <!-- CSS / Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    
    <div class="app-layout">
        <!-- Sidebar Navigation -->
        @include('partials.sidebar')

        <!-- Main Column -->
        <main class="main-col">
            <!-- Top Navigation Bar -->
            @include('partials.navbar')

            <!-- Dynamic Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="flash flash-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="flash flash-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>

```

## File: resources\views\layouts\base.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AI-IPMS') }} — @yield('title', 'Dashboard')</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-zinc-50 text-zinc-900">

<div class="flex min-h-screen">
    @yield('sidebar')

    <main class="flex-1 flex flex-col min-w-0">
        @yield('topbar')

        <div class="px-6 py-10 lg:px-12 max-w-7xl w-full mx-auto">
            <x-ui.flash />
            @yield('content')
        </div>
    </main>
</div>

</body>
</html>
```

## File: resources\views\layouts\guest.blade.php
```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') | AI Internship Platform</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-zinc-900 bg-zinc-50">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        @yield('content')
    </div>
    
    @include('components.toast')
</body>
</html>

```

## File: resources\views\layouts\hr.blade.php
```php
@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="hr" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'HR Panel'))" subtitle="HR workspace" />
@endsection

```

## File: resources\views\layouts\intern.blade.php
```php
@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="intern" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'Intern Panel'))" subtitle="Intern workspace" />
@endsection

```

## File: resources\views\layouts\mentor.blade.php
```php
@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="mentor" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'Mentor Panel'))" subtitle="Mentor workspace" />
@endsection

```

## File: resources\views\layouts\navigation.blade.php
```php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

```

## File: resources\views\layouts\topbar.blade.php
```php
@props(['title' => 'Dashboard'])

<div class="topbar">
    <div class="topbar-title">
        {{ $title }}
    </div>

    <div class="topbar-meta">
        {{ auth()->user()->name ?? 'User' }}
    </div>
</div>
```

## File: resources\views\mentor\dashboard.blade.php
```php
@extends('layouts.mentor')
@section('title', 'Mentor Workspace')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
    <div>
        <h2 class="text-2xl font-bold text-zinc-900 tracking-tight">Mentor Workspace</h2>
        <p class="text-sm text-zinc-500 mt-1">Manage your interns, track topic progress, and review submissions.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('mentor.topics.create') }}" class="btn btn-primary">
            + New Task
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <x-ui.stat-card 
        label="Assigned Interns" 
        :value="$assignedInternsCount" 
        trend="Active oversight"
        :trendUp="true"
        :icon="view('components.icons.users', ['size' => 18])"
    />
    <x-ui.stat-card 
        label="Published Topics" 
        :value="$publishedTopicsCount" 
        trend="Available course material"
        :trendUp="true"
        :icon="view('components.icons.clipboard-list', ['size' => 18])"
    />
    <x-ui.stat-card 
        label="Pending Reviews" 
        :value="$pendingSubmissionsCount" 
        trend="Awaiting your feedback"
        :trendUp="false"
        :icon="view('components.icons.check-circle', ['size' => 18])"
    />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <x-ui.card title="Recent Submissions" subtitle="Latest tasks submitted for your review.">
        <div class="space-y-4">
            @forelse($recentSubmissions as $submission)
                <div class="flex items-center justify-between p-4 rounded-xl border border-zinc-100 bg-zinc-50 group hover:border-zinc-200 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-white border border-zinc-200 flex items-center justify-center text-zinc-400">
                            {{ substr($submission->intern->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-zinc-900">{{ $submission->topic->title }}</div>
                            <div class="text-[11px] text-zinc-500 font-medium">{{ $submission->intern->name }} • {{ $submission->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <a href="{{ route('mentor.submissions.show', $submission->id) }}" class="btn btn-secondary btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                        Review
                    </a>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="text-zinc-400 mb-2">
                        @include('components.icons.check-circle', ['size' => 32, 'class' => 'mx-auto'])
                    </div>
                    <p class="text-sm text-zinc-500">All caught up! No pending submissions.</p>
                </div>
            @endforelse
            
            @if($recentSubmissions->count() > 0)
                <div class="pt-4 border-t border-zinc-100">
                    <a href="{{ route('mentor.reviews.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-500 flex items-center gap-1 group">
                        View all submissions
                        <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            @endif
        </div>
    </x-ui.card>

    <x-ui.card title="Quick Actions" subtitle="Streamline your mentorship workflow.">
        <div class="grid grid-cols-1 gap-4">
            <a href="{{ route('mentor.topics.index') }}" class="flex items-center p-4 rounded-xl border border-zinc-100 hover:bg-zinc-50 transition-colors group">
                <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    @include('components.icons.clipboard-list', ['size' => 20])
                </div>
                <div class="ml-4">
                    <div class="text-sm font-bold text-zinc-900">Manage Tasks</div>
                    <div class="text-xs text-zinc-500">Create, edit, and publish topic sets.</div>
                </div>
            </a>

            <a href="{{ route('mentor.interns.index') }}" class="flex items-center p-4 rounded-xl border border-zinc-100 hover:bg-zinc-50 transition-colors group">
                <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    @include('components.icons.users', ['size' => 20])
                </div>
                <div class="ml-4">
                    <div class="text-sm font-bold text-zinc-900">Track Progress</div>
                    <div class="text-xs text-zinc-500">Monitor intern performance and growth.</div>
                </div>
            </a>
        </div>
    </x-ui.card>
</div>
@endsection

```

## File: resources\views\mentor\interns\index.blade.php
```php
@extends('layouts.app')
@section('title', 'My Interns')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Intern Directory</div>
        <h1 class="page-shell-title">Assigned Interns</h1>
        <p class="page-shell-subtitle">Select an intern below to view their assignments and review their code submissions.</p>
    </div>
</div>

<div class="intern-grid mt-4">
    @forelse($interns as $intern)
        <div class="intern-card ui-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div class="intern-avatar" style="margin-bottom: 0;">
                    {{ strtoupper(substr($intern->name, 0, 1)) }}
                </div>
                @if($intern->pending_reviews > 0)
                    <span class="badge badge-warning">{{ $intern->pending_reviews }} Pending</span>
                @else
                    <span class="badge badge-success">All Caught Up</span>
                @endif
            </div>
            
            <h3 class="intern-name">{{ $intern->name }}</h3>
            <p class="intern-email">{{ $intern->email }}</p>
            
            <div style="margin-top: 16px; margin-bottom: 16px;">
                <div class="cell-mono" style="font-size: 11px; color: var(--ui-text-soft);">
                    Total Submissions: <strong style="color: var(--ui-primary);">{{ $intern->total_submissions }}</strong>
                </div>
            </div>
            
            <div class="intern-meta">
                <span class="cell-mono" style="font-size: 10px; color: var(--ui-text-muted);">Assigned: {{ \Carbon\Carbon::parse($intern->assigned_at)->format('d M Y') }}</span>
                <x-ui.button :href="route('mentor.interns.progress', $intern->id)" size="sm" variant="ghost">View Submissions</x-ui.button>
            </div>
        </div>
    @empty
        <div class="ui-card flex items-center justify-center p-8 w-full" style="grid-column: 1 / -1;">
            <p class="text-muted">No interns assigned to you yet.</p>
        </div>
    @endforelse
</div>
@endsection

```

## File: resources\views\mentor\interns\submissions.blade.php
```php
@extends('layouts.app')
@section('title', 'Intern Submissions')

@section('content')
<!-- BREADCRUMB / BACK LINK -->
<div class="mb-4">
    <a href="{{ route('mentor.interns') }}" class="cell-mono" style="text-decoration: none; color: var(--ui-text-muted); font-size: 11px;">
        &larr; BACK TO INTERNS
    </a>
</div>

<div class="page-shell-header">
    <div class="page-shell-copy">
        <h1 class="page-shell-title">{{ $intern->name }}'s Submissions</h1>
        <p class="page-shell-subtitle">Review code evaluated by AI for this specific intern.</p>
    </div>
    
    <div class="page-shell-actions" style="display: flex; gap: 16px;">
        <div style="text-align: right;">
            <div class="form-label" style="font-size: 10px;">Avg Score</div>
            <div class="cell-mono" style="font-size: 18px; font-weight: 700;">{{ $avgScore ?? '--' }}</div>
        </div>
        <div style="text-align: right;">
            <div class="form-label" style="font-size: 10px;">Evaluated</div>
            <div class="cell-mono" style="font-size: 18px; font-weight: 700;">{{ $evaluatedCount }}/{{ $totalSubmissions }}</div>
        </div>
    </div>
</div>

<div class="table-card ui-card mt-4">
    <table class="data-table">
        <thead>
            <tr>
                <th>Topic & Task Type</th>
                <th>Status</th>
                <th>AI Score</th>
                <th>Submitted Date</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submissions as $submission)
                <tr>
                    <td>
                        <span class="table-chip">{{ $submission->question->topic->title ?? 'Unknown Topic' }}</span>
                        @if($submission->question->type)
                            <div class="cell-mono" style="font-size: 10px; margin-top: 4px; opacity: 0.6;">
                                {{ strtoupper(str_replace('_', ' ', $submission->question->type)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <x-badge :status="$submission->status" />
                    </td>
                    <td class="cell-mono" style="font-weight: 600;">
                        {{ $submission->ai_total_score ?? '--' }}<span style="font-size: 11px; font-weight: 400; opacity: 0.5;">/100</span>
                    </td>
                    <td class="cell-mono">
                        {{ $submission->created_at->format('M d, Y h:i A') }}
                    </td>
                    <td style="text-align: right;">
                        <x-ui.button :href="route('mentor.submissions.show', $submission->id)" size="sm">
                            Review
                        </x-ui.button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state" style="text-align: center; padding: 32px;">
                        No submissions exist for this intern yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

```

## File: resources\views\mentor\interns.blade.php
```php
@extends('layouts.app')
@section('title', 'My Interns')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <h1 class="page-shell-title">Assigned Interns</h1>
        <p class="page-shell-subtitle">List of students currently under your mentorship.</p>
    </div>
</div>

<div class="intern-grid mt-4">
    @forelse($interns as $intern)
        <div class="intern-card ui-card">
            <div class="intern-avatar">
                {{ strtoupper(substr($intern->name, 0, 1)) }}
            </div>
            <h3 class="intern-name">{{ $intern->name }}</h3>
            <p class="intern-email">{{ $intern->email }}</p>
            
            <div class="intern-meta">
                <span class="cell-mono" style="font-size: 10px; color: var(--ui-text-muted);">Assigned: {{ \Carbon\Carbon::parse($intern->assigned_at)->format('d M Y') }}</span>
                <x-ui.button :href="route('mentor.interns.progress', $intern->id)" size="sm" variant="ghost">View Progress</x-ui.button>
            </div>
        </div>
    @empty
        <div class="ui-card flex items-center justify-center p-8 w-full" style="grid-column: 1 / -1;">
            <p class="text-muted">No interns assigned to you yet.</p>
        </div>
    @endforelse
</div>
@endsection
```

## File: resources\views\mentor\Intern_progress.blade.php
```php
@extends('layouts.mentor')
@section('title', 'Intern Progress')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header {
        display: flex; justify-content: space-between;
        align-items: flex-start; padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5; margin-bottom: 28px;
    }
    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .back-link  {
        font-family: 'DM Mono', monospace; font-size: 11px;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: #888; text-decoration: none;
    }
    .back-link:hover { color: #1a1a1a; }

    .stat-mosaic {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 1px; background: #e5e5e5;
        border: 1px solid #e5e5e5; border-radius: 2px;
        overflow: hidden; margin-bottom: 28px;
    }
    .stat-cell { background: #fff; padding: 20px 22px; }
    .stat-label { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: #aaa; margin-bottom: 6px; }
    .stat-value { font-family: 'DM Mono', monospace; font-size: 28px; font-weight: 400; color: #1a1a1a; line-height: 1; }
    .stat-value.muted { color: #ccc; }

    .section-label { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: #aaa; margin-bottom: 14px; margin-top: 28px; }

    .table-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 12px 16px; font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: #888; font-family: 'DM Mono', monospace; border-bottom: 1px solid #e5e5e5; }
    .data-table td { padding: 13px 16px; border-bottom: 1px solid #f0f0f0; color: #1a1a1a; vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 2px; font-size: 10px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; }
    .badge-assigned   { background: #f0f0f0; color: #888; }
    .badge-submitted  { background: #e8f0f5; color: #1a5092; }
    .badge-evaluated  { background: #eaf5e8; color: #1a6a1a; }
    .badge-ai_evaluated { background: #e8f0f5; color: #1a5092; }
    .badge-reviewed   { background: #eaf5e8; color: #1a6a1a; }

    .grade-pill {
        font-family: 'DM Mono', monospace; font-size: 18px; font-weight: 500;
        width: 32px; height: 32px; border-radius: 2px;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .grade-A { background: #eafaea; color: #1a7a1a; }
    .grade-B { background: #e8eeff; color: #1a3a8a; }
    .grade-C { background: #fffbe8; color: #7a6000; }
    .grade-D { background: #fff0e8; color: #7a3010; }
    .grade-E { background: #fff0f0; color: #8a0000; }
    .grade-none { background: #f0f0f0; color: #ccc; }

    .mono { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .q-text { max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .empty-row td { padding: 32px 16px; text-align: center; font-family: 'DM Mono', monospace; font-size: 12px; color: #ccc; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ $intern->name }}</div>
        <div class="page-meta">{{ $intern->email }} · Progress Overview</div>
    </div>
    <a href="{{ route('mentor.interns') }}" class="back-link">← All Interns</a>
</div>

{{-- Stats --}}
<div class="stat-mosaic">
    <div class="stat-cell">
        <div class="stat-label">Assignments</div>
        <div class="stat-value">{{ $assignments->count() }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Submitted</div>
        <div class="stat-value">{{ $totalSubmissions }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Evaluated</div>
        <div class="stat-value">{{ $evaluatedCount }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Avg Score</div>
        <div class="stat-value {{ $avgScore ? '' : 'muted' }}">{{ $avgScore ?? '—' }}</div>
    </div>
</div>

{{-- Topic Assignments --}}
<div class="section-label">Topic Assignments</div>
<div class="table-card" style="margin-bottom:0;">
    <table class="data-table">
        <thead><tr>
            <th>Topic</th><th>Status</th><th>Grade</th><th>Deadline</th><th>Feedback</th>
        </tr></thead>
        <tbody>
        @forelse($assignments as $asgn)
            <tr>
                <td style="font-weight:500;">{{ $asgn->topic->title ?? '—' }}</td>
                <td><span class="badge badge-{{ $asgn->status }}">{{ ucfirst($asgn->status) }}</span></td>
                <td>
                    @if($asgn->grade)
                        <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                    @else
                        <span class="grade-pill grade-none">—</span>
                    @endif
                </td>
                <td class="mono">{{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}</td>
                <td style="font-size:12px;color:#666;max-width:260px;">
                    {{ $asgn->feedback ? Str::limit($asgn->feedback, 80) : '—' }}
                </td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No topic assignments</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Submissions --}}
<div class="section-label">All Submissions</div>
<div class="table-card">
    <table class="data-table">
        <thead><tr>
            <th>#</th><th>Question</th><th>Type</th><th>Status</th><th>Date</th>
        </tr></thead>
        <tbody>
        @forelse($submissions as $i => $sub)
            <tr>
                <td class="mono">{{ $i + 1 }}</td>
                <td><div class="q-text">{{ $sub->question->problem_statement ?? '—' }}</div></td>
                <td class="mono">{{ str_replace('_',' ', $sub->question->type ?? '') }}</td>
                <td><span class="badge badge-{{ $sub->status }}">{{ str_replace('_',' ', ucfirst($sub->status)) }}</span></td>
                <td class="mono">{{ $sub->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No submissions yet</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
```

## File: resources\views\mentor\submissions\index.blade.php
```php
@extends('layouts.app')
@section('title', 'Intern Submissions')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <h1 class="page-shell-title">Evaluation Center</h1>
        <p class="page-shell-subtitle">Review AI-evaluated topic assignments and individual question submissions.</p>
    </div>
</div>

{{-- Holistic Topic Assignments --}}
<div class="mt-8">
    <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4 flex items-center gap-2">
        <i data-lucide="layers" class="w-4 h-4"></i> Pending Topic Evaluations
    </h2>
    <div class="table-card ui-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Intern</th>
                    <th>Topic</th>
                    <th>AI Score</th>
                    <th>Grade</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingAssignments as $assignment)
                    <tr>
                        <td>
                            <div class="cell-name font-bold">{{ $assignment->intern->name }}</div>
                            <div class="text-[10px] opacity-50">{{ $assignment->intern->email }}</div>
                        </td>
                        <td>
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold uppercase">{{ $assignment->topic->title }}</span>
                        </td>
                        <td class="cell-mono font-bold text-blue-600">
                            {{ $assignment->score }}<span class="text-[10px] font-normal opacity-40">/100</span>
                        </td>
                        <td>
                            <span class="role-pill">{{ $assignment->grade }}</span>
                        </td>
                        <td style="text-align: right;">
                            <x-ui.button :href="route('mentor.assignment.review', $assignment->id)" size="sm" variant="primary">
                                Holistic Review
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state py-8">No pending topic evaluations.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Individual Question Submissions --}}
<div class="mt-12">
    <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4 flex items-center gap-2">
        <i data-lucide="help-circle" class="w-4 h-4"></i> Individual Answers
    </h2>
    <div class="table-card ui-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Intern Name</th>
                    <th>Question/Type</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSubmissions as $submission)
                    <tr>
                        <td>
                            <div class="cell-name">{{ $submission->intern->name }}</div>
                        </td>
                        <td>
                            <div class="text-[11px] font-bold">{{ $submission->question->topic->title }}</div>
                            <div class="cell-mono" style="font-size: 10px; opacity: 0.6;">{{ strtoupper(str_replace('_', ' ', $submission->question->type)) }}</div>
                        </td>
                        <td>
                            <x-badge :status="$submission->status" />
                        </td>
                        <td style="text-align: right;">
                            <x-ui.button :href="route('mentor.submissions.show', $submission->id)" size="sm" variant="secondary">
                                Details
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state py-8">No individual submissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\mentor\submissions\review.blade.php
```php
@extends('layouts.app')
@section('title', 'Reviewing Submission')

@push('styles')
<style>
    .review-wizard { max-width: 860px; margin: 0 auto; }
    .step-card { display: none; }
    .step-card.active { display: block; }
    
    .code-panel {
        background: #0f1117;
        padding: 24px;
        border-radius: var(--ui-radius-sm);
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        color: #e2e8f0;
        overflow-x: auto;
        white-space: pre-wrap;
        margin: 20px 0;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .evaluation-summary {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--ui-border);
    }
    
    .score-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #eef2ff;
        color: var(--ui-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        font-family: 'DM Mono', monospace;
    }

    .wizard-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--ui-border);
    }

    .topic-header-card {
        background: #fff;
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-radius);
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="review-wizard">
    <div class="page-shell-header">
        <div class="page-shell-copy">
            <a href="{{ route('mentor.interns.progress', $submission->intern_id) }}" class="cell-mono" style="text-decoration: none; color: var(--ui-text-muted); font-size: 11px; margin-bottom: 8px; display: block;">&larr; BACK TO INTERN SUBMISSIONS</a>
            <h1 class="page-shell-title">Review Session</h1>
        </div>
        <div class="page-shell-actions">
            <div class="cell-mono" style="font-weight: 700; color: var(--ui-primary);" id="wizard-progress">Step 1 of {{ $allSubmissions->count() }}</div>
        </div>
    </div>

    <!-- Persistent Context Card -->
    <div class="topic-header-card ui-card">
        <div>
            <div class="form-label">Intern</div>
            <div class="cell-name" style="font-size: 16px;">{{ $submission->intern->name }}</div>
        </div>
        <div>
            <div class="form-label">Topic</div>
            <span class="table-chip">{{ $submission->question->topic->title }}</span>
        </div>
        <div style="text-align: right;">
            <div class="form-label">Total Questions</div>
            <div class="cell-mono">{{ $allSubmissions->count() }}</div>
        </div>
    </div>

    <form id="multi-review-form" method="POST" action="{{ route('mentor.submissions.review', $submission->id) }}">
        @csrf
        
        <!-- STEP-BY-STEP MODULES -->
        <div class="wizard-steps">
            @foreach($allSubmissions as $index => $step)
                <div class="step-card ui-card {{ $step->id == $submission->id ? 'active' : '' }}" data-step="{{ $index + 1 }}">
                    <div class="ui-card-header">
                        <div class="cell-mono" style="font-size: 10px; text-transform: uppercase; color: var(--ui-primary); margin-bottom: 8px;">Question {{ $index + 1 }}</div>
                        <h3 class="ui-card-title">{{ $step->question->problem_statement }}</h3>
                        <p class="ui-card-subtitle">Language: {{ strtoupper($step->question->language ?? 'PHP') }}</p>
                    </div>

                    <div class="form-label">Student Submission</div>
                    <div class="code-panel">@if(empty($step->submitted_code)) // No code submitted @else{{ $step->submitted_code }}@endif</div>

                    <div class="evaluation-summary">
                        <div style="text-align: center;">
                            <div class="form-label">AI Score</div>
                            <div class="score-circle" style="margin: 8px auto;">{{ $step->ai_total_score ?? '?' }}</div>
                        </div>
                        <div>
                            <div class="form-label">AI Feedback</div>
                            <p style="font-size: 13px; line-height: 1.6; margin-top: 8px; color: var(--ui-text-soft);">
                                {{ $step->feedback ?? 'Evaluation pending or no AI feedback available.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- FINAL STEP: SUMMARY & GRADE -->
            <div class="step-card ui-card" data-step="{{ $allSubmissions->count() + 1 }}">
                <div class="ui-card-header" style="text-align: center;">
                    <h3 class="ui-card-title" style="font-size: 24px;">Complete Review</h3>
                    <p class="ui-card-subtitle">You have reviewed all individual question modules for this topic.</p>
                </div>

                @php
                    $assignment = $submission->intern->internTopicAssignments()
                        ->where('topic_id', $submission->question->topic_id)
                        ->first();
                @endphp

                @if($assignment && $assignment->status === 'evaluated')
                    <div class="p-4 mb-4 rounded" style="background: rgba(var(--ui-primary-rgb), 0.05); border: 1px solid var(--ui-primary);">
                        <div class="form-label" style="color: var(--ui-primary); font-weight: 700;">AI Senior Mentor Evaluation</div>
                        <div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 12px;">
                            <div class="score-circle" style="background: var(--ui-primary); color: #fff; flex-shrink: 0;">
                                {{ $assignment->grade }}
                            </div>
                            <div>
                                <p style="font-size: 14px; line-height: 1.6; color: var(--ui-text-soft);">
                                    {{ $assignment->feedback }}
                                </p>
                                @if(!empty($assignment->weak_areas))
                                    <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;">
                                        @foreach($assignment->weak_areas as $area)
                                            <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 4px 10px; font-size: 11px;">
                                                {{ $area }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div style="max-width: 400px; margin: 20px auto;">
                    <div class="form-group text-center">
                        <label class="form-label">Human Mentor Final Grade</label>
                        <select name="grade" class="form-input" style="font-size: 32px; font-weight: 700; text-align: center; height: auto; padding: 10px;">
                            <option value="A" {{ ($submission->grade ?? '') == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ ($submission->grade ?? '') == 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ ($submission->grade ?? '') == 'C' ? 'selected' : '' }}>C</option>
                            <option value="D" {{ ($submission->grade ?? '') == 'D' ? 'selected' : '' }}>D</option>
                        </select>
                    </div>
                    
                    <div class="form-group mt-4">
                        <label class="form-label">Reviewer's Final Feedback</label>
                        <textarea name="feedback" class="form-textarea" rows="4" placeholder="Your overall assessment of the intern's work..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER NAVIGATION -->
        <div class="wizard-footer">
            <x-ui.button type="button" id="prev-btn" variant="secondary" style="visibility: hidden;">Previous Question</x-ui.button>
            
            <div class="wizard-actions">
                <x-ui.button type="button" id="next-btn">Next Question</x-ui.button>
                <x-ui.button type="submit" id="submit-btn" style="display: none;">Submit Final Review</x-ui.button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step-card');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const progress = document.getElementById('wizard-progress');
        const totalSubmissions = {{ $allSubmissions->count() }};
        const totalSteps = totalSubmissions + 1;

        // Find initial active step based on $submission->id context
        let currentStepNum = parseInt(document.querySelector('.step-card.active').dataset.step);

        function updateWizard() {
            steps.forEach(s => s.classList.remove('active'));
            const currentStep = document.querySelector(`.step-card[data-step="${currentStepNum}"]`);
            currentStep.classList.add('active');

            // Header progress
            if (currentStepNum <= totalSubmissions) {
                progress.textContent = `Question ${currentStepNum} of ${totalSubmissions}`;
                nextBtn.textContent = (currentStepNum === totalSubmissions) ? 'Finalize Review' : 'Next Question';
                nextBtn.style.display = 'inline-flex';
                submitBtn.style.display = 'none';
            } else {
                progress.textContent = `Final Evaluaton`;
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-flex';
            }

            // Prev button visibility
            prevBtn.style.visibility = (currentStepNum === 1) ? 'hidden' : 'visible';
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        nextBtn.addEventListener('click', () => {
            if (currentStepNum < totalSteps) {
                currentStepNum++;
                updateWizard();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStepNum > 1) {
                currentStepNum--;
                updateWizard();
            }
        });

        // Initialize state
        updateWizard();
    });
</script>
@endpush

```

## File: resources\views\mentor\submissions\review_assignment.blade.php
```php
@extends('layouts.app')
@section('title', 'Holistic Review')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Intern Topic Assignment</div>
        <h1 class="page-shell-title">Holistic Evaluation</h1>
        <p class="page-shell-subtitle">Review the AI's assessment for {{ $assignment->intern->name }} on "{{ $assignment->topic->title }}". You can override the automated scoring and feedback here.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('mentor.submissions.index')" variant="secondary">Back to List</x-ui.button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
    <div class="lg:col-span-2 space-y-8">
        {{-- Submission Summary --}}
        <div class="card p-0 overflow-hidden bg-white border border-slate-200">
            <div class="p-6 border-bottom border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Question Breakdown</h3>
                <span class="text-xs font-medium text-slate-500">{{ $assignment->topic->questions->count() }} Questions Reviewed by AI</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                @foreach($assignment->topic->questions as $index => $question)
                    @php 
                        $submission = $question->submissions->first(); 
                    @endphp
                    <div class="p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-md bg-slate-900 text-white text-[10px] font-bold flex items-center justify-center">{{ $index + 1 }}</span>
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ str_replace('_', ' ', $question->type) }}</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $question->problem_statement }}</h4>
                            </div>
                            <div class="text-right">
                                @if($submission)
                                    <span class="text-[10px] font-bold uppercase py-1 px-2 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">Answered</span>
                                @else
                                    <span class="text-[10px] font-bold uppercase py-1 px-2 rounded-full bg-rose-50 text-rose-600 border border-rose-100">Unanswered</span>
                                @endif
                            </div>
                        </div>

                        @if($submission)
                            <div class="bg-slate-950 rounded-lg p-5 text-zinc-300 font-mono text-xs overflow-x-auto mb-4 border border-slate-800">
                                @if($question->type === 'mcq')
                                    <div class="text-emerald-400 font-bold mb-1">// Selected Option</div>
                                    <div class="text-white text-base">{{ $submission->submitted_code }}</div>
                                @elseif($question->type === 'description')
                                    <div class="text-blue-400 font-bold mb-1">// GitHub Repository</div>
                                    <a href="{{ $submission->github_link }}" target="_blank" class="text-white underline hover:text-blue-300 text-sm">{{ $submission->github_link ?? 'N/A' }}</a>
                                    @if($submission->file_path)
                                        <div class="mt-3">
                                            <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="px-3 py-1.5 bg-zinc-800 text-zinc-300 rounded-md hover:bg-zinc-700 transition-colors inline-flex items-center gap-2">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Attachment
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-slate-500 mb-2">// User Implementation</div>
                                    <pre class="leading-relaxed">{{ $submission->submitted_code }}</pre>
                                @endif
                            </div>
                        @else
                            <div class="py-10 text-center border-2 border-dashed border-slate-100 rounded-lg">
                                <p class="text-xs text-slate-400 italic">This section was skipped by the intern.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="space-y-8">
        {{-- Override Form --}}
        <div class="card p-8 bg-zinc-900 border-none shadow-2xl relative overflow-hidden text-white">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i data-lucide="award" class="w-32 h-32"></i>
            </div>

            <form action="{{ route('mentor.assignment.review.update', $assignment->id) }}" method="POST" class="relative z-10">
                @csrf
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-0.5 bg-blue-500 text-[10px] font-bold uppercase rounded text-white">AI Evaluation Summary</span>
                        <span class="text-xs text-zinc-500">Generated Holistic Assessment</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Final Score</label>
                            <input type="number" name="score" value="{{ $assignment->score }}" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Final Grade</label>
                            <input type="text" name="grade" value="{{ $assignment->grade }}" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Evaluation Tone</label>
                        <select name="tone" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                            <option value="constructive" {{ $assignment->tone === 'constructive' ? 'selected' : '' }}>Constructive</option>
                            <option value="encouraging" {{ $assignment->tone === 'encouraging' ? 'selected' : '' }}>Encouraging</option>
                            <option value="formal" {{ $assignment->tone === 'formal' ? 'selected' : '' }}>Formal</option>
                            <option value="critical" {{ $assignment->tone === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>

                    <div class="mb-8">
                        <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Feedback Summary</label>
                        <textarea name="feedback" rows="8" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-zinc-300 text-sm p-4 focus:ring-blue-500 outline-none leading-relaxed">{{ $assignment->feedback }}</textarea>
                    </div>

                    <x-ui.button type="submit" variant="primary" class="w-full py-4 font-black shadow-lg shadow-blue-900">
                        Finalize & Save Review
                    </x-ui.button>
                </div>
            </form>
        </div>

        {{-- Insights Box --}}
        <div class="card p-6 bg-slate-50 border-slate-200">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4">Mentor's Note</h4>
            <div class="text-xs text-slate-500 space-y-3 leading-relaxed">
                <p>The AI evaluated this entire task based on code structure, adherence to requirements, and task performance.</p>
                <p>You are reviewing the **Holistic Synthesis**. Any changes you make here will be immediately reflected on the intern's dashboard.</p>
            </div>
        </div>
    </aside>
</div>

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\mentor\submissions\show.blade.php
```php
@extends('layouts.app')
@section('title', 'Review Workspace')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Mentor review</div>
        <h1 class="page-shell-title">Review Workspace</h1>
        <p class="page-shell-subtitle">
            Evaluate the intern response, compare it with the task prompt, and save the final mentor score.
        </p>
    </div>

    <div class="page-shell-actions">
        <x-ui.button :href="route('mentor.reviews.index')" variant="secondary">Back to Reviews</x-ui.button>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Intern</div>
        <div class="summary-value summary-value--compact">{{ $submission->intern->name ?? 'Unknown' }}</div>
        <div class="summary-note">{{ $submission->intern->email ?? 'No email available' }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Task</div>
        <div class="summary-value summary-value--compact">{{ $question->topic->title ?? 'Untitled task' }}</div>
        <div class="summary-note">{{ ucfirst(str_replace('_', ' ', $question->type ?? 'unknown')) }} question</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Status</div>
        <div class="summary-value summary-value--compact">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</div>
        <div class="summary-note">Submitted {{ $submission->created_at->format('d M Y, h:i A') }}</div>
    </div>
</div>

<div class="review-shell">
    <x-ui.card title="Question and Answer" subtitle="Prompt, reference, and the intern response for this review.">
        <div class="detail-list">
            <div class="detail-list-item">
                <div class="detail-list-label">Prompt</div>
                <div class="question-statement">{{ $question->problem_statement }}</div>
            </div>

            @if($question->code)
                <div class="detail-list-item">
                    <div class="detail-list-label">Code Snippet</div>
                    <pre class="question-code">{{ $question->code }}</pre>
                </div>
            @endif

            @if($question->type === 'mcq')
                <div class="detail-list-item">
                    <div class="detail-list-label">Choices</div>
                    <div class="choice-list">
                        @foreach(['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d] as $key => $value)
                            @if($value)
                                <div class="choice-item {{ $question->correct_answer === $key ? 'is-selected' : '' }}">
                                    <span class="choice-bullet">{{ $key }}</span>
                                    <div>{{ $value }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if(in_array($question->type, ['mcq', 'blank', 'true_false', 'output']))
                <div class="detail-list-item">
                    <div class="detail-list-label">Expected Answer</div>
                    <div class="detail-list-value">
                        <span class="table-chip">Expected</span>
                        <span class="inline-gap-8">{{ $question->correct_answer }}</span>
                    </div>
                </div>
            @endif

            @if($reference)
                <div class="detail-list-item">
                    <div class="detail-list-label">Reference Solution</div>
                    <pre class="question-code">{{ $reference->solution_code }}</pre>
                    @if($reference->explanation)
                        <div class="detail-list-value">{{ $reference->explanation }}</div>
                    @endif
                </div>
            @endif

            <div class="detail-list-item">
                <div class="detail-list-label">Intern Answer</div>
                @if($question->type === 'mcq')
                    <div class="detail-list-value">
                        <span class="table-chip">Selected option {{ $submission->submitted_code }}</span>
                        <span class="inline-gap-8">{{ $question->getOptionText($submission->submitted_code) }}</span>
                    </div>
                @else
                    <pre class="answer-box answer-light">{{ $submission->submitted_code }}</pre>
                @endif
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Final Scoring" subtitle="Confirm the AI score or override it with mentor feedback.">
        <div class="review-score-grid">
            <div class="score-card">
                <div class="score-card-label">AI Score</div>
                <div class="score-card-value">{{ $submission->ai_total_score ?? '-' }}</div>
            </div>
            <div class="score-card">
                <div class="score-card-label">Current Final Score</div>
                <div class="score-card-value">{{ $submission->final_score ?? $submission->ai_total_score ?? '-' }}</div>
            </div>
        </div>

        @if($submission->feedback)
            <div class="detail-list-item mb-18">
                <div class="detail-list-label">AI Feedback</div>
                <div class="detail-list-value">{{ $submission->feedback }}</div>
            </div>
        @endif

        @if($submission->status === 'reviewed')
            <div class="detail-list">
                <div class="detail-list-item">
                    <div class="detail-list-label">Mentor Score</div>
                    <div class="detail-list-value">{{ $submission->mentor_override_score ?? $submission->final_score ?? 'Not provided' }}</div>
                </div>
                <div class="detail-list-item">
                    <div class="detail-list-label">Feedback</div>
                    <div class="detail-list-value">{{ $submission->feedback ?: 'No mentor feedback was added.' }}</div>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('mentor.submissions.review', $submission->id) }}">
                @csrf

                <x-ui.input label="Mentor Score" id="mentor_override_score" type="number" min="0" max="30"
                    name="mentor_override_score" :value="old('mentor_override_score', $submission->ai_total_score)" />

                <x-ui.textarea label="Feedback" id="feedback" name="feedback" rows="8">{{ old('feedback', $submission->feedback) }}</x-ui.textarea>

                <div class="form-actions">
                    <x-ui.button type="submit">Save Review</x-ui.button>
                    <x-ui.button :href="route('mentor.reviews.index')" variant="secondary">Cancel</x-ui.button>
                </div>
            </form>
        @endif
    </x-ui.card>
</div>
@endsection

```

## File: resources\views\mentor\topics\assign.blade.php
```php
@extends('layouts.mentor')
@section('title', 'Assignments')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 24px; }
    .page-title  { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta   { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .btn-create  { background: #1a1a1a; color: #fff; padding: 9px 18px; border-radius: 2px; font-size: 13px; font-weight: 500; text-decoration: none; transition: background 0.12s; }
    .btn-create:hover { background: #333; }

    .table-card  { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .data-table  { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 12px 16px; font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: #888; font-family: 'DM Mono', monospace; border-bottom: 2px solid #e5e5e5; }
    .data-table td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; color: #1a1a1a; vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .badge { display: inline-block; padding: 2px 10px; border-radius: 2px; font-size: 10px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; }
    .badge-assigned   { background: #f0f0f0; color: #888; }
    .badge-in_progress{ background: #e8f0f5; color: #1a5092; }
    .badge-submitted  { background: #fff8e8; color: #8a6000; }
    .badge-evaluated  { background: #eaf5e8; color: #1a6a1a; }

    .grade-pill { font-family: 'DM Mono', monospace; font-size: 16px; font-weight: 500; width: 28px; height: 28px; border-radius: 2px; display: inline-flex; align-items: center; justify-content: center; }
    .grade-A { background: #eafaea; color: #1a7a1a; }
    .grade-B { background: #e8eeff; color: #1a3a8a; }
    .grade-C { background: #fffbe8; color: #7a6000; }
    .grade-D { background: #fff0e8; color: #7a3010; }
    .grade-E { background: #fff0f0; color: #8a0000; }
    .grade-none { background: #f0f0f0; color: #ccc; }

    .mono { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .tlink { font-size: 13px; color: #555; text-decoration: underline; text-underline-offset: 2px; }
    .tlink:hover { color: #1a1a1a; }
    .overdue { color: #c0392b !important; }
    .empty-state { padding: 56px; text-align: center; font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Assignments</div>
        <div class="page-meta">{{ $assignments->count() }} total</div>
    </div>
    <a href="{{ route('mentor.topics.assign') }}" class="btn-create">+ New Assignment</a>
</div>

<div class="table-card">
    @if($assignments->isEmpty())
        <div class="empty-state">No assignments yet.<br>Publish a topic first, then assign it to an intern.</div>
    @else
    <table class="data-table">
        <thead><tr>
            <th>Intern</th><th>Topic</th><th>Status</th><th>Grade</th><th>Deadline</th><th>Assigned</th>
        </tr></thead>
        <tbody>
        @foreach($assignments as $asgn)
        @php $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast() && !in_array($asgn->status, ['submitted','evaluated']); @endphp
        <tr>
            <td>
                <div style="font-weight:500;">{{ $asgn->intern->name ?? '—' }}</div>
                <div class="mono">{{ $asgn->intern->email ?? '' }}</div>
            </td>
            <td style="color:#555;">{{ $asgn->topic->title ?? '—' }}</td>
            <td><span class="badge badge-{{ $asgn->status }}">{{ ucfirst(str_replace('_',' ',$asgn->status)) }}</span></td>
            <td>
                @if($asgn->grade)
                    <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                @else
                    <span class="grade-pill grade-none">—</span>
                @endif
            </td>
            <td class="mono {{ $isOverdue ? 'overdue' : '' }}">
                {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}
                @if($isOverdue) · overdue @endif
            </td>
            <td class="mono">{{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
```

## File: resources\views\mentor\topics\create.blade.php
```php
@extends('layouts.app')
@section('title', 'New Topic')

@section('content')
<div class="page-header">
    <div class="page-title">New Topic</div>
    <a href="{{ route('mentor.topics.index') }}" class="back-link">← All Topics</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('mentor.topics.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="form-input" placeholder="e.g. PHP Arrays & Functions" required>
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea"
                      placeholder="Brief description of what this topic covers...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group" style="margin-top:28px;">
            <div style="font-family:'DM Mono',monospace;font-size:11px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;color:#888;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #ebebeb;">
                Question Counts
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach(['mcq' => 'MCQ', 'blank' => 'Fill in Blank', 'true_false' => 'True / False', 'output' => 'Output', 'coding' => 'Coding'] as $key => $label)
                    <div>
                        <label class="form-label">{{ $label }}</label>
                        <input type="number" name="{{ $key }}_count"
                               value="{{ old($key.'_count', 0) }}"
                               min="0" class="form-input"
                               style="font-family:'DM Mono',monospace;">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Create Topic</button>
            <a href="{{ route('mentor.topics.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
```

## File: resources\views\mentor\topics\edit.blade.php
```php
@extends('layouts.app')
@section('title', 'Edit Topic')

@section('content')
<div class="page-header">
    <div class="page-title">Edit Topic</div>
    <a href="{{ route('mentor.topics.show', $topic->id) }}" class="back-link">← Back to Topic</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('mentor.topics.update', $topic->id) }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $topic->title) }}"
                   class="form-input" required>
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea">{{ old('description', $topic->description) }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('mentor.topics.show', $topic->id) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
```

## File: resources\views\mentor\topics\index.blade.php
```php
@extends('layouts.app')
@section('title', 'Task Management')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Mentor workspace</div>
        <h1 class="page-shell-title">Task Management</h1>
        <p class="page-shell-subtitle">
            Create task sets, review AI generation progress, and publish them once they are ready for interns.
        </p>
    </div>

    <div class="page-shell-actions">
        <x-ui.button data-modal-open="create-task-modal">+ Create Task</x-ui.button>
    </div>
</div>

<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Total Tasks</div>
        <div class="summary-value">{{ $stats['total'] }}</div>
        <div class="summary-note">All tasks created under your mentorship.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Published</div>
        <div class="summary-value">{{ $stats['published'] }}</div>
        <div class="summary-note">Ready to assign to interns.</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Needs Attention</div>
        <div class="summary-value">{{ $stats['needs_attention'] }}</div>
        <div class="summary-note">Draft or generated tasks waiting for your action.</div>
    </div>
</div>

<x-ui.table>
    @if($topics->isEmpty())
        <tbody>
            <tr>
                <td colspan="4" class="empty-state">No tasks yet. Use Create Task to add your first task.</td>
            </tr>
        </tbody>
    @else
        <thead>
            <tr>
                <th>Title</th>
                <th>Difficulty</th>
                <th>Created Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topics as $topic)
                <tr>
                    <td>
                        <div class="table-title">{{ $topic->title }}</div>
                        <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($topic->description ?: 'No description added yet.', 120) }}</div>
                    </td>
                    <td><x-badge :status="$topic->difficulty_label" /></td>
                    <td class="cell-mono">{{ $topic->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('mentor.topics.show', $topic->id) }}" class="action-link">Open</a>

                            @if($topic->status === 'draft')
                                <a href="{{ route('mentor.topics.edit', $topic->id) }}" class="action-link">Edit</a>
                                <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}" onsubmit="return confirm('Generate AI questions for this task now?')">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm">Generate AI</x-ui.button>
                                </form>
                            @endif

                            @if($topic->status === 'ai_generated')
                                <form method="POST" action="{{ route('mentor.topics.publish', $topic->id) }}" onsubmit="return confirm('Publish this task for intern assignment?')">
                                    @csrf
                                    <x-ui.button type="submit" size="sm">Publish</x-ui.button>
                                </form>
                            @endif

                            @if($topic->status === 'published')
                                <x-ui.button :href="route('mentor.topics.assign')" variant="secondary" size="sm">Assign</x-ui.button>
                            @endif

                            @if(in_array($topic->status, ['draft', 'ai_generated']))
                                <form method="POST" action="{{ route('mentor.topics.destroy', $topic->id) }}" onsubmit="return confirm('Delete this task permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-link text-danger-link">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endif
</x-ui.table>

{{ $topics->links() }}

<x-ui.modal id="create-task-modal" title="Create Task" subtitle="Add a task shell with a title, short description, and target difficulty.">
    <form method="POST" action="{{ route('mentor.tasks.store') }}">
        @csrf

        <x-ui.input label="Title" type="text" name="title" :value="old('title')" placeholder="Laravel API task" required />
        <x-ui.textarea label="Description" name="description" rows="5" placeholder="Describe what the intern should practice or solve...">{{ old('description') }}</x-ui.textarea>
        <x-ui.select label="Difficulty" name="difficulty" :options="['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard']" :selected="old('difficulty', 'medium')" />

        <div class="form-actions">
            <x-ui.button type="submit">Create Task</x-ui.button>
            <x-ui.button variant="secondary" data-modal-close="create-task-modal">Cancel</x-ui.button>
        </div>
    </form>
</x-ui.modal>
@endsection

```

## File: resources\views\mentor\topics\questions.blade.php
```php
@extends('layouts.app')
@section('title', ucfirst(str_replace('_', ' ', $type)) . ' Questions')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">{{ $topic->title }}</div>
        <h1 class="page-shell-title" style="text-transform: capitalize;">{{ str_replace('_', ' ', $type) }} Questions</h1>
        <p class="page-shell-subtitle">Review and manage the specific questions generated for this module.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('mentor.topics.show', $topic->id)" variant="secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Topic
        </x-ui.button>
    </div>
</div>

@if($questions->isEmpty())
    <div class="card p-20 text-center border-dashed bg-slate-50/50">
        <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="help-circle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">No questions found</h3>
        <p class="text-xs text-slate-400 mt-2">AI has not generated questions for this type yet.</p>
    </div>
@else
    <div class="mt-8 space-y-6">
        @foreach($questions as $i => $q)
            <div id="question-card-{{ $q->id }}" class="card p-0 overflow-hidden bg-white border border-slate-200 hover:shadow-xl transition-all duration-300 group">
                <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded bg-slate-900 text-white text-xs font-bold flex items-center justify-center">Q{{ $i + 1 }}</span>
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ str_replace('_', ' ', $type) }}</span>
                    </div>
                </div>

                <div class="p-8">
                    <div class="text-base font-bold text-slate-900 leading-relaxed mb-6 whitespace-pre-line">
                        {{ $q->problem_statement }}
                    </div>

                    @if($q->code)
                        <div class="bg-slate-950 rounded-lg p-6 mb-6 font-mono text-xs text-zinc-300 border border-slate-800 overflow-x-auto">
                            <div class="text-slate-500 mb-2">// Code Snippet</div>
                            <pre class="leading-relaxed">{{ $q->code }}</pre>
                        </div>
                    @endif

                    @if($type === 'mcq')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                            @foreach(['A', 'B', 'C', 'D'] as $key)
                                @php 
                                    $optProp = 'option_' . strtolower($key);
                                    $val = $q->$optProp;
                                    $isCorrect = $q->correct_answer === $key;
                                @endphp
                                @if($val)
                                    <div class="flex items-start gap-4 p-4 rounded-xl border {{ $isCorrect ? 'bg-emerald-50/50 border-emerald-200 ring-2 ring-emerald-500/10' : 'bg-slate-50/50 border-slate-100 opacity-80' }} transition-all">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $key }}
                                        </div>
                                        <div class="text-sm {{ $isCorrect ? 'text-emerald-900 font-bold' : 'text-slate-600' }}">{{ $val }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if(in_array($type, ['true_false', 'blank', 'output']))
                        <div class="mt-6 flex items-center gap-3">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Expected Result</span>
                            <div class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100 text-sm font-bold flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                {{ $q->correct_answer }}
                            </div>
                        </div>
                    @endif

                    @if($type === 'coding' && $q->referenceSolution?->solution_code)
                        <div class="mt-8 border-t border-slate-100 pt-8">
                            <div class="flex items-center gap-2 mb-4">
                                <i data-lucide="code-2" class="w-4 h-4 text-blue-500"></i>
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Reference Solution</span>
                            </div>
                            <div class="bg-zinc-900 rounded-xl p-6 font-mono text-xs text-blue-400 border border-zinc-800 overflow-x-auto shadow-inner">
                                <pre class="leading-loose">{{ $q->referenceSolution->solution_code }}</pre>
                            </div>
                            @if($q->referenceSolution->explanation)
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="text-[9px] font-black uppercase text-blue-500 mb-1">Logic Explanation</div>
                                    <p class="text-xs text-blue-900 leading-relaxed">{{ $q->referenceSolution->explanation }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    lucide.createIcons();
</script>
@endsection

```

## File: resources\views\mentor\topics\show.blade.php
```php
@extends('layouts.app')
@section('title', $topic->title)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $topic->title }}</div>
        <div class="page-meta" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
            <x-badge :status="$topic->status" />
            <span>{{ $topic->questions->count() }} questions total</span>
        </div>
    </div>
    <a href="{{ route('mentor.topics.index') }}" class="back-link">← All Topics</a>
</div>

@if($topic->description)
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:20px 24px;margin-bottom:24px;">
        <div class="section-label" style="margin-bottom:4px;">Description</div>
        <div style="font-size:13px;color:#1a1a1a;">{{ $topic->description }}</div>
    </div>
@endif

{{-- Action buttons based on topic status --}}
<div class="action-group" style="margin-bottom:28px;">
    @if($topic->status === 'draft')
        <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}"
              onsubmit="return confirm('Generate AI questions for this topic?')">
            @csrf
            <button class="btn-primary">✦ Generate AI Questions</button>
        </form>
        <a href="{{ route('mentor.topics.edit', $topic->id) }}" class="btn-outline">Edit Topic</a>
    @endif

    @if($topic->status === 'ai_generated')
        <form method="POST" action="{{ route('mentor.topics.publish', $topic->id) }}"
              onsubmit="return confirm('Publish this topic so it can be assigned to interns?')">
            @csrf
            <button class="btn-primary">Publish Topic</button>
        </form>
        <form method="POST" action="{{ route('mentor.topics.generateAI', $topic->id) }}"
              onsubmit="return confirm('Regenerate? Existing AI questions will be deleted.')">
            @csrf
            <button class="btn-outline">Regenerate</button>
        </form>
    @endif

    @if($topic->status === 'published')
        <a href="{{ route('mentor.topics.assign') }}" class="btn-primary">Assign to Intern</a>
    @endif

    @if(in_array($topic->status, ['draft', 'ai_generated']))
        <form method="POST" action="{{ route('mentor.topics.destroy', $topic->id) }}"
              onsubmit="return confirm('Permanently delete this topic?')">
            @csrf @method('DELETE')
            <button class="btn-danger">Delete</button>
        </form>
    @endif
</div>

{{-- Question type cards --}}
@php $grouped = $topic->questions->groupBy('type'); @endphp

@if($grouped->isEmpty())
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:56px;text-align:center;font-family:'DM Mono',monospace;font-size:13px;color:#aaa;">
        No questions yet.
        @if($topic->status === 'draft')
            Click "Generate AI Questions" above to create questions automatically.
        @endif
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1px;background:#e5e5e5;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
        @foreach($grouped as $type => $qs)
            <a href="{{ route('mentor.topics.questions', [$topic, $type]) }}"
               style="background:#fff;padding:24px 22px 20px;text-decoration:none;display:block;position:relative;transition:background 0.12s;">
                <div style="position:absolute;top:20px;right:20px;font-size:14px;color:#ccc;">→</div>
                <div class="module-type">{{ str_replace('_', ' ', $type) }}</div>
                <div class="module-count">{{ count($qs) }}</div>
                <div class="module-count-label">Questions</div>
            </a>
        @endforeach
    </div>
@endif
@endsection
```

## File: resources\views\partials\navbar.blade.php
```php
<header class="topbar">
    <div class="topbar-copy">
        <h2 class="topbar-title">@yield('title', 'Welcome Back')</h2>
        <p class="topbar-subtitle">Intern Management System v1.0</p>
    </div>

    <div class="topbar-user">
        <div class="topbar-user-copy">
            <p class="topbar-user-name">{{ Auth::user()->name }}</p>
            <p class="topbar-user-role">{{ ucfirst(Auth::user()->role->name ?? 'User') }}</p>
        </div>
        <div class="topbar-user-avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</header>

```

## File: resources\views\partials\sidebar.blade.php
```php
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-name">IPMS</div>
        <div class="sidebar-brand-sub">AI-Powered Intern Management</div>
    </div>

    <nav class="sidebar-nav">
        @php
            $role = auth()->user()->role->name ?? 'member';
        @endphp

        <!-- Common Sections -->
        <div class="nav-section-label">General</div>
        <a href="{{ route($role . '.dashboard') }}" class="nav-link {{ request()->routeIs($role . '.dashboard') ? 'active' : '' }}">
            <span class="nav-dot"></span>
            Dashboard
        </a>

        @if($role === 'mentor')
            <div class="nav-section-label">Management</div>
            
            <a href="{{ route('mentor.interns') }}" class="nav-link {{ request()->routeIs('mentor.interns*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Interns
            </a>

            <a href="{{ route('mentor.tasks.index') }}" class="nav-link {{ request()->routeIs('mentor.tasks*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Task Creation
            </a>

            <a href="{{ route('mentor.topics.assign') }}" class="nav-link {{ request()->routeIs('mentor.topics.assign*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Assignments
            </a>

        @elseif($role === 'intern')
            <div class="nav-section-label">Learning</div>
            
            <a href="{{ route('intern.topic') }}" class="nav-link {{ request()->routeIs('intern.topic*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Available Topics
            </a>

            <a href="{{ route('intern.submissions') }}" class="nav-link {{ request()->routeIs('intern.submissions*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                My Submissions
            </a>

            <a href="{{ route('intern.performance') }}" class="nav-link {{ request()->routeIs('intern.performance*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Performance
            </a>

        @elseif($role === 'hr')
            <div class="nav-section-label">Organization</div>
            
            <a href="{{ route('hr.users') }}" class="nav-link {{ request()->routeIs('hr.users*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                User Management
            </a>

            <a href="{{ route('hr.mentor.assignments') }}" class="nav-link {{ request()->routeIs('hr.mentor.assignments*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Mentor Assignment
            </a>

            <a href="{{ route('hr.intern.progress') }}" class="nav-link {{ request()->routeIs('hr.intern.progress*') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                Intern Progress
            </a>
        @endif

        <div class="nav-section-label">Account</div>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <span class="nav-dot"></span>
            Profile
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <span class="nav-dot" style="background:#ff4d4d"></span>
                Logout
            </button>
        </form>
    </div>
</aside>

```

## File: resources\views\profile\edit.blade.php
```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

```

## File: resources\views\profile\partials\delete-user-form.blade.php
```php
<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

```

## File: resources\views\profile\partials\update-password-form.blade.php
```php
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

```

## File: resources\views\profile\partials\update-profile-information-form.blade.php
```php
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

```

## File: resources\views\teamlead\dashboard.blade.php
```php
@extends('layouts.app')
@section('title', 'Mentor Dashboard')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Mentor Overview</div>
        <h1 class="page-shell-title">Dashboard</h1>
        <p class="page-shell-subtitle">Get a clear picture of your interns' progress and pending reviews.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="url('teamlead/submissions')" variant="secondary">View Submissions</x-ui.button>
    </div>
</div>

<div class="stat-mosaic">
    <div class="stat-cell">
        <div class="stat-label">Total Interns</div>
        <div class="stat-value">12</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Pending Reviews</div>
        <div class="stat-value warn">8</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Completed Reviews</div>
        <div class="stat-value accent">45</div>
    </div>
</div>
@endsection

```

## File: resources\views\teamlead\submissions\index.blade.php
```php
@extends('layouts.app')
@section('title', 'Submissions')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Submissions Module</div>
        <h1 class="page-shell-title">Intern Submissions</h1>
        <p class="page-shell-subtitle">Focus on reviewing one submission at a time to ensure quality feedback.</p>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Intern Name</th>
                <th>Topic</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="cell-name">John Smith</div>
                    <div class="cell-mono">john@example.com</div>
                </td>
                <td><span class="table-chip">Laravel Authentication</span></td>
                <td><x-badge status="pending" /></td>
                <td style="text-align: right;">
                    <x-ui.button :href="url('teamlead/submissions/review')" size="sm">Review</x-ui.button>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="cell-name">Emily Davis</div>
                    <div class="cell-mono">emily@example.com</div>
                </td>
                <td><span class="table-chip">React Hooks</span></td>
                <td><x-badge status="reviewed" /></td>
                <td style="text-align: right;">
                    <x-ui.button :href="url('teamlead/submissions/review')" variant="secondary" size="sm">View</x-ui.button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

```

## File: resources\views\teamlead\submissions\review.blade.php
```php
@extends('layouts.app')
@section('title', 'Review Submission')

@push('styles')
<style>
    .question-module { display: none; }
    .question-module.is-active { display: block; }
    
    .review-layout {
        max-width: 960px;
        margin: 0 auto;
    }
    
    .review-header-card {
        background: #fff;
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-radius);
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--ui-shadow-soft);
    }
    
    .code-viewer {
        background: #1a1a1a;
        color: #d4d4d4;
        padding: 20px;
        border-radius: var(--ui-radius-sm);
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
        white-space: pre-wrap;
        margin: 20px 0;
    }
    
    .ai-feedback-box {
        background: #f8fafc;
        border-left: 4px solid var(--ui-primary);
        padding: 20px;
        border-radius: var(--ui-radius-sm);
        margin-bottom: 24px;
    }
    
    .ai-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #eef2ff;
        color: var(--ui-primary-dark);
        font-family: 'DM Mono', monospace;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    
    .review-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--ui-border);
    }
    
    .final-score-panel {
        background: #fff;
        border: 1px solid var(--ui-border-strong);
        border-radius: var(--ui-radius);
        padding: 40px;
        text-align: center;
    }
    
    .module-step-indicator {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--ui-primary);
        margin-bottom: 10px;
        display: block;
    }
</style>
@endpush

@section('content')
<div class="review-layout">
    <div class="page-shell-header mb-0">
        <div class="page-shell-copy">
            <a href="{{ url('teamlead/submissions') }}" class="back-link mb-2">&larr; Back to List</a>
            <h1 class="page-shell-title">Review Submission</h1>
        </div>
    </div>

    <!-- Minimal Header Row -->
    <div class="review-header-card mt-4">
        <div>
            <div class="form-label">Intern</div>
            <div class="cell-name text-lg">John Smith</div>
        </div>
        <div>
            <div class="form-label">Topic</div>
            <div class="table-chip">Laravel Authentication</div>
        </div>
        <div>
            <div class="form-label">Status</div>
            <x-badge status="pending" />
        </div>
        <div style="text-align: right;">
            <div class="form-label">Progress</div>
            <div class="cell-mono" style="font-weight:700; color:var(--ui-primary);" id="progress-indicator">Step 1 of 2</div>
        </div>
    </div>

    <!-- ONE MODULE AT A TIME CONTAINER -->
    <div id="review-steps-container">
        
        <!-- Question 1 -->
        <div class="ui-card question-module is-active" data-step="1">
            <span class="module-step-indicator">Question 1</span>
            <h3 class="ui-card-title">Implement Auth Middleware</h3>
            <p class="ui-card-subtitle" style="margin-bottom:20px;">Create a middleware that checks if a user is authenticated. If not, redirect to login.</p>
            
            <div class="form-label">Submitted Code</div>
            <div class="code-viewer">
&lt;?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAuthenticated {
    public function handle($request, Closure $next) {
        if (!Auth::check()) {
            return redirect('login');
        }
        return $next($request);
    }
}
</div>
            
            <div class="ai-feedback-box">
                <div class="ai-score-badge">85</div>
                <div class="form-label" style="color:var(--ui-primary);">AI Evaluation Feedback</div>
                <p style="font-size:13px; margin-top:8px;">The logic is robust and securely handles unauthenticated requests via the facade. Proper namespaces and uses are defined.</p>
            </div>
        </div>

        <!-- Question 2 -->
        <div class="ui-card question-module" data-step="2">
            <span class="module-step-indicator">Question 2</span>
            <h3 class="ui-card-title">API Route Protection</h3>
            <p class="ui-card-subtitle" style="margin-bottom:20px;">Protect the '/api/users' route using sanctum middleware in web/api routes.</p>
            
            <div class="form-label">Submitted Code</div>
            <div class="code-viewer">
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
</div>
            
            <div class="ai-feedback-box">
                <div class="ai-score-badge">90</div>
                <div class="form-label" style="color:var(--ui-primary);">AI Evaluation Feedback</div>
                <p style="font-size:13px; margin-top:8px;">Perfect usage of the sanctum guard for API route protection.</p>
            </div>
        </div>

        <!-- Final Step: Score Output -->
        <div class="question-module" data-step="3">
            <div class="final-score-panel">
                <h3 style="font-size:24px; font-weight:700; margin-bottom:12px;">Final Evaluation</h3>
                <p style="font-size:14px; color:var(--ui-text-soft); max-width:400px; margin:0 auto 30px;">
                    You have reviewed all submitted questions. Enter the final score below to complete the evaluation.
                </p>
                
                <div style="max-width:260px; margin:0 auto; text-align:left;">
                    <div class="form-group">
                        <label class="form-label">Final Score / 100</label>
                        <input type="number" class="form-input" style="text-align:center; font-size:24px; font-weight:700; padding:16px;" placeholder="e.g. 88">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Global Navigation Actions -->
    <div class="review-navigation">
        <button type="button" class="btn-outline" id="btn-prev" disabled>Previous step</button>
        <button type="button" class="btn-primary" id="btn-next">Next step</button>
        <button type="button" class="btn-primary" id="btn-submit" style="display: none;">Submit Final Review</button>
    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modules = document.querySelectorAll('.question-module');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');
        const progressIndicator = document.getElementById('progress-indicator');
        
        let currentStep = 1;
        const totalSteps = modules.length;

        function updateUI() {
            // Update active module
            modules.forEach(m => m.classList.remove('is-active'));
            document.querySelector(`.question-module[data-step="${currentStep}"]`).classList.add('is-active');

            // Update Progress Indicator
            if (currentStep < totalSteps) {
                progressIndicator.textContent = `Step ${currentStep} of ${totalSteps - 1}`;
            } else {
                progressIndicator.textContent = `Finalizing`;
            }

            // Button States
            btnPrev.disabled = currentStep === 1;
            
            if (currentStep === totalSteps) {
                btnNext.style.display = 'none';
                btnSubmit.style.display = 'inline-flex';
            } else {
                btnNext.style.display = 'inline-flex';
                btnSubmit.style.display = 'none';
            }
        }

        btnNext.addEventListener('click', () => {
            if (currentStep < totalSteps) {
                currentStep++;
                updateUI();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        btnPrev.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                updateUI();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
</script>
@endpush

```

## File: resources\views\teamlead\topics.blade.php
```php
@extends('layouts.app')

@section('title', 'Manage Topics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manage Topics & Tasks</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTopicModal">
        <i class="bi bi-plus-lg me-1"></i> New Topic
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted uppercase small">
                    <tr>
                        <th class="ps-4">Topic Name</th>
                        <th>Assigned Interns</th>
                        <th>Questions</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4">
                            <h6 class="mb-0 fw-bold">Laravel Eloquent ORM</h6>
                            <small class="text-muted">Database interactions</small>
                        </td>
                        <td>
                            <div class="avatar-group">
                                <span class="badge bg-secondary rounded-pill">JS</span>
                                <span class="badge bg-secondary rounded-pill">ED</span>
                                <span class="text-muted small ms-1">+2 more</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark rounded-pill px-3">5 Questions</span>
                        </td>
                        <td><span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Active</span></td>
                        <td class="pe-4 text-end">
                            <button class="btn btn-sm btn-outline-info me-1" title="Generate AI Questions"><i class="bi bi-magic"></i> Generate</button>
                            <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-4">
                            <h6 class="mb-0 fw-bold">React Container Components</h6>
                            <small class="text-muted">Frontend Architecture</small>
                        </td>
                        <td>
                            <div class="avatar-group">
                                <span class="badge bg-secondary rounded-pill">MK</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark rounded-pill px-3">2 Questions</span>
                        </td>
                        <td><span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Active</span></td>
                        <td class="pe-4 text-end">
                            <button class="btn btn-sm btn-outline-info me-1" title="Generate AI Questions"><i class="bi bi-magic"></i> Generate</button>
                            <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Topic Modal -->
<div class="modal fade" id="createTopicModal" tabindex="-1" aria-labelledby="createTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createTopicModalLabel">Create New Topic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Topic Title</label>
                        <input type="text" class="form-control" placeholder="e.g. Advanced PHP Arrays">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Brief description of the topic..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Assign To Interns</label>
                        <select class="form-select" multiple>
                            <option>John Smith</option>
                            <option>Emily Davis</option>
                            <option>Michael King</option>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Topic</button>
            </div>
        </div>
    </div>
</div>
@endsection

```

## File: resources\views\vendor\pagination\ipms-simple.blade.php
```php
@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="app-pagination__meta">
            Page {{ $paginator->currentPage() }}
        </div>

        <div class="app-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true">
                    Previous
                </span>
            @else
                <a class="app-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="app-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next
                </a>
            @else
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif

```

## File: resources\views\vendor\pagination\ipms.blade.php
```php
@if ($paginator->hasPages())
    <nav class="app-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="app-pagination__meta">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>

        <div class="app-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    Previous
                </span>
            @else
                <a class="app-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    Previous
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="app-pagination__ellipsis" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="app-pagination__button app-pagination__button--active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="app-pagination__button" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="app-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                    Next
                </a>
            @else
                <span class="app-pagination__button app-pagination__button--disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif

```

## File: resources\views\welcome.blade.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Internship Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body style="background:#f5f5f4;color:#1a1a1a;font-family:'DM Sans',sans-serif;">

{{-- ── Nav ── --}}
<header style="background:#fff;border-bottom:1px solid #e5e5e5;padding:0 40px;
               height:52px;display:flex;align-items:center;justify-content:space-between;
               position:sticky;top:0;z-index:100;">
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:13px;font-weight:500;letter-spacing:-0.01em;">
            AI Internship Platform
        </span>
        <span style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;
                     letter-spacing:0.08em;text-transform:uppercase;">
            Enterprise
        </span>
    </div>
    <nav style="display:flex;align-items:center;gap:8px;">
        @auth
            <a href="{{ route(strtolower(auth()->user()->role->name) . '.dashboard') }}"
               class="btn-primary btn-sm">
                Dashboard →
            </a>
        @else
            <a href="{{ route('login') }}"
               style="font-size:13px;color:#555;text-decoration:none;padding:6px 12px;">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="btn-primary btn-sm">
                Request Access
            </a>
        @endauth
    </nav>
</header>

{{-- ── Hero ── --}}
<section style="max-width:1100px;margin:0 auto;padding:80px 40px 72px;
                display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:start;">
    <div>
        <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:0.1em;
                    text-transform:uppercase;color:#888;margin-bottom:20px;">
            Internship Lifecycle Management
        </div>

        <h1 style="font-size:36px;font-weight:500;letter-spacing:-0.03em;
                   line-height:1.18;color:#1a1a1a;margin-bottom:20px;">
            Structured internship<br>operations, end to end.
        </h1>

        <p style="font-size:14px;color:#666;line-height:1.75;
                  margin-bottom:32px;font-weight:300;">
            A role-based enterprise platform for HR teams, team leads, and interns.
            Manages onboarding, approvals, mentor supervision, task tracking,
            and AI-powered evaluations in one controlled environment.
        </p>

        @guest
            <div style="display:flex;gap:10px;">
                <a href="{{ route('register') }}" class="btn-primary">Request Access</a>
                <a href="{{ route('login') }}"    class="btn-outline">Sign In</a>
            </div>
        @endguest
    </div>

    {{-- Highlights panel --}}
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:28px;">
        <div class="section-label" style="margin-bottom:20px;">Platform Capabilities</div>

        @foreach([
            'Secure role-based access — HR, Mentor, Intern',
            'HR approval workflow before system access is granted',
            'AI-driven question generation and code evaluation',
            'Technology-based intern and mentor assignment',
            'Attendance tracking via office WiFi IP detection',
            'Controlled topic, submission and evaluation pipeline',
            'Digital certificates with unique verification codes',
        ] as $item)
            <div style="display:flex;align-items:flex-start;gap:12px;
                        padding:10px 0;border-bottom:1px solid #f0f0f0;
                        font-size:13px;color:#444;line-height:1.5;">
                <span style="width:5px;height:5px;border-radius:50%;background:#1a1a1a;
                             flex-shrink:0;margin-top:6px;"></span>
                {{ $item }}
            </div>
        @endforeach
    </div>
</section>

{{-- ── Role capabilities ── --}}
<section style="background:#fff;border-top:1px solid #e5e5e5;
                border-bottom:1px solid #e5e5e5;padding:64px 40px;">
    <div style="max-width:1100px;margin:0 auto;">

        <div class="section-label" style="margin-bottom:10px;">Access Levels</div>
        <div style="font-size:22px;font-weight:500;letter-spacing:-0.02em;margin-bottom:40px;">
            Role Capabilities
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;
                    background:#e5e5e5;border:1px solid #e5e5e5;
                    border-radius:2px;overflow:hidden;">

            @foreach([
                'HR' => [
                    'Approve or reject intern registrations',
                    'Assign technologies and mentors to interns',
                    'Monitor all intern progress and activity',
                    'Generate internship performance reports',
                ],
                'Mentor / Team Lead' => [
                    'Create and publish internship topics',
                    'Generate AI questions and review them',
                    'Evaluate intern submissions and override scores',
                    'Track assigned intern progress in detail',
                ],
                'Intern' => [
                    'Register with technology selection',
                    'Solve assigned exercises in built-in editor',
                    'View AI and mentor feedback on submissions',
                    'Track attendance, scores, and final grade',
                ],
            ] as $role => $items)
                <div style="background:#fff;padding:28px;">
                    <div style="font-family:'DM Mono',monospace;font-size:12px;font-weight:500;
                                letter-spacing:0.08em;text-transform:uppercase;color:#1a1a1a;
                                margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #ebebeb;">
                        {{ $role }}
                    </div>
                    @foreach($items as $item)
                        <div style="display:flex;align-items:flex-start;gap:10px;
                                    font-size:13px;color:#555;margin-bottom:10px;line-height:1.5;">
                            <span style="font-family:'DM Mono',monospace;font-size:11px;
                                         color:#ccc;flex-shrink:0;margin-top:1px;">—</span>
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ── Security section ── --}}
<section style="max-width:1100px;margin:0 auto;padding:64px 40px;
                display:grid;grid-template-columns:1fr 2fr;gap:60px;align-items:start;">
    <div>
        <div class="section-label" style="margin-bottom:10px;">Infrastructure</div>
        <div style="font-size:18px;font-weight:500;letter-spacing:-0.02em;line-height:1.4;">
            Enterprise Security Architecture
        </div>
    </div>
    <div>
        <p style="font-size:13.5px;color:#555;line-height:1.8;font-weight:300;margin-bottom:20px;">
            The system enforces multi-layered security including authentication,
            status-based login restrictions, role-based middleware authorization,
            resource-level policies, and rate limiting to prevent abuse.
            Designed to meet enterprise-level access control standards.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach(['Role Middleware','Status Gating','Rate Limiting',
                      'Email Verification','Resource Policies','CSRF Protection'] as $tag)
                <span style="font-family:'DM Mono',monospace;font-size:11px;color:#555;
                             background:#f0f0f0;padding:4px 10px;border-radius:2px;
                             letter-spacing:0.04em;">
                    {{ $tag }}
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Footer ── --}}
<footer style="background:#fff;border-top:1px solid #e5e5e5;padding:20px 40px;
               display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:12px;color:#aaa;font-family:'DM Mono',monospace;">
        AI Internship Platform
    </span>
    <span style="font-size:12px;color:#ccc;font-family:'DM Mono',monospace;">
        © {{ date('Y') }}
    </span>
</footer>

{{-- Responsive --}}
<style>
    @media (max-width: 768px) {
        section { grid-template-columns: 1fr !important; gap: 32px !important; }
        header  { padding: 0 20px !important; }
    }
</style>

</body>
</html>
```

## File: routes\auth.php
```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('office');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('office')
        ->name('logout');
});

```

## File: routes\console.php
```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

```

## File: routes\web.php
```php
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;

// HR
use App\Http\Controllers\HR\DashboardController as HRDashboardController;
use App\Http\Controllers\HR\UserController as HRUserController;
use App\Http\Controllers\HR\InternProgressController as HRInternProgressController;
use App\Http\Controllers\HR\MentorAssignmentController;

// Intern
use App\Http\Controllers\Intern\WaitingController;
use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\TopicController as InternTopicController;
use App\Http\Controllers\Intern\SubmissionController;

// Mentor
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\TopicController;
use App\Http\Controllers\Mentor\InternController;
use App\Http\Controllers\Mentor\TopicAssignController;
use App\Http\Controllers\Mentor\SubmissionReviewController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));


/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/

// Waiting: approved but no mentor yet
Route::get('/intern/waiting', [WaitingController::class, 'index'])
    ->middleware(['auth', 'verified', 'approved'])
    ->name('intern.waiting');

// All intern routes
Route::prefix('intern')
    ->middleware(['auth', 'verified', 'approved', 'assigned'])
    ->name('intern.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [InternDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/topic', [InternTopicController::class, 'index'])
            ->name('topic');

        Route::get('/tasks', [InternTopicController::class, 'index'])
            ->name('tasks');

        // Exam mode
        Route::get('/exam/{assignmentId}/{type}', [InternTopicController::class, 'exam'])
            ->name('exam');

        Route::get('/exercise/{assignmentId}/{type}', [InternTopicController::class, 'exam'])
            ->name('exercise');

        // AJAX: save a single answer during exam
        Route::post('/exam/save', [InternTopicController::class, 'saveAnswer'])
            ->name('exam.save');

        // Final submit — evaluates all answers via AI
        Route::post('/final-submit/{assignmentId}', [SubmissionController::class, 'finalSubmit'])
            ->name('final.submit');

        // PHP code runner for coding questions
        Route::post('/run-code', [SubmissionController::class, 'runCode'])
            ->middleware('throttle:code-executions')
            ->name('run.code');

        // Submissions list
        Route::get('/submissions', [SubmissionController::class, 'index'])
            ->name('submissions');

        Route::get('/attendance', [InternDashboardController::class, 'attendance'])
            ->name('attendance');

        Route::get('/performance', [InternDashboardController::class, 'performance'])
            ->name('performance');
    });


/*
|--------------------------------------------------------------------------
| Mentor Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'approved', 'checkrole:mentor'])
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function () {

        Route::get('/dashboard', [MentorDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/interns', [InternController::class, 'index'])
            ->name('interns');

        Route::get('/interns/{internId}/progress', [InternController::class, 'progress'])
            ->name('interns.progress');

        Route::get('/assignments', [TopicAssignController::class, 'index'])
            ->name('assignments');

        Route::get('/tasks', [TopicController::class, 'index'])
            ->name('tasks.index');

        Route::post('/tasks', [TopicController::class, 'store'])
            ->name('tasks.store');

        // IMPORTANT: These come BEFORE Route::resource('topics') to avoid pattern conflicts
        Route::get('/assign', [TopicAssignController::class, 'create'])
            ->name('topics.assign');

        Route::post('/assign', [TopicAssignController::class, 'store'])
            ->name('topics.assign.store');

        // Topic CRUD
        Route::resource('topics', TopicController::class);

        // AI question generation
        Route::post('topics/{topic}/generate-ai', [TopicController::class, 'generateAI'])
            ->middleware('throttle:ai-generations')
            ->name('topics.generateAI');

        // Publish topic
        Route::post('topics/{topic}/publish', [TopicController::class, 'publish'])
            ->name('topics.publish');

        // View questions by type
        Route::get('topics/{topic}/questions/{type}', [TopicController::class, 'showQuestions'])
            ->name('topics.questions');

        // Submission review
        Route::get('/submissions', [SubmissionReviewController::class, 'index'])
            ->name('submissions.index');

        Route::get('/reviews', [SubmissionReviewController::class, 'index'])
            ->name('reviews.index');

        Route::get('/submissions/{id}', [SubmissionReviewController::class, 'show'])
            ->name('submissions.show');

        Route::get('/assignment-review/{id}', [SubmissionReviewController::class, 'reviewAssignment'])
            ->name('assignment.review');

        Route::post('/assignment-review/{id}', [SubmissionReviewController::class, 'updateAssignmentReview'])
            ->name('assignment.review.update');

        Route::get('/review/{id}', [SubmissionReviewController::class, 'show'])
            ->name('reviews.show');

        Route::post('/submissions/{id}/review', [SubmissionReviewController::class, 'review'])
            ->name('submissions.review');
    });


/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'checkrole:hr'])
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        Route::get('/dashboard', [HRDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/attendance', [HRDashboardController::class, 'attendance'])
            ->name('attendance');

        Route::get('/users', [HRUserController::class, 'index'])
            ->name('users');

        Route::patch('/users/{id}/approve', [HRUserController::class, 'approve'])
            ->name('users.approve');

        Route::patch('/users/{id}/reject', [HRUserController::class, 'reject'])
            ->name('users.reject');

        Route::post('/assigned-mentor', [MentorAssignmentController::class, 'assign'])
            ->name('assigned.mentor');

        Route::get('/mentor-assignments', [MentorAssignmentController::class, 'index'])
            ->name('mentor.assignments');

        Route::get('/intern-mentor-list', [MentorAssignmentController::class, 'list'])
            ->name('intern.mentor.list');

        Route::get('/intern-progress', [HRInternProgressController::class, 'index'])
            ->name('intern.progress');

        Route::get('/intern-progress/{id}', [HRInternProgressController::class, 'show'])
            ->name('intern.progress.show');
    });


/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

```

## File: tailwind.config.js
```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0a0a0a',
                    muted: '#525252',
                },
                accent: {
                    DEFAULT: '#0066ff',
                    bg: '#eff6ff',
                }
            }
        },
    },

    plugins: [forms],
};

```

## File: tests\Feature\Auth\AuthenticationTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('intern.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}

```

## File: tests\Feature\Auth\EmailVerificationTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('intern.dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}

```

## File: tests\Feature\Auth\OfficeNetworkAuthTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeNetworkAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_blocked_outside_the_office_network(): void
    {
        config()->set('attendance.allowed_ips', ['127.0.0.1']);

        $user = User::factory()->create();

        $response = $this
            ->from('/login')
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.50'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'This action is only allowed from the office WiFi network.');
        $this->assertGuest();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_login_and_logout_record_attendance_from_allowed_ip(): void
    {
        config()->set('attendance.allowed_ips', ['127.0.0.1']);

        $user = User::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('intern.dashboard', absolute: false));

        $attendance = Attendance::first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->login_time);
        $this->assertNull($attendance->logout_time);

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/logout')
            ->assertRedirect('/');

        $attendance->refresh();

        $this->assertNotNull($attendance->logout_time);
        $this->assertGreaterThanOrEqual(0, $attendance->total_seconds);
    }
}

```

## File: tests\Feature\Auth\PasswordConfirmationTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}

```

## File: tests\Feature\Auth\PasswordResetTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_request_does_not_reveal_unknown_email_addresses(): void
    {
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $response
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_password_reset_invalidates_existing_database_sessions(): void
    {
        $user = User::factory()->create();

        $this->app['db']->table('sessions')->insert([
            'id' => 'existing-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('sessions', [
            'id' => 'existing-session-id',
        ]);
    }
}

```

## File: tests\Feature\Auth\PasswordUpdateTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
}

```

## File: tests\Feature\Auth\RegistrationTest.php
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $role = Role::firstOrCreate(['name' => 'mentor']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role->name,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
    }
}

```

## File: tests\Feature\ExampleTest.php
```php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

```

## File: tests\Feature\ProfileTest.php
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}

```

## File: tests\TestCase.php
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}

```

## File: tests\Unit\ExampleTest.php
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}

```

## File: vite.config.js
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});

```

