@extends('admin.layouts.app')

@section('title', 'Edit Quiz: ' . $quiz->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <nav aria-label="breadcrumb" class="d-flex justify-content-between align-items-center mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.index') }}">Competitions</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.quizzes.index', $competition) }}">{{ $competition->title }} - Quizzes</a>
            </li>
            <li class="breadcrumb-item active">Edit: {{ $quiz->title }}</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Edit Quiz: {{ $quiz->title }}</h5>
                    <p class="mb-0">Last updated: {{ $quiz->updated_at->diffForHumans() }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class='bx bx-list-ul me-1'></i> Manage Questions
                    </a>
                    <a href="{{ route('admin.competitions.quizzes.statistics', [$competition, $quiz]) }}" 
                       class="btn btn-outline-info btn-sm">
                        <i class='bx bx-stats me-1'></i> View Statistics
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('admin.quizzes.form')

    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Danger Zone</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Delete this quiz</h6>
                        <p class="mb-0">Once you delete a quiz, there is no going back. Please be certain.</p>
                    </div>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteQuizModal">
                        <i class='bx bx-trash me-1'></i> Delete Quiz
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the quiz <strong>{{ $quiz->title }}</strong>? This action cannot be undone and will permanently delete all associated questions and answers.</p>
                <div class="alert alert-warning mb-0">
                    <i class='bx bx-error-circle me-2'></i>
                    <strong>Warning:</strong> This will also remove all participant data and cannot be recovered.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class='bx bx-x me-1'></i> Cancel
                </button>
                <form action="{{ route('admin.competitions.quizzes.destroy', [$competition, $quiz]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-trash me-1'></i> Delete Quiz
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
