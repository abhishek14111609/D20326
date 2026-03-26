@extends('admin.layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Leaderboard</h5>
            <div class="d-flex">
                <div class="input-group input-group-merge me-3" style="width: 250px;">
                    <span class="input-group-text"><i class='bx bx-filter-alt'></i></span>
                    <select class="form-select" id="perPageSelect">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search users..." value="{{ request('search') }}">
                </div>
            </div>
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
                            <th>User</th>
                            <th>Total Challenges</th>
                            <th>Total Competitions</th>
                            <th>Total Wins</th>
                            <th>Total Losses</th>
                            <th>Win Rate</th>
                            <th>Points</th>
                            <th>Member Since</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $user->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $user->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                             alt="Avatar" 
                                             class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->total_challenges ?? '0' }}</td>
                            <td>{{ $user->total_competitions ?? '0' }}</td>
                            <td>{{ $user->total_wins ?? '0' }}</td>
                            <td>{{ $user->total_losses ?? '0' }}</td>
                            <td>
                                @php
                                    $totalMatches = ($user->total_wins ?? 0) + ($user->total_losses ?? 0);
                                    $winRate = $totalMatches > 0 ? (($user->total_wins / $totalMatches) * 100) : 0;
                                @endphp
                                <span class="fw-semibold {{ $winRate >= 50 ? 'text-success' : ($winRate > 0 ? 'text-warning' : 'text-muted') }}">
                                    {{ number_format($winRate, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ number_format($user->points) }} pts</span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.users.show', $user) }}"
                                    edit-route="{{ route('admin.users.edit', $user) }}"
                                    delete-route="{{ route('admin.users.destroy', $user) }}"
                                    delete-confirm-message="Are you sure you want to delete this user?"
                                    :show-edit="false"
                                    :show-delete="false"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-user-x bx-lg mb-2 text-muted'></i>
                                    <p class="mb-0">No users found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="card-footer">
                    <x-pagination :paginator="$users" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Per page selector
        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                window.location.href = url.toString();
            });
        }

        // Search functionality with debounce
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(() => {
                    const searchTerm = this.value.trim();
                    const url = new URL(window.location.href);
                    
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    
                    // Reset to first page when searching
                    url.searchParams.set('page', '1');
                    
                    window.location.href = url.toString();
                }, 500);
            });
            
            // Handle Enter key to submit search immediately
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimeout);
                    const searchTerm = this.value.trim();
                    const url = new URL(window.location.href);
                    
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    
                    // Reset to first page when searching
                    url.searchParams.set('page', '1');
                    
                    window.location.href = url.toString();
                }
            });
        }
    });
</script>
@endpush
