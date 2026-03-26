@extends('admin.layouts.app')

@section('title', 'Manage Quizzes')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">Manage Quizzes</h4>
        <a href="{{ route('admin.competitions.quizzes.create', $competition) }}" class="btn btn-primary">
            <i class='bx bx-plus'></i> Add New Quiz
        </a>
    </div>

    <div class="card">
        <h5 class="card-header">Quizzes for {{ $competition->title }}</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Questions</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($quizzes as $quiz)
                    <tr>
                        <td>
                            <strong>{{ $quiz->title }}</strong>
                            @if($quiz->description)
                            <p class="text-muted mb-0">{{ Str::limit($quiz->description, 100) }}</p>
                            @endif
                        </td>
                        <td>{{ $quiz->questions_count ?? 0 }}</td>
                        <td>{{ $quiz->duration }} minutes</td>
                        <td>
                            <span class="badge bg-{{ $quiz->isActive() ? 'success' : 'secondary' }}">
                                {{ $quiz->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Manage Questions
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.competitions.quizzes.edit', [$competition, $quiz]) }}">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.competitions.quizzes.statistics', [$competition, $quiz]) }}">
                                        <i class="bx bx-stats me-1"></i> Statistics
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('admin.competitions.quizzes.toggle-status', [$competition, $quiz]) }}" method="POST" class="d-inline-block w-100">
                                        @csrf
                                        <button type="submit" class="dropdown-item" 
                                                onclick="return confirm('Are you sure you want to {{ $quiz->isActive() ? 'deactivate' : 'activate' }} this quiz?')">
                                            <i class="bx bx-{{ $quiz->isActive() ? 'x' : 'check' }}-circle me-1"></i>
                                            {{ $quiz->isActive() ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('admin.competitions.quizzes.destroy', [$competition, $quiz]) }}" method="POST" class="d-inline" 
                                        onsubmit="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-book-open text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">No quizzes found.</p>
                                <a href="{{ route('admin.competitions.quizzes.create', $competition) }}" class="btn btn-sm btn-primary mt-2">
                                    Create New Quiz
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($quizzes->hasPages())
        <div class="card-footer">
            {{ $quizzes->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Any JavaScript specific to this page
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
