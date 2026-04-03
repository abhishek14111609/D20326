@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('content')
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Admin /</span> Profile
        </h4>

        <div class="row">
            <!-- User Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- User Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <div class="user-avatar-wrapper">
                                    <div class="avatar avatar-xl">
                                        <span
                                            class="avatar-initial rounded-circle bg-label-primary">{{ substr($admin->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="user-info text-center mt-3">
                                    <h4 class="mb-1">{{ $admin->name }}</h4>
                                    <span
                                        class="badge bg-label-{{ $admin->role == 'super_admin' ? 'danger' : ($admin->role == 'admin' ? 'success' : 'info') }} mb-1">
                                        {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                    </span>
                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                        <i class='bx bx-envelope me-1'></i>
                                        <span class="text-muted">{{ $admin->email }}</span>
                                    </div>
                                    @if ($admin->phone)
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class='bx bx-phone me-1'></i>
                                            <span class="text-muted">{{ $admin->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-around my-4 pt-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-initial bg-label-primary rounded">
                                        <i class='bx bx-calendar'></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <div class="text-muted">Joined</div>
                                    <h6 class="mb-0">{{ $admin->created_at->format('M Y') }}</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div
                                        class="avatar-initial bg-label-{{ $admin->status == 'active' ? 'success' : 'danger' }} rounded">
                                        <i class='bx bx-{{ $admin->status == 'active' ? 'check' : 'x' }}'></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <div class="text-muted">Status</div>
                                    <h6 class="mb-0">{{ ucfirst($admin->status) }}</h6>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfile">
                                <i class='bx bx-edit-alt me-1'></i> Edit Profile
                            </button>
                            <button class="btn btn-label-secondary" data-bs-toggle="modal" data-bs-target="#changePassword">
                                <i class='bx bx-lock-alt me-1'></i> Change Password
                            </button>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-label-danger">
                                    <i class='bx bx-log-out me-1'></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /User Card -->
            </div>
            <!--/ User Sidebar -->

            <!-- User Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                <!-- Activity Timeline -->
                <div class="card mb-4">
                    <h5 class="card-header">Activity Timeline</h5>
                    <div class="card-body">
                        <ul class="timeline">
                            @if ($admin->last_login_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-primary"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-1">
                                            <h6 class="mb-0">Last Login</h6>
                                            <small
                                                class="text-muted">{{ $admin->last_login_at->timezone(config('app.admin_display_timezone', 'Asia/Kolkata'))->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-2">Successfully logged in from IP:
                                            {{ $admin->last_login_ip ?? 'Unknown' }}</p>
                                        <div class="d-flex">
                                            <span class="badge bg-label-primary me-2">Login</span>
                                            <span
                                                class="text-muted">{{ $admin->last_login_at->timezone(config('app.admin_display_timezone', 'Asia/Kolkata'))->format('M d, Y h:i:s A') }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-success"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Profile Updated</h6>
                                        <small class="text-muted">{{ $admin->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0">Your profile was last updated</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-info"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Account Created</h6>
                                        <small class="text-muted">{{ $admin->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0">Your account was created</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /Activity Timeline -->

                <!-- Account Details -->
                <div class="card">
                    <h5 class="card-header">Account Details</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <div class="form-control">{{ $admin->email }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <div class="form-control">{{ $admin->phone ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <div class="form-control">
                                    <span
                                        class="badge bg-label-{{ $admin->role == 'super_admin' ? 'danger' : ($admin->role == 'admin' ? 'success' : 'info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-control">
                                    <span class="badge bg-label-{{ $admin->status == 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($admin->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Login</label>
                                <div class="form-control">
                                    {{ $admin->last_login_at ? $admin->last_login_at->timezone(config('app.admin_display_timezone', 'Asia/Kolkata'))->format('M d, Y h:i:s A') : 'Never' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Login IP</label>
                                <div class="form-control">
                                    {{ $admin->last_login_ip ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Account Details -->
            </div>
            <!--/ User Content -->
        </div>
    </div>
    <!-- / Content -->

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Edit Profile</h3>
                        <p class="text-muted">Update your profile details.</p>
                    </div>
                    <form action="{{ route('admin.account.settings.update') }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <label class="form-label" for="profileName">Name</label>
                            <input type="text" id="profileName" name="name" class="form-control"
                                value="{{ old('name', $admin->name) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="profileEmail">Email</label>
                            <input type="email" id="profileEmail" name="email" class="form-control"
                                value="{{ old('email', $admin->email) }}" required>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Update Profile</button>
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit Profile Modal -->

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Change Password</h3>
                        <p class="text-muted">Your new password must be different from previously used passwords</p>
                    </div>
                    <form action="{{ route('admin.account.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="currentPassword">Current Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="currentPassword" class="form-control" name="current_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="currentPassword" required />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="newPassword">New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="newPassword" class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="newPassword" required />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="confirmPassword">Confirm New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="confirmPassword" class="form-control"
                                    name="password_confirmation"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="confirmPassword" required />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="col-12 mb-4">
                            <h6>Password Requirements:</h6>
                            <ul class="ps-3 mb-0">
                                <li class="mb-1">Minimum 8 characters long - the more, the better</li>
                                <li class="mb-1">At least one lowercase character</li>
                                <li>At least one number, symbol, or whitespace character</li>
                            </ul>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Update Password</button>
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Change Password Modal -->

    @push('scripts')
        <script>
            // Add any custom scripts here
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize any plugins or add event listeners
            });
        </script>
    @endpush
@endsection
