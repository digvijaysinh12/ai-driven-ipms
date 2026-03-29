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

        return view('mentor.interns', compact('interns'));
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

        return view('mentor.intern_progress', compact(
            'intern',
            'assignments',
            'submissions',
            'totalSubmissions',
            'evaluatedCount',
            'avgScore'
        ));
    }
}