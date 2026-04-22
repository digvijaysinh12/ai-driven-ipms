@extends('layouts.app')

@section('title', 'Submission Result')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow border-0">
            <div class="card-header bg-white pb-0 border-bottom-0 text-center pt-4">
                <div class="mb-3">
                    <!-- Status Icon -->
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1">Submission Successful</h4>
                <p class="text-muted">Implement a Stack</p>
            </div>
            <div class="card-body p-4 pt-2">
                <hr>
                
                <div class="row text-center mb-4">
                    <div class="col-md-6 border-end">
                        <div class="text-muted mb-1">Overall Score</div>
                        <h2 class="fw-bold text-success mb-0">85/100</h2>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted mb-1">Status</div>
                        <h4 class="mb-0"><span class="badge bg-success">Passed</span></h4>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">AI Feedback</h6>
                    <div class="p-3 bg-light rounded bg-opacity-50">
                        <p class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i><strong>Strengths:</strong></p>
                        <p class="text-muted ms-4 mb-3">Your implementation correctly utilizes PHP's empty arrays to mimic stack behavior via `array_push` and `array_pop`. The logic is sound and handles standard cases well.</p>

                        <p class="mb-2"><i class="bi bi-info-circle text-info me-2"></i><strong>Areas for Improvement:</strong></p>
                        <p class="text-muted ms-4 mb-0">Consider adding error handling or throwing an exception in `pop()` or `peek()` if the stack is completely empty, to prevent undefined index warnings or unexpected returns.</p>
                    </div>
                </div>

                <div>
                    <h6 class="fw-bold border-bottom pb-2">Test Cases</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Push single item
                            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Passed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Pop single item
                            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Passed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Peek empty stack
                            <span class="badge bg-danger rounded-pill"><i class="bi bi-x"></i> Failed</span>
                        </li>
                    </ul>
                </div>
                
            </div>
            <div class="card-footer bg-white border-top-0 pb-4 text-center">
                <a href="{{ url('/intern/questions') }}" class="btn btn-outline-secondary me-2">Back to Questions</a>
                <a href="{{ url('/intern/editor') }}" class="btn btn-primary">Try Again</a>
            </div>
        </div>
    </div>
</div>
@endsection
