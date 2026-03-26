@extends('admin.layouts.app')

@section('title', 'View Challenge: ' . $challenge->title)

@push('styles')
<style>
    .challenge-image { max-width: 300px; max-height: 300px; border-radius: 8px; }
    .detail-label { font-weight: 600; color: #566a7f; }
    .detail-value { margin-bottom: 1rem; }
    .badge { font-size: 0.9em; padding: 0.5em 0.8em; }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Challenge Details</h5>
                    <div>
                        <a href="{{ route('admin.challenges.index') }}" class="btn btn-label-secondary me-2">
                            <i class='bx bx-arrow-back me-1'></i> Back
                        </a>
                        <a href="{{ route('admin.challenges.edit', $challenge) }}" class="btn btn-primary">
                            <i class='bx bx-edit me-1'></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h4>{{ $challenge->title }}</h4>
                                <div class="d-flex gap-2 mb-3">
                                    @php
                                        $typeLabels = [
                                            'daily' => ['label' => 'Daily', 'class' => 'bg-label-info'],
                                            'weekly' => ['label' => 'Weekly', 'class' => 'bg-label-primary'],
                                            'monthly' => ['label' => 'Monthly', 'class' => 'bg-label-success'],
                                            'one_time' => ['label' => 'One Time', 'class' => 'bg-label-secondary']
                                        ];
                                        $type = $typeLabels[$challenge->type] ?? ['label' => ucfirst($challenge->type), 'class' => 'bg-label-secondary'];
                                        $statusLabels = [
                                            'draft' => ['label' => 'Draft', 'class' => 'bg-label-secondary'],
                                            'active' => ['label' => 'Active', 'class' => 'bg-label-success'],
                                            'completed' => ['label' => 'Completed', 'class' => 'bg-label-info'],
                                            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-label-danger']
                                        ];
                                        $status = $statusLabels[$challenge->status] ?? ['label' => ucfirst($challenge->status), 'class' => 'bg-label-secondary'];
                                    @endphp
                                    <span class="badge {{ $type['class'] }}">{{ $type['label'] }}</span>
                                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    @if($challenge->is_featured)
                                        <span class="badge bg-warning">Featured</span>
                                    @endif
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="detail-label">Start Date</p>
                                        <p class="detail-value">{{ $challenge->start_date->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="detail-label">End Date</p>
                                        <p class="detail-value">{{ $challenge->end_date->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="detail-label">Target Count</p>
                                        <p class="detail-value">{{ number_format($challenge->target_count) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="detail-label">Reward Points</p>
                                        <p class="detail-value">{{ number_format($challenge->reward_points) }}</p>
                                    </div>
                                </div>
                                
                                @if($challenge->description)
                                <div class="mb-3">
                                    <p class="detail-label">Description</p>
                                    <div class="detail-value">{!! $challenge->description !!}</div>
                                </div>
                                @endif
                                
                                @if($challenge->rules)
                                <div class="mb-3">
                                    <p class="detail-label">Rules</p>
                                    <div class="detail-value">{!! $challenge->rules !!}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    @if($challenge->image)
                                        <img src="{{ ('https://duos.webvibeinfotech.in/storage/app/public/' . $challenge->image) }}" alt="{{ $challenge->title }}" class="img-fluid challenge-image">
                                    @else
                                        <div class="bg-light p-5 text-center rounded">
                                            <i class='bx bx-image text-muted' style="font-size: 3rem;"></i>
                                            <p class="mt-2 mb-0">No image</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Stats</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Participants:</span>
                                        <span class="fw-semibold">{{ number_format($challenge->participants_count) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Created:</span>
                                        <span>{{ $challenge->created_at->format('M d, Y') }}</span>
                                    </div>
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
