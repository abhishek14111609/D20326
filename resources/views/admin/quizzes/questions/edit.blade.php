@extends('admin.layouts.app')

@section('title', 'Edit Question')

@push('styles')
<style>
    .option-item {
        position: relative;
        margin-bottom: 10px;
    }
    .remove-option {
        position: absolute;
        right: -35px;
        top: 50%;
        transform: translateY(-50%);
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
                <a href="{{ route('admin.competitions.show', $competition) }}">{{ $competition->title }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}">{{ $quiz->title }}</a>
            </li>
            <li class="breadcrumb-item active">Edit Question</li>
        </ol>
        <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" class="btn btn-sm btn-outline-secondary">
            <i class='bx bx-arrow-back me-1'></i> Back to Questions
        </a>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Question</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.competitions.quizzes.questions.update', [$competition->id, $quiz->id, $question->id]) }}" method="POST" id="questionForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="question" class="form-label">Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="question" name="question" rows="3" required>{{ old('question', $question->question) }}</textarea>
                            @error('question')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Question Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required onchange="updateQuestionType()">
                                        <option value="multiple_choice" {{ old('type', $question->type) == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                        <option value="true_false" {{ old('type', $question->type) == 'true_false' ? 'selected' : '' }}>True/False</option>
                                        <!-- <option value="short_answer" {{ old('type', $question->type) == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                        <option value="essay" {{ old('type', $question->type) == 'essay' ? 'selected' : '' }}>Essay</option> -->
                                    </select>
                                    @error('type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control" id="points" name="points" value="{{ old('points', $question->points) }}" required>
                                    @error('points')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (seconds)</label>
                                    <input type="number" min="0" class="form-control" id="time_limit" name="time_limit" value="{{ old('time_limit', $question->time_limit) }}">
                                    <small class="text-muted">0 for no time limit</small>
                                    @error('time_limit')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Options Container (for multiple choice/true-false) -->
                        <div id="options-container" class="mb-3">
                            <!-- Will be populated by JavaScript based on question type -->
                        </div>

                        <!-- Explanation Field -->
                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation (Optional)</label>
                            <textarea class="form-control" id="explanation" name="explanation" rows="3" placeholder="Provide an explanation for the correct answer (optional)">{{ old('explanation', $question->explanation ?? '') }}</textarea>
                            @error('explanation')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This will be shown to users after they answer the question.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" class="btn btn-outline-secondary">
                                <i class='bx bx-x me-1'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Update Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Question Help</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Multiple Choice:</strong> Create questions with multiple possible answers, one or more of which are correct.
                    </p>
                    <p class="text-muted">
                        <strong>True/False:</strong> Simple true or false questions with one correct answer.
                    </p>
                   <!--  <p class="text-muted">
                        <strong>Short Answer:</strong> Questions that require a short, specific answer.
                    </p>
                    <p class="text-muted">
                        <strong>Essay:</strong> Open-ended questions that require a longer, more detailed response.
                    </p> -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize the form with the current question type
    document.addEventListener('DOMContentLoaded', function() {
        // Parse the options and correct answers
        const questionType = '{{ $question->question_type }}';
        let options = [];
        let correctAnswers = [];

        @if($question->question_type === 'multiple_choice' || $question->question_type === 'true_false')
            @php
                // Handle options (could be JSON string or already an array)
                $options = $question->options;
                if (is_string($options)) {
                    $options = json_decode($options, true);
                }
                $options = is_array($options) ? $options : [];
                
                // Handle correct answers (could be JSON string or already an array)
                $correctAnswers = $question->correct_answer;
                if (is_string($correctAnswers)) {
                    $correctAnswers = json_decode($correctAnswers, true);
                }
                $correctAnswers = is_array($correctAnswers) ? $correctAnswers : [];
            @endphp
            options = @json($options);
            correctAnswers = @json($correctAnswers);
        @endif

        // Set up the form based on the question type
        updateQuestionType(questionType, options, correctAnswers);
    });

    function updateQuestionType(type = null, options = [], correctAnswers = []) {
        const questionType = type || document.getElementById('type').value;
        const optionsContainer = document.getElementById('options-container');
        
        // Clear previous content
        optionsContainer.innerHTML = '';
        
        if (questionType === 'multiple_choice') {
            let optionsHtml = `
                <div class="mb-3">
                    <label class="form-label">Options <span class="text-danger">*</span></label>
                    <div id="mcq-options">`;
            
            if (options && options.length > 0) {
                options.forEach((option, index) => {
                    const isChecked = correctAnswers.includes(index) ? 'checked' : '';
                    optionsHtml += `
                        <div class="option-item mb-2 d-flex align-items-center">
                            <input type="text" name="options[]" class="form-control me-2" 
                                   value="${option}" placeholder="Option ${index + 1}" required>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="correct_answers[]" value="${index}" ${isChecked}>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                    onclick="removeOption(this)">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>`;
                });
            } else {
                // Default empty option
                optionsHtml += `
                    <div class="option-item mb-2 d-flex align-items-center">
                        <input type="text" name="options[]" class="form-control me-2" 
                               placeholder="Option 1" required>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="correct_answers[]" value="0">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                disabled>
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>`;
            }
            
            optionsHtml += `
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                            onclick="addOption()">
                        <i class='bx bx-plus me-1'></i> Add Option
                    </button>
                </div>
                <div class="form-text">Check the box next to the correct answer(s).</div>`;
                
            optionsContainer.innerHTML = optionsHtml;
        } 
        else if (questionType === 'true_false') {
            const trueChecked = correctAnswers.includes(0) ? 'checked' : '';
            const falseChecked = correctAnswers.includes(1) ? 'checked' : '';
            
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Select the correct answer <span class="text-danger">*</span></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="correct_answers[]" 
                               id="trueOption" value="0" ${trueChecked}>
                        <label class="form-check-label" for="trueOption">
                            True
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="correct_answers[]" 
                               id="falseOption" value="1" ${falseChecked}>
                        <label class="form-check-label" for="falseOption">
                            False
                        </label>
                    </div>
                </div>`;
        }
        else if (questionType === 'short_answer') {
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label for="expected_answer" class="form-label">Expected Answer (Optional)</label>
                    <input type="text" class="form-control" id="expected_answer" 
                           name="expected_answer" value="{{ old('expected_answer', $question->expected_answer ?? '') }}">
                    <div class="form-text">Provide a sample correct answer (optional).</div>
                </div>`;
        }
        else if (questionType === 'essay') {
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label for="guidelines" class="form-label">Guidelines for Grading (Optional)</label>
                    <textarea class="form-control" id="guidelines" name="guidelines" rows="3">
                        {{ old('guidelines', $question->guidelines ?? '') }}
                    </textarea>
                    <div class="form-text">Provide guidelines for grading this essay question (optional).</div>
                </div>`;
        }
    }

    function addOption() {
        const mcqOptions = document.getElementById('mcq-options');
        if (!mcqOptions) return;
        
        const optionCount = document.querySelectorAll('#mcq-options .option-item').length;
        const newOption = document.createElement('div');
        newOption.className = 'option-item mb-2 d-flex align-items-center';
        newOption.innerHTML = `
            <input type="text" name="options[]" class="form-control me-2" 
                   placeholder="Option ${optionCount + 1}" required>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       name="correct_answers[]" value="${optionCount}">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                    onclick="removeOption(this)">
                <i class='bx bx-trash'></i>
            </button>`;
        
        mcqOptions.appendChild(newOption);
    }
    
    function removeOption(button) {
        const optionItem = button.closest('.option-item');
        if (optionItem) {
            optionItem.remove();
            
            // Update the option indexes and labels
            document.querySelectorAll('#mcq-options .option-item').forEach((item, index) => {
                const input = item.querySelector('input[type="text"]');
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (input) {
                    input.placeholder = `Option ${index + 1}`;
                }
                if (checkbox) {
                    checkbox.value = index;
                }
            });
        }
    }
</script>
@endpush
