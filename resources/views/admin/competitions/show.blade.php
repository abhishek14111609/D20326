@extends('admin.layouts.app')

@section('title', 'View Competition: ' . $competition->title)

@push('styles')
<style>
    .competition-image { 
        max-width: 100%; 
        border-radius: 8px; 
        margin-bottom: 1rem;
    }
    .detail-label { 
        font-weight: 600; 
        color: #566a7f; 
    }
    .detail-value { 
        margin-bottom: 1.25rem;
    }
    .participant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Competition Details</h5>
                    <div>
                        <a href="{{ route('admin.competitions.index') }}" class="btn btn-label-secondary me-2">
                            <i class='bx bx-arrow-back me-1'></i> Back
                        </a>
                        <a href="{{ route('admin.competitions.edit', $competition) }}" class="btn btn-primary">
                            <i class='bx bx-edit me-1'></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($competition->image)
                                <img src="{{ ('https://duos.webvibeinfotech.in/storage/app/public/' . $competition->image) }}" 
                                     alt="{{ $competition->title }}" 
                                     class="img-fluid competition-image">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 200px; background-color: #f5f5f5;">
                                    <span class="text-muted">No Image</span>
                                </div>
                            @endif
                            
                            @if($competition->banner_image)
                                <img src="{{ ('https://duos.webvibeinfotech.in/storage/app/public/' . $competition->banner_image) }}" 
                                     alt="{{ $competition->title }} Banner" 
                                     class="img-fluid mt-3 rounded">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h3>{{ $competition->title }}</h3>
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge bg-label-{{ $competition->status === 'active' ? 'success' : ($competition->status === 'upcoming' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($competition->status) }}
                                    </span>
                                    <span class="badge bg-label-primary">
                                        {{ ucfirst($competition->type) }}
                                    </span>
                                    @if($competition->is_featured)
                                        <span class="badge bg-label-warning">Featured</span>
                                    @endif
                                </div>
                                
                                <p class="mb-4">
                                    @if(is_array($competition->description) || is_object($competition->description))
                                        {{ json_encode($competition->description) }}
                                    @else
                                        {{ $competition->description ?? 'No description provided' }}
                                    @endif
                                </p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label">Registration Period</div>
                                        <div class="detail-value">
                                            @if($competition->registration_start && $competition->registration_end)
                                                {{ $competition->registration_start->format('M d, Y H:i A') }} - 
                                                {{ $competition->registration_end->format('M d, Y H:i A') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </div>
                                        
                                        <div class="detail-label">Competition Period</div>
                                        <div class="detail-value">
                                            @if($competition->competition_start && $competition->competition_end)
                                                {{ $competition->competition_start->format('M d, Y H:i') }} - 
                                                {{ $competition->competition_end->format('M d, Y H:i') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </div>
                                        
                                        <div class="detail-label">Entry Fee</div>
                                        <div class="detail-value">
                                            {{ $competition->entry_fee > 0 ? $competition->currency . ' ' . number_format($competition->entry_fee, 2) : 'Free' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label">Participants</div>
                                        <div class="detail-value">
                                            {{ $competition->participants_count ?? 0 }} 
                                            @if($competition->max_participants)
                                                / {{ $competition->max_participants }}
                                            @endif
                                        </div>
                                        
                                        @if($competition->min_team_size || $competition->max_team_size)
                                            <div class="detail-label">Team Size</div>
                                            <div class="detail-value">
                                                {{ $competition->min_team_size ?? '1' }} - 
                                                {{ $competition->max_team_size ?? 'Unlimited' }} members
                                            </div>
                                        @endif
                                        
                                        <div class="detail-label">Created</div>
                                        <div class="detail-value">
                                            {{ $competition->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                
                                @if($competition->rules)
                                    <div class="mt-4">
                                        <h5>Rules</h5>
                                        <div class="card bg-light p-3">
                                            {!! nl2br(e($competition->rules)) !!}
                                        </div>
                                    </div>
                                @endif
                                
                                @if($competition->prizes)
                                    <div class="mt-4">
                                        <h5>Prizes</h5>
                                        <div class="card bg-light p-3">
                                            @if(is_array($competition->prizes) && count($competition->prizes) > 0)
                                                <ul class="mb-0">
                                                    @foreach($competition->prizes as $prize)
                                                        <li>{{ is_array($prize) ? json_encode($prize) : $prize }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {{ is_string($competition->prizes) ? $competition->prizes : 'No prizes specified' }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <h5 class="mb-3">Participants ({{ $competition->participants->count() }})</h5>
                        @if($competition->participants->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Status</th>
                                            <th>Joined At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($competition->participants as $participant)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $participant->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/' . $participant->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                                             alt="{{ $participant->name }}" 
                                                             class="participant-avatar me-2">
                                                        <div>
                                                            <div class="fw-semibold">{{ $participant->name }}</div>
                                                            <small class="text-muted">{{ $participant->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-{{ $participant->status === 'approved' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($participant->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $participant->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <a href="{{ route('admin.users.show', $participant) }}" 
                                                       class="btn btn-sm btn-icon btn-label-primary"
                                                       data-bs-toggle="tooltip"
                                                       title="View User">
                                                        <i class="bx bx-user"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                No participants have joined this competition yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
