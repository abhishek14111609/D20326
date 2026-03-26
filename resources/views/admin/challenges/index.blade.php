@extends('admin.layouts.app')

@section('title', 'Challenges')

@push('styles')
<style>
    .challenge-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }
    .status-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
    }
    .avatar-xs {
        width: 24px;
        height: 24px;
        font-size: 0.8rem;
    }
    .btn-icon {
        padding: 0.35rem 0.5rem;
    }
    .btn-icon i {
        font-size: 1.1em;
    }
    @media (max-width: 768px) {
        .btn-text {
            display: none;
        }
        .btn-icon {
            padding: 0.35rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Challenges</h5>
            <a href="{{ route('admin.challenges.create') }}" class="btn btn-primary">
                <i class='bx bx-plus me-1'></i> Create Challenge
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Participants</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($challenges as $challenge)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($challenge->image)
                                    <img src="{{ ('https://duos.webvibeinfotech.in/storage/app/public/' . $challenge->image) }}" alt="{{ $challenge->title }}" class="challenge-image">
                                @else
                                    <div class="challenge-image bg-light d-flex align-items-center justify-content-center">
                                        <i class='bx bx-medal text-muted' style="font-size: 1.5rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $challenge->title }}</span>
                                    @if($challenge->is_featured)
                                        <span class="badge bg-label-warning mt-1" style="width: fit-content;">Featured</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'daily' => ['label' => 'Daily', 'class' => 'bg-label-info'],
                                        'weekly' => ['label' => 'Weekly', 'class' => 'bg-label-primary'],
                                        'monthly' => ['label' => 'Monthly', 'class' => 'bg-label-success'],
                                        'one_time' => ['label' => 'One Time', 'class' => 'bg-label-secondary']
                                    ];
                                    $type = $typeLabels[$challenge->type] ?? ['label' => ucfirst($challenge->type), 'class' => 'bg-label-secondary'];
                                @endphp
                                <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
                            </td>
                            <td>
                                @php
                                    $statusLabels = [
                                        'draft' => ['label' => 'Draft', 'class' => 'bg-label-secondary'],
                                        'active' => ['label' => 'Active', 'class' => 'bg-label-success'],
                                        'upcoming' => ['label' => 'Upcoming', 'class' => 'bg-label-info'],
                                        'completed' => ['label' => 'Completed', 'class' => 'bg-label-primary'],
                                        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-label-danger']
                                    ];
                                    $status = $statusLabels[$challenge->status] ?? ['label' => ucfirst($challenge->status), 'class' => 'bg-label-secondary'];
                                @endphp
                                <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">{{ $challenge->participants_count ?? 0 }}</span>
                                    @if(($challenge->participants_count ?? 0) > 0)
                                        <div class="avatar-group">
                                            @foreach($challenge->participants->take(3) as $participant)
                                                <div class="avatar avatar-xs">
                                                  <img 
    src="{{ 
        optional($participant->user)->hasMedia('avatars') 
            ? $participant->user->getFirstMediaUrl('avatars', 'thumb') 
            : asset('assets/img/avatars/default-avatar.png') 
    }}"
    alt="Avatar"
    class="rounded-circle"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
    title="{{ optional($participant->user)->name }}"
>

                                                </div>
                                            @endforeach
                                            @if(($challenge->participants_count ?? 0) > 3)
                                                <div class="avatar avatar-xs">
                                                    <div class="avatar-initial rounded-circle bg-label-secondary">
                                                        +{{ ($challenge->participants_count ?? 0) - 3 }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="d-block">{{ $challenge->start_date->format('M d, Y') }}</span>
                                <small class="text-muted">{{ $challenge->start_date->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($challenge->end_date)
                                    <span class="d-block">{{ $challenge->end_date->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $challenge->end_date->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.challenges.show', $challenge) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="View">
                                        <i class='bx bx-show'></i>
                                    </a>
                                    <a href="{{ route('admin.challenges.edit', $challenge) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Edit">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.challenges.destroy', $challenge) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this challenge?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Delete">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </form>
                                   @if($challenge->status === 'active' || $challenge->status === 'upcoming')
                                        <form action="{{ route('admin.challenges.cancel', $challenge) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-warning"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Cancel Challenge"
                                                    onclick="return confirm('Are you sure you want to cancel this challenge?')">
                                                <i class='bx bx-x'></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-trophy bx-lg mb-2 text-muted'></i>
                                    <p class="mb-0">No challenges found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($challenges->hasPages())
                <div class="card-footer">
                    {{ $challenges->links() }}
                </div>
            @endif
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
        
        // Initialize datatables if present
        if (typeof $().DataTable === 'function') {
            $('.datatables-basic').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 25
            });
        }
    });
</script>
@endpush
