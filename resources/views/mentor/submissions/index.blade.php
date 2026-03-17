@extends('layouts.mentor')
@section('title', 'Submissions')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

/* ── Page header ── */
.page-header {
    display: flex; justify-content: space-between; align-items: flex-end;
    padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 24px;
}
.page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
.page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }

/* ── Tabs ── */
.tabs { display: flex; border-bottom: 2px solid #e5e5e5; margin-bottom: 20px; gap: 0; }
.tab-btn {
    font-family: 'DM Mono', monospace; font-size: 11px; letter-spacing: 0.06em;
    text-transform: uppercase; padding: 10px 20px; cursor: pointer; color: #888;
    border: none; background: none; border-bottom: 2px solid transparent;
    margin-bottom: -2px; transition: color 0.12s;
}
.tab-btn.active { color: #1a1a1a; border-bottom-color: #1a1a1a; }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* ── Count pill ── */
.cpill {
    display: inline-flex; align-items: center; justify-content: center;
    background: #1a1a1a; color: #fff;
    font-family: 'DM Mono', monospace; font-size: 10px;
    min-width: 18px; height: 18px; border-radius: 2px; padding: 0 5px; margin-left: 6px;
}
.cpill.grey { background: #ccc; }

/* ── Accordion wrapper ── */
.accordion { border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; background: #e5e5e5; }

/* ── Row header (always visible) ── */
.acc-row {
    background: #fff; border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 0;
    cursor: pointer; transition: background 0.1s; user-select: none;
}
.acc-row:last-of-type { border-bottom: none; }
.acc-row:hover { background: #fafafa; }
.acc-row.open { background: #f8f8f8; border-bottom: none; }

.acc-toggle {
    width: 44px; min-width: 44px; height: 100%; display: flex; align-items: center;
    justify-content: center; padding: 16px 0;
    color: #ccc; font-size: 12px; transition: color 0.12s, transform 0.2s;
}
.acc-row.open .acc-toggle { color: #1a1a1a; transform: rotate(90deg); }

.acc-meta {
    flex: 1; padding: 14px 16px 14px 0;
    display: flex; align-items: center; gap: 16px; min-width: 0;
}

.intern-avatar {
    width: 30px; height: 30px; border-radius: 2px;
    background: #1a1a1a; color: #fff; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'DM Mono', monospace; font-size: 11px;
}

.acc-intern   { font-size: 13px; font-weight: 500; color: #1a1a1a; white-space: nowrap; }
.acc-question {
    font-size: 12px; color: #888; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis; max-width: 420px;
}

.acc-right {
    display: flex; align-items: center; gap: 12px;
    padding-right: 16px; flex-shrink: 0;
}

.badge {
    display: inline-block; padding: 2px 8px; border-radius: 2px;
    font-size: 10px; font-family: 'DM Mono', monospace;
    letter-spacing: 0.05em; text-transform: uppercase;
}
.badge-ai_evaluated { background: #e8f0f5; color: #1a5092; }
.badge-reviewed     { background: #eaf5e8; color: #1a6a1a; }
.badge-mcq          { background: #f5f0e8; color: #92681a; }
.badge-true_false   { background: #f0ebf5; color: #5a1a92; }
.badge-blank        { background: #f0f5eb; color: #3a6a1a; }
.badge-output       { background: #e8f5f5; color: #1a6a6a; }
.badge-coding       { background: #1a1a1a; color: #fff; }

.acc-date { font-family: 'DM Mono', monospace; font-size: 11px; color: #ccc; }

/* ── Expand body ── */
.acc-body {
    display: none; background: #fff;
    border-bottom: 1px solid #e5e5e5;
}
.acc-body.open { display: block; }

.acc-inner {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 1px; background: #e5e5e5;
    margin: 0 1px 1px;
    border-radius: 0 0 2px 2px;
    overflow: hidden;
}

/* ── Left panel: question + reference ── */
.detail-panel { background: #fff; padding: 22px 24px; }
.panel-label  {
    font-family: 'DM Mono', monospace; font-size: 10px;
    letter-spacing: 0.1em; text-transform: uppercase; color: #aaa;
    margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;
}

.q-statement { font-size: 14px; color: #1a1a1a; line-height: 1.75; margin-bottom: 14px; }

.code-block {
    background: #1a1a1a; color: #c8c8c8; padding: 13px 16px; border-radius: 2px;
    font-family: 'DM Mono', monospace; font-size: 12.5px; line-height: 1.65;
    overflow-x: auto; margin-bottom: 12px; white-space: pre-wrap;
}

.mcq-opts { display: flex; flex-direction: column; gap: 5px; margin-bottom: 12px; }
.mcq-opt  {
    display: flex; align-items: flex-start; gap: 8px; padding: 7px 12px;
    border: 1px solid #ebebeb; border-radius: 2px; font-size: 13px;
}
.mcq-opt.correct { background: #f0faf0; border-color: #b8ddb8; }
.mcq-opt.intern-pick { background: #e8f0f5; border-color: #9ab8d8; }
.mcq-opt.correct.intern-pick { background: #f0faf0; border-color: #1a6a1a; }
.opt-key { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; min-width: 16px; padding-top: 1px; }
.mcq-opt.correct .opt-key { color: #1a6a1a; }
.mcq-opt.intern-pick .opt-key { color: #1a5092; }
.mcq-opt.correct.intern-pick .opt-key { color: #1a6a1a; }

.answer-tag {
    display: inline-block; padding: 3px 12px; border-radius: 2px;
    font-family: 'DM Mono', monospace; font-size: 12px;
}
.answer-tag.correct  { background: #f0faf0; color: #1a6a1a; border: 1px solid #b8ddb8; }
.answer-tag.intern   { background: #e8f0f5; color: #1a3a6a; border: 1px solid #9ab8d8; }
.answer-tag.wrong    { background: #fdf0f0; color: #8a1a1a; border: 1px solid #e8b8b8; }

.mini-label { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 5px; margin-top: 12px; }

.ref-box {
    background: #f8f8f8; border: 1px solid #ebebeb; border-radius: 2px;
    padding: 12px 14px; font-family: 'DM Mono', monospace; font-size: 12px;
    color: #444; line-height: 1.65; white-space: pre-wrap; overflow-x: auto;
}
.ref-explanation { font-size: 12px; color: #888; line-height: 1.6; margin-top: 8px; font-style: italic; }

.intern-answer-box {
    background: #f8fbff; border: 1px solid #d0e0f0; border-radius: 2px;
    padding: 12px 14px; font-family: 'DM Mono', monospace; font-size: 12px;
    color: #1a1a1a; line-height: 1.65; white-space: pre-wrap; overflow-x: auto;
}

/* AI feedback */
.ai-feedback-box {
    background: #f5f5f4; border: 1px solid #e5e5e5; border-radius: 2px;
    padding: 12px 14px; font-size: 13px; color: #555; line-height: 1.7;
    margin-top: 10px;
}

/* ── Right panel: review form ── */
.review-form-panel { background: #fff; padding: 22px 24px; display: flex; flex-direction: column; }

.score-input-row { display: flex; gap: 10px; align-items: flex-end; }
.score-input {
    width: 100px; border: 1px solid #d4d4d4; border-radius: 2px;
    padding: 9px 12px; font-size: 16px; font-family: 'DM Mono', monospace;
    color: #1a1a1a; background: #fafafa; outline: none; text-align: center;
    transition: border-color 0.12s;
}
.score-input:focus { border-color: #1a1a1a; background: #fff; }
.score-hint { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; padding-bottom: 2px; }

.feedback-textarea {
    width: 100%; border: 1px solid #d4d4d4; border-radius: 2px;
    padding: 10px 12px; font-size: 13px; font-family: 'DM Sans', sans-serif;
    color: #1a1a1a; background: #fafafa; outline: none; resize: vertical;
    min-height: 90px; transition: border-color 0.12s; line-height: 1.6;
}
.feedback-textarea:focus { border-color: #1a1a1a; background: #fff; }

.review-actions { display: flex; gap: 10px; margin-top: 16px; }
.btn-save {
    background: #1a1a1a; color: #fff; border: none; border-radius: 2px;
    padding: 9px 22px; font-size: 13px; font-weight: 500; cursor: pointer;
    font-family: 'DM Sans', sans-serif; transition: background 0.12s;
}
.btn-save:hover { background: #333; }
.btn-save.saving { opacity: 0.6; }

.already-reviewed-note {
    display: flex; align-items: center; gap: 8px;
    background: #f0faf0; border: 1px solid #c6e6c6; border-radius: 2px;
    padding: 10px 14px; font-family: 'DM Mono', monospace; font-size: 11px;
    color: #1a6a1a; margin-top: auto;
}

/* ── Empty ── */
.empty-state {
    padding: 48px; text-align: center; background: #fff;
    border: 1px solid #e5e5e5; border-radius: 2px;
    font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa;
}
</style>

<div class="page-header">
    <div>
        <div class="page-title">Submissions</div>
        <div class="page-meta">{{ $pendingSubmissions->count() + $reviewedSubmissions->count() }} total</div>
    </div>
</div>

{{-- Tabs --}}
<div class="tabs">
    <button class="tab-btn active" id="tab-btn-pending" onclick="switchTab('pending')">
        Pending Review
        @if($pendingSubmissions->count())
            <span class="cpill">{{ $pendingSubmissions->count() }}</span>
        @endif
    </button>
    <button class="tab-btn" id="tab-btn-reviewed" onclick="switchTab('reviewed')">
        Reviewed
        <span class="cpill grey">{{ $reviewedSubmissions->count() }}</span>
    </button>
</div>

{{-- ── PENDING TAB ── --}}
<div class="tab-pane active" id="pane-pending">
    @if($pendingSubmissions->isEmpty())
        <div class="empty-state">No submissions pending review. 🎉</div>
    @else
        <div class="accordion" id="accordion-pending">
            @foreach($pendingSubmissions as $sub)
            @php
                $q   = $sub->question;
                $ref = $q->referenceSolution;
                $internAnswer = $sub->submitted_code;
            @endphp

            {{-- Row header --}}
            <div class="acc-row" id="row-{{ $sub->id }}" onclick="toggle({{ $sub->id }})">
                <div class="acc-toggle">▶</div>
                <div class="acc-meta">
                    <div class="intern-avatar">{{ strtoupper(substr($sub->intern->name ?? 'IN', 0, 2)) }}</div>
                    <div>
                        <div class="acc-intern">{{ $sub->intern->name ?? '—' }}</div>
                        <div class="acc-question">{{ Str::limit($q->problem_statement ?? '', 90) }}</div>
                    </div>
                </div>
                <div class="acc-right">
                    <span class="badge badge-{{ $q->type ?? 'mcq' }}">{{ str_replace('_',' ', $q->type ?? '') }}</span>
                    <span class="badge badge-ai_evaluated">Pending</span>
                    <span class="acc-date">{{ $sub->created_at->format('d M') }}</span>
                </div>
            </div>

            {{-- Expand body --}}
            <div class="acc-body" id="body-{{ $sub->id }}">
                <div class="acc-inner">

                    {{-- LEFT: question + intern answer + reference --}}
                    <div class="detail-panel">
                        <div class="panel-label">Question &amp; Answer</div>

                        <div class="q-statement">{{ $q->problem_statement }}</div>

                        @if($q->code)
                            <pre class="code-block">{{ $q->code }}</pre>
                        @endif

                        {{-- MCQ options --}}
                        @if($q->type === 'mcq')
                            <div class="mcq-opts">
                                @foreach(['A'=>$q->option_a,'B'=>$q->option_b,'C'=>$q->option_c,'D'=>$q->option_d] as $k=>$v)
                                    @if($v)
                                    <div class="mcq-opt
                                        {{ $q->correct_answer===$k ? 'correct' : '' }}
                                        {{ strtoupper(trim($internAnswer))===$k ? 'intern-pick' : '' }}">
                                        <span class="opt-key">{{ $k }}</span>
                                        <span>{{ $v }}</span>
                                        @if($q->correct_answer===$k)
                                            <span style="margin-left:auto;font-family:'DM Mono',monospace;font-size:10px;color:#1a6a1a;">✓ correct</span>
                                        @endif
                                        @if(strtoupper(trim($internAnswer))===$k && $q->correct_answer!==$k)
                                            <span style="margin-left:auto;font-family:'DM Mono',monospace;font-size:10px;color:#1a5092;">← intern</span>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>

                        {{-- True/False, Blank, Output --}}
                        @elseif(in_array($q->type, ['true_false','blank','output']))
                            <div class="mini-label">Intern's Answer</div>
                            @php $isCorrect = strtolower(trim($internAnswer)) === strtolower(trim($q->correct_answer ?? '')); @endphp
                            <span class="answer-tag {{ $isCorrect ? 'correct' : 'wrong' }}">{{ $internAnswer }}</span>

                            <div class="mini-label">Correct Answer</div>
                            <span class="answer-tag correct">{{ $q->correct_answer }}</span>

                        {{-- Coding --}}
                        @else
                            <div class="mini-label">Intern's Code</div>
                            <pre class="intern-answer-box">{{ $internAnswer }}</pre>
                        @endif

                        {{-- Reference solution --}}
                        @if($ref)
                            <div class="mini-label" style="margin-top:16px;">Reference Solution</div>
                            <pre class="ref-box">{{ $ref->solution_code }}</pre>
                            @if($ref->explanation)
                                <div class="ref-explanation">{{ $ref->explanation }}</div>
                            @endif
                        @endif

                        {{-- AI feedback --}}
                        @if($sub->feedback)
                            <div class="mini-label" style="margin-top:16px;">AI Feedback</div>
                            <div class="ai-feedback-box">{{ $sub->feedback }}</div>
                        @endif
                    </div>

                    {{-- RIGHT: review form --}}
                    <div class="review-form-panel">
                        <div class="panel-label">Mentor Review</div>

                        <div class="mini-label" style="margin-top:0;">Override Score <span style="color:#ccc;">(0–30)</span></div>
                        <div class="score-input-row">
                            <input type="number" class="score-input" id="score-{{ $sub->id }}"
                                   min="0" max="30"
                                   value="{{ $sub->ai_total_score ?? 0 }}"
                                   placeholder="0">
                            <span class="score-hint">AI scored: <strong>{{ $sub->ai_total_score ?? '—' }}</strong>/30</span>
                        </div>

                        <div class="mini-label" style="margin-top:16px;">Feedback for Intern</div>
                        <textarea class="feedback-textarea" id="feedback-{{ $sub->id }}"
                                  placeholder="Write feedback the intern will see...">{{ $sub->feedback }}</textarea>

                        <div class="review-actions">
                            <button class="btn-save" id="savebtn-{{ $sub->id }}"
                                    onclick="saveReview({{ $sub->id }})">
                                Save Review
                            </button>
                            <span id="savestatus-{{ $sub->id }}"
                                  style="font-family:'DM Mono',monospace;font-size:11px;color:#aaa;padding-top:10px;"></span>
                        </div>
                    </div>

                </div>{{-- .acc-inner --}}
            </div>{{-- .acc-body --}}

            @endforeach
        </div>{{-- .accordion --}}
    @endif
</div>

{{-- ── REVIEWED TAB ── --}}
<div class="tab-pane" id="pane-reviewed">
    @if($reviewedSubmissions->isEmpty())
        <div class="empty-state">No reviewed submissions yet.</div>
    @else
        <div class="accordion" id="accordion-reviewed">
            @foreach($reviewedSubmissions as $sub)
            @php
                $q   = $sub->question;
                $ref = $q->referenceSolution;
                $internAnswer = $sub->submitted_code;
            @endphp

            <div class="acc-row" id="row-r-{{ $sub->id }}" onclick="toggle('r-{{ $sub->id }}')">
                <div class="acc-toggle">▶</div>
                <div class="acc-meta">
                    <div class="intern-avatar">{{ strtoupper(substr($sub->intern->name ?? 'IN', 0, 2)) }}</div>
                    <div>
                        <div class="acc-intern">{{ $sub->intern->name ?? '—' }}</div>
                        <div class="acc-question">{{ Str::limit($q->problem_statement ?? '', 90) }}</div>
                    </div>
                </div>
                <div class="acc-right">
                    <span class="badge badge-{{ $q->type ?? 'mcq' }}">{{ str_replace('_',' ', $q->type ?? '') }}</span>
                    <span class="badge badge-reviewed">Reviewed</span>
                    @if($sub->final_score !== null)
                        <span style="font-family:'DM Mono',monospace;font-size:12px;font-weight:500;color:#1a1a1a;">
                            {{ $sub->final_score }}<span style="color:#ccc;">/30</span>
                        </span>
                    @endif
                    <span class="acc-date">{{ $sub->updated_at->format('d M') }}</span>
                </div>
            </div>

            <div class="acc-body" id="body-r-{{ $sub->id }}">
                <div class="acc-inner">

                    {{-- LEFT: question + answer --}}
                    <div class="detail-panel">
                        <div class="panel-label">Question &amp; Answer</div>

                        <div class="q-statement">{{ $q->problem_statement }}</div>

                        @if($q->code)
                            <pre class="code-block">{{ $q->code }}</pre>
                        @endif

                        @if($q->type === 'mcq')
                            <div class="mcq-opts">
                                @foreach(['A'=>$q->option_a,'B'=>$q->option_b,'C'=>$q->option_c,'D'=>$q->option_d] as $k=>$v)
                                    @if($v)
                                    <div class="mcq-opt
                                        {{ $q->correct_answer===$k ? 'correct' : '' }}
                                        {{ strtoupper(trim($internAnswer))===$k ? 'intern-pick' : '' }}">
                                        <span class="opt-key">{{ $k }}</span>
                                        <span>{{ $v }}</span>
                                        @if($q->correct_answer===$k)
                                            <span style="margin-left:auto;font-family:'DM Mono',monospace;font-size:10px;color:#1a6a1a;">✓</span>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif(in_array($q->type, ['true_false','blank','output']))
                            @php $isCorrect = strtolower(trim($internAnswer)) === strtolower(trim($q->correct_answer ?? '')); @endphp
                            <div class="mini-label">Intern's Answer</div>
                            <span class="answer-tag {{ $isCorrect ? 'correct' : 'wrong' }}">{{ $internAnswer }}</span>
                            <div class="mini-label">Correct Answer</div>
                            <span class="answer-tag correct">{{ $q->correct_answer }}</span>
                        @else
                            <div class="mini-label">Intern's Code</div>
                            <pre class="intern-answer-box">{{ $internAnswer }}</pre>
                        @endif

                        @if($ref)
                            <div class="mini-label" style="margin-top:16px;">Reference Solution</div>
                            <pre class="ref-box">{{ $ref->solution_code }}</pre>
                        @endif
                    </div>

                    {{-- RIGHT: review summary (read-only) --}}
                    <div class="review-form-panel">
                        <div class="panel-label">Review Summary</div>

                        <div class="mini-label" style="margin-top:0;">Score Given</div>
                        <div style="font-family:'DM Mono',monospace;font-size:32px;font-weight:400;color:#1a1a1a;line-height:1;margin-bottom:4px;">
                            {{ $sub->final_score ?? '—' }}<span style="font-size:14px;color:#ccc;">/30</span>
                        </div>
                        <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;">AI had scored: {{ $sub->ai_total_score ?? '—' }}/30</div>

                        @if($sub->feedback)
                            <div class="mini-label" style="margin-top:16px;">Feedback Sent</div>
                            <div class="ai-feedback-box">{{ $sub->feedback }}</div>
                        @endif

                        <div class="already-reviewed-note" style="margin-top:auto;margin-bottom:0;">
                            ✓ Reviewed on {{ $sub->updated_at->format('d M Y') }}
                        </div>
                    </div>

                </div>
            </div>

            @endforeach
        </div>
    @endif
</div>

<script>
// ── Tab switch ────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('pane-' + name).classList.add('active');
    document.getElementById('tab-btn-' + name).classList.add('active');
}

// ── Accordion toggle ─────────────────────────────────────────
function toggle(id) {
    const row  = document.getElementById('row-'  + id);
    const body = document.getElementById('body-' + id);
    const isOpen = body.classList.contains('open');

    // Close all in same accordion first
    const accordion = row.closest('.accordion');
    accordion.querySelectorAll('.acc-body.open').forEach(b => b.classList.remove('open'));
    accordion.querySelectorAll('.acc-row.open').forEach(r => r.classList.remove('open'));

    if (!isOpen) {
        body.classList.add('open');
        row.classList.add('open');
    }
}

// ── AJAX review save ─────────────────────────────────────────
function saveReview(id) {
    const score    = document.getElementById('score-' + id).value;
    const feedback = document.getElementById('feedback-' + id).value;
    const btn      = document.getElementById('savebtn-' + id);
    const status   = document.getElementById('savestatus-' + id);

    if (score === '' || score < 0 || score > 30) {
        status.textContent = 'Score must be 0–30';
        status.style.color = '#c0392b';
        return;
    }

    btn.textContent = 'Saving...';
    btn.classList.add('saving');
    status.textContent = '';

    fetch('{{ route('mentor.submissions.review', ['id' => '__ID__']) }}'.replace('__ID__', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            mentor_override_score: parseInt(score),
            feedback: feedback
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            btn.textContent = 'Saved ✓';
            btn.style.background = '#1a6a1a';
            btn.classList.remove('saving');
            status.textContent = '';

            // Move row to reviewed tab after short delay
            setTimeout(() => {
                const row  = document.getElementById('row-' + id);
                const body = document.getElementById('body-' + id);
                if (row)  row.remove();
                if (body) body.remove();

                // Update pending count pill
                const pendingCount = document.querySelectorAll('#accordion-pending .acc-row').length;
                const pill = document.querySelector('#tab-btn-pending .cpill');
                if (pill) {
                    if (pendingCount === 0) {
                        pill.remove();
                        document.getElementById('pane-pending').innerHTML =
                            '<div class="empty-state">No submissions pending review. 🎉</div>';
                    } else {
                        pill.textContent = pendingCount;
                    }
                }

                // Refresh reviewed count
                const reviewedPill = document.querySelector('#tab-btn-reviewed .cpill');
                if (reviewedPill) {
                    reviewedPill.textContent = parseInt(reviewedPill.textContent || 0) + 1;
                }
            }, 800);
        } else {
            btn.textContent = 'Save Review';
            btn.classList.remove('saving');
            status.textContent = 'Error — try again';
            status.style.color = '#c0392b';
        }
    })
    .catch(() => {
        btn.textContent = 'Save Review';
        btn.classList.remove('saving');
        status.textContent = 'Network error';
        status.style.color = '#c0392b';
    });
}

// ── Auto-open if redirected with open_submission ──────────────
@if(session('open_submission'))
    document.addEventListener('DOMContentLoaded', () => {
        toggle({{ session('open_submission') }});
    });
@endif
</script>
@endsection