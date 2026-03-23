<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Topic;
use App\Models\InternTopicAssignment;

class TopicAssignController extends Controller
{
    // ─────────────────────────────────────────────
    // Show assignment form
    // Mentor selects: intern → topic → deadline
    // ─────────────────────────────────────────────
    public function create()
    {
        $mentorId = Auth::id();

        // Interns actively assigned to this mentor
        $interns = DB::table('mentor_assignments')
            ->join('users', 'mentor_assignments.intern_id', '=', 'users.id')
            ->where('mentor_assignments.mentor_id', $mentorId)
            ->where('mentor_assignments.is_active', 1)
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        // Only published topics by this mentor (ready to assign)
        $topics = Cache::remember("mentor.{$mentorId}.published_topics", 60*60, function () use ($mentorId) {
            return Topic::where('mentor_id', $mentorId)
                ->where('status', 'published')
                ->withCount('questions')
                ->orderBy('title')
                ->get();
        });

        return view('mentor.topics.assign', compact('interns', 'topics'));
    }

    // ─────────────────────────────────────────────
    // Store the assignment
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'topic_id'  => 'required|exists:topics,id',
            'deadline'  => 'required|date|after:today',
        ]);

        $mentorId = Auth::id();

        // Security: confirm topic belongs to this mentor and is published
        $topic = Topic::where('id', $request->topic_id)
            ->where('mentor_id', $mentorId)
            ->where('status', 'published')
            ->firstOrFail();

        // Prevent same topic being assigned twice to same intern
        $alreadyAssigned = InternTopicAssignment::where('intern_id', $request->intern_id)
            ->where('topic_id', $request->topic_id)
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->withInput()
                ->withErrors(['topic_id' => 'This topic is already assigned to this intern.']);
        }

        DB::transaction(function () use ($request, $mentorId) {
            InternTopicAssignment::create([
                'intern_id'   => $request->intern_id,
                'topic_id'    => $request->topic_id,
                'assigned_by' => $mentorId,
                'deadline'    => $request->deadline,
                'status'      => 'assigned',
                'assigned_at' => now(),
            ]);
        });

        return redirect()
            ->route('mentor.topics.index')
            ->with('success', 'Topic assigned to intern successfully.');
    }

    // ─────────────────────────────────────────────
    // List all assignments made by this mentor
    // ─────────────────────────────────────────────
    public function index()
    {
        $mentorId = Auth::id();

        $topicIds = Topic::where('mentor_id', $mentorId)->pluck('id');

        $assignments = InternTopicAssignment::whereIn('topic_id', $topicIds)
            ->with(['intern', 'topic'])
            ->latest('assigned_at')
            ->get();

        return view('mentor.assignments', compact('assignments'));
    }
}