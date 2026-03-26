@extends('admin.layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users List</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class='bx bx-plus'></i> Add New User
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
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
								
                               <img src="{{ $user->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $user->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                    alt="{{ $user->name }}" 
                                    class="rounded-circle" 
                                    style="width: 40px; height: 40px; object-fit: cover;"> 

                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->profile->mobile ?? $user->profile->partner1_mobile ?? $user->profile->partner2_mobile ?? '' }}</td>
                            <td>
								  @php
									  // Define color mapping for each status
									  $statusColors = [
										  'active'     => 'success',
										  'inactive'   => 'secondary',
										  'suspended'  => 'warning',
										  'pending'    => 'info',
										  'registered' => 'primary',
										  'verified'   => 'success',
										  'banned'     => 'danger',
									  ];

									  // Normalize status (lowercase to avoid case mismatches)
									  $status = strtolower($user->status);

									  // Fallback color if status not found
									  $badgeColor = $statusColors[$status] ?? 'dark';
								  @endphp

								  <span class="badge bg-{{ $badgeColor }}">
									  {{ ucfirst($user->status) }}
								  </span>
								</td>

                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <x-admin.action-buttons
                                    view-route="{{ route('admin.users.show', $user) }}"
                                    edit-route="{{ route('admin.users.edit', $user) }}"
                                    delete-route="{{ route('admin.users.destroy', $user) }}"
                                    delete-confirm-message="Are you sure you want to delete this user?"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="card-footer">
                    <x-pagination :paginator="$users" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('css')
    <style>
        .badge-pink {
            background-color: #e91e63;
            color: white;
        }
        .btn-icon {
            padding: 0.35rem 0.5rem;
        }
        .btn-icon i {
            font-size: 1.1em;
        }
        @media (max-width: 768px) {
            .btn-text {
                display: none;
            }
            .btn-icon {
                padding: 0.35rem;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
