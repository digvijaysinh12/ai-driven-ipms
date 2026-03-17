<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Services\GroqQuestionService;

class TopicController extends Controller
{
    // ─────────────────────────────────────────────
    // List all topics for this mentor
    // ─────────────────────────────────────────────
    public function index()
    {
        $topics = Topic::where('mentor_id', Auth::id())
            ->withCount('questions')
            ->latest()
            ->get();

        return view('mentor.topics.index', compact('topics'));
    }

    // ─────────────────────────────────────────────
    // Show create form
    // ─────────────────────────────────────────────
    public function create()
    {
        return view('mentor.topics.create');
    }

    // ─────────────────────────────────────────────
    // Store new topic
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'mcq_count'       => 'nullable|integer|min:0|max:50',
            'blank_count'     => 'nullable|integer|min:0|max:50',
            'true_false_count'=> 'nullable|integer|min:0|max:50',
            'output_count'    => 'nullable|integer|min:0|max:50',
            'coding_count'    => 'nullable|integer|min:0|max:50',
        ]);

        Topic::create([
            'mentor_id'        => Auth::id(),
            'title'            => $request->title,
            'description'      => $request->description,
            'status'           => 'draft',
            'mcq_count'        => $request->mcq_count        ?? 0,
            'blank_count'      => $request->blank_count      ?? 0,
            'true_false_count' => $request->true_false_count ?? 0,
            'output_count'     => $request->output_count     ?? 0,
            'coding_count'     => $request->coding_count     ?? 0,
        ]);

        return redirect()
            ->route('mentor.topics.index')
            ->with('success', 'Topic created successfully.');
    }

    // ─────────────────────────────────────────────
    // Show topic detail — question type cards
    // ─────────────────────────────────────────────
    public function show(Topic $topic)
    {
        $this->authorizeTopic($topic);

        // Count per type for the mosaic cards
        $typeCounts = $topic->questions()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('mentor.topics.show', compact('topic', 'typeCounts'));
    }

    // ─────────────────────────────────────────────
    // Show questions list for a specific type
    // ─────────────────────────────────────────────
    public function showQuestions(Topic $topic, string $type)
    {
        $this->authorizeTopic($topic);

        $validTypes = ['mcq', 'blank', 'true_false', 'output', 'coding'];
        abort_unless(in_array($type, $validTypes), 404);

        $questions = $topic->questions()
            ->where('type', $type)
            ->with('referenceSolution')
            ->get();

        return view('mentor.topics.questions', compact('topic', 'questions', 'type'));
    }

    // ─────────────────────────────────────────────
    // Trigger AI question generation via Groq
    // ─────────────────────────────────────────────
    public function generateAI(Topic $topic, GroqQuestionService $aiService)
    {
        $this->authorizeTopic($topic);

        // Only allow generation on draft or re-generation on ai_generated
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

        $errors = [];

        foreach ($modules as $type => $count) {
            if ($count <= 0) continue;

            try {
                $aiService->generateQuestions($topic, $type, $count);
            } catch (\Exception $e) {
                $errors[] = "Failed to generate {$type} questions: " . $e->getMessage();
            }
        }

        $topic->update(['status' => 'ai_generated']);

        if (!empty($errors)) {
            return back()->with('error', implode(' | ', $errors));
        }

        return back()->with('success', 'AI questions generated successfully. Review them before publishing.');
    }

    // ─────────────────────────────────────────────
    // Publish a topic (makes it assignable to interns)
    // ─────────────────────────────────────────────
    public function publish(Topic $topic)
    {
        $this->authorizeTopic($topic);

        if ($topic->questions()->count() === 0) {
            return back()->with('error', 'Cannot publish a topic with no questions.');
        }

        $topic->update(['status' => 'published']);

        return back()->with('success', 'Topic published successfully. It can now be assigned to interns.');
    }

    // ─────────────────────────────────────────────
    // Delete a topic (only draft/ai_generated)
    // ─────────────────────────────────────────────
    public function destroy(Topic $topic)
    {
        $this->authorizeTopic($topic);

        if ($topic->status === 'published') {
            return back()->with('error', 'Published topics cannot be deleted.');
        }

        $topic->delete();

        return redirect()
            ->route('mentor.topics.index')
            ->with('success', 'Topic deleted.');
    }

    // ─────────────────────────────────────────────
    // Private helper — abort if topic doesn't belong to this mentor
    // ─────────────────────────────────────────────
    private function authorizeTopic(Topic $topic): void
    {
        if ($topic->mentor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this topic.');
        }
    }
}