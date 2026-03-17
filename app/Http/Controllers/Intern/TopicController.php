<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\InternTopicAssignment;
use App\Models\Submission;

class TopicController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Topic overview page — module cards + final submit
    // ─────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────
    // Exam mode — one question at a time for a given type module
    // ─────────────────────────────────────────────────────────────
    public function exam(int $assignmentId, string $type)
    {
        $validTypes = ['mcq', 'blank', 'true_false', 'output', 'coding'];
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

        $answeredMap = $questions->pluck('id')->mapWithKeys(fn($id) => [
            $id => in_array($id, $submittedIds)
        ])->toArray();

        // Build saved answers map: question_id => submitted_code (for review/resume)
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

    // ─────────────────────────────────────────────────────────────
    // AJAX — save a single answer during exam (no evaluation yet)
    // ─────────────────────────────────────────────────────────────
    public function saveAnswer(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'question_id'    => 'required|exists:questions,id',
            'submitted_code' => 'required|string|min:1',
        ]);

        $internId   = Auth::id();
        $questionId = $request->question_id;

        // Verify this question belongs to intern's assigned topic
        $assignment = InternTopicAssignment::where('intern_id', $internId)
            ->latest()->first();

        if (!$assignment) {
            return response()->json(['ok' => false, 'msg' => 'No assignment found'], 403);
        }

        $belongsToTopic = \App\Models\Question::where('id', $questionId)
            ->where('topic_id', $assignment->topic_id)
            ->exists();

        if (!$belongsToTopic) {
            return response()->json(['ok' => false, 'msg' => 'Question not in your topic'], 403);
        }

        // Upsert — create or update the submission row (status stays 'submitted' until final)
        \App\Models\Submission::updateOrCreate(
            ['intern_id' => $internId, 'question_id' => $questionId],
            ['submitted_code' => $request->submitted_code, 'status' => 'submitted']
        );

        return response()->json(['ok' => true]);
    }
}