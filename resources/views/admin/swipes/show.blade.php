@extends('admin.layouts.app')

@section('title', 'Swipe Details - #' . $swipe->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Swipe Details #{{ $swipe->id }}</h5>
                    <a href="{{ route('admin.swipes.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class='bx bx-arrow-back me-1'></i> Back to Swipes
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Swiper Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Swiper</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
										<img src="{{ $swipe->swiper->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $swipe->swiper->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                    alt="{{ $swipe->swiper->name }}" 
                                    class="rounded-circle" 
                                    style="width: 40px; height: 40px; object-fit: cover;"> 
                                       
                                        <div>
                                            <h5 class="mb-0">
                                                <a href="{{ route('admin.users.show', $swipe->swiper) }}">{{ $swipe->swiper->name }}</a>
                                            </h5>
                                            <p class="mb-0 text-muted">{{ $swipe->swiper->email }}</p>
                                            <span class="badge bg-{{ $swipe->swiper->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($swipe->swiper->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Swiped User Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Swiped User</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
										<img src="{{ $swipe->swiped->avatar ? ('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $swipe->swiped->avatar) : asset('assets/img/avatars/default-avatar.png') }}" 
                                    alt="{{ $swipe->swiped->name }}" 
                                    class="rounded-circle" 
                                    style="width: 40px; height: 40px; object-fit: cover;"> 
                                      
                                        <div>
                                            <h5 class="mb-0">
                                                <a href="{{ route('admin.users.show', $swipe->swiped) }}">{{ $swipe->swiped->name }}</a>
                                            </h5>
                                            <p class="mb-0 text-muted">{{ $swipe->swiped->email }}</p>
                                            <span class="badge bg-{{ $swipe->swiped->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($swipe->swiped->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Swipe Details -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Swipe Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th width="30%">Swipe ID</th>
                                                    <td>#{{ $swipe->id }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Match Status</th>
                                                    <td>
                                                        <span class="badge bg-{{ $swipe->matched ? 'success' : 'secondary' }}">
                                                            {{ $swipe->matched ? 'Matched' : 'Not Matched' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Swipe Direction</th>
                                                    <td>
                                                        <span class="badge bg-{{ $swipe->direction === 'right' ? 'primary' : 'secondary' }}">
                                                            Swiped {{ ucfirst($swipe->direction) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Swipe Date</th>
                                                    <td>{{ $activities['swiped_at'] }}</td>
                                                </tr>
                                                @if($swipe->matched)
                                                <tr>
                                                    <th>Match Date</th>
                                                    <td>{{ $activities['match_date'] }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        <form action="{{ route('admin.swipes.destroy', $swipe) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this swipe? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class='bx bx-trash me-1'></i> Delete Swipe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
