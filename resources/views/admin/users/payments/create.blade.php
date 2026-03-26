@extends('admin.layouts.app')

@section('title', 'Add New Payment - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">
                <a href="{{ route('admin.users.show', $user) }}" class="text-muted">{{ $user->name }}</a> /
                <a href="{{ route('admin.users.payments.index', $user) }}" class="text-muted">Payments</a> /
            </span>
            Add New Payment
        </h4>
        <a href="{{ route('admin.users.payments.index', $user) }}" class="btn btn-label-secondary">
            <i class='bx bx-arrow-back me-1'></i> Back to Payments
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Information</h5>
                    <small class="text-muted">Fill in the payment details below</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.payments.store', $user) }}" method="POST">
                        @csrf
                        
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
                                        value="{{ old('amount') }}" 
                                        required
                                        placeholder="0.00"
                                    >
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                                    <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <!-- Add more currencies as needed -->
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                value="{{ old('description') }}" 
                                required
                                placeholder="e.g., Subscription Payment"
                            >
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                    <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>Select payment method</option>
                                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="3"
                                placeholder="Any additional notes about this payment"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class='bx bx-save me-1'></i> Save Payment
                            </button>
                            <a href="{{ route('admin.users.payments.index', $user) }}" class="btn btn-label-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar with user info -->
        <div class="col-md-4">
            <div class="card">
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
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class='bx bx-info-circle me-2'></i>Important Notes</h6>
                        <ul class="mb-0 ps-3">
                            <li>Double-check the amount before saving</li>
                            <li>Verify the payment method is correct</li>
                            <li>Add any relevant notes for future reference</li>
                            <li>Set the appropriate status for the payment</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any client-side validation or interactivity here
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Format amount field on blur
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('blur', function(e) {
                const value = parseFloat(e.target.value);
                if (!isNaN(value)) {
                    e.target.value = value.toFixed(2);
                }
            });
        }
    });
</script>
@endpush
