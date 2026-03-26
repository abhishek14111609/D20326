@extends('admin.layouts.app')

@section('title', 'Manage Questions: ' . $quiz->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/dragula/dragula.min.css') }}">
<style>
    .question-item {
        cursor: move;
        transition: all 0.2s;
    }
    .question-item:hover {
        background-color: #f8f9fa;
    }
    .question-item.dragging {
        opacity: 0.5;
        background-color: #f8f9fa;
    }
    .question-type-badge {
        font-size: 0.75rem;
    }
    .options-list {
        list-style-type: none;
        padding-left: 1.5rem;
        margin-bottom: 0;
    }
    .options-list li {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 0.25rem;
    }
    .options-list li.correct-answer::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #198754;
        font-weight: bold;
    }
</style>
@endpush

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
            <li class="breadcrumb-item active">{{ $quiz->title }} - Questions</li>
        </ol>
        <div>
            <a href="{{ route('admin.competitions.quizzes.edit', [$competition, $quiz]) }}" 
               class="btn btn-outline-primary btn-sm">
                <i class='bx bx-edit-alt me-1'></i> Edit Quiz
            </a>
            <a href="{{ route('admin.competitions.quizzes.statistics', [$competition, $quiz]) }}" 
               class="btn btn-outline-info btn-sm">
                <i class='bx bx-stats me-1'></i> View Statistics
            </a>
        </div>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Questions ({{ $quiz->questions->count() }})</h5>
                    <a href="{{ route('admin.competitions.quizzes.questions.create', [$competition, $quiz]) }}" class="btn btn-sm btn-primary">
                        <i class='bx bx-plus me-1'></i> Add Question
                    </a>
                </div>
                <div class="card-body">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class='bx bx-question-mark text-muted' style="font-size: 3rem;"></i>
                            </div>
                            <h5>No questions yet</h5>
                            <p class="text-muted">Add your first question to get started</p>
                            <a href="{{ route('admin.competitions.quizzes.questions.create', [$competition, $quiz]) }}" class="btn btn-primary">
                                <i class='bx bx-plus me-1'></i> Add Question
                            </a>
                        </div>
                    @else
                        <div id="questions-list">
                            @foreach($quiz->questions as $index => $question)
                                <div class="card question-item mb-3" data-id="{{ $question->id }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center">
                                                <div class="drag-handle me-3" style="cursor: move;">
                                                    <i class='bx bx-menu align-middle' style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <span class="badge bg-label-primary me-2">{{ $index + 1 }}</span>
                                                        {{ $question->question }}
                                                    </h6>
                                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                                        <span class="badge bg-label-{{ $question->getTypeColor() }} question-type-badge">
                                                            {{ $question->getTypeName() }}
                                                        </span>
                                                        <span class="badge bg-label-info question-type-badge">
                                                            {{ $question->points }} {{ Str::plural('point', $question->points) }}
                                                        </span>
                                                        @if($question->time_limit > 0)
                                                            <span class="badge bg-label-warning question-type-badge">
                                                                {{ $question->time_limit }}s time limit
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(in_array($question->type, ['multiple_choice', 'true_false']))
                                                        <ul class="options-list">
                                                            @foreach($question->options as $option)
                                                                <li class="{{ in_array($option, $question->correct_answers) ? 'correct-answer' : '' }}">
                                                                    {{ $option }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="{{ route('admin.competitions.quizzes.questions.edit', [$competition->id, $quiz->id, $question->id]) }}" class="dropdown-item">
                                                        <i class='bx bx-edit-alt me-1'></i> Edit
                                                    </a>
                                                    <button class="dropdown-item text-danger delete-question" 
                                                            data-question-id="{{ $question->id }}">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quiz Information</h5>
                </div>
                <div class="card-body">
                    <h6>{{ $quiz->title }}</h6>
                    @if($quiz->description)
                        <p class="text-muted">{{ $quiz->description }}</p>
                    @endif
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-{{ $quiz->isActive() ? 'success' : 'secondary' }}">
                                {{ $quiz->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Duration:</span>
                            <span>{{ $quiz->duration }} minutes</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Passing Score:</span>
                            <span>{{ $quiz->passing_score }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Max Attempts:</span>
                            <span>{{ $quiz->max_attempts > 0 ? $quiz->max_attempts : 'Unlimited' }}</span>
                        </div>
                        @if($quiz->start_time)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Starts:</span>
                                <span>{{ $quiz->start_time->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if($quiz->end_time)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ends:</span>
                                <span>{{ $quiz->end_time->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.competitions.quizzes.questions.create', [$competition, $quiz]) }}" class="btn btn-primary mb-2">
                            <i class='bx bx-plus me-1'></i> Add New Question
                        </a>
                        <a href="{{ route('admin.competitions.quizzes.edit', [$competition, $quiz]) }}" class="btn btn-outline-secondary mb-2">
                            <i class='bx bx-edit me-1'></i> Edit Quiz Details
                        </a>
                        <a href="{{ route('admin.competitions.quizzes.statistics', [$competition, $quiz]) }}" class="btn btn-outline-info mb-2">
                            <i class='bx bx-stats me-1'></i> View Statistics
                        </a>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#previewQuizModal">
                            <i class='bx bx-show me-1'></i> Preview Quiz
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this question? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteQuestionForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-trash me-1'></i> Delete Question
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Quiz Modal -->
<div class="modal fade" id="previewQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quiz Preview: {{ $quiz->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class='bx bx-info-circle me-2'></i>
                    This is a preview of how the quiz will appear to participants.
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h4 class="text-center mb-4">{{ $quiz->title }}</h4>
                        
                        @if($quiz->description)
                            <div class="alert alert-light mb-4">
                                {{ $quiz->description }}
                            </div>
                        @endif
                        
                        @if($quiz->instructions)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Instructions</h6>
                                </div>
                                <div class="card-body">
                                    {!! $quiz->instructions !!}
                                </div>
                            </div>
                        @endif
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Duration:</span>
                                <span>{{ $quiz->duration }} minutes</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Passing Score:</span>
                                <span>{{ $quiz->passing_score }}%</span>
                            </div>
                            @if($quiz->max_attempts > 0)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Max Attempts:</span>
                                    <span>{{ $quiz->max_attempts }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-primary" disabled>
                                Start Quiz
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class='bx bx-x me-1'></i> Close Preview
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Dragula JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded');
        
        // Initialize modal
        const questionModal = new bootstrap.Modal(document.getElementById('questionModal'));
        
        // Initialize drag and drop for questions if the container exists
        const questionsList = document.getElementById('questions-list');
        if (questionsList) {
            const drake = dragula([questionsList], {
                moves: function (el, container, handle) {
                    return handle.classList.contains('drag-handle') || handle.closest('.drag-handle');
                }
            });

            // Update order after drag and drop
            drake.on('drop', function(el, target, source, sibling) {
                updateQuestionOrder();
            });
        }

        // Function to update question order
        function updateQuestionOrder() {
            const questionIds = [];
            document.querySelectorAll('#questions-list .question-item').forEach((item, index) => {
                questionIds.push(item.dataset.id);
            });

            // Send AJAX request to update order
            fetch('{{ route("admin.competitions.quizzes.questions.reorder", [$competition, $quiz]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    questions: questionIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to update question order:', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating question order:', error);
            });
        }

        // Question type change handler
        const questionTypeSelect = document.getElementById('type');
        if (questionTypeSelect) {
            questionTypeSelect.addEventListener('change', updateQuestionForm);
        }

        // Initialize the form based on question type
        function updateQuestionForm() {
            const type = questionTypeSelect.value;
            const optionsContainer = document.getElementById('options-container');
            const correctAnswerContainer = document.getElementById('correct-answer-container');
            
            // Clear existing content
            optionsContainer.innerHTML = '';
            correctAnswerContainer.innerHTML = '';
            
            switch(type) {
                case 'multiple_choice':
                    optionsContainer.innerHTML = `
                        <div class="mb-3">
                            <label class="form-label">Options <span class="text-danger">*</span></label>
                            <div id="mcq-options">
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="correct_answers[]" value="0">
                                    </div>
                                    <input type="text" class="form-control" name="options[]" required>
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="correct_answers[]" value="1">
                                    </div>
                                    <input type="text" class="form-control" name="options[]" required>
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-option">
                                <i class='bx bx-plus'></i> Add Option
                            </button>
                        </div>
                    `;
                    
                    // Add event listeners for dynamic options
                    document.getElementById('add-option')?.addEventListener('click', addMcqOption);
                    document.querySelectorAll('.remove-option').forEach(btn => {
                        btn.addEventListener('click', removeMcqOption);
                    });
                    break;
                    
                case 'true_false':
                    optionsContainer.innerHTML = `
                        <div class="mb-3">
                            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answers[]" value="True" id="trueOption" checked>
                                <label class="form-check-label" for="trueOption">
                                    True
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answers[]" value="False" id="falseOption">
                                <label class="form-check-label" for="falseOption">
                                    False
                                </label>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'short_answer':
                    correctAnswerContainer.innerHTML = `
                        <div class="mb-3">
                            <label for="correct_short_answer" class="form-label">Correct Answer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="correct_short_answer" name="correct_answers[]" required>
                        </div>
                    `;
                    break;
                    
                case 'essay':
                    correctAnswerContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class='bx bx-info-circle me-2'></i>
                            Essay questions will be manually graded by an administrator.
                        </div>
                    `;
                    break;
            }
        }

        // Add new MCQ option
        function addMcqOption() {
            const optionsContainer = document.getElementById('mcq-options');
            const optionCount = optionsContainer.children.length;
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'input-group mb-2';
            optionDiv.innerHTML = `
                <div class="input-group-text">
                    <input class="form-check-input mt-0 me-2" type="checkbox" name="correct_answers[]" value="${optionCount}">
                </div>
                <input type="text" class="form-control" name="options[]" required>
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class='bx bx-trash'></i>
                </button>
            `;
            
            optionsContainer.appendChild(optionDiv);
            
            // Add event listener to the new remove button
            optionDiv.querySelector('.remove-option').addEventListener('click', removeMcqOption);
        }
        
        // Remove MCQ option
        function removeMcqOption(e) {
            const optionsContainer = document.getElementById('mcq-options');
            if (optionsContainer.children.length > 2) { // Keep at least 2 options
                e.target.closest('.input-group').remove();
                
                // Update the values of the checkboxes
                document.querySelectorAll('#mcq-options .form-check-input').forEach((checkbox, index) => {
                    checkbox.value = index;
                });
            } else {
                alert('A question must have at least 2 options.');
            }
        }
        
        // Initialize the form when the page loads
        updateQuestionForm();
        
        // Handle add question button click
        document.querySelectorAll('[data-bs-target="#questionModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('questionModal');
                const form = modal.querySelector('form');
                form.reset();
                form._method.value = 'POST';
                form.action = '{{ route("admin.competitions.quizzes.questions.store", [$competition, $quiz]) }}';
                modal.querySelector('.modal-title').textContent = 'Add New Question';
                updateQuestionForm();
            });
        });
        
        // Handle edit question button click
        document.querySelectorAll('.edit-question').forEach(button => {
            button.addEventListener('click', function() {
                const question = JSON.parse(this.dataset.question);
                const modal = document.getElementById('questionModal');
                const form = modal.querySelector('form');
                
                // Set form action and method
                form._method.value = 'PUT';
                form.action = `/admin/competitions/${question.quiz_id}/quizzes/${question.id}/questions/${question.id}`;
                modal.querySelector('.modal-title').textContent = 'Edit Question';
                
                // Set form values
                document.getElementById('question').value = question.question;
                document.getElementById('type').value = question.type;
                document.getElementById('points').value = question.points;
                document.getElementById('time_limit').value = question.time_limit || 0;
                
                // Update the form based on question type
                updateQuestionForm();
                
                // For MCQ and True/False, set the options and correct answers
                if (['multiple_choice', 'true_false'].includes(question.type)) {
                    // Wait for the form to update
                    setTimeout(() => {
                        if (question.type === 'multiple_choice') {
                            const optionsContainer = document.getElementById('mcq-options');
                            optionsContainer.innerHTML = ''; // Clear default options
                            
                            question.options.forEach((option, index) => {
                                const isCorrect = question.correct_answers.includes(option);
                                const optionDiv = document.createElement('div');
                                optionDiv.className = 'input-group mb-2';
                                optionDiv.innerHTML = `
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" 
                                               name="correct_answers[]" value="${index}" ${isCorrect ? 'checked' : ''}>
                                    </div>
                                    <input type="text" class="form-control" name="options[]" value="${option}" required>
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                `;
                                optionsContainer.appendChild(optionDiv);
                                
                                // Add event listener to the new remove button
                                optionDiv.querySelector('.remove-option').addEventListener('click', removeMcqOption);
                            });
                            
                            // Add event listener to the add option button
                            document.getElementById('add-option').addEventListener('click', addMcqOption);
                            
                        } else if (question.type === 'true_false') {
                            const correctAnswer = question.correct_answers[0];
                            if (correctAnswer === 'True') {
                                document.getElementById('trueOption').checked = true;
                            } else {
                                document.getElementById('falseOption').checked = true;
                            }
                        }
                    }, 100);
                } else if (question.type === 'short_answer' && question.correct_answers.length > 0) {
                    // For short answer, set the correct answer
                    setTimeout(() => {
                        document.getElementById('correct_short_answer').value = question.correct_answers[0];
                    }, 100);
                }
                
                // Show the modal
                new bootstrap.Modal(modal).show();
            });
        });
        
        // Handle delete question button click
        document.querySelectorAll('.delete-question').forEach(button => {
            button.addEventListener('click', function() {
                const questionId = this.dataset.questionId;
                const form = document.getElementById('deleteQuestionForm');
                form.action = `/admin/competitions/${$competition->id}/quizzes/${$quiz->id}/questions/${questionId}`;
                
                // Show the delete confirmation modal
                const modal = new bootstrap.Modal(document.getElementById('deleteQuestionModal'));
                modal.show();
            });
        });
    });
</script>

<script>
    // Set up delete modal with correct form action
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteQuestionModal');
        const deleteForm = document.getElementById('deleteQuestionForm');
        
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const questionId = button.getAttribute('data-question-id');
            const deleteUrl = '{{ route("admin.competitions.quizzes.questions.destroy", ["competition" => $competition->id, "quiz" => $quiz->id, "question" => "QUESTION_ID"]) }}';
            
            // Update form action with the correct question ID
            deleteForm.action = deleteUrl.replace('QUESTION_ID', questionId);
        });
    });
