@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                <i class='bx bx-user me-1'></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.users.payments.index', $user) }}" class="nav-link" id="payments-tab" role="tab" aria-controls="payments" aria-selected="false">
                <i class='bx bx-credit-card me-1'></i> Payments
                @if($user->payments_count > 0)
                    <span class="badge bg-label-primary rounded-pill ms-1">{{ $user->payments_count }}</span>
                @endif
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-4 col-xxl-3 mb-4">
                    <!-- User Profile Card -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="mb-3">
                               <img src="{{ $user->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $user->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                    alt="{{ $user->name }}" 
                                    class="rounded-circle" 
                                    style="width: 40px; height: 40px; object-fit: cover;"> 
                                <h4 class="mb-1">{{ $user->name }}</h4>
                                <p class="text-muted">{{ $user->email }}</p>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class='bx bx-trash me-1'></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body border-top">
                            <h5 class="card-title mb-3">User Information</h5>
                            <div class="info-container">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <span class="fw-medium me-2">Joined:</span>
                                        <span>{{ $user->created_at->format('M d, Y') }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="fw-medium me-2">Last Login:</span>
                                        <span>{{ $recentActivities['last_login'] }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="fw-medium me-2">Total Matches:</span>
                                        <span>{{ $recentActivities['total_matches'] }}</span>
                                    </li>
                                    @if($subscription)
                                    <li class="mb-2">
                                        <span class="fw-medium me-2">Membership:</span>
                                        <span class="badge bg-label-info">{{ $subscription->plan->name ?? 'N/A' }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="fw-medium me-2">Expires:</span>
                                        <span>{{ $subscription->ends_at?->format('M d, Y') ?? 'N/A' }}</span>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Stats Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Activity Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-primary rounded">
                                            <i class='bx bx-chat'></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">{{ $user->sent_messages_count + $user->received_messages_count }}</h6>
                                        </div>
                                        <small class="text-muted">Total Messages</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-success rounded">
                                            <i class='bx bx-like'></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">{{ $user->sent_swipes_count + $user->received_swipes_count }}</h6>
                                        </div>
                                        <small class="text-muted">Total Swipes</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-info rounded">
                                            <i class='bx bx-user-check'></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">{{ $recentActivities['total_matches'] }}</h6>
                                        </div>
                                        <small class="text-muted">Matches</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-warning rounded">
                                            <i class='bx bx-star'></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">{{ $user->memberships_count }}</h6>
                                        </div>
                                        <small class="text-muted">Memberships</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-8 col-xxl-9">

                    <!-- Recent Activity Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <ul class="timeline mt-3 mb-0">
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-primary"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header">
                                            <h6 class="mb-0">Account Created</h6>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0">User registered on the platform</p>
                                    </div>
                                </li>
                                @if($user->last_seen)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header">
                                            <h6 class="mb-0">Last Active</h6>
                                            <small class="text-muted">{{ $user->last_seen->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0">User was last seen online</p>
                                    </div>
                                </li>
                                @endif
                                @if($subscription)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-info"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header">
                                            <h6 class="mb-0">Membership</h6>
                                            <small class="text-muted">{{ $subscription->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0">Subscribed to {{ $subscription->plan->name ?? 'a plan' }}</p>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Additional content can be added here -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Details</h5>
                        </div>
                        <div class="card-body">
                            <p>Additional user details and information can be displayed here.</p>
                            <!-- Add more user details as needed -->
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    padding-left: 0;
    list-style: none;
    position: relative;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
    border-left: 1px solid #d9dee3;
}
.timeline-item:last-child {
    border-left-color: transparent;
}
.timeline-point {
    position: absolute;
    left: -8px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    z-index: 2;
}
.timeline-event {
    position: relative;
    padding-left: 1.5rem;
}
</style>
@endpush
