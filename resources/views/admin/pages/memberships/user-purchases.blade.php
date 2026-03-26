@extends('admin.layouts.app')

@section('title', 'User Membership Purchases')

@push('styles')
<!-- daterange picker -->
<link rel="stylesheet" href="{{ asset('adminlte/plugins/daterangepicker/daterangepicker.css') }}">
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .badge-active {
        background-color: #28a745;
    }
    .badge-expired {
        background-color: #6c757d;
    }
    .badge-cancelled {
        background-color: #dc3545;
    }
    .plan-badge {
        font-size: 0.8em;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    .action-buttons .btn {
        margin-right: 5px;
    }
    .action-buttons .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Membership Purchases</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.memberships.user-purchases') }}" class="btn btn-outline-secondary">
                    <i class='bx bx-reset me-1'></i> Reset
                </a>
            </div>
        </div>
        
        <!-- Search & Filter Form -->
        <div class="card-body">
            <form action="{{ route('admin.memberships.user-purchases') }}" method="GET" id="search-form" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-search'></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search users..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="plan_id" class="form-select" onchange="document.getElementById('search-form').submit()">
                            <option value="">All Plans</option>
                            @foreach($plans as $id => $name)
                                <option value="{{ $id }}" {{ request('plan_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="document.getElementById('search-form').submit()">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Memberships</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired Memberships</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class='bx bx-filter-alt me-1'></i> Filter
                        </button>
                    </div>
                </div>
            </form>
            
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
                            <th>User</th>
                            <th>Email</th>
                            <th>Memberships</th>
                            <th>Payment Method</th>
                            <th>Last Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if($user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle">
                                        @else
                                            <div class="avatar-initial bg-label-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ '@' . $user->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-label-success mb-1" style="width: fit-content;">
                                        <i class='bx bx-check-circle me-1'></i>
                                        {{ $user->active_memberships }} Active
                                    </span>
                                    <span class="badge bg-label-info" style="width: fit-content;">
                                        <i class='bx bx-layer me-1'></i>
                                        {{ $user->memberships_count ?? 0 }} Total
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($user->memberships->isNotEmpty())
                                    <span class="badge bg-label-primary">
                                        {{ $user->memberships->first()->payment_method ?? 'N/A' }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span class="d-block">{{ $user->last_login_at->format('MM DD, YYYY') }}</span>
                                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="View">
                                        <i class='bx bx-show'></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Edit User">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Delete User">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.user-memberships.index', ['user_id' => $user->id]) }}" 
                                       class="btn btn-sm btn-outline-secondary"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="View Memberships">
                                        <i class='bx bx-list-ul'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-user-x bx-lg text-muted mb-3'></i>
                                    <h5 class="mb-1">No users found with memberships</h5>
                                    <p class="text-muted mb-0">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- InputMask -->
<script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
<!-- date-range-picker -->
<script src="{{ asset('adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize date range picker
        $('input[name="date_range"]').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear',
                applyLabel: 'Apply',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            startDate: '{{ request('date_from') ?: "" }}',
            endDate: '{{ request('date_to') ?: "" }}',
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        
        // Set initial values if they exist
        @if(request('date_from') && request('date_to'))
            $('input[name="date_range"]').val('{{ request('date_from') }} - {{ request('date_to') }}');
            $('input[name="date_from"]').val('{{ request('date_from') }}');
            $('input[name="date_to"]').val('{{ request('date_to') }}');
        @endif
        
        // Update hidden inputs when date range is selected
        $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('input[name="date_from"]').val(picker.startDate.format('YYYY-MM-DD'));
            $('input[name="date_to"]').val(picker.endDate.format('YYYY-MM-DD'));
            $('#search-form').submit();
        });
        
        // Clear date range
        $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('input[name="date_from"]').val('');
            $('input[name="date_to"]').val('');
            $('#search-form').submit();
        });
    });
</script>
@endpush
