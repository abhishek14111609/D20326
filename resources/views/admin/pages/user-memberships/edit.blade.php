@extends('admin.layouts.app')

@section('title', 'Edit Membership: ' . $membership->user->name . ' - ' . $membership->plan->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Membership: {{ $membership->user->name }} - {{ $membership->plan->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('admin.user-memberships.update', $membership->id) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                        @foreach($users as $id => $name)
                                            <option value="{{ $id }}" {{ old('user_id', $membership->user_id) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="membership_plan_id">Membership Plan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="membership_plan_id" name="membership_plan_id" required>
                                        @foreach($plans as $id => $name)
                                            <option value="{{ $id }}" {{ old('membership_plan_id', $membership->membership_plan_id) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="starts_at">Start Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" 
                                           value="{{ old('starts_at', $membership->starts_at->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ends_at">End Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" 
                                           value="{{ old('ends_at', $membership->ends_at->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" {{ old('status', $membership->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="cancelling" {{ old('status', $membership->status) == 'cancelling' ? 'selected' : '' }}>Cancelling</option>
                                        <option value="cancelled" {{ old('status', $membership->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="expired" {{ old('status', $membership->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="paused" {{ old('status', $membership->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                                        <option value="payment_failed" {{ old('status', $membership->status) == 'payment_failed' ? 'selected' : '' }}>Payment Failed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" id="auto_renew" 
                                               name="auto_renew" value="1" {{ old('auto_renew', $membership->auto_renew) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="auto_renew">Auto Renew</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <input type="text" class="form-control" id="payment_method" name="payment_method" 
                                           value="{{ old('payment_method', $membership->payment_method) }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="transaction_id">Transaction ID</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                           value="{{ old('transaction_id', $membership->transaction_id) }}">
                                </div>
                                
                                <div class="form-group">
                                    <label>Created At:</label>
                                    <p class="form-control-static">{{ $membership->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                
                                @if($membership->deleted_at)
                                    <div class="form-group">
                                        <label>Deleted At:</label>
                                        <p class="form-control-static text-danger">{{ $membership->deleted_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Membership
                        </button>
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        
                        @if(!$membership->trashed())
                            <button type="button" class="btn btn-danger float-right" 
                                    onclick="confirmDelete({{ $membership->id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        @endif
                    </div>
                </form>
                
                @if(!$membership->trashed())
                    <form id="delete-form-{{ $membership->id }}" 
                          action="{{ route('admin.user-memberships.destroy', $membership->id) }}" 
                          method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
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
    .form-control-static {
        padding-top: calc(0.375rem + 1px);
        padding-bottom: calc(0.375rem + 1px);
        margin-bottom: 0;
        line-height: 1.5;
        min-height: 38px;
        border-bottom: 1px solid #e9ecef;
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
        
        // Set minimum end date based on start date
        $('#starts_at').change(function() {
            const startDate = new Date($(this).val());
            const endDate = new Date($('#ends_at').val());
            
            if (endDate < startDate) {
                $('#ends_at').val($(this).val());
            }
            
            $('#ends_at').attr('min', $(this).val());
        });
    });
    
    // Confirm delete
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this membership? This action cannot be undone.')) {
            event.preventDefault();
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
