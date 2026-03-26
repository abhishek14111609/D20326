@extends('admin.layouts.app')

@section('title', 'View Membership Plan: ' . $membership->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Membership Plan Details</h3>
                    <div>
                        <a href="{{ route('admin.memberships.edit', $membership) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.memberships.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Basic Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Plan Name:</th>
                                    <td>{{ $membership->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $membership->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>{{ $membership->currency }} {{ number_format($membership->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>
                                        {{ $membership->duration_value }} {{ Str::title($membership->duration_unit) }}(s)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>{{ $membership->level }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge {{ $membership->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $membership->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $membership->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $membership->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h4>Plan Features</h4>
                            @php
                                // Try to decode features if it's a JSON string
                                $features = is_string($membership->features) ? json_decode($membership->features, true) : $membership->features;
                                $features = is_array($features) ? $features : [];
                            @endphp
                            
                            @if(count($features) > 0)
                                <div class="list-group">
                                    @foreach($features as $feature)
                                        <div class="list-group-item">
                                            @if(is_array($feature))
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>{{ $feature['name'] ?? 'Feature' }}</span>
                                                    @if(isset($feature['enabled']) && is_bool($feature['enabled']))
                                                        <span class="badge {{ $feature['enabled'] ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $feature['enabled'] ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(!empty($feature['description']))
                                                    <small class="text-muted d-block mt-1">{{ $feature['description'] }}</small>
                                                @endif
                                                @if(isset($feature['value']))
                                                    <div class="mt-1">
                                                        <strong>Value:</strong> {{ $feature['value'] }}
                                                    </div>
                                                @endif
                                            @else
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>{{ $feature }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">No features defined for this plan.</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Plan Benefits</h5>
                                </div>
                                <div class="card-body">
                                    {!! $membership->benefits ?? '<p class="text-muted">No benefits information available.</p>' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer d-flex justify-content-between">
                    <form action="{{ route('admin.memberships.destroy', $membership) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete Plan
                        </button>
                    </form>
                    <div>
                        <a href="{{ route('admin.memberships.edit', $membership) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit Plan
                        </a>
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    .table th {
        background-color: #f8f9fa;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
</style>
@endpush
