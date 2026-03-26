@php
    $isEdit = isset($quiz);
    $action = $isEdit 
        ? route('admin.competitions.quizzes.update', [$competition, $quiz])
        : route('admin.competitions.quizzes.store', $competition);
    $method = $isEdit ? 'PUT' : 'POST';
    $title = $isEdit ? 'Edit Quiz' : 'Create New Quiz';
    $buttonText = $isEdit ? 'Update Quiz' : 'Create Quiz';
@endphp

<div class="card mb-4">
    <h5 class="card-header">{{ $title }}</h5>
    <div class="card-body">
        <form id="quizForm" action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method($method)

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $quiz->title ?? '') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                 id="description" name="description" rows="3">{{ old('description', $quiz->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" value="{{ old('duration', $quiz->duration ?? 30) }}" required>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="100" class="form-control @error('passing_score') is-invalid @enderror" 
                                       id="passing_score" name="passing_score" value="{{ old('passing_score', $quiz->passing_score ?? 70) }}" required>
                                @error('passing_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_attempts" class="form-label">Max Attempts (0 for unlimited)</label>
                                <input type="number" min="0" class="form-control @error('max_attempts') is-invalid @enderror" 
                                       id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts ?? 0) }}">
                                @error('max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" 
                                       value="{{ old('start_time', isset($quiz->start_time) ? $quiz->start_time->format('Y-m-d\TH:i') : '') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time"
                                       value="{{ old('end_time', isset($quiz->end_time) ? $quiz->end_time->format('Y-m-d\TH:i') : '') }}">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           value="1" {{ old('is_active', $quiz->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label">Instructions</label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                 id="instructions" name="instructions" rows="3">{{ old('instructions', $quiz->instructions ?? '') }}</textarea>
                        @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Quiz Settings</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Show Correct Answers</label>
                                <select class="form-select" name="show_answers">
                                    <option value="after_submission" {{ old('show_answers', $quiz->show_answers ?? 'after_submission') === 'after_submission' ? 'selected' : '' }}>
                                        After Submission
                                    </option>
                                    <option value="after_quiz" {{ old('show_answers', $quiz->show_answers ?? '') === 'after_quiz' ? 'selected' : '' }}>
                                        After Quiz Ends
                                    </option>
                                    <option value="never" {{ old('show_answers', $quiz->show_answers ?? '') === 'never' ? 'selected' : '' }}>
                                        Never
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Time Limit Per Question (seconds)</label>
                                <input type="number" min="0" class="form-control" 
                                       name="time_per_question" value="{{ old('time_per_question', $quiz->time_per_question ?? 0) }}">
                                <small class="text-muted">0 for no time limit</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shuffle_questions" 
                                           name="shuffle_questions" value="1" 
                                           {{ old('shuffle_questions', $quiz->shuffle_questions ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_questions">
                                        Shuffle Questions
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allow_skipping" 
                                           name="allow_skipping" value="1" 
                                           {{ old('allow_skipping', $quiz->allow_skipping ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_skipping">
                                        Allow Skipping Questions
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="show_progress" 
                                           name="show_progress" value="1" 
                                           {{ old('show_progress', $quiz->show_progress ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_progress">
                                        Show Progress Bar
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> {{ $buttonText }}
                                </button>
                                <a href="{{ route('admin.competitions.quizzes.index', $competition) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/ckeditor/plugins/codesnippet/lib/highlight/styles/github.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('vendor/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize CKEditor for description and instructions
        const editors = [
            { id: 'description', height: 150 },
            { id: 'instructions', height: 150 }
        ];

        editors.forEach(editor => {
            if (document.getElementById(editor.id)) {
                CKEDITOR.replace(editor.id, {
                    height: editor.height,
                    removeButtons: 'Source,Save,NewPage,ExportPdf,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,RemoveFormat,CreateDiv,Blockquote,BidiLtr,BidiRtl,Language,Link,Unlink,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,FontSize,TextColor,BGColor,ShowBlocks,About',
                    removePlugins: 'elementspath',
                    resize_enabled: false,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                        { name: 'styles', items: ['Styles', 'Format'] },
                        { name: 'colors', items: ['TextColor', 'BGColor'] },
                        { name: 'tools', items: ['Maximize', 'Source'] }
                    ]
                });
            }
        });

        // Initialize date time pickers
        const dateTimeOptions = {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            minDate: 'today'
        };

        // Initialize Flatpickr for datetime inputs
        flatpickr('#start_time', dateTimeOptions);
        flatpickr('#end_time', {
            ...dateTimeOptions,
            minDate: document.getElementById('start_time')?.value || 'today'
        });

        // Update end time min date when start time changes
        document.getElementById('start_time')?.addEventListener('change', function(e) {
            const endTimePicker = flatpickr('#end_time');
            endTimePicker.set('minDate', e.target.value || 'today');
            
            // If end time is before new start time, update it
            if (endTimePicker.selectedDates[0] && new Date(e.target.value) > endTimePicker.selectedDates[0]) {
                endTimePicker.setDate(e.target.value);
            }
        });
    });
</script>
@endpush
