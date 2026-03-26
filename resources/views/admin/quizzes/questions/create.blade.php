@extends('admin.layouts.app')

@section('title', 'Add New Question')

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
            <li class="breadcrumb-item active">Add Question</li>
        </ol>
        <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" class="btn btn-sm btn-outline-secondary">
            <i class='bx bx-arrow-back me-1'></i> Back to Questions
        </a>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Question</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.competitions.quizzes.questions.store', ['competition' => $competition->id, 'quiz' => $quiz->id]) }}" method="POST" id="questionForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="question" class="form-label">Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="question" name="question" rows="3" required>{{ old('question') }}</textarea>
                            @error('question')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Question Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required onchange="updateQuestionType()">
                                        <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                        <option value="true_false" {{ old('type') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                        <!-- <option value="short_answer" {{ old('type') == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                        <option value="essay" {{ old('type') == 'essay' ? 'selected' : '' }}>Essay</option> -->
                                    </select>
                                    @error('type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control" id="points" name="points" value="{{ old('points', 1) }}" required>
                                    @error('points')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (seconds)</label>
                                    <input type="number" min="0" class="form-control" id="time_limit" name="time_limit" value="{{ old('time_limit', 0) }}">
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

                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation (Optional)</label>
                            <textarea class="form-control" id="explanation" name="explanation" rows="2">{{ old('explanation') }}</textarea>
                            <small class="text-muted">Provide an explanation for the correct answer (shown after quiz submission).</small>
                            @error('explanation')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" class="btn btn-outline-secondary">
                                <i class='bx bx-x me-1'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Save Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Question Types</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-2">Multiple Choice</h6>
                        <p class="small text-muted mb-2">Multiple options with one or more correct answers.</p>
                        
                        <h6 class="mb-2 mt-3">True/False</h6>
                        <p class="small text-muted mb-2">Simple true or false question.</p>
                        
                        <!-- <h6 class="mb-2 mt-3">Short Answer</h6>
                        <p class="small text-muted mb-2">Short text response with optional expected answer.</p>
                        
                        <h6 class="mb-2 mt-3">Essay</h6>
                        <p class="small text-muted">Long form text response with optional guidelines.</p> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the form based on the selected question type
        updateQuestionType();
    });

    function updateQuestionType() {
        const type = document.getElementById('type').value;
        const optionsContainer = document.getElementById('options-container');
        
        // Clear previous content
        optionsContainer.innerHTML = '';
        
        if (type === 'multiple_choice' || type === 'true_false') {
            const isTrueFalse = type === 'true_false';
            const options = isTrueFalse 
                ? ['True', 'False'] 
                : ['', ''];
            
            let optionsHtml = `
                <label class="form-label">Options <span class="text-danger">*</span></label>
                <small class="d-block text-muted mb-2">Check the correct answer(s) below:</small>
                <div id="mcq-options">
            `;
            
            // Add options
            options.forEach((option, index) => {
                optionsHtml += `
                    <div class="option-item">
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <div class="form-check">
                                    <input class="form-check-input" type="${isTrueFalse ? 'radio' : 'checkbox'}" 
                                           name="correct_answers[]" value="${index}" 
                                           id="correct_${index}" ${index === 0 ? 'checked' : ''}>
                                    <label class="form-check-label d-none" for="correct_${index}">
                                        Correct Answer
                                    </label>
                                </div>
                            </div>
                            <input type="text" class="form-control" name="options[]" 
                                   value="${option}" placeholder="Option ${index + 1}" required>
                            ${!isTrueFalse ? `
                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" title="Remove option">
                                <i class='bx bx-trash'></i>
                            </button>
                            ` : ''}
                        </div>
                        <div class="form-text ms-4 text-success" id="correct-label-${index}" style="display: none;">
                            <i class='bx bx-check-circle me-1'></i> Marked as correct answer
                        </div>
                    </div>
                `;
            });
            
            if (!isTrueFalse) {
                optionsHtml += `
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                            <i class='bx bx-plus me-1'></i> Add Option
                        </button>
                        <small class="text-muted ms-2">Check the box to mark as correct answer</small>
                    </div>
                `;
            }
            
            optionsContainer.innerHTML = optionsHtml + '</div>';
            
            // Add event listeners to show/hide correct answer labels
            document.querySelectorAll('input[name^="correct_answers"]').forEach(input => {
                input.addEventListener('change', function() {
                    const index = this.value;
                    const label = document.getElementById(`correct-label-${index}`);
                    if (label) {
                        label.style.display = this.checked ? 'block' : 'none';
                    }
                });
                // Trigger change to show initial state
                input.dispatchEvent(new Event('change'));
            });
            
        } else if (type === 'short_answer') {
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label for="expected_answer" class="form-label">Expected Answer (Optional)</label>
                    <input type="text" class="form-control" id="expected_answer" name="expected_answer" 
                           value="{{ old('expected_answer') }}" placeholder="Expected answer">
                    <small class="text-muted">The expected answer for automated checking (case-insensitive).</small>
                </div>
            `;
            
        } else if (type === 'essay') {
            optionsContainer.innerHTML = `
                <div class="mb-3">
                    <label for="guidelines" class="form-label">Guidelines (Optional)</label>
                    <textarea class="form-control" id="guidelines" name="guidelines" 
                              rows="3" placeholder="Provide guidelines for the essay">{{ old('guidelines') }}</textarea>
                </div>
            `;
        }
    }

    function addOption() {
        const mcqOptions = document.getElementById('mcq-options');
        if (!mcqOptions) return;
        
        const optionCount = document.querySelectorAll('#mcq-options .option-item').length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-item';
        optionDiv.innerHTML = `
            <div class="input-group mb-2">
                <div class="input-group-text">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="correct_answers[]" value="${optionCount}">
                        <label class="form-check-label d-none" for="correct_${optionCount}">
                            Correct Answer
                        </label>
                    </div>
                </div>
                <input type="text" class="form-control" name="options[]" 
                       placeholder="Option ${optionCount + 1}" required>
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" title="Remove option">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
            <div class="form-text ms-4 text-success" id="correct-label-${optionCount}" style="display: none;">
                <i class='bx bx-check-circle me-1'></i> Marked as correct answer
            </div>
        `;
        
        // Insert before the add button container
        const addButtonContainer = mcqOptions.querySelector('.mt-2');
        if (addButtonContainer) {
            mcqOptions.insertBefore(optionDiv, addButtonContainer);
        } else {
            mcqOptions.appendChild(optionDiv);
        }
        
        // Add event listener to show/hide correct answer label
        const input = optionDiv.querySelector('input[name^="correct_answers"]');
        input.addEventListener('change', function() {
            const index = this.value;
            const label = document.getElementById(`correct-label-${index}`);
            if (label) {
                label.style.display = this.checked ? 'block' : 'none';
            }
        });
        // Trigger change to show initial state
        input.dispatchEvent(new Event('change'));
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
