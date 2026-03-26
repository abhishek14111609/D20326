@extends('layouts.auth')

@section('title', 'Verify Email - ' . config('app.name'))

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Verify Email -->
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
          <h4 class="mb-2">Verify your email ✉️</h4>
          
          @if (session('resent'))
            <div class="alert alert-success" role="alert">
              <div class="alert-body">
                <i class="bx bx-check-circle"></i>
                {{ __('A fresh verification link has been sent to your email address.') }}
              </div>
            </div>
          @endif
          
          <p class="mb-4">
            {{ __('Before proceeding, please check your email for a verification link.') }}
            {{ __('If you did not receive the email') }},
          </p>
          
          <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-primary d-grid w-100">
              {{ __('Click here to request another') }}
            </button>
          </form>
          
          <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
              <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
              Back to login
            </a>
          </div>
        </div>
      </div>
      <!-- /Verify Email -->
    </div>
  </div>
</div>
@endsection
