@extends('admin.layouts.app')

@section('title', 'Membership Plans')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Membership Plans</h3>
                    <a href="{{ route('admin.memberships.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Plan
                    </a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $plan->name }}</td>
                                        <td>{{ $plan->currency }} {{ number_format($plan->price, 2) }}</td>
                                        <td>{{ $plan->duration_value }} {{ Str::title($plan->duration_unit) }}(s)</td>
                                        <td>{{ $plan->level }}</td>
                                        <td>
                                            <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <x-admin.action-buttons
                                                view-route="{{ route('admin.memberships.show', $plan->id) }}"
                                                edit-route="{{ route('admin.memberships.edit', $plan->id) }}"
                                                delete-route="{{ route('admin.memberships.destroy', $plan->id) }}"
                                                delete-confirm-message="Are you sure you want to delete this plan?"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No membership plans found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $plans->links() }}
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin-right: 5px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
