@extends('admin.layouts.app')

@section('title', 'Manage Payments')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payments List</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.payments.export') }}" class="btn btn-outline-secondary">
                    <i class='bx bx-export'></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Filters -->
            <div class="mb-4">
                <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
           
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->transaction_id }}</td>
                            <td>
								
                                <a href="{{ route('admin.users.show', $payment->user_id) }}">
                                    {{ $payment->user->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $payment->status == 'completed' ? 'success' : 
                                    ($payment->status == 'pending' ? 'warning' : 'danger') 
                                }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.payments.show', $payment) }}"
                                    :show-edit="false"
                                    delete-route="{{ route('admin.payments.destroy', $payment) }}"
                                    delete-confirm-message="Are you sure you want to delete this payment record?"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No payments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($payments->hasPages())
                <div class="card-footer">
                    <x-pagination :paginator="$payments" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
