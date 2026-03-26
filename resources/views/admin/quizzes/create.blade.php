@extends('admin.layouts.app')

@section('title', 'Create New Quiz')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <nav aria-label="breadcrumb" class="d-flex justify-content-between align-items-center mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.index') }}">Competitions</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.quizzes.index', $competition) }}">{{ $competition->title }} - Quizzes</a>
            </li>
            <li class="breadcrumb-item active">Create New Quiz</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Create New Quiz</h5>
            <p class="mb-0">Fill in the details below to create a new quiz for {{ $competition->title }}</p>
        </div>
    </div>

    @include('admin.quizzes.form')
</div>
@endsection
