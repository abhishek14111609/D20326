@extends('admin.layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit User</h5>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                        <i class='bx bx-arrow-back me-1'></i> Back to User
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Profile Image</label>
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        @if($user->getFirstMediaUrl('profile_image'))
                                            <img src="{{ $user->getFirstMediaUrl('profile_image') }}" alt="Profile Image" class="d-block rounded" height="100" width="100" id="profile-image-preview">
                                        @else
                                            <div class="avatar avatar-xl">
                                                <span class="avatar-initial rounded bg-label-primary">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="button-wrapper">
                                            <label for="profile_image" class="btn btn-primary me-2 mb-4" tabindex="0">
                                                <span class="d-none d-sm-block">Upload new photo</span>
                                                <i class="bx bx-upload d-block d-sm-none"></i>
                                                <input type="file" name="profile_image" id="profile_image" class="account-file-input" hidden accept="image/png, image/jpeg" onchange="previewImage(this)" />
                                            </label>
                                            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" onclick="resetImage()">
                                                <i class="bx bx-reset d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </button>
                                            <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="name">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter full name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Enter email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Enter phone number">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Banned</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="role">Role</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter new password (leave blank to keep current)">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Image preview
    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profile-image-preview');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    // If preview element doesn't exist, create it
                    const img = document.createElement('img');
                    img.id = 'profile-image-preview';
                    img.src = e.target.result;
                    img.className = 'd-block rounded';
                    img.height = 100;
                    img.width = 100;
                    input.parentNode.parentNode.insertBefore(img, input.parentNode);
                }
            };
            reader.readAsDataURL(file);
        }
    }

    // Reset image
    function resetImage() {
        const preview = document.getElementById('profile-image-preview');
        const fileInput = document.getElementById('profile_image');
        
        if (preview) {
            preview.remove();
        }
        if (fileInput) {
            fileInput.value = '';
        }
    }

    // Initialize form validation
    (function () {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
@endpush
