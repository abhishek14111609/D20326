@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Login -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo">
                <img src="https://duos.webvibeinfotech.in/public/assets/img/logo.png" alt="Logo" style="height: 40px;">
              </span>
              <span class="app-brand-text demo text-body fw-bold">{{ config('app.name') }}</span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-2">Welcome to {{ config('app.name') }}! 👋</h4>
          <p class="mb-4">Please sign-in to your account and start the adventure</p>

          <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="text"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="{{ old('email') }}"
                autofocus
                required
              />
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}">
                    <small>Forgot Password?</small>
                  </a>
                @endif
              </div>
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
            
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                  Remember Me
                </label>
              </div>
            </div>
            
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
            </div>
          </form>

          @if (Route::has('register'))
            <p class="text-center">
              <span>New on our platform?</span>
              <a href="{{ route('register') }}">
                <span>Create an account</span>
              </a>
            </p>
          @endif
          
          <div class="divider my-4">
            <div class="divider-text">or</div>
          </div>
          
          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
              <i class="tf-icons bx bxl-facebook"></i>
            </a>
            <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
              <i class="tf-icons bx bxl-google"></i>
            </a>
            <a href="javascript:;" class="btn btn-icon btn-label-twitter">
              <i class="tf-icons bx bxl-twitter"></i>
            </a>
          </div>
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>
@endsection
