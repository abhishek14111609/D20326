@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Reset Password -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="height: 40px;">
              </span>
              <span class="app-brand-text demo text-body fw-bold">{{ config('app.name') }}</span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-2">Reset Password 🔒</h4>
          <p class="mb-4">Enter your new password for your account</p>

          <form id="formAuthentication" class="mb-3" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="text"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="{{ $email ?? old('email') }}"
                autofocus
                required
              />
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="mb-3 form-password-toggle">
              <label class="form-label" for="password">New Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password"
                  required
                />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="mb-3 form-password-toggle">
              <label class="form-label" for="password-confirm">Confirm New Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password-confirm"
                  class="form-control"
                  name="password_confirmation"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password"
                  required
                />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>
            
            <button class="btn btn-primary d-grid w-100 mb-3">Set New Password</button>
            
            <div class="text-center">
              <a href="{{ route('login') }}">
                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                Back to login
              </a>
            </div>
          </form>
        </div>
      </div>
      <!-- /Reset Password -->
    </div>
  </div>
</div>
@endsection
