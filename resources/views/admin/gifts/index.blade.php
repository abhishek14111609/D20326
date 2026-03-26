@extends('admin.layouts.app')

@section('title', 'Manage Gifts')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gifts List</h5>
            <a href="{{ route('admin.gifts.create') }}" class="btn btn-primary">
                <i class='bx bx-plus'></i> Add New Gift
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gifts as $gift)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                           <td>
                                <img src="{{ $gift->image_path ? ('https://duos.webvibeinfotech.in/storage/app/public/' . $gift->image_path) : asset('assets/img/avatars/default-avatar.png') }}" alt="{{ $gift->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>{{ $gift->name }}</td>
                            <td>${{ number_format($gift->price, 2) }}</td>
                            <td>{{ $gift->created_at->format('M d, Y') }}</td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.gifts.show', $gift) }}"
                                    edit-route="{{ route('admin.gifts.edit', $gift) }}"
                                    delete-route="{{ route('admin.gifts.destroy', $gift) }}"
                                    delete-confirm-message="Are you sure you want to delete this gift?"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No gifts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($gifts->hasPages())
                <div class="card-footer">
                    <x-pagination :paginator="$gifts" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .img-thumbnail {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
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
