@extends('admin.layouts.app')

@section('title', 'Edit Gift: ' . $gift->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Gift: {{ $gift->name }}</h5>
                    <a href="{{ route('admin.gifts.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class='bx bx-arrow-back'></i> Back to Gifts
                    </a>
                </div>
                <div class="card-body">
                    @include('admin.gifts._form', ['gift' => $gift])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
