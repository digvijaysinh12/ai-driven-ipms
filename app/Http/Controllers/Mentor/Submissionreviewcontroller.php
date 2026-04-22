<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewTaskRequest;
use App\Models\TaskSubmission;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionReviewController extends Controller
{
    public function __construct(private readonly SubmissionService $submissionService)
    {
    }

    public function index(Request $request): View
    {
        $submissions = $this->submissionService->listForMentor($request->user());

        return view('mentor.submissions.index', compact('submissions'));
    }

    public function show(TaskSubmission $submission): View
    {

        $submission->load([
            'task:id,title,task_type_id',
            'task.type:id,name,slug',
            'task.questions:id,task_id,question,options,correct_answer,source',
            'answers:id,task_submission_id,task_question_id,answer_text,file_path,github_link,ai_score,ai_feedback',
            'intern:id,name,email',
            'reviewer:id,name,email',
        ]);
        return view('mentor.submissions.show', compact('submission'));
    }

    public function review(TaskSubmission $submission, \Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string'
        ]);

        $this->submissionService->review(
            $submission,
            $request->user(),
            (float) $request->score,
            $request->feedback,
        );

        return redirect()
            ->route('user.mentor.submissions.show', $submission)
            ->with('success', 'Submission reviewed successfully.');
    }

    public function aiEvaluate(TaskSubmission $submission, Request $request): RedirectResponse
    {
        $this->authorize('review', $submission);

        $this->submissionService->evaluateNow($submission, $request->user());

        return back()->with('success', 'AI evaluation completed instantly.');

    }
}
