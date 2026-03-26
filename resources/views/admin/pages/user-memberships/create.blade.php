@extends('admin.layouts.app')

@section('title', 'Assign New Membership')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign New Membership</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('admin.user-memberships.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">User <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="user_id" name="user_id" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $id => $name)
                                            <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="membership_plan_id">Membership Plan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="membership_plan_id" name="membership_plan_id" required>
                                        <option value="">Select Plan</option>
                                        @foreach($plans as $id => $name)
                                            <option value="{{ $id }}" {{ old('membership_plan_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="starts_at">Start Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" 
                                           value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_value">Duration Value <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="duration_value" name="duration_value" 
                                           min="1" value="{{ old('duration_value', '1') }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="duration_unit">Duration Unit <span class="text-danger">*</span></label>
                                    <select class="form-control" id="duration_unit" name="duration_unit" required>
                                        <option value="day" {{ old('duration_unit') == 'day' ? 'selected' : '' }}>Days</option>
                                        <option value="week" {{ old('duration_unit') == 'week' ? 'selected' : 'selected' }}>Weeks</option>
                                        <option value="month" {{ old('duration_unit') == 'month' ? 'selected' : '' }}>Months</option>
                                        <option value="year" {{ old('duration_unit') == 'year' ? 'selected' : '' }}>Years</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" id="auto_renew" 
                                               name="auto_renew" value="1" {{ old('auto_renew') ? 'checked' : 'checked' }}>
                                        <label class="custom-control-label" for="auto_renew">Auto Renew</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <input type="text" class="form-control" id="payment_method" name="payment_method" 
                                           value="{{ old('payment_method', 'admin') }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="transaction_id">Transaction ID</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                           value="{{ old('transaction_id') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Assign Membership
                        </button>
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        padding: 5px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@push('scripts')
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    });
</script>
@endpush