</script>

<script>
    // Initialize question type options when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateQuestionType();
    });

    // Function to update the form based on question type
    function updateQuestionType() {
        const type = document.getElementById('type').value;
        const optionsContainer = document.getElementById('options-container');
        const correctAnswerContainer = document.getElementById('correct-answer-container');
        
        // Clear previous content
        optionsContainer.innerHTML = '';
        correctAnswerContainer.innerHTML = '';
        
        if (type === 'multiple_choice' || type === 'true_false') {
            // Add options container for multiple choice/true-false
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Options <span class="text-danger">*</span></label>
                    <div id="mcq-options">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="options[]" placeholder="Option 1" required>
                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="options[]" placeholder="Option 2" required>
                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                        <i class='bx bx-plus'></i> Add Option
                    </button>
                </div>
            `;
            
            // Add correct answer selection
            correctAnswerContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
                    <div id="correct-answers">
                        ${type === 'multiple_choice' ? 
                            '<div class="form-check"><input class="form-check-input" type="checkbox" name="correct_answers[]" value="0" id="correct-0"><label class="form-check-label" for="correct-0">Option 1</label></div>' +
                            '<div class="form-check"><input class="form-check-input" type="checkbox" name="correct_answers[]" value="1" id="correct-1"><label class="form-check-label" for="correct-1">Option 2</label></div>' 
                            : 
                            '<div class="form-check"><input class="form-check-input" type="radio" name="correct_answers[]" value="0" id="correct-0" checked><label class="form-check-label" for="correct-0">True</label></div>' +
                            '<div class="form-check"><input class="form-check-input" type="radio" name="correct_answers[]" value="1" id="correct-1"><label class="form-check-label" for="correct-1">False</label></div>'
                        }
                    </div>
                </div>
            `;
        } else if (type === 'short_answer') {
            // For short answer questions
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Expected Answer</label>
                    <input type="text" class="form-control" name="expected_answer" placeholder="Expected answer (optional)">
                </div>
            `;
        } else if (type === 'essay') {
            // For essay questions
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Answer Guidelines</label>
                    <textarea class="form-control" name="guidelines" placeholder="Provide any guidelines for the essay (optional)" rows="3"></textarea>
                </div>
            `;
        }
    }

    // Function to add a new option
    function addOption() {
        const optionsContainer = document.getElementById('mcq-options');
        const optionCount = optionsContainer.children.length;
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'input-group mb-2';
        optionDiv.innerHTML = `
            <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount + 1}" required>
            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
        `;
        
        optionsContainer.appendChild(optionDiv);
        
        // Add the new option to the correct answers list
        const correctAnswers = document.getElementById('correct-answers');
        const newOption = document.createElement('div');
        newOption.className = 'form-check';
        newOption.innerHTML = `
            <input class="form-check-input" type="${document.getElementById('type').value === 'multiple_choice' ? 'checkbox' : 'radio'}" 
                   name="correct_answers[]" 
                   value="${optionCount}" 
                   id="correct-${optionCount}">
            <label class="form-check-label" for="correct-${optionCount}">Option ${optionCount + 1}</label>
        `;
        correctAnswers.appendChild(newOption);
    }
    
    // Function to remove an option
    function removeOption(button) {
        const optionDiv = button.closest('.input-group');
        const optionIndex = Array.from(optionDiv.parentNode.children).indexOf(optionDiv);
        
        // Remove the option
        optionDiv.remove();
        
        // Update the correct answers checkboxes/radios
        const correctAnswers = document.getElementById('correct-answers');
        const answerInputs = correctAnswers.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        
        if (answerInputs[optionIndex]) {
            answerInputs[optionIndex].parentNode.remove();
        }
        
        // Update the remaining options
        const remainingOptions = correctAnswers.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        remainingOptions.forEach((input, index) => {
            input.value = index;
            input.id = `correct-${index}`;
            input.nextElementSibling.htmlFor = `correct-${index}`;
            input.nextElementSibling.textContent = `Option ${index + 1}`;
        });
        
        // Update the input placeholders
        const optionInputs = document.querySelectorAll('#mcq-options .form-control');
        optionInputs.forEach((input, index) => {
            input.placeholder = `Option ${index + 1}`;
        });
    }
    
    // Function to prepare form data before submission
    function prepareFormData(event) {
        const form = event.target;
        const formData = new FormData(form);
        
        // For AJAX submission (uncomment if needed)
        /*
        event.preventDefault();
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Handle success (e.g., close modal, show success message, refresh questions list)
                const modal = bootstrap.Modal.getInstance(document.getElementById('questionModal'));
                modal.hide();
                window.location.reload();
            } else {
                // Handle validation errors
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
        
        return false;
        */
        
        return true; // Allow normal form submission
    }
</script>
@endpush
