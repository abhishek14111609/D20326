@extends('admin.layouts.app')

@section('title', 'Edit Competition')

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
</style>
@endpush

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
        display: {{ $competition->image ? 'block' : 'none' }};
    }
    .banner-preview {
        max-width: 100%;
        max-height: 200px;
        margin-top: 10px;
        display: {{ $competition->banner_image ? 'block' : 'none' }};
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
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Competitions /</span> Edit Competition
    </h4>
    <form action="{{ route('admin.competitions.update', $competition) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Competition: {{ $competition->title }}</h5>
                        <div>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-label-secondary me-2">
                                <i class='bx bx-arrow-back me-1'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save me-1'></i> Update Competition
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
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" 
                                                   value="{{ old('title', $competition->title) }}" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        

                                        <div class="mb-3">
											<label class="form-label">Description <span class="text-danger">*</span></label>

											<textarea 
												name="description" 
												class="form-control" 
												rows="4"
											>{{ old('description', $competition->description) }}</textarea>

											@error('description')
												<div class="text-danger small">{{ $message }}</div>
											@enderror
										</div>


                                        <div class="mb-3">
                                            <label class="form-label" for="tags">Tags</label>
                                            @php
                                                $tags = is_array($competition->tags) ? $competition->tags : (is_string($competition->tags) ? json_decode($competition->tags, true) : []);
                                                $tags = is_array($tags) ? $tags : [];
                                                $tagsString = implode(',', array_filter($tags, 'is_string'));
                                            @endphp
                                            <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                                   id="tags" name="tags" 
                                                   value="{{ old('tags', $tagsString) }}">
                                            <small class="text-muted">Separate tags with commas</small>
                                            @error('tags')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">Competition Image</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <img src="{{ $competition->image ? 'https://duos.webvibeinfotech.in/storage/app/public/' . $competition->image : '#' }}" id="image-preview" class="img-fluid rounded mb-2 {{ $competition->image ? '' : 'd-none' }}" style="max-height: 200px;">
                                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                                           id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                                    <small class="text-muted d-block mt-2">Recommended size: 800x450px (16:9 aspect ratio)</small>
                                                    @error('image')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Banner Image</h5>
                                            </div>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <img src="{{ $competition->banner_image ? 'https://duos.webvibeinfotech.in/storage/app/public/' . $competition->banner_image : '#' }}" id="banner-preview" class="img-fluid rounded mb-2 {{ $competition->banner_image ? '' : 'd-none' }}" style="max-height: 200px;">
                                                    <input type="file" class="form-control @error('banner_image') is-invalid @enderror" 
                                                           id="banner_image" name="banner_image" accept="image/*" onchange="previewBanner(this)">
                                                    <small class="text-muted d-block mt-2">Recommended size: 1200x400px</small>
                                                    @error('banner_image')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-4">
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
                                                   value="{{ old('registration_start', $competition->registration_start ? $competition->registration_start->format('Y-m-d\TH:i') : '') }}" required>
                                            @error('registration_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="registration_end">Registration End <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('registration_end') is-invalid @enderror" 
                                                   id="registration_end" name="registration_end" 
                                                   value="{{ old('registration_end', $competition->registration_end ? $competition->registration_end->format('Y-m-d\TH:i') : '') }}" required>
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
                                                   value="{{ old('competition_start', $competition->competition_start ? $competition->competition_start->format('Y-m-d\TH:i') : '') }}" required>
                                            @error('competition_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="competition_end">Competition End <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control flatpickr-datetime @error('competition_end') is-invalid @enderror" 
                                                   id="competition_end" name="competition_end" 
                                                   value="{{ old('competition_end', $competition->competition_end ? $competition->competition_end->format('Y-m-d\TH:i') : '') }}" required>
                                            @error('competition_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                                                @foreach(timezone_identifiers_list() as $timezone)
                                                    <option value="{{ $timezone }}" {{ old('timezone', $competition->timezone) == $timezone ? 'selected' : '' }}>
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
                                                   id="max_participants" name="max_participants" 
                                                   value="{{ old('max_participants', $competition->max_participants) }}" min="1">
                                            @error('max_participants')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="min_participants">Min Participants <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('min_participants') is-invalid @enderror" 
                                                   id="min_participants" name="min_participants" 
                                                   value="{{ old('min_participants', $competition->min_participants ?? 1) }}" 
                                                   min="1" required>
                                            <small class="text-muted">Minimum number of participants required for the competition to proceed</small>
                                            @error('min_participants')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
											<div class="col-md-6 mb-3">
												<label class="form-label" for="type">Competition Type <span class="text-danger">*</span></label>

												<select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
													<option value="" disabled >Select type</option>

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

												<select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
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
                                            <label class="form-label" for="entry_fee">Entry Fee</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control @error('entry_fee') is-invalid @enderror" 
                                                       id="entry_fee" name="entry_fee" 
                                                       value="{{ old('entry_fee', $competition->entry_fee) }}" min="0" step="0.01">
                                                @error('entry_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="currency">Currency <span class="text-danger">*</span></label>
                                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                                <option value="USD" {{ old('currency', $competition->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                                <option value="EUR" {{ old('currency', $competition->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                                <option value="GBP" {{ old('currency', $competition->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                                <option value="JPY" {{ old('currency', $competition->currency) == 'JPY' ? 'selected' : '' }}>JPY (¥)</option>
                                                <option value="AUD" {{ old('currency', $competition->currency) == 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                                <option value="CAD" {{ old('currency', $competition->currency) == 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                            </select>
                                            @error('currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                                   value="1" {{ old('is_featured', $competition->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured Competition</label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_private" name="is_private" 
                                                   value="1" {{ old('is_private', $competition->is_private) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_private">Private Competition</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="rules">Rules</label>
                                            <div id="rules-editor" style="height: 200px;">
                                                {!! old('rules', $competition->rules) !!}
                                            </div>
                                            <input type="hidden" name="rules" id="rules">
                                            @error('rules')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="prize_distribution">Prize Distribution</label>
                                            <div id="prize-distribution-editor" style="height: 200px;">
                                                {!! old('prize_distribution', $competition->prize_distribution) !!}
                                            </div>
                                            <input type="hidden" name="prize_distribution" id="prize-distribution">
                                            @error('prize_distribution')
                                                <div class="text-danger small">{{ $message }}</div>
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
                                    @php
                                        $prizes = old('prizes') ? json_decode(old('prizes'), true) : ($competition->prizes ? json_decode($competition->prizes, true) : []);
                                    @endphp

                                    @if(count($prizes) > 0)
                                        @foreach($prizes as $index => $prize)
                                            <div class="prize-tier-item card mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-1 d-flex align-items-center">
                                                            <span class="badge bg-primary rank-badge">{{ $index + 1 }}</span>
                                                            <input type="hidden" name="prizes[{{ $index }}][rank]" value="{{ $index + 1 }}">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label">Prize Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="prizes[{{ $index }}][name]" 
                                                                   value="{{ $prize['name'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Prize Value <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">{{ $competition->currency ?? 'USD' }}</span>
                                                                <input type="number" class="form-control" name="prizes[{{ $index }}][value]" 
                                                                       value="{{ $prize['value'] ?? '' }}" min="0" step="0.01" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button" class="btn btn-danger btn-sm remove-prize-tier" data-index="{{ $index }}">
                                                                <i class='bx bx-trash me-1'></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No prize tiers added yet. Click the button below to add one.</p>
                                    @endif
                                </div>

                                <button type="button" class="btn btn-primary mt-3" id="add-prize-tier">
                                    <i class='bx bx-plus me-1'></i> Add Prize Tier
                                </button>

                                <template id="prize-tier-template">
                                    <div class="prize-tier-item card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-1 d-flex align-items-center">
                                                    <span class="badge bg-primary rank-badge">1</span>
                                                    <input type="hidden" name="prizes[__INDEX__][rank]" value="__RANK__">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Prize Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="prizes[__INDEX__][name]" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Prize Value <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">{{ $competition->currency ?? 'USD' }}</span>
                                                        <input type="number" class="form-control" name="prizes[__INDEX__][value]" min="0" step="0.01" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-prize-tier">
                                                        <i class='bx bx-trash me-1'></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <input type="hidden" name="prizes" id="prizes-json" value="{{ old('prizes', $competition->prizes) }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <a href="{{ route('admin.competitions.index') }}" class="btn btn-label-secondary me-2">
                            <i class='bx bx-x me-1'></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i> Update Competition
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
    // Format date for input[type=datetime-local]
    function formatForDateTimeLocal(date) {
        const pad = num => num.toString().padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date/time pickers
        if (document.querySelector('.flatpickr-datetime')) {
            const registrationStartInput = document.getElementById('registration_start');
            const registrationEndInput = document.getElementById('registration_end');
            const competitionStartInput = document.getElementById('competition_start');
            const competitionEndInput = document.getElementById('competition_end');

            const dateTimeOptions = {
                enableTime: true,
                dateFormat: 'Y-m-d\\TH:i',
                time_24hr: true,
                minDate: 'today',
                allowInput: true
            };
        const registrationStart = document.getElementById('registration_start');
        const registrationEnd = document.getElementById('registration_end');
        const competitionStart = document.getElementById('competition_start');
        const competitionEnd = document.getElementById('competition_end');

        if (registrationStart) {
            flatpickr(registrationStart, {
                ...dateTimeOptions,
                onChange: function(selectedDates, dateStr) {
                    if (registrationEnd) {
                        registrationEnd._flatpickr.set('minDate', dateStr);
                    }
                    if (competitionStart) {
                        competitionStart._flatpickr.set('minDate', dateStr);
                    }
                }
            });

        if (registrationEnd) {
            flatpickr(registrationEnd, {
                ...dateTimeOptions,
                minDate: registrationStart ? registrationStart.value : 'today',
                onChange: function(selectedDates, dateStr) {
                    if (competitionStart) {
                        competitionStart._flatpickr.set('minDate', dateStr);
                    }
                }
            });
        }

        // Image preview
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                previewImage(e.target);
            });
        }

        // Banner preview
        const bannerInput = document.getElementById('banner_image');
        if (bannerInput) {
            bannerInput.addEventListener('change', function(e) {
                previewBanner(e.target);
            });
        }

        // Initialize Quill editor for description
        const descriptionEditor = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter competition description...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],
                    ['link', 'image', 'video']
                ]
            }
        });

        // Set initial content for description
        const descriptionContent = document.getElementById('description-editor').innerHTML.trim();
        if (descriptionContent) {
            descriptionEditor.clipboard.dangerouslyPasteHTML(descriptionContent);
        }

        // Initialize Quill editor for rules
        const rulesEditor = new Quill('#rules-editor', {
            theme: 'snow',
            placeholder: 'Enter competition rules...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['clean']
                ]
            }
        });

        // Set initial content for rules
        const rulesContent = document.getElementById('rules-editor').innerHTML.trim();
        if (rulesContent) {
            rulesEditor.clipboard.dangerouslyPasteHTML(rulesContent);
        }

        // Initialize Quill editor for prize distribution
        const prizeDistributionEditor = new Quill('#prize-distribution-editor', {
            theme: 'snow',
            placeholder: 'Enter prize distribution details...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Set initial content for prize distribution
        const prizeDistributionContent = document.getElementById('prize-distribution-editor').innerHTML.trim();
        if (prizeDistributionContent) {
            prizeDistributionEditor.clipboard.dangerouslyPasteHTML(prizeDistributionContent);
        }

            // Set min dates for end date fields
            if (registrationStartInput && registrationEndInput) {
                registrationStartInput.addEventListener('change', function() {
                    registrationEndInput.min = this.value;
                    if (new Date(registrationEndInput.value) < new Date(this.value)) {
                        registrationEndInput.value = this.value;
                    }
                });
            }

            if (competitionStartInput && competitionEndInput) {
                competitionStartInput.addEventListener('change', function() {
                    competitionEndInput.min = this.value;
                    if (new Date(competitionEndInput.value) < new Date(this.value)) {
                        competitionEndInput.value = this.value;
                    }
                });
            }
        }

        // Initialize Quill editor
        const descriptionEditor = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter competition description...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],
                    ['link', 'image', 'video']
                ]
            }
        });

        // Update hidden input on form submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                document.getElementById('description').value = descriptionEditor.root.innerHTML;
            });
        }

        // Initialize tagify for tags
        const tagsInput = document.getElementById('tags');
        if (tagsInput) {
            const tagify = new Tagify(tagsInput, {
                delimiters: ',| ',
                maxTags: 10,
                dropdown: {
                    maxItems: 20,
                    classname: 'tagify-dropdown',
                    enabled: 1,
                    closeOnSelect: false
                }
            });
        }

        // Handle form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Update hidden inputs with Quill content
                const descriptionInput = document.createElement('input');
                descriptionInput.type = 'hidden';
                descriptionInput.name = 'description';
                descriptionInput.value = descriptionEditor.root.innerHTML;
                form.appendChild(descriptionInput);

                const rulesInput = document.createElement('input');
                rulesInput.type = 'hidden';
                rulesInput.name = 'rules';
                rulesInput.value = rulesEditor.root.innerHTML;
                form.appendChild(rulesInput);

                const prizeDistributionInput = document.createElement('input');
                prizeDistributionInput.type = 'hidden';
                prizeDistributionInput.name = 'prize_distribution';
                prizeDistributionInput.value = prizeDistributionEditor.root.innerHTML;
                form.appendChild(prizeDistributionInput);

                // Proceed with form submission
                return true;
            });
        }

        // Image preview
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('image-preview');
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Banner preview
        const bannerInput = document.getElementById('banner_image');
        if (bannerInput) {
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('banner-preview');
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush
