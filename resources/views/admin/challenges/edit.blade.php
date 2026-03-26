@extends('admin.layouts.app')

@section('title', 'Edit Challenge')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/quill/typography.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/quill/editor.css') }}">
<style>
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
    }
    .select2-container {
        width: 100% !important;
    }
    .ql-editor {
        min-height: 150px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('admin.challenges.update', $challenge) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Challenge: {{ $challenge->title }}</h5>
                        <div>
                            <a href="{{ route('admin.challenges.index') }}" class="btn btn-label-secondary me-2">
                                <i class='bx bx-arrow-back me-1'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Update Challenge
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $challenge->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="type">Challenge Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="" disabled>Select type</option>
                                            <option value="daily" {{ old('type', $challenge->type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('type', $challenge->type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ old('type', $challenge->type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="one_time" {{ old('type', $challenge->type) == 'one_time' ? 'selected' : '' }}>One Time</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="draft" {{ old('status', $challenge->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="active" {{ old('status', $challenge->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="completed" {{ old('status', $challenge->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status', $challenge->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control flatpickr-datetime @error('start_date') is-invalid @enderror" 
                                            id="start_date" name="start_date" 
                                            value="{{ old('start_date', $challenge->start_date ? $challenge->start_date->format('Y-m-d H:i') : '') }}" 
                                            placeholder="YYYY-MM-DD HH:MM" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="end_date">End Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control flatpickr-datetime @error('end_date') is-invalid @enderror" 
                                            id="end_date" name="end_date" 
                                            value="{{ old('end_date', $challenge->end_date ? $challenge->end_date->format('Y-m-d H:i') : '') }}" 
                                            placeholder="YYYY-MM-DD HH:MM" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="target_count">Target Count <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('target_count') is-invalid @enderror" 
                                            id="target_count" name="target_count" min="1" 
                                            value="{{ old('target_count', $challenge->target_count) }}" required>
                                        <small class="text-muted">Number of times users need to complete this challenge</small>
                                        @error('target_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="reward_points">Reward Points <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('reward_points') is-invalid @enderror" 
                                                id="reward_points" name="reward_points" min="0" 
                                                value="{{ old('reward_points', $challenge->reward_points) }}" required>
                                            <span class="input-group-text">points</span>
                                        </div>
                                        @error('reward_points')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <div id="description-editor">{!! old('description', $challenge->description) !!}</div>
                                        <input type="hidden" name="description" id="description">
                                        @error('description')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="rules">Rules & Guidelines</label>
                                        <div id="rules-editor">{!! old('rules', $challenge->rules) !!}</div>
                                        <input type="hidden" name="rules" id="rules">
                                        @error('rules')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Challenge Image</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if($challenge->image)
                                                <img src="{{ asset('storage/' . $challenge->image) }}" id="image-preview" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                            @else
                                                <img src="#" id="image-preview" class="img-fluid rounded mb-2 d-none" style="max-height: 200px;">
                                            @endif
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                            <small class="text-muted d-block mt-2">Recommended size: 800x450px (16:9 aspect ratio)</small>
                                            @if($challenge->image)
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                                    <label class="form-check-label text-danger" for="remove_image">
                                                        Remove current image
                                                    </label>
                                                </div>
                                            @endif
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $challenge->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured Challenge</label>
                                            <small class="text-muted d-block">Featured challenges will be highlighted on the challenges page</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="sort_order">Sort Order</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                id="sort_order" name="sort_order" min="0" 
                                                value="{{ old('sort_order', $challenge->sort_order) }}">
                                            <small class="text-muted">Lower numbers appear first</small>
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Created At</label>
                                            <p class="form-control-static">{{ $challenge->created_at->format('M j, Y H:i') }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Last Updated</label>
                                            <p class="form-control-static">{{ $challenge->updated_at->format('M j, Y H:i') }}</p>
                                        </div>
                                        
                                        @if($challenge->deleted_at)
                                            <div class="alert alert-warning">
                                                <i class='bx bx-info-circle'></i> This challenge was deleted on {{ $challenge->deleted_at->format('M j, Y') }}.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-label-danger me-auto" onclick="confirmDelete({{ $challenge->id }})">
                            <i class='bx bx-trash me-1'></i> Delete Challenge
                        </button>
                        
                        <a href="{{ route('admin.challenges.index') }}" class="btn btn-label-secondary me-2">
                            <i class='bx bx-x me-1'></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i> Update Challenge
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" action="{{ route('admin.challenges.destroy', $challenge) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('vendor/libs/quill/katex.js') }}"></script>
<script src="{{ asset('vendor/libs/quill/quill.js') }}"></script>

<script>
    // Initialize date/time picker
    document.addEventListener('DOMContentLoaded', function() {
        // Flatpickr initialization
        if (document.querySelector('.flatpickr-datetime')) {
            flatpickr('.flatpickr-datetime', {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                time_24hr: true,
                minDate: '{{ $challenge->start_date->isPast() ? $challenge->start_date->format("Y-m-d") : "today" }}',
                defaultDate: '{{ now()->format("Y-m-d H:i") }}',
            });
        }
        
        // Initialize Quill editor for description
        const descriptionEditor = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter challenge description...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });
        
        // Initialize Quill editor for rules
        const rulesEditor = new Quill('#rules-editor', {
            theme: 'snow',
            placeholder: 'Enter challenge rules and guidelines...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });
        
        // Update hidden inputs before form submission
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
            document.getElementById('rules').value = rulesEditor.root.innerHTML;
        });
    });
    
    // Image preview
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onloadend = function() {
            preview.src = reader.result;
            preview.classList.remove('d-none');
        }
        
        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = '{{ $challenge->image ? asset("storage/" . $challenge->image) : "#" }}';
            if (!preview.src || preview.src === window.location.href + '#') {
                preview.classList.add('d-none');
            }
        }
    }
    
    // Confirm before deleting
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this challenge? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
