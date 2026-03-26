@extends('admin.layouts.app')

@section('title', 'Edit Payment - ' . $payment->transaction_id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">
                <a href="{{ route('admin.users.show', $user) }}" class="text-muted">{{ $user->name }}</a> /
                <a href="{{ route('admin.users.payments.index', $user) }}" class="text-muted">Payments</a> /
                <a href="{{ route('admin.users.payments.show', [$user, $payment]) }}" class="text-muted">#{{ $payment->transaction_id }}</a> /
            </span>
            Edit
        </h4>
        <div>
            <a href="{{ route('admin.users.payments.show', [$user, $payment]) }}" class="btn btn-label-secondary">
                <i class='bx bx-arrow-back me-1'></i> Back to Payment
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Payment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.payments.update', [$user, $payment]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        class="form-control @error('amount') is-invalid @enderror" 
                                        id="amount" 
                                        name="amount" 
                                        value="{{ old('amount', $payment->amount) }}" 
                                        required
                                    >
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ old('status', $payment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ old('status', $payment->status) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input 
                                type="text" 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                value="{{ old('description', $payment->description) }}"
                            >
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="3"
                            >{{ old('notes', $payment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class='bx bx-save me-1'></i> Update Payment
                            </button>
                            <a href="{{ route('admin.users.payments.show', [$user, $payment]) }}" class="btn btn-label-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Transaction ID:</span>
                            <span class="fw-medium">{{ $payment->transaction_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Created:</span>
                            <span class="text-muted">{{ $payment->created_at->format('M d, Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Last Updated:</span>
                            <span class="text-muted">{{ $payment->updated_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
