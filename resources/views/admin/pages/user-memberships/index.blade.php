@extends('admin.layouts.app')

@section('title', 'User Memberships')

@push('styles')
<!-- daterange picker -->
<link rel="stylesheet" href="{{ asset('adminlte/plugins/daterangepicker/daterangepicker.css') }}">
<style>
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin-right: 5px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Memberships</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.user-memberships.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Assign Membership
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Search & Filter Form -->
            <div class="mb-4">
                <form method="GET" action="{{ route('admin.user-memberships.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Membership Plan</label>
                        <select name="plan_id" class="form-select">
                            <option value="">All Plans</option>
                            @foreach($plans as $id => $name)
                                <option value="{{ $id }}" {{ request('plan_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control float-right" id="date-range" name="date_range" value="{{ request('date_range') }}" placeholder="Date range">
                        </div>
                        <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Membership Plan</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberships as $membership)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $membership->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="Avatar" class="rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $membership->user->name }}</h6>
                                        <small class="text-muted">{{ $membership->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $membership->plan->name }}</td>
                            <td>{{ $membership->starts_at->format('M d, Y') }}</td>
                            <td>{{ $membership->ends_at->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $statusClass = [
                                        'active' => 'bg-success',
                                        'expired' => 'bg-secondary',
                                        'cancelled' => 'bg-danger',
                                        'pending' => 'bg-warning',
                                    ][$membership->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($membership->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.user-memberships.show', $membership->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="View">
                                        <i class='bx bx-show'></i>
                                    </a>
                                    <a href="{{ route('admin.user-memberships.edit', ['user_membership' => $membership->id]) }}?user_id={{ request('user_id') }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Edit">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.user-memberships.destroy', $membership->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this membership?')">
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-ghost text-muted mb-2' style="font-size: 2rem;"></i>
                                    <p class="mb-0">No membership records found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($memberships->hasPages())
                <div class="card-footer">
                    {{ $memberships->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- InputMask -->
<script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<!-- date-range-picker -->
<script src="{{ asset('adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>

<script>
    $(function () {
        // Date range picker
        $('#date-range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD',
                separator: ' to ',
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            startDate: '{{ request('date_from') ?: "\'now\' - 30 days" }}',
            endDate: '{{ request('date_to') ?: "now" }}',
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
            $('#date-range').val('{{ request('date_from') }} - {{ request('date_to') }}');
        @endif

        // Update the form when dates are selected
        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#date_from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#date_to').val(picker.endDate.format('YYYY-MM-DD'));
            // Optionally submit the form automatically when a date range is selected
            // $('#search-form').submit();
        });

        // Clear the date range
        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#date_from').val('');
            $('#date_to').val('');
            $('#search-form').submit();
        });
    });
</script>
@endpush
