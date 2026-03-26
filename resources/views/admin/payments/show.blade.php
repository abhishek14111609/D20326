@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Payment Details</h1>
        <div>
            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th class="w-25">Transaction ID:</th>
                                    <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>User:</th>
                                    <td>
                                        @if($payment->user)
                                            <a href="{{ route('admin.users.show', $payment->user) }}">
                                                {{ $payment->user->name }} ({{ $payment->user->email }})
                                            </a>
                                        @else
                                            User Deleted
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td>{{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $payment->status === 'completed' ? 'success' : 
                                            ($payment->status === 'pending' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ $payment->payment_method }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $payment->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $payment->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->status === 'pending')
                            <form action="{{ route('admin.payments.update', $payment) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-check-circle me-1"></i> Mark as Completed
                                </button>
                            </form>
                        @endif
                        
                        @if($payment->status !== 'refunded' && $payment->status !== 'failed')
                            <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#refundModal">
                                <i class="fas fa-undo me-1"></i> Process Refund
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i> Edit Payment
                        </a>
                        
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Delete Payment
                        </button>
                    </div>
                </div>
            </div>
            
            @if($payment->user && $payment->user->payments->count() > 1)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">User's Other Payments</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($payment->user->payments->where('id', '!=', $payment->id)->take(5) as $otherPayment)
                                <a href="{{ route('admin.payments.show', $otherPayment) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">#{{ $otherPayment->transaction_id ?? $otherPayment->id }}</h6>
                                        <small class="text-{{ 
                                            $otherPayment->status === 'completed' ? 'success' : 
                                            ($otherPayment->status === 'pending' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($otherPayment->status) }}
                                        </small>
                                    </div>
                                    <p class="mb-1">{{ number_format($otherPayment->amount, 2) }} {{ strtoupper($otherPayment->currency) }}</p>
                                    <small>{{ $otherPayment->created_at->diffForHumans() }}</small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this payment record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="refunded">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="refundAmount" class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ strtoupper($payment->currency) }}</span>
                            <input type="number" class="form-control" id="refundAmount" name="amount" 
                                   step="0.01" min="0.01" max="{{ $payment->amount }}" 
                                   value="{{ $payment->amount }}" required>
                        </div>
                        <div class="form-text">
                            Maximum refundable amount: {{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="refundReason" class="form-label">Reason for Refund (Optional)</label>
                        <textarea class="form-control" id="refundReason" name="refund_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Refund</button>
                </div>
            </form>
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
        
        // Set max amount for refund
        document.getElementById('refundAmount').addEventListener('input', function(e) {
            if (parseFloat(this.value) > {{ $payment->amount }}) {
                this.value = {{ $payment->amount }};
            }
        });
    });
</script>
@endpush
