@extends('layouts.app')
@section('title', 'My Submissions')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">My Submissions</div>
        <div class="page-meta">{{ $totalSubmissions }} answers saved</div>
    </div>
</div>

{{-- ── Exercise grade cards ── --}}
<div class="section-label" style="margin-bottom:14px;">Exercise Results</div>

@if($assignments->isEmpty())
    <p class="cell-mono" style="margin-bottom:28px;">No exercises assigned yet.</p>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
                gap:1px;background:#e5e5e5;border:1px solid #e5e5e5;
                border-radius:2px;overflow:hidden;margin-bottom:32px;">
        @foreach($assignments as $asgn)
            @php
                $labels = [
                    'A' => 'Excellent', 'B' => 'Good', 'C' => 'Average',
                    'D' => 'Below Average', 'E' => 'Needs Improvement',
                ];
            @endphp
            <div style="background:#fff;padding:22px 24px;">
                <div class="cell-name" style="font-size:14px;margin-bottom:4px;">
                    {{ $asgn->topic->title ?? '—' }}
                </div>
                <div class="cell-mono" style="margin-bottom:14px;">
                    Assigned {{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M Y') }}
                    &nbsp;·&nbsp;
                    <x-badge :status="$asgn->status" />
                </div>

                <div style="display:flex;align-items:center;gap:12px;">
                    @if($asgn->grade)
                        <span class="grade-pill grade-{{ $asgn->grade }}"
                              style="font-size:36px;width:52px;height:52px;">
                            {{ $asgn->grade }}
                        </span>
                        <div>
                            <div style="font-size:13px;font-weight:500;">
                                {{ $labels[$asgn->grade] ?? '' }}
                            </div>
                            @if($asgn->feedback)
                                <div style="font-size:12px;color:#777;margin-top:3px;line-height:1.5;
                                            display:-webkit-box;-webkit-line-clamp:2;
                                            -webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $asgn->feedback }}
                                </div>
                            @endif
                        </div>
                    @elseif($asgn->status === 'submitted')
                        <span class="grade-pill grade-none"
                              style="font-size:24px;width:52px;height:52px;">…</span>
                        <div style="font-size:13px;color:#aaa;">Evaluation in progress</div>
                    @else
                        <span class="grade-pill grade-none"
                              style="font-size:24px;width:52px;height:52px;">—</span>
                        <div style="font-size:13px;color:#aaa;">Not submitted yet</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- ── All saved answers table ── --}}
<div class="section-label" style="margin-bottom:14px;">All Saved Answers</div>

<div class="table-card">
    @if($submissions->isEmpty())
        <div class="empty-state">
            No answers saved yet.<br>Open an exercise from My Topic to start.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Saved</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $i => $sub)
                    <tr>
                        <td class="cell-mono">{{ $i + 1 }}</td>
                        <td>
                            <div style="max-width:380px;white-space:nowrap;
                                        overflow:hidden;text-overflow:ellipsis;">
                                {{ $sub->question->problem_statement ?? '—' }}
                            </div>
                        </td>
                        <td class="cell-mono">
                            {{ str_replace('_', ' ', $sub->question->type ?? '') }}
                        </td>
                        <td><x-badge :status="$sub->status" /></td>
                        <td class="cell-mono">{{ $sub->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection