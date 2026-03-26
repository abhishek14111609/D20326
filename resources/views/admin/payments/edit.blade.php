@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="fas fa-edit me-2"></i>Edit Payment
            <small class="text-muted">#{{ $payment->transaction_id ?? $payment->id }}</small>
        </h1>
        <div>
            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-secondary me-2">
                <i class="fas fa-eye me-1"></i> View Details
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-pencil-alt me-1"></i> Update Payment Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.payments._form')
            </form>

            @if($payment->status !== 'refunded' && $payment->status !== 'failed')
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Danger Zone</h5>
                        <p class="text-muted mb-0">
                            <small>These actions cannot be undone. Proceed with caution.</small>
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundModal">
                        <i class="fas fa-undo me-1"></i> Process Refund
                    </button>
                </div>

                <!-- Refund Modal -->
                <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title" id="refundModalLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Process Refund
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="refunded">
                                <div class="modal-body">
                                    <p>You are about to mark this payment as refunded. Please confirm the refund details below.</p>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Original Payment:</strong> 
                                        {{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
                                        <br>
                                        <strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                    </div>

                                    <div class="mb-3">
                                        <label for="refund_amount" class="form-label">Refund Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ strtoupper($payment->currency) }}</span>
                                            <input type="number" class="form-control" id="refund_amount" 
                                                   name="amount" step="0.01" min="0.01" 
                                                   max="{{ $payment->amount }}" 
                                                   value="{{ $payment->amount }}" required>
                                        </div>
                                        <div class="form-text">
                                            Maximum refundable amount: {{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="refund_reason" class="form-label">Reason for Refund <small class="text-muted">(Optional)</small></label>
                                        <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" 
                                                  placeholder="Enter the reason for this refund"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-check me-1"></i> Confirm Refund
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
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
        const refundAmount = document.getElementById('refund_amount');
        if (refundAmount) {
            refundAmount.addEventListener('input', function(e) {
                if (parseFloat(this.value) > {{ $payment->amount }}) {
                    this.value = {{ $payment->amount }};
                }
            });
        }
    });
</script>
@endpush
