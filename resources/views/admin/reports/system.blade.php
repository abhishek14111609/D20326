@extends('admin.layouts.app')

@section('title', 'System Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Reports</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <!-- Storage Usage -->
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="card-title m-0 me-2">Storage Usage</h5>
                                    <div class="dropdown
                                    <button
                                        class="btn p-0"
                                        type="button"
                                        id="storageOptions"
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class='bx bx-dots-vertical-rounded'></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="storageOptions">
                                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Detailed Report</a>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Used: {{ $reports['storage_usage']['used'] }}</span>
                                            <span>Total: {{ $reports['storage_usage']['total'] }}</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                style="width: {{ $reports['storage_usage']['percentage'] }}%" 
                                                aria-valuenow="{{ $reports['storage_usage']['percentage'] }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="text-muted text-end mt-1">
                                            {{ $reports['storage_usage']['free'] }} free
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Statistics -->
                        <div class="col-md-6 col-xl-8">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">System Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-0">Total Matches</h6>
                                                        <p class="text-muted mb-0">All time</p>
                                                    </div>
                                                    <div class="avatar">
                                                        <div class="avatar-initial bg-label-primary rounded">
                                                            <i class='bx bx-heart'></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="mt-3 mb-0">{{ number_format($reports['total_matches']) }}</h4>
                                            </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="mb-0">Messages Today</h6>
                                                            <p class="text-muted mb-0">Last 24 hours</p>
                                                        </div>
                                                        <div class="avatar">
                                                            <div class="avatar-initial bg-label-success rounded">
                                                                <i class='bx bx-message-dots'></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h4 class="mt-3 mb-0">{{ number_format($reports['messages_today']) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="mb-0">Active Sessions</h6>
                                                            <p class="text-muted mb-0">Last 5 minutes</p>
                                                        </div>
                                                        <div class="avatar">
                                                            <div class="avatar-initial bg-label-warning rounded">
                                                                <i class='bx bx-user-check'></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h4 class="mt-3 mb-0">{{ number_format($reports['active_sessions']) }}</h4>
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
        </div>
    </div>
</div>
@endsection
