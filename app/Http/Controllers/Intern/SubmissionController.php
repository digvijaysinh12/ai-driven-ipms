<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\Submission;
use App\Models\InternTopicAssignment;
use App\Services\GroqEvaluationService;

class SubmissionController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // FINAL SUBMIT — sends all answers to AI in ONE prompt
    // AI returns a grade (A/B/C/D/E) + overall feedback
    // ─────────────────────────────────────────────────────────────
    public function finalSubmit(Request $request, int $assignmentId, GroqEvaluationService $evaluator)
    {
        $internId   = Auth::id();

        $assignment = InternTopicAssignment::where('id', $assignmentId)
            ->where('intern_id', $internId)
            ->with('topic')
            ->firstOrFail();

        // Guard: already evaluated
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
        \DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);
        });

        // ── Send ALL answers to Groq in one prompt ──────────────
        try {
            $result = $evaluator->evaluateExercise($assignment);

            return redirect()->route('intern.topic')
                ->with('exercise_result', [
                    'grade'    => $result['grade'],
                    'summary'  => $result['summary'],
                    'feedback' => $result['feedback'],
                ]);

        } catch (\Exception $e) {
            \Log::error("Exercise evaluation failed for assignment {$assignmentId}: " . $e->getMessage());

            // Submission is saved, evaluation pending — mentor can grade manually
            return redirect()->route('intern.topic')
                ->with('success', 'Exercise submitted! Evaluation is being processed — check back shortly.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Submissions list — show grade when evaluated, hide raw scores
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $internId = Auth::id();

        // Load topic assignments with grade result
        $assignments = InternTopicAssignment::where('intern_id', $internId)
            ->with('topic')
            ->latest('assigned_at')
            ->get();

        $submissions = Submission::where('intern_id', $internId)
            ->with('question')
            ->latest()
            ->paginate(20);

        $totalSubmissions = $submissions->total();
        $reviewedCount    = $submissions->where('status', 'reviewed')->count();

        return view('intern.submissions', compact(
            'assignments',
            'submissions',
            'totalSubmissions',
            'reviewedCount'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // PHP Code runner for coding questions in the exercise
    // ─────────────────────────────────────────────────────────────
    public function runCode(Request $request)
    {
        $request->validate(['code' => 'required|string|max:5000']);

        $code = $request->code;

        $blocked = [
            'exec', 'shell_exec', 'system', 'passthru', 'popen',
            'proc_open', 'pcntl_exec', 'file_put_contents', 'unlink',
            'rmdir', 'rename', 'copy', 'eval', 'assert', 'create_function',
        ];

        foreach ($blocked as $fn) {
            if (stripos($code, $fn) !== false) {
                return response()->json([
                    'error' => "Function '{$fn}()' is not allowed in the exercise runner."
                ]);
            }
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'ex_') . '.php';

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

        return response()->json(['output' => $output]);
    }
}