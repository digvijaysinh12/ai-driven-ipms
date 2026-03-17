<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Topic;

class SubmissionReviewController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Index — loads EVERYTHING for the accordion
    // No separate show page needed
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $mentorId    = Auth::id();
        $topicIds    = Topic::where('mentor_id', $mentorId)->pluck('id');
        $questionIds = Question::whereIn('topic_id', $topicIds)->pluck('id');

        $with = ['question.topic', 'question.referenceSolution', 'intern'];

        $pendingSubmissions = Submission::whereIn('question_id', $questionIds)
            ->where('status', 'ai_evaluated')
            ->with($with)
            ->latest()
            ->get();

        $reviewedSubmissions = Submission::whereIn('question_id', $questionIds)
            ->where('status', 'reviewed')
            ->with($with)
            ->latest()
            ->get();

        return view('mentor.submissions.index', compact(
            'pendingSubmissions',
            'reviewedSubmissions'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // Show — kept for direct URL access, redirects to index
    // ─────────────────────────────────────────────────────────────
    public function show(int $submissionId)
    {
        return redirect()->route('mentor.submissions.index')
            ->with('open_submission', $submissionId);
    }

    // ─────────────────────────────────────────────────────────────
    // Review — AJAX + regular POST both supported
    // ─────────────────────────────────────────────────────────────
    public function review(Request $request, int $submissionId)
    {
        $request->validate([
            'mentor_override_score' => 'required|integer|min:0|max:30',
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
            ->route('mentor.submissions.index')
            ->with('success', 'Review saved successfully.');
    }

    private function authorizeSubmission(Submission $submission): void
    {
        $mentorId = Auth::id();
        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');
        $allowed  = Question::whereIn('topic_id', $topicIds)
            ->where('id', $submission->question_id)
            ->exists();

        abort_unless($allowed, 403, 'Unauthorized.');
    }
}