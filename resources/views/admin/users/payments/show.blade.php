@extends('admin.layouts.app')

@section('title', 'Payment Details - ' . $payment->transaction_id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">
                <a href="{{ route('admin.users.show', $user) }}" class="text-muted">{{ $user->name }}</a> /
                <a href="{{ route('admin.users.payments.index', $user) }}" class="text-muted">Payments</a> /
            </span>
            Payment #{{ $payment->transaction_id }}
        </h4>
        <div>
            <a href="{{ route('admin.users.payments.edit', [$user, $payment]) }}" class="btn btn-label-primary me-2">
                <i class='bx bx-edit me-1'></i> Edit
            </a>
            <a href="{{ route('admin.users.payments.index', $user) }}" class="btn btn-label-secondary">
                <i class='bx bx-arrow-back me-1'></i> Back to Payments
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Payment Details -->
        <div class="col-md-8 mb-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Transaction Details</h5>
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
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Transaction ID</h6>
                            <p class="mb-0">{{ $payment->transaction_id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Date</h6>
                            <p class="mb-0">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Amount</h6>
                            <h4 class="mb-0">${{ number_format($payment->amount, 2) }} <small class="text-muted">{{ strtoupper($payment->currency) }}</small></h4>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Payment Method</h6>
                            <div class="d-flex align-items-center">
                                @php
                                    $paymentIcons = [
                                        'credit_card' => ['bx-credit-card', 'Credit Card'],
                                        'paypal' => ['bxl-paypal', 'PayPal'],
                                        'bank_transfer' => ['bx-transfer', 'Bank Transfer'],
                                        'stripe' => ['bxl-stripe', 'Stripe'],
                                        'razorpay' => ['bxl-razorpay', 'Razorpay'],
                                        'other' => ['bx-credit-card', 'Other']
                                    ][$payment->payment_method] ?? ['bx-credit-card', 'Credit Card'];
                                @endphp
                                <i class='bx {{ $paymentIcons[0] }} me-2 fs-4 text-primary'></i>
                                <span>{{ $paymentIcons[1] }}</span>
                            </div>
                        </div>
                    </div>

                    @if($payment->description)
                        <div class="mb-4">
                            <h6 class="text-muted">Description</h6>
                            <p class="mb-0">{{ $payment->description }}</p>
                        </div>
                    @endif

                    @if($payment->notes)
                        <div class="mb-4">
                            <h6 class="text-muted">Notes</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($payment->notes)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Created At</h6>
                                <p class="mb-0">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-2">Last Updated</h6>
                                <p class="mb-0">{{ $payment->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User & Actions -->
        <div class="col-md-4 mb-4">
            <!-- User Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $user->name }}</h5>
                            <small class="text-muted">User ID: {{ $user->id }}</small>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-label-primary">
                            <i class='bx bx-user me-1'></i> View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($payment->status === 'pending')
                        <form action="{{ route('admin.users.payments.status.update', [$user, $payment]) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class='bx bx-check-circle me-1'></i> Mark as Completed
                            </button>
                        </form>
                    @endif

                    @if($payment->status === 'completed')
                        <form action="{{ route('admin.users.payments.status.update', [$user, $payment]) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="refunded">
                            <button type="submit" class="btn btn-warning w-100 mb-2">
                                <i class='bx bx-undo me-1'></i> Issue Refund
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.users.payments.edit', [$user, $payment]) }}" class="btn btn-label-primary w-100 mb-2">
                        <i class='bx bx-edit me-1'></i> Edit Payment
                    </a>

                    <button type="button" class="btn btn-label-danger w-100" data-bs-toggle="modal" data-bs-target="#deletePaymentModal">
                        <i class='bx bx-trash me-1'></i> Delete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class='bx bx-error-circle me-2'></i>
                    <strong>Warning:</strong> This will permanently delete the payment record.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.users.payments.destroy', [$user, $payment]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-trash me-1'></i> Delete Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
