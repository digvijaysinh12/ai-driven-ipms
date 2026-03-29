<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
=======
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
use App\Models\Question;
use App\Models\Submission;
use App\Models\InternTopicAssignment;
use App\Services\GroqEvaluationService;

class SubmissionController extends Controller
{
<<<<<<< HEAD
    /**
     * FINAL SUBMIT — sends all answers to AI in ONE prompt.
     * AI returns a grade (A/B/C/D/E) + overall feedback.
     */
    public function finalSubmit(Request $request, int $assignmentId, GroqEvaluationService $evaluator)
    {
        $internId = Auth::id();
=======
    // ─────────────────────────────────────────────────────────────
    // FINAL SUBMIT — sends all answers to AI in ONE prompt
    // AI returns a grade (A/B/C/D/E) + overall feedback
    // ─────────────────────────────────────────────────────────────
    public function finalSubmit(Request $request, int $assignmentId, GroqEvaluationService $evaluator)
    {
        $internId   = Auth::id();
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

        $assignment = InternTopicAssignment::where('id', $assignmentId)
            ->where('intern_id', $internId)
            ->with('topic')
            ->firstOrFail();

<<<<<<< HEAD
        // Guard: already submitted/evaluated
=======
        // Guard: already evaluated
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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
<<<<<<< HEAD
        DB::transaction(function () use ($assignment) {
=======
        \DB::transaction(function () use ($assignment) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            $assignment->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);
        });

<<<<<<< HEAD
        // Send ALL answers to Groq in one prompt
=======
        // ── Send ALL answers to Groq in one prompt ──────────────
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        try {
            $result = $evaluator->evaluateExercise($assignment);

            return redirect()->route('intern.topic')
                ->with('exercise_result', [
                    'grade'    => $result['grade'],
                    'summary'  => $result['summary'],
                    'feedback' => $result['feedback'],
                ]);

        } catch (\Exception $e) {
<<<<<<< HEAD
            Log::error("Exercise evaluation failed for assignment {$assignmentId}: " . $e->getMessage());

            return redirect()->route('intern.topic')
                ->with('success', 'Exercise submitted! AI evaluation is in progress — check back shortly.');
        }
    }

    /**
     * Submissions list — show grade when evaluated.
     */
=======
            \Log::error("Exercise evaluation failed for assignment {$assignmentId}: " . $e->getMessage());

            // Submission is saved, evaluation pending — mentor can grade manually
            return redirect()->route('intern.topic')
                ->with('success', 'Exercise submitted! Evaluation is being processed — check back shortly.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Submissions list — show grade when evaluated, hide raw scores
    // ─────────────────────────────────────────────────────────────
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    public function index()
    {
        $internId = Auth::id();

<<<<<<< HEAD
=======
        // Load topic assignments with grade result
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $assignments = InternTopicAssignment::where('intern_id', $internId)
            ->with('topic')
            ->latest('assigned_at')
            ->get();

        $submissions = Submission::where('intern_id', $internId)
            ->with('question')
            ->latest()
            ->paginate(20);

        $totalSubmissions = $submissions->total();
<<<<<<< HEAD
        $reviewedCount    = Submission::where('intern_id', $internId)
            ->where('status', 'reviewed')
            ->count();
=======
        $reviewedCount    = $submissions->where('status', 'reviewed')->count();
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

        return view('intern.submissions', compact(
            'assignments',
            'submissions',
            'totalSubmissions',
            'reviewedCount'
        ));
    }

<<<<<<< HEAD
    /**
     * PHP Code runner for coding questions.
     */
=======
    // ─────────────────────────────────────────────────────────────
    // PHP Code runner for coding questions in the exercise
    // ─────────────────────────────────────────────────────────────
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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
<<<<<<< HEAD
                    'error' => "Function '{$fn}()' is not allowed in the exercise runner.",
=======
                    'error' => "Function '{$fn}()' is not allowed in the exercise runner."
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                ]);
            }
        }

<<<<<<< HEAD
        $tmpFile     = tempnam(sys_get_temp_dir(), 'ex_') . '.php';
=======
        $tmpFile = tempnam(sys_get_temp_dir(), 'ex_') . '.php';

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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