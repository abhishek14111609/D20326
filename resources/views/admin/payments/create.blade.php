@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create New Payment</h1>
        <div>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payments.store') }}" method="POST">
                @include('admin.payments._form')
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate transaction ID if empty
    document.addEventListener('DOMContentLoaded', function() {
        const transactionIdField = document.getElementById('transaction_id');
        
        if (transactionIdField && !transactionIdField.value) {
            // Generate a random transaction ID if the field is empty
            const randomId = 'TXN' + Math.random().toString(36).substr(2, 9).toUpperCase();
            transactionIdField.value = randomId;
        }
    });
</script>
@endpush
