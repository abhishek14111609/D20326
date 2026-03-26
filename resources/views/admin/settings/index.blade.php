@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Settings</h5>
                    <small class="text-muted">Update your system configuration</small>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <h5 class="card-header">General Settings</h5>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Name</label>
                                            <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                                id="site_name" name="site_name" 
                                                value="{{ old('site_name', $settings['site_name']) }}" required>
                                            @error('site_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="site_email" class="form-label">Site Email</label>
                                            <input type="email" class="form-control @error('site_email') is-invalid @enderror" 
                                                id="site_email" name="site_email" 
                                                value="{{ old('site_email', $settings['site_email']) }}" required>
                                            @error('site_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="default_user_role" class="form-label">Default User Role</label>
                                            <select class="form-select @error('default_user_role') is-invalid @enderror" 
                                                id="default_user_role" name="default_user_role" required>
                                                <option value="user" {{ old('default_user_role', $settings['default_user_role']) == 'user' ? 'selected' : '' }}>User</option>
                                                <option value="premium" {{ old('default_user_role', $settings['default_user_role']) == 'premium' ? 'selected' : '' }}>Premium</option>
                                                <option value="admin" {{ old('default_user_role', $settings['default_user_role']) == 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                            @error('default_user_role')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <h5 class="card-header">System Settings</h5>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                                    name="maintenance_mode" value="1" 
                                                    {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                            </div>
                                            <small class="text-muted">When enabled, only administrators can access the site.</small>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="registration_enabled" 
                                                    name="registration_enabled" value="1"
                                                    {{ old('registration_enabled', $settings['registration_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="registration_enabled">User Registration</label>
                                            </div>
                                            <small class="text-muted">Allow new users to register on the site.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <h5 class="card-header">API Settings</h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="google_maps_api_key" class="form-label">Google Maps API Key</label>
                                                    <input type="password" class="form-control @error('google_maps_api_key') is-invalid @enderror" 
                                                        id="google_maps_api_key" name="google_maps_api_key" 
                                                        value="{{ old('google_maps_api_key', $settings['google_maps_api_key'] ?? '') }}">
                                                    @error('google_maps_api_key')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Required for location-based features</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="video_call_api_key" class="form-label">Video Call API Key</label>
                                                    <input type="password" class="form-control @error('video_call_api_key') is-invalid @enderror" 
                                                        id="video_call_api_key" name="video_call_api_key" 
                                                        value="{{ old('video_call_api_key', $settings['video_call_api_key'] ?? '') }}">
                                                    @error('video_call_api_key')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Required for video calling features</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="audio_call_api_key" class="form-label">Audio Call API Key</label>
                                                    <input type="password" class="form-control @error('audio_call_api_key') is-invalid @enderror" 
                                                        id="audio_call_api_key" name="audio_call_api_key" 
                                                        value="{{ old('audio_call_api_key', $settings['audio_call_api_key'] ?? '') }}">
                                                    @error('audio_call_api_key')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Required for audio calling features</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label d-block">Push Notifications</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="enable_push_notifications" 
                                                            name="enable_push_notifications" value="1"
                                                            {{ old('enable_push_notifications', $settings['enable_push_notifications'] ?? 1) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="enable_push_notifications">
                                                            {{ $settings['enable_push_notifications'] ?? 1 ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                    <small class="text-muted">Enable or disable push notifications system-wide</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-footer d-flex justify-content-end">
                                        <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </div>
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
    // Add any additional JavaScript if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Toggle maintenance mode description
        const maintenanceToggle = document.getElementById('maintenance_mode');
        if (maintenanceToggle) {
            maintenanceToggle.addEventListener('change', function() {
                // Add any custom behavior when maintenance mode is toggled
            });
        }
    });
</script>
@endpush
