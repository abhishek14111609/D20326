@extends('admin.layouts.app')

@section('title', 'Create Competition')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/quill/typography.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/quill/editor.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/tagify/tagify.css') }}">
<style>
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
        display: none;
    }
    .select2-container {
        width: 100% !important;
    }
    .ql-editor {
        min-height: 150px;
    }
    .tagify {
        width: 100%;
    }
    .nav-tabs .nav-link {
        cursor: pointer;
    }
    .image-upload-container {
        position: relative;
        transition: all 0.3s ease;
    }
    .image-upload-container:hover {
        cursor: pointer;
    }
    .image-upload-container.was-validated .form-control:invalid {
        border-color: #dc3545;
    }
    .preview-placeholder {
        transition: all 0.3s ease;
    }
    .preview-placeholder:hover {
        border-color: #7367f0 !important;
        background-color: rgba(115, 103, 240, 0.05) !important;
    }
    .btn-remove-image {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: all 0.2s ease;
    }
    .btn-remove-image:hover {
        opacity: 1;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('admin.competitions.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create New Competition</h5>
                        <div>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-label-secondary me-2">
                                <i class='bx bx-arrow-back me-1'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Save Competition
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

                        <ul class="nav nav-tabs mb-4" id="competitionTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="basic-tab" data-bs-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                    <i class='bx bx-info-circle me-1'></i> Basic Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="schedule-tab" data-bs-toggle="tab" href="#schedule" role="tab" aria-controls="schedule" aria-selected="false">
                                    <i class='bx bx-calendar me-1'></i> Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="false">
                                    <i class='bx bx-detail me-1'></i> Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="prizes-tab" data-bs-toggle="tab" href="#prizes" role="tab" aria-controls="prizes" aria-selected="false">
                                    <i class='bx bx-award me-1'></i> Prizes
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="competitionTabsContent">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $competition->title) }}">
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="type">Competition Type <span class="text-danger">*</span></label>
                                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                                    <option value="" disabled {{ old('type', $competition->type) ? '' : 'selected' }}>Select type</option>
                                                    <option value="solo" {{ old('type', $competition->type) == 'solo' ? 'selected' : '' }}>Solo</option>
                                                    <option value="team" {{ old('type', $competition->type) == 'team' ? 'selected' : '' }}>Team</option>
                                                    <option value="tournament" {{ old('type', $competition->type) == 'tournament' ? 'selected' : '' }}>Tournament</option>
                                                    <option value="league" {{ old('type', $competition->type) == 'league' ? 'selected' : '' }}>League</option>
                                                </select>
                                                @error('type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                                    <option value="draft" {{ old('status', $competition->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="upcoming" {{ old('status', $competition->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                    <option value="active" {{ old('status', $competition->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="completed" {{ old('status', $competition->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ old('status', $competition->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                                            <div id="description-editor">{!! old('description', $competition->description ?? '') !!}</div>
                                            <input type="hidden" name="description" id="description">
                                            @error('description')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="tags">Tags</label>
                                            <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', $competition->tags ? implode(',', json_decode($competition->tags)) : '') }}">
                                            <small class="text-muted">Separate tags with commas</small>
                                            @error('tags')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">Competition Images</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label" for="image">Thumbnail Image <span class="text-muted">(Optional)</span></label>
                                                    <img src="#" id="image-preview" class="img-fluid rounded mb-2 d-none" style="max-height: 200px;">
                                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(this, 'image-preview')">
                                                    <small class="text-muted d-block mt-2">Recommended size: 800x450px (16:9 aspect ratio)</small>
                                                    @error('image')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label" for="banner_image">Banner Image <span class="text-muted">(Optional)</span></label>
                                                    <div class="image-upload-container">
                                                        <div class="image-preview-container position-relative mb-2">
                                                            <img src="#" id="banner-preview" class="img-fluid rounded d-none" style="max-height: 200px; width: 100%; object-fit: cover;">
                                                            <div class="preview-placeholder bg-light d-flex align-items-center justify-content-center" style="height: 200px; border: 2px dashed #d1d7e0; border-radius: 0.375rem;">
                                                                <div class="text-center p-3">
                                                                    <i class='bx bx-image-add fs-1 text-muted mb-2'></i>
                                                                    <p class="mb-0">Click to upload banner image</p>
                                                                    <small class="d-block text-muted">Recommended: 1920×600px (16:5), Max: 5MB</small>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 d-none" id="remove-banner" style="z-index: 5;">
                                                                <i class='bx bx-trash'></i>
                                                            </button>
                                                        </div>
                                                        <input type="file" class="form-control d-none" id="banner_image" name="banner_image" accept="image/jpeg, image/png, image/webp">
                                                        <div id="banner-feedback" class="invalid-feedback"></div>
                                                        <div class="image-info small text-muted mt-1 d-none">
                                                            <div>Dimensions: <span id="banner-dimensions">-</span></div>
                                                            <div>Size: <span id="banner-size">-</span></div>
                                                        </div>
                                                        @error('banner_image')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Settings</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $competition->is_featured) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_featured">Featured Competition</label>
                                                    <small class="text-muted d-block">Featured competitions will be highlighted on the competitions page</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label" for="sort_order">Sort Order</label>
                                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                        id="sort_order" name="sort_order" min="0" 
                                                        value="{{ old('sort_order', $competition->sort_order) }}">
                                                    <small class="text-muted">Lower numbers appear first</small>
                                                    @error('sort_order')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Schedule Tab -->
                            <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="registration_start">Registration Start <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('registration_start') is-invalid @enderror" 
                                                id="registration_start" name="registration_start" 
                                                value="{{ old('registration_start', $competition->registration_start ? \Carbon\Carbon::parse($competition->registration_start)->format('Y-m-d\TH:i') : '') }}">
                                            @error('registration_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="registration_end">Registration End <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('registration_end') is-invalid @enderror" 
                                                id="registration_end" name="registration_end" 
                                                value="{{ old('registration_end', $competition->registration_end ? \Carbon\Carbon::parse($competition->registration_end)->format('Y-m-d\TH:i') : '') }}">
                                            @error('registration_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="competition_start">Competition Start <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('competition_start') is-invalid @enderror" 
                                                id="competition_start" name="competition_start" 
                                                value="{{ old('competition_start', $competition->competition_start ? \Carbon\Carbon::parse($competition->competition_start)->format('Y-m-d\TH:i') : '') }}">
                                            @error('competition_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="competition_end">Competition End <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('competition_end') is-invalid @enderror" 
                                                id="competition_end" name="competition_end" 
                                                value="{{ old('competition_end', $competition->competition_end ? \Carbon\Carbon::parse($competition->competition_end)->format('Y-m-d\TH:i') : '') }}">
                                            @error('competition_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                                @foreach(timezone_identifiers_list() as $timezone)
                                                    <option value="{{ $timezone }}" {{ old('timezone', $competition->timezone ?? 'UTC') == $timezone ? 'selected' : '' }}>
                                                        {{ $timezone }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('timezone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Details Tab -->
                            <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="max_participants">Max Participants</label>
                                            <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                                                id="max_participants" name="max_participants" min="0" 
                                                value="{{ old('max_participants', $competition->max_participants) }}">
                                            <small class="text-muted">Leave empty for unlimited participants</small>
                                            @error('max_participants')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="min_team_size">Min Team Size</label>
                                            <input type="number" class="form-control @error('min_team_size') is-invalid @enderror" 
                                                id="min_team_size" name="min_team_size" min="1" 
                                                value="{{ old('min_team_size', $competition->min_team_size) }}">
                                            <small class="text-muted">For team competitions only</small>
                                            @error('min_team_size')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="max_team_size">Max Team Size</label>
                                            <input type="number" class="form-control @error('max_team_size') is-invalid @enderror" 
                                                id="max_team_size" name="max_team_size" min="1" 
                                                value="{{ old('max_team_size', $competition->max_team_size) }}">
                                            <small class="text-muted">For team competitions only</small>
                                            @error('max_team_size')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="entry_fee">Entry Fee</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control @error('entry_fee') is-invalid @enderror" 
                                                    id="entry_fee" name="entry_fee" min="0" step="0.01" 
                                                    value="{{ old('entry_fee', $competition->entry_fee) }}">
                                                @error('entry_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Set to 0 for free entry</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="currency">Currency</label>
                                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                                                <option value="USD" {{ old('currency', $competition->currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                                <option value="EUR" {{ old('currency', $competition->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                                <option value="GBP" {{ old('currency', $competition->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                                <option value="INR" {{ old('currency', $competition->currency) == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                            </select>
                                            @error('currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    

                                </div>
                            </div>
                            
                            <!-- Prizes Tab -->
                            <div class="tab-pane fade" id="prizes" role="tabpanel" aria-labelledby="prizes-tab">
                                <div class="alert alert-info">
                                    <i class='bx bx-info-circle me-2'></i>
                                    Add the prize tiers for this competition. You can add multiple prize tiers with different ranks and rewards.
                                </div>
                                
                                <div id="prize-tiers-container">
                                    <!-- Prize tiers will be added here dynamically -->
                                    <div class="prize-tier-item card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-1 d-flex align-items-center">
                                                    <span class="badge bg-primary rank-badge">1</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Prize Name</label>
                                                    <input type="text" class="form-control prize-name" name="prizes[0][name]" placeholder="e.g., 1st Place">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Prize Value</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control prize-value" name="prizes[0][value]" min="0" step="0.01" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control prize-description" name="prizes[0][description]" placeholder="e.g., Cash Prize">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-tier" disabled>
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-primary" id="add-prize-tier">
                                        <i class='bx bx-plus me-1'></i> Add Prize Tier
                                    </button>
                                </div>
                                
                                <template id="prize-tier-template">
                                    <div class="prize-tier-item card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-1 d-flex align-items-center">
                                                    <span class="badge bg-primary rank-badge"></span>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control prize-name" name="prizes[__INDEX__][name]" placeholder="e.g., 2nd Place">
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control prize-value" name="prizes[__INDEX__][value]" min="0" step="0.01" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control prize-description" name="prizes[__INDEX__][description]" placeholder="e.g., Gift Card">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-tier">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <a href="{{ route('admin.competitions.index') }}" class="btn btn-label-secondary me-2">
                            <i class='bx bx-x me-1'></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i> Save Competition
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('vendor/libs/quill/katex.js') }}"></script>
<script src="{{ asset('vendor/libs/quill/quill.js') }}"></script>
<script src="{{ asset('vendor/libs/tagify/tagify.js') }}"></script>

<script>
    // Initialize date/time pickers
    document.addEventListener('DOMContentLoaded', function() {
        // Format date for input[type=datetime-local]
        function formatForDateTimeLocal(date) {
            const pad = num => num.toString().padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        // Flatpickr initialization for datetime inputs
        if (document.querySelector('.flatpickr-datetime')) {
            const registrationStartInput = document.getElementById('registration_start');
            const registrationEndInput = document.getElementById('registration_end');
            const competitionStartInput = document.getElementById('competition_start');
            const competitionEndInput = document.getElementById('competition_end');

            // Function to validate competition start date
            function validateCompetitionStart() {
                if (registrationEndInput.value && competitionStartInput.value) {
                    const regEnd = new Date(registrationEndInput.value);
                    const compStart = new Date(competitionStartInput.value);
                    
                    if (compStart <= regEnd) {
                        competitionStartInput.setCustomValidity('The competition start must be after the registration end.');
                        competitionStartInput.classList.add('is-invalid');
                        return false;
                    } else {
                        competitionStartInput.setCustomValidity('');
                        competitionStartInput.classList.remove('is-invalid');
                        return true;
                    }
                }
                return true;
            }

            // Initialize Flatpickr with proper configuration
            const flatpickrConfig = {
                enableTime: true,
                dateFormat: 'Y-m-d\TH:i',
                altInput: true,
                altFormat: 'F j, Y H:i',
                time_24hr: true,
                minDate: 'today',
                defaultDate: new Date(),
                onChange: function(selectedDates, dateStr, instance) {
                    // Update the input value to match the required format
                    instance._input.value = formatForDateTimeLocal(selectedDates[0]);
                    
                    // Run validation when any date changes
                    validateCompetitionStart();
                    
                    // Update min/max dates for related fields
                    if (instance.element === registrationStartInput) {
                        registrationEndFlatpickr.set('minDate', selectedDates[0]);
                    } else if (instance.element === registrationEndInput) {
                        competitionStartFlatpickr.set('minDate', selectedDates[0]);
                        validateCompetitionStart();
                    } else if (instance.element === competitionStartInput) {
                        competitionEndFlatpickr.set('minDate', selectedDates[0]);
                        validateCompetitionStart();
                    } else if (instance.element === competitionEndInput) {
                        // No need to update anything after end date
                    }
                }
            };

            // Initialize all date pickers
            const registrationStartFlatpickr = flatpickr(registrationStartInput, {
                ...flatpickrConfig,
                onChange: function(selectedDates, dateStr, instance) {
                    flatpickrConfig.onChange(selectedDates, dateStr, instance);
                    registrationEndFlatpickr.set('minDate', selectedDates[0]);
                }
            });

            const registrationEndFlatpickr = flatpickr(registrationEndInput, {
                ...flatpickrConfig,
                onChange: function(selectedDates, dateStr, instance) {
                    flatpickrConfig.onChange(selectedDates, dateStr, instance);
                    competitionStartFlatpickr.set('minDate', selectedDates[0]);
                    validateCompetitionStart();
                }
            });

            const competitionStartFlatpickr = flatpickr(competitionStartInput, {
                ...flatpickrConfig,
                onChange: function(selectedDates, dateStr, instance) {
                    flatpickrConfig.onChange(selectedDates, dateStr, instance);
                    competitionEndFlatpickr.set('minDate', selectedDates[0]);
                    validateCompetitionStart();
                }
            });

            const competitionEndFlatpickr = flatpickr(competitionEndInput, flatpickrConfig);

            // Add event listeners for manual input
            [registrationStartInput, registrationEndInput, competitionStartInput, competitionEndInput].forEach(input => {
                input.addEventListener('change', validateCompetitionStart);
            });
        }
        
        // Initialize Quill editor for description
        const descriptionEditor = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter competition description...',
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
        

        // Initialize Tagify for tags
        const input = document.querySelector('input[name="tags"]');
        if (input) {
            new Tagify(input, {
                delimiters: ',| ',
                pattern: /^[a-zA-Z0-9\s-]+$/,
                maxTags: 10,
                dropdown: {
                    maxItems: 20,
                    classname: 'tags-look',
                    enabled: 0,
                    closeOnSelect: false
                }
            });
        }
        
        // Update hidden inputs before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // Update Quill editor content
            document.getElementById('description').value = descriptionEditor.root.innerHTML;
            document.getElementById('rules').value = rulesEditor.root.innerHTML;
            document.getElementById('terms').value = termsEditor.root.innerHTML;
            
            // Format prizes as array of objects
            const prizeItems = document.querySelectorAll('.prize-tier-item');
            const prizes = [];
            
            prizeItems.forEach((item, index) => {
                const name = item.querySelector('.prize-name')?.value || '';
                const value = item.querySelector('.prize-value')?.value || '0';
                const description = item.querySelector('.prize-description')?.value || '';
                
                if (name || value || description) {
                    prizes.push({
                        name: name,
                        value: parseFloat(value) || 0,
                        description: description,
                        rank: index + 1
                    });
                }
            });
            
            // Add a hidden input for prizes if not exists
            let prizesInput = document.querySelector('input[name="prizes"]');
            if (!prizesInput) {
                prizesInput = document.createElement('input');
                prizesInput.type = 'hidden';
                prizesInput.name = 'prizes';
                this.appendChild(prizesInput);
            }
            prizesInput.value = JSON.stringify(prizes);
        });
        
        // Dynamic prize tiers
        const prizeTiersContainer = document.getElementById('prize-tiers-container');
        const addPrizeTierBtn = document.getElementById('add-prize-tier');
        const prizeTierTemplate = document.getElementById('prize-tier-template').innerHTML;
        let prizeTierCount = 1; // Start from 1 because we have one by default
        
        // Add prize tier
        addPrizeTierBtn.addEventListener('click', function() {
            prizeTierCount++;
            const newPrizeTier = prizeTierTemplate.replace(/__INDEX__/g, prizeTierCount - 1);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newPrizeTier.trim();
            const newElement = tempDiv.firstChild;
            
            // Update the rank badge
            const rankBadge = newElement.querySelector('.rank-badge');
            if (rankBadge) {
                rankBadge.textContent = prizeTierCount;
            }
            
            prizeTiersContainer.appendChild(newElement);
            updateRemoveButtons();
        });
        
        // Remove prize tier
        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-tier');
            removeButtons.forEach((button, index) => {
                // Enable all remove buttons except the first one
                if (index === 0) {
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    // Remove any existing event listeners
                    button.replaceWith(button.cloneNode(true));
                    
                    // Add new event listener
                    const newButton = button.parentNode.querySelector('.remove-tier');
                    newButton.addEventListener('click', function() {
                        this.closest('.prize-tier-item').remove();
                        updateRanks();
                    });
                }
            });
        }
        
        // Update rank numbers when tiers are removed
        function updateRanks() {
            const prizeTierItems = document.querySelectorAll('.prize-tier-item');
            prizeTierItems.forEach((item, index) => {
                const rankBadge = item.querySelector('.rank-badge');
                if (rankBadge) {
                    rankBadge.textContent = index + 1;
                }
                
                // Update the name attributes to maintain proper array indexing
                const inputs = item.querySelectorAll('input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                    }
                });
            });
            
            prizeTierCount = prizeTierItems.length;
            updateRemoveButtons();
        }
        
        // Initialize remove buttons
        updateRemoveButtons();
    });
    
    // Enhanced image preview with validation
    function setupImageUpload(inputId, previewId, removeBtnId, dimensionsId, sizeId, maxSizeMB = 5, minWidth = 100, minHeight = 100, aspectRatio = 16/5) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const removeBtn = document.getElementById(removeBtnId);
        const dimensionsEl = document.getElementById(dimensionsId);
        const sizeEl = document.getElementById(sizeId);
        const container = input.closest('.image-upload-container');
        const placeholder = container.querySelector('.preview-placeholder');
        const feedback = container.querySelector('.invalid-feedback');
        const infoContainer = container.querySelector('.image-info');

        // Format file size
        const formatFileSize = (bytes) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        };

        // Validate image
        const validateImage = (file) => {
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxSize = maxSizeMB * 1024 * 1024; // Convert MB to bytes
            
            if (!validTypes.includes(file.type)) {
                return 'Only JPG, PNG, and WebP images are allowed.';
            }
            
            if (file.size > maxSize) {
                return `Image size must be less than ${maxSizeMB}MB.`;
            }
            
            return null; // No error
        };

        // Handle file selection
        const handleFileSelect = (file) => {
            const error = validateImage(file);
            if (error) {
                input.value = '';
                feedback.textContent = error;
                container.classList.add('was-validated');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Check dimensions if needed
                    const width = this.width;
                    const height = this.height;
                    const ratio = width / height;
                    
                    dimensionsEl.textContent = `${width}×${height}px`;
                    sizeEl.textContent = formatFileSize(file.size);
                    
                    // Show warning if dimensions don't match recommended aspect ratio
                    if (Math.abs(ratio - aspectRatio) > 0.1) {
                        feedback.textContent = `Recommended aspect ratio is 16:5 (${Math.round(aspectRatio * 100) / 100}:1). Current: ${ratio.toFixed(2)}:1`;
                        feedback.classList.add('text-warning');
                        feedback.classList.remove('d-none');
                    } else {
                        feedback.classList.add('d-none');
                    }
                    
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    placeholder.classList.add('d-none');
                    removeBtn.classList.remove('d-none');
                    infoContainer.classList.remove('d-none');
                    container.classList.remove('was-validated');
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        };

        // Handle click on placeholder
        placeholder?.addEventListener('click', () => input.click());
        
        // Handle file input change
        input.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        // Handle remove button click
        removeBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            input.value = '';
            preview.src = '#';
            preview.classList.add('d-none');
            placeholder.classList.remove('d-none');
            removeBtn.classList.add('d-none');
            infoContainer.classList.add('d-none');
            feedback.classList.add('d-none');
            container.classList.remove('was-validated');
        });
        
        // Handle drag and drop
        const preventDefaults = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container?.addEventListener(eventName, preventDefaults, false);
        });
        
        const highlight = () => container?.classList.add('border-primary');
        const unhighlight = () => container?.classList.remove('border-primary');
        
        ['dragenter', 'dragover'].forEach(eventName => {
            container?.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            container?.addEventListener(eventName, unhighlight, false);
        });
        
        container?.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            if (file && file.type.startsWith('image/')) {
                input.files = dt.files;
                handleFileSelect(file);
            }
        });
    }
    
    // Initialize the image upload for banner
    document.addEventListener('DOMContentLoaded', function() {
        setupImageUpload(
            'banner_image', 
            'banner-preview', 
            'remove-banner', 
            'banner-dimensions', 
            'banner-size',
            5, // Max 5MB
            800, // Min width
            250, // Min height
            16/5 // Aspect ratio (16:5)
        );
        
        // For thumbnail image (if needed)
        setupImageUpload(
            'image',
            'image-preview',
            'remove-image',
            'image-dimensions',
            'image-size',
            2, // Max 2MB
            400, // Min width
            225, // Min height
            16/9 // Aspect ratio (16:9)
        );
    });
    
    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onloadend = function() {
            preview.src = reader.result;
            preview.classList.remove('d-none');
        }
        
        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    }
    
    // Toggle team size fields based on competition type
    document.addEventListener('DOMContentLoaded', function() {
        const competitionType = document.getElementById('type');
        const teamSizeFields = document.querySelectorAll('.team-size-field');
        
        function toggleTeamFields() {
            const isTeamType = competitionType.value === 'team' || competitionType.value === 'tournament';
            teamSizeFields.forEach(field => {
                field.style.display = isTeamType ? 'block' : 'none';
            });
        }
        
        // Initialize on page load
        toggleTeamFields();
        
        // Update on type change
        if (competitionType) {
            competitionType.addEventListener('change', toggleTeamFields);
        }
    });
</script>
@endpush
