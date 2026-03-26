@extends('admin.layouts.app')

@section('title', 'Competitions')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<style>
    .competition-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    .badge {
        font-size: 0.8em;
        font-weight: 500;
    }
    .participant-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        margin-left: -10px;
        transition: transform 0.2s;
    }
    .participant-avatar:hover {
        z-index: 10;
        transform: scale(1.2);
    }
    .participant-avatar:first-child {
        margin-left: 0;
    }
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Competitions</h5>
            <a href="{{ route('admin.competitions.create') }}" class="btn btn-primary">
                <i class='bx bx-plus me-1'></i> Add Competition
            </a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-competitions table border-top">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Participants</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competitions as $competition)
                    <tr>
                        <td>
                            @if($competition->image)
                                <img src="{{ ('https://duos.webvibeinfotech.in/storage/app/public/' . $competition->image) }}" alt="{{ $competition->title }}" class="competition-image">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 60px; height: 60px;">
                                    <i class='bx bx-trophy text-warning' style="font-size: 1.5rem;"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $competition->title }}</span>
                                @if($competition->is_featured)
                                    <span class="badge bg-label-primary mt-1" style="width: fit-content;">Featured</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'solo' => 'bg-label-info',
                                    'team' => 'bg-label-success',
                                    'tournament' => 'bg-label-warning',
                                    'league' => 'bg-label-danger'
                                ];
                                $typeNames = [
                                    'solo' => 'Solo',
                                    'team' => 'Team',
                                    'tournament' => 'Tournament',
                                    'league' => 'League'
                                ];
                                
                                // Get the participant type if available, otherwise use competition type
                                $participantType = $competition->participants->first()->pivot->type ?? $competition->type;
                            @endphp
                            <span class="badge {{ $typeColors[$participantType] ?? 'bg-label-secondary' }}">
                                {{ $typeNames[$participantType] ?? ucfirst($participantType) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-label-secondary',
                                    'registered' => 'bg-label-info',
                                    'approved' => 'bg-label-success',
                                    'rejected' => 'bg-label-danger',
                                    'completed' => 'bg-label-primary',
                                    'disqualified' => 'bg-label-dark'
                                ];
                                
                                // Get the participant status if available, otherwise use competition status
                                $participantStatus = $competition->participants->first()->pivot->status ?? $competition->status;
                            @endphp
                            <span class="badge {{ $statusColors[$participantStatus] ?? 'bg-label-secondary' }}">
                                {{ ucfirst($participantStatus) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-group">
                                    @foreach($competition->participants->take(3) as $participant)
                                        <div class="avatar-container" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $participant->name }}">
                                            <img src="{{ $participant->avatar ? asset('storage/' . $participant->avatar) : asset('assets/img/avatars/1.png') }}" 
                                                 class="participant-avatar" 
                                                 alt="{{ $participant->name }}">
                                        </div>
                                    @endforeach
                                    @if($competition->participants_count > 3)
                                        <div class="avatar-container">
                                            <span class="participant-avatar bg-label-primary d-flex align-items-center justify-content-center">
                                                +{{ $competition->participants_count - 3 }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <span class="ms-2">{{ $competition->participants_count }} {{ Str::plural('participant', $competition->participants_count) }}</span>
                            </div>
                        </td>
                        <td>{{ $competition->start_date->format('M j, Y h:i A') }}</td>
                        <td>{{ $competition->end_date->format('M j, Y h:i A') }}</td>
                        <td>
                            <x-admin.action-buttons
                                view-route="{{ route('admin.competitions.show', $competition) }}"
                                edit-route="{{ route('admin.competitions.edit', $competition) }}"
                                delete-route="{{ route('admin.competitions.destroy', $competition) }}"
                                delete-confirm-message="Are you sure you want to delete this competition? This action cannot be undone."
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($competitions->hasPages() && !request()->has('draw'))
            <div class="card-footer">
                <x-pagination :paginator="$competitions" />
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/libs/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('vendor/libs/datatables-responsive/datatables.responsive.js') }}"></script>

<script>
    // Only initialize DataTables if not already initialized by server-side processing
    @if(!request()->has('draw'))
    $(document).ready(function() {
        $('.datatables-competitions').DataTable({
            responsive: true,
            paging: false, // Disable DataTables pagination since we're using our own
            info: false,   // Disable DataTables info since we're using our own
            searching: true,
            order: [[5, 'desc']], // Default sort by start date
            columnDefs: [
                { orderable: false, targets: [0, 7] }, // Disable sorting for image and actions columns
                { searchable: false, targets: [0, 3, 4, 7] } // Disable search for certain columns
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No entries found',
                infoFiltered: '(filtered from _MAX_ total entries)',
                zeroRecords: 'No matching records found',
                paginate: false
            }
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    @endif
</script>
@endpush
