@extends('admin.layouts.auth')

@section('title', 'Login - ' . config('app.name') . ' Admin')

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
                <img src="https://duos.webvibeinfotech.in/public/assets/img/favicon/only_logo1.png" alt="Logo" style="height: 100px;">
              </span>
              <!-- <span class="app-brand-text demo text-body fw-bold">{{ config('app.name') }} Admin</span> -->
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-2">Welcome to {{ config('app.name') }} Admin! 👋</h4>
          <p class="mb-4">Please sign-in to your admin account</p>

          @if($errors->any())
            <div class="alert alert-danger">
              <h6 class="alert-heading">Please fix the following errors</h6>
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if(session('success'))
            <div class="alert alert-success">
              {{ session('success') }}
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger">
              {{ session('error') }}
            </div>
          @endif

          <form id="formAuthentication" class="mb-3" action="{{ route('admin.login') }}" method="POST">
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
                @if (Route::has('admin.password.request'))
                  <a href="{{ route('admin.password.request') }}">
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

          <!-- <p class="text-center">
            <span>Are you a user?</span>
            <a href="{{ route('login') }}">
              <span>Sign in as user</span>
            </a>
          </p> -->
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>
@endsection

@section('auth_footer')
    <p class="mb-1 text-center">
        <small>&copy; {{ date('Y') }} {{ config('app.name') }} Admin Panel. All rights reserved.</small>
    </p>
@stop
