@extends('admin.layouts.app')

@section('title', 'Membership Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Membership Management</h5>
            <div class="d-flex">
                <div class="input-group input-group-merge me-3" style="width: 300px;">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search memberships...">
                </div>
                <select class="form-select" id="statusFilter" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="canceled">Canceled</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberships as $membership)
                        @php
                            $isExpired = \Carbon\Carbon::parse($membership->end_date)->isPast();
                            $statusClass = [
                                'active' => 'bg-label-success',
                                'expired' => 'bg-label-secondary',
                                'canceled' => 'bg-label-danger',
                                'pending' => 'bg-label-warning'
                            ][$isExpired ? 'expired' : strtolower($membership->status)];
                        @endphp
                        <tr class="membership-row" 
                            data-status="{{ strtolower($membership->status) }}"
                            data-expired="{{ $isExpired ? 'true' : 'false' }}">
                            <td>#{{ $membership->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $membership->user->hasMedia('avatars') ? $membership->user->getFirstMediaUrl('avatars', 'thumb') : asset('assets/img/avatars/default-avatar.png') }}" 
                                             alt="Avatar" 
                                             class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $membership->user->name }}</h6>
                                        <small class="text-muted">{{ $membership->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $membership->plan->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }}">
                                    @if($isExpired)
                                        Expired
                                    @else
                                        {{ ucfirst($membership->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $membership->start_date->format('M d, Y') }}</td>
                            <td>{{ $membership->end_date->format('M d, Y') }}</td>
                            <td>${{ number_format($membership->amount, 2) }}</td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.memberships.show', $membership) }}"
                                    edit-route="{{ route('admin.memberships.edit', $membership) }}"
                                    delete-route="{{ route('admin.memberships.destroy', $membership) }}"
                                    delete-confirm-message="Are you sure you want to delete this membership?"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No memberships found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $memberships->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .membership-row {
        transition: all 0.3s ease;
    }
    .membership-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.membership-row');
        
        rows.forEach(row => {
            const userInfo = row.querySelector('h6, small').textContent.toLowerCase();
            const planInfo = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            
            if (userInfo.includes(searchTerm) || planInfo.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('.membership-row');
        
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const isExpired = row.getAttribute('data-expired') === 'true';
            
            if (!status || 
                (status === 'active' && rowStatus === 'active' && !isExpired) ||
                (status === 'expired' && isExpired) ||
                (status === rowStatus && !isExpired)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush
