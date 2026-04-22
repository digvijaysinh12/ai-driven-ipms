@extends('layouts.app')
@section('title', 'Reviewing Submission')

@push('styles')
<style>
    .review-wizard { max-width: 860px; margin: 0 auto; }
    .step-card { display: none; }
    .step-card.active { display: block; }
    
    .code-panel {
        background: #0f1117;
        padding: 24px;
        border-radius: var(--ui-radius-sm);
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        color: #e2e8f0;
        overflow-x: auto;
        white-space: pre-wrap;
        margin: 20px 0;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .evaluation-summary {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--ui-border);
    }
    
    .score-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #eef2ff;
        color: var(--ui-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        font-family: 'DM Mono', monospace;
    }

    .wizard-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--ui-border);
    }

    .topic-header-card {
        background: #fff;
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-radius);
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="review-wizard">
    <div class="page-shell-header">
        <div class="page-shell-copy">
            <a href="{{ route('user.mentor.interns.progress', $submission->intern_id) }}" class="cell-mono" style="text-decoration: none; color: var(--ui-text-muted); font-size: 11px; margin-bottom: 8px; display: block;">&larr; BACK TO INTERN SUBMISSIONS</a>
            <h1 class="page-shell-title">Review Session</h1>
        </div>
        <div class="page-shell-actions">
            <div class="cell-mono" style="font-weight: 700; color: var(--ui-primary);" id="wizard-progress">Step 1 of {{ $allSubmissions->count() }}</div>
        </div>
    </div>

    <!-- Persistent Context Card -->
    <div class="topic-header-card ui-card">
        <div>
            <div class="form-label">Intern</div>
            <div class="cell-name" style="font-size: 16px;">{{ $submission->intern->name }}</div>
        </div>
        <div>
            <div class="form-label">Topic</div>
            <span class="table-chip">{{ $submission->question->topic->title }}</span>
        </div>
        <div style="text-align: right;">
            <div class="form-label">Total Questions</div>
            <div class="cell-mono">{{ $allSubmissions->count() }}</div>
        </div>
    </div>

    <form id="multi-review-form" method="POST" action="{{ route('user.mentor.submissions.review', $submission->id) }}">
        @csrf
        
        <!-- STEP-BY-STEP MODULES -->
        <div class="wizard-steps">
            @foreach($allSubmissions as $index => $step)
                <div class="step-card ui-card {{ $step->id == $submission->id ? 'active' : '' }}" data-step="{{ $index + 1 }}">
                    <div class="ui-card-header">
                        <div class="cell-mono" style="font-size: 10px; text-transform: uppercase; color: var(--ui-primary); margin-bottom: 8px;">Question {{ $index + 1 }}</div>
                        <h3 class="ui-card-title">{{ $step->question->problem_statement }}</h3>
                        <p class="ui-card-subtitle">Language: {{ strtoupper($step->question->language ?? 'PHP') }}</p>
                    </div>

                    <div class="form-label">Student Submission</div>
                    <div class="code-panel">@if(empty($step->submitted_code)) // No code submitted @else{{ $step->submitted_code }}@endif</div>

                    <div class="evaluation-summary">
                        <div style="text-align: center;">
                            <div class="form-label">AI Score</div>
                            <div class="score-circle" style="margin: 8px auto;">{{ $step->ai_total_score ?? '?' }}</div>
                        </div>
                        <div>
                            <div class="form-label">AI Feedback</div>
                            <p style="font-size: 13px; line-height: 1.6; margin-top: 8px; color: var(--ui-text-soft);">
                                {{ $step->feedback ?? 'Evaluation pending or no AI feedback available.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- FINAL STEP: SUMMARY & GRADE -->
            <div class="step-card ui-card" data-step="{{ $allSubmissions->count() + 1 }}">
                <div class="ui-card-header" style="text-align: center;">
                    <h3 class="ui-card-title" style="font-size: 24px;">Complete Review</h3>
                    <p class="ui-card-subtitle">You have reviewed all individual question modules for this topic.</p>
                </div>

                @php
                    $assignment = $submission->intern->internTopicAssignments()
                        ->where('topic_id', $submission->question->topic_id)
                        ->first();
                @endphp

                @if($assignment && $assignment->status === 'evaluated')
                    <div class="p-4 mb-4 rounded" style="background: rgba(var(--ui-primary-rgb), 0.05); border: 1px solid var(--ui-primary);">
                        <div class="form-label" style="color: var(--ui-primary); font-weight: 700;">AI Senior Mentor Evaluation</div>
                        <div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 12px;">
                            <div class="score-circle" style="background: var(--ui-primary); color: #fff; flex-shrink: 0;">
                                {{ $assignment->grade }}
                            </div>
                            <div>
                                <p style="font-size: 14px; line-height: 1.6; color: var(--ui-text-soft);">
                                    {{ $assignment->feedback }}
                                </p>
                                @if(!empty($assignment->weak_areas))
                                    <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;">
                                        @foreach($assignment->weak_areas as $area)
                                            <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 4px 10px; font-size: 11px;">
                                                {{ $area }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div style="max-width: 400px; margin: 20px auto;">
                    <div class="form-group text-center">
                        <label class="form-label">Human Mentor Final Grade</label>
                        <select name="grade" class="form-input" style="font-size: 32px; font-weight: 700; text-align: center; height: auto; padding: 10px;">
                            <option value="A" {{ ($submission->grade ?? '') == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ ($submission->grade ?? '') == 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ ($submission->grade ?? '') == 'C' ? 'selected' : '' }}>C</option>
                            <option value="D" {{ ($submission->grade ?? '') == 'D' ? 'selected' : '' }}>D</option>
                        </select>
                    </div>
                    
                    <div class="form-group mt-4">
                        <label class="form-label">Reviewer's Final Feedback</label>
                        <textarea name="feedback" class="form-textarea" rows="4" placeholder="Your overall assessment of the intern's work..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER NAVIGATION -->
        <div class="wizard-footer">
            <x-ui.button type="button" id="prev-btn" variant="secondary" style="visibility: hidden;">Previous Question</x-ui.button>
            
            <div class="wizard-actions">
                <x-ui.button type="button" id="next-btn">Next Question</x-ui.button>
                <x-ui.button type="submit" id="submit-btn" style="display: none;">Submit Final Review</x-ui.button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step-card');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const progress = document.getElementById('wizard-progress');
        const totalSubmissions = {{ $allSubmissions->count() }};
        const totalSteps = totalSubmissions + 1;

        // Find initial active step based on $submission->id context
        let currentStepNum = parseInt(document.querySelector('.step-card.active').dataset.step);

        function updateWizard() {
            steps.forEach(s => s.classList.remove('active'));
            const currentStep = document.querySelector(`.step-card[data-step="${currentStepNum}"]`);
            currentStep.classList.add('active');

            // Header progress
            if (currentStepNum <= totalSubmissions) {
                progress.textContent = `Question ${currentStepNum} of ${totalSubmissions}`;
                nextBtn.textContent = (currentStepNum === totalSubmissions) ? 'Finalize Review' : 'Next Question';
                nextBtn.style.display = 'inline-flex';
                submitBtn.style.display = 'none';
            } else {
                progress.textContent = `Final Evaluaton`;
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-flex';
            }

            // Prev button visibility
            prevBtn.style.visibility = (currentStepNum === 1) ? 'hidden' : 'visible';
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        nextBtn.addEventListener('click', () => {
            if (currentStepNum < totalSteps) {
                currentStepNum++;
                updateWizard();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStepNum > 1) {
                currentStepNum--;
                updateWizard();
            }
        });

        // Initialize state
        updateWizard();
    });
</script>
@endpush
