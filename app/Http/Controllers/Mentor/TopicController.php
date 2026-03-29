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
        $topics = Topic::where('mentor_id', Auth::id())
            ->withCount('questions')
            ->latest()
            ->paginate(10);

        return view('mentor.topics.index', compact('topics'));
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

    /**
     * Show topic detail — question type cards.
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