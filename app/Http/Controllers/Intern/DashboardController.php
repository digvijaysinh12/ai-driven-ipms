<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
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

        // ── Mentor info ──────────────────────────────────────────
        $mentorAssignment = MentorAssignment::where('intern_id', $intern->id)
            ->where('is_active', true)
            ->with('mentor')
            ->first();

        $mentor = $mentorAssignment?->mentor;

        // ── Current (latest) topic assignment ────────────────────
        $currentAssignment = InternTopicAssignment::where('intern_id', $intern->id)
            ->with('topic')
            ->latest()
            ->first();

        // ── Stats ────────────────────────────────────────────────

        // Total topic assignments ever given to this intern
        $topicCount = InternTopicAssignment::where('intern_id', $intern->id)->count();

        // Total questions in the current topic
        $questionCount = 0;
        if ($currentAssignment?->topic) {
            $questionCount = $currentAssignment->topic->questions()->count();
        }

        // How many questions this intern has submitted answers for
        $submittedCount = Submission::where('intern_id', $intern->id)->count();

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

        return view('intern.dashboard', compact(
            'mentor',
            'currentAssignment',
            'topicCount',
            'questionCount',
            'submittedCount',
            'pendingCount',
            'evaluatedCount',
            'reviewedCount',
            'avgScore'
        ));
    }
}