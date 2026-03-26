@extends('admin.layouts.app')

@section('title', 'Payment History - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Users / {{ $user->name }} /</span> Payments
        </h4>
        <div>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-label-secondary">
                <i class='bx bx-arrow-back me-1'></i> Back to User
            </a>
            <a href="{{ route('admin.users.payments.create', $user) }}" class="btn btn-primary">
                <i class='bx bx-plus me-1'></i> Add Payment
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-2">Total Payments</h6>
                            <h3 class="mb-0">{{ $payments->total() }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-primary rounded">
                                <i class='bx bx-credit-card'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-2">Total Amount</h6>
                            <h3 class="mb-0">${{ number_format($payments->sum('amount'), 2) }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-success rounded">
                                <i class='bx bx-dollar-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction History</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-merge" style="width: 250px;">
                    <span class="input-group-text"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control" placeholder="Search..." id="searchInput">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Transaction</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded bg-label-{{ [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'info'
                                        ][$payment->status] ?? 'secondary' }}">
                                            {{ strtoupper(substr($payment->id, -4)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">#{{ $payment->transaction_id }}</h6>
                                        <small class="text-muted">{{ $payment->description ?: 'Payment' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'info'
                                    ][$payment->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusClasses }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.users.payments.show', [$user, $payment]) }}">
                                            <i class="bx bx-show me-1"></i> View
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.users.payments.edit', [$user, $payment]) }}">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('delete-payment-{{ $payment->id }}').submit();">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </a>
                                        <form id="delete-payment-{{ $payment->id }}" action="{{ route('admin.users.payments.destroy', [$user, $payment]) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="misc-wrapper">
                                    <div class="misc-inner p-2 p-sm-3 mx-auto">
                                        <div class="w-100 text-center">
                                            <h2 class="mb-2">No payments found</h2>
                                            <p class="mb-4">
                                                This user doesn't have any payment records yet.
                                            </p>
                                            <a href="{{ route('admin.users.payments.create', $user) }}" class="btn btn-primary">
                                                <i class='bx bx-plus me-1'></i> Add Payment
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
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
@endsection

@push('scripts')
<script>
    // Simple search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
</script>
@endpush
