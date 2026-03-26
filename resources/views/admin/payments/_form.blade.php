@csrf

<div class="row mb-3">
    <div class="col-md-6">
        <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
            <option value="">Select User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $payment->user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('user_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-6">
        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" 
                   id="amount" name="amount" value="{{ old('amount', $payment->amount ?? '') }}" required>
            <select class="form-select @error('currency') is-invalid @enderror" name="currency" style="max-width: 100px;" required>
                @foreach(['usd' => 'USD', 'eur' => 'EUR', 'gbp' => 'GBP'] as $code => $name)
                    <option value="{{ $code }}" {{ old('currency', $payment->currency ?? 'usd') == $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @error('currency')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
            <option value="">Select Payment Method</option>
            @foreach(['credit_card', 'paypal', 'bank_transfer', 'stripe', 'other'] as $method)
                <option value="{{ $method }}" {{ old('payment_method', $payment->payment_method ?? '') == $method ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $method)) }}
                </option>
            @endforeach
        </select>
        @error('payment_method')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-6">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(['pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $value => $label)
                <option value="{{ $value }}" {{ old('status', $payment->status ?? 'pending') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="transaction_id" class="form-label">Transaction ID</label>
    <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" 
           id="transaction_id" name="transaction_id" 
           value="{{ old('transaction_id', $payment->transaction_id ?? '') }}">
    @error('transaction_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Leave blank to auto-generate a transaction ID.</div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
              id="description" name="description" rows="3">{{ old('description', $payment->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ isset($payment) ? 'Update' : 'Create' }} Payment
    </button>
</div>
