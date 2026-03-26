@extends('admin.layouts.app')

@section('title', 'Membership Details: ' . $membership->user->name . ' - ' . $membership->plan->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Membership Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('admin.user-memberships.edit', $membership->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">User</th>
                                    <td>{{ $membership->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Membership Plan</th>
                                    <td>{{ $membership->plan->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'active' => 'success',
                                                'cancelling' => 'warning',
                                                'cancelled' => 'secondary',
                                                'expired' => 'danger',
                                                'paused' => 'info',
                                                'payment_failed' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$membership->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucfirst($membership->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date</th>
                                    <td>{{ $membership->starts_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date</th>
                                    <td class="{{ $membership->ends_at < now() ? 'text-danger' : '' }}">
                                        {{ $membership->ends_at->format('M d, Y h:i A') }}
                                        @if($membership->ends_at < now())
                                            <span class="badge bg-danger">Expired</span>
                                        @else
                                            <span class="text-muted">({{ $membership->ends_at->diffForHumans() }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Auto Renew</th>
                                    <td>
                                        @if($membership->auto_renew)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method</th>
                                    <td>{{ $membership->payment_method ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Transaction ID</th>
                                    <td>{{ $membership->transaction_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $membership->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $membership->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if($membership->deleted_at)
                                    <tr>
                                        <th>Deleted At</th>
                                        <td class="text-danger">
                                            {{ $membership->deleted_at->format('M d, Y h:i A') }}
                                            <span class="badge bg-danger">Deleted</span>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <a href="{{ route('admin.user-memberships.edit', $membership->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Membership
                    </a>
                    <a href="{{ route('admin.user-memberships.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if(!$membership->trashed())
                        <button type="button" class="btn btn-danger float-right" 
                                onclick="confirmDelete({{ $membership->id }})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        
                        <form id="delete-form-{{ $membership->id }}" 
                              action="{{ route('admin.user-memberships.destroy', $membership->id) }}" 
                              method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
            <!-- /.card -->
        </div>
        
        <div class="col-md-6">
            <!-- Plan Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Details</h3>
                </div>
                <div class="card-body">
                    <h4>{{ $membership->plan->name }}</h4>
                    <p>{{ $membership->plan->description }}</p>
                    
                    <h5 class="mt-4">Features:</h5>
                    @if(!empty($membership->plan->features))
                        <ul class="list-unstyled">
                            @foreach($membership->plan->features as $feature)
                                <li><i class="fas fa-check text-success mr-2"></i> {{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No features defined for this plan.</p>
                    @endif
                    
                    <div class="mt-4">
                        <h5>Pricing:</h5>
                        <p class="h3">
                            {{ $membership->plan->currency }} {{ number_format($membership->plan->price, 2) }}
                            <small class="text-muted">
                                / {{ $membership->plan->duration_value }} 
                                {{ Str::plural($membership->plan->duration_unit, $membership->plan->duration_value) }}
                            </small>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- User Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($membership->user->profile_photo_path)
                            <img src="{{ asset('storage/' . $membership->user->profile_photo_path) }}" 
                                 alt="{{ $membership->user->name }}" 
                                 class="img-circle img-fluid" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 100px; height: 100px; font-size: 2rem; color: white;">
                                {{ substr($membership->user->name, 0, 1) }}
                            </div>
                        @endif
                        <h4 class="mt-3 mb-0">{{ $membership->user->name }}</h4>
                        <p class="text-muted">
                            <i class="fas fa-envelope mr-1"></i> {{ $membership->user->email }}
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.users.show', $membership->user_id) }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-user mr-1"></i> View User Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm delete
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this membership? This action cannot be undone.')) {
            event.preventDefault();
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
