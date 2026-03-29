<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Topic;
use App\Models\Question;
use App\Models\Submission;
use App\Models\InternTopicAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $mentorId = Auth::id();

        // Total active interns assigned to this mentor
        $internCount = DB::table('mentor_assignments')
            ->where('mentor_id', $mentorId)
            ->where('is_active', 1)
            ->count();

        // All topics by this mentor
        $topics = Topic::where('mentor_id', $mentorId)->get();
        $topicIds = $topics->pluck('id');

        $topicCount = $topics->count();

        // Total questions across all topics
        $questionCount = Question::whereIn('topic_id', $topicIds)->count();

        // Topics that went through AI generation
        $aiTopics = $topics->where('status', 'ai_generated')->count();

        // Topic status breakdown
        $publishedTopics  = $topics->where('status', 'published')->count();
        $draftTopics      = $topics->where('status', 'draft')->count();

        // Submissions pending mentor review
        $pendingReview = Submission::whereIn(
            'question_id',
            Question::whereIn('topic_id', $topicIds)->pluck('id')
        )->where('status', 'ai_evaluated')->count();

        // Fully reviewed submissions
        $reviewedCount = Submission::whereIn(
            'question_id',
            Question::whereIn('topic_id', $topicIds)->pluck('id')
        )->where('status', 'reviewed')->count();

        // Recent topics for quick access
        $recentTopics = Topic::where('mentor_id', $mentorId)
            ->latest()
            ->take(5)
            ->get();

        // Recent intern assignments
        $recentAssignments = InternTopicAssignment::whereIn('topic_id', $topicIds)
            ->with(['intern', 'topic'])
            ->latest('assigned_at')
            ->take(5)
            ->get();

        return view('mentor.dashboard', compact(
            'internCount',
            'topicCount',
            'questionCount',
            'aiTopics',
            'publishedTopics',
            'draftTopics',
            'pendingReview',
            'reviewedCount',
            'recentTopics',
            'recentAssignments'
        ));
    }
}