@extends('admin.layouts.app')

@section('title', 'Swipe Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Swipe Activity</h5>
            <div class="d-flex">
                <div class="input-group input-group-merge me-3" style="width: 300px;">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search swipes...">
                </div>
                <select class="form-select" id="typeFilter" style="width: 150px;">
                    <option value="">All Types</option>
                    <option value="like">Likes</option>
                    <option value="super_like">Super Likes</option>
                    <option value="dislike">Dislikes</option>
                </select>
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
                            <th>ID</th>
                            <th>Swiper</th>
                            <th>Swiped</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Matched</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($swipes as $index => $swipe)
                        @php
                            $typeClass = [
                                'like' => 'bg-label-primary',
                                'super_like' => 'bg-label-info',
                                'dislike' => 'bg-label-secondary'
                            ][$swipe->type] ?? 'bg-label-secondary';
                            
                            $typeLabel = [
                                'like' => 'Like',
                                'super_like' => 'Super Like',
                                'dislike' => 'Dislike'
                            ][$swipe->type] ?? 'Unknown';
                        @endphp
                        <tr class="swipe-row" data-type="{{ $swipe->type }}">
                            <td>{{ $swipes->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $swipe->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/' . $swipe->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                             alt="Avatar" 
                                             class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $swipe->swiper->name ?? 'N/A'}}</h6>
                                       <small class="text-muted">{{ '@' . ($swipe->swiper?->username ?? 'N/A') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $swipe->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/' . $swipe->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                             alt="Avatar" 
                                             class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $swipe->swiped->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ '@' . ($swipe->swiped?->username ?? 'N/A') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $typeClass }}">
                                    <i class="bx {{ $swipe->type === 'super_like' ? 'bxs-star' : ($swipe->type === 'like' ? 'bxs-like' : 'bxs-dislike') }} me-1"></i>
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>
                                <span class="d-block">{{ $swipe->created_at->format('M d, Y') }}</span>
                                <small class="text-muted">{{ $swipe->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($swipe->is_matched)
                                    <span class="badge bg-label-success">
                                        <i class='bx bx-check-circle me-1'></i>Matched
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary">
                                        <i class='bx bx-x-circle me-1'></i>No Match
                                    </span>
                                @endif
                            </td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.swipes.show', $swipe) }}"
                                    edit-route="{{ route('admin.swipes.edit', $swipe) }}"
                                    delete-route="{{ route('admin.swipes.destroy', $swipe) }}"
                                    delete-confirm-message="Are you sure you want to delete this swipe?"
                                    :show-edit="false"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-list-ul bx-lg mb-2 text-muted'></i>
                                    <p class="mb-0">No swipe activity found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($swipes->hasPages())
                <div class="card-footer">
                    <x-pagination :paginator="$swipes" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .swipe-row {
        transition: all 0.3s ease;
    }
    
    .swipe-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
    }
    
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .badge i {
        font-size: 0.8em;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.swipe-row');
                
                rows.forEach(row => {
                    const swiperName = row.querySelector('td:nth-child(2) h6')?.textContent?.toLowerCase() || '';
                    const swiperUsername = row.querySelector('td:nth-child(2) small')?.textContent?.toLowerCase() || '';
                    const swipedName = row.querySelector('td:nth-child(3) h6')?.textContent?.toLowerCase() || '';
                    const swipedUsername = row.querySelector('td:nth-child(3) small')?.textContent?.toLowerCase() || '';
                    
                    if (swiperName.includes(searchTerm) || 
                        swiperUsername.includes(searchTerm) || 
                        swipedName.includes(searchTerm) || 
                        swipedUsername.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Type filter functionality
        const typeFilter = document.getElementById('typeFilter');
        if (typeFilter) {
            typeFilter.addEventListener('change', function() {
                const type = this.value;
                const rows = document.querySelectorAll('.swipe-row');
                
                rows.forEach(row => {
                    if (!type || row.getAttribute('data-type') === type) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endpush
