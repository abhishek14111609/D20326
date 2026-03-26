@extends('admin.layouts.app')

@section('title', 'Create Membership Plan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Membership Plan</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.memberships.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <form action="{{ route('admin.memberships.store') }}" method="POST">
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
                                    <label for="name">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name') }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3">{{ old('description') }}</textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Price <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" class="form-control" id="price" name="price" 
                                                       step="0.01" min="0" value="{{ old('price', '0') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency">Currency <span class="text-danger">*</span></label>
                                            <select class="form-control" id="currency" name="currency" required>
                                                <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                                <!-- Add more currencies as needed -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="duration_value">Duration Value <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="duration_value" 
                                                   name="duration_value" min="1" value="{{ old('duration_value', '1') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="duration_unit">Duration Unit <span class="text-danger">*</span></label>
                                            <select class="form-control" id="duration_unit" name="duration_unit" required>
                                                @foreach($durations as $key => $label)
                                                    <option value="{{ $key }}" {{ old('duration_unit') == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="level">Level <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="level" name="level" 
                                           min="1" value="{{ old('level', '1') }}" required>
                                    <small class="form-text text-muted">Higher level plans will override lower level plans.</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active') ? 'checked' : 'checked' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Plan Features</h5>
                                <div id="features-container">
                                    @if(old('features'))
                                        @foreach(old('features') as $index => $feature)
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="features[]" 
                                                       value="{{ $feature }}" placeholder="Enter feature">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-feature">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="features[]" 
                                                   placeholder="Enter feature">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-feature">
                                                    <i class="fas fa-times"></i>Delete
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" id="add-feature" class="btn btn-sm btn-secondary mt-2">
                                    <i class="fas fa-plus"></i> Add Feature
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Payment Gateway IDs</h5>
                                <div class="form-group">
                                    <label for="stripe_plan_id">Stripe Plan ID</label>
                                    <input type="text" class="form-control" id="stripe_plan_id" 
                                           name="stripe_plan_id" value="{{ old('stripe_plan_id') }}">
                                </div>
                                <div class="form-group">
                                    <label for="paypal_plan_id">PayPal Plan ID</label>
                                    <input type="text" class="form-control" id="paypal_plan_id" 
                                           name="paypal_plan_id" value="{{ old('paypal_plan_id') }}">
                                </div>
                                <div class="form-group">
                                    <label for="razorpay_plan_id">Razorpay Plan ID</label>
                                    <input type="text" class="form-control" id="razorpay_plan_id" 
                                           name="razorpay_plan_id" value="{{ old('razorpay_plan_id') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Plan
                        </button>
                        <a href="{{ route('admin.memberships.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Add feature field
        $('#add-feature').click(function() {
            const featureHtml = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="features[]" placeholder="Enter feature">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-feature">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#features-container').append(featureHtml);
        });

        // Remove feature field
        $(document).on('click', '.remove-feature', function() {
            if ($('#features-container .input-group').length > 1) {
                $(this).closest('.input-group').remove();
            } else {
                $(this).closest('.input-group').find('input').val('');
            }
        });
    });
</script>
@endpush
