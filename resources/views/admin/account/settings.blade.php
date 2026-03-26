@extends('admin.layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Account Settings</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Profile Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Profile Information</h5>
                                    <small class="text-muted">Update your account's profile information and email address.</small>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.account.settings.update') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="mb-3 text-center">
                                            <div class="position-relative d-inline-block">
                                                <img src="{{ $admin->avatar_url }}" 
                                                     alt="Avatar" 
                                                     class="rounded-circle mb-3" 
                                                     width="120" 
                                                     height="120"
                                                     id="avatarPreview">
                                                <div class="position-absolute bottom-0 end-0">
                                                    <label for="avatar" class="btn btn-icon btn-primary rounded-circle">
                                                        <i class='bx bx-edit-alt'></i>
                                                        <input type="file" 
                                                               id="avatar" 
                                                               name="avatar" 
                                                               class="d-none" 
                                                               accept="image/*" 
                                                               onchange="previewImage(this, 'avatarPreview')">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="name">Name</label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name', $admin->name) }}" 
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email', $admin->email) }}" 
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class='bx bx-save me-1'></i> Save Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Update Password -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Update Password</h5>
                                    <small class="text-muted">Ensure your account is using a long, random password to stay secure.</small>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.account.password.update') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="current_password">Current Password</label>
                                            <input type="password" 
                                                   class="form-control @error('current_password') is-invalid @enderror" 
                                                   id="current_password" 
                                                   name="current_password" 
                                                   required>
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="new_password">New Password</label>
                                            <input type="password" 
                                                   class="form-control @error('new_password') is-invalid @enderror" 
                                                   id="new_password" 
                                                   name="new_password" 
                                                   required>
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="new_password_confirmation" 
                                                   name="new_password_confirmation" 
                                                   required>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class='bx bx-key me-1'></i> Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "{{ $admin->avatar_url }}";
        }
    }
</script>
@endpush
