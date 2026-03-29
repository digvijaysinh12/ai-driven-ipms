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

        return view('intern.attendance.index', compact('mentorAssignment', 'currentAssignment'));
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
