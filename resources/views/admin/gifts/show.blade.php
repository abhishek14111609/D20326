@extends('admin.layouts.app')

@section('title', 'Gift Details: ' . $gift->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gift Details: {{ $gift->name }}</h5>
            <a href="{{ route('admin.gifts.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class='bx bx-arrow-back'></i> Back to Gifts
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <img src="{{ $gift->image_path ? ('https://duos.webvibeinfotech.in/storage/app/public/' . $gift->image_path) : asset('assets/img/avatars/default-avatar.png') }}" alt="{{ $gift->name }}" class="img-thumbnail" style="width: 300px; height: 300px; object-fit: cover;">
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Gift Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $gift->name }}</td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>${{ number_format($gift->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $gift->category->name ?? 'Uncategorized' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $gift->is_active ? 'success' : 'secondary' }}">
                                            {{ $gift->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $gift->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $gift->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Description</h6>
                        </div>
                        <div class="card-body">
                            {!! $gift->description ? nl2br(e($gift->description)) : '<p class="text-muted">No description provided.</p>' !!}
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Gift Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="text-center">
                                            <h3 class="mb-0">{{ $totalGiftsSent ?? "" }}</h3>
                                            <small class="text-muted">Total Sent</small>
                                        </div>
                                        <div class="text-center">
                                            <h3 class="mb-0">${{ number_format($totalRevenue, 2) }}</h3>
                                            <small class="text-muted">Total Revenue</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.gifts.edit', $gift) }}" class="btn btn-warning">
                                            <i class='bx bxs-edit-alt'></i> Edit Gift
                                        </a>
                                        <form action="{{ route('admin.gifts.destroy', $gift) }}" method="POST" class="d-grid">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this gift?')">
                                                <i class='bx bxs-trash'></i> Delete Gift
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Senders</h6>
                                </div>
                                <div class="card-body">
                                    @if($topSenders->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th class="text-end">Sent</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topSenders as $sender)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-sm me-2">
                                                                        <img src="{{ $sender->profile_photo_url }}" 
                                                                             alt="{{ $sender->name }}" 
                                                                             class="rounded-circle"
                                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold">{{ $sender->name }}</div>
                                                                        <small class="text-muted">{{ $sender->email }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">{{ $sender->gift_count }} times</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No senders found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Receivers</h6>
                                </div>
                                <div class="card-body">
                                    @if($topReceivers->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th class="text-end">Received</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topReceivers as $receiver)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-sm me-2">
                                                                        <img src="{{ $receiver->profile_photo_url }}" 
                                                                             alt="{{ $receiver->name }}" 
                                                                             class="rounded-circle"
                                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold">{{ $receiver->name }}</div>
                                                                        <small class="text-muted">{{ $receiver->email }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">{{ $receiver->received_count }} times</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No receivers found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Recent Gift Activity</h6>
                        </div>
                        <div class="card-body">
                            @if($gift->userGifts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Sender</th>
                                                <th>Receiver</th>
                                                <th class="text-end">Amount</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($gift->userGifts as $userGift)
                                                <tr>
                                                    <td>{{ $userGift->created_at->format('M d, Y h:i A') }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-xs me-2">
                                                                <img src="{{ $userGift->sender->profile_photo_url }}" 
                                                                     alt="{{ $userGift->sender->name }}" 
                                                                     class="rounded-circle"
                                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                                            </div>
                                                            <span>{{ $userGift->sender->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-xs me-2">
                                                                <img src="{{ $userGift->receiver->profile_photo_url }}" 
                                                                     alt="{{ $userGift->receiver->name }}" 
                                                                     class="rounded-circle"
                                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                                            </div>
                                                            <span>{{ $userGift->receiver->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">${{ number_format($userGift->price, 2) }}</td>
                                                    <td>{{ $userGift->message ?: 'No message' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No gift activity found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.gifts.edit', $gift) }}" class="btn btn-warning">
                    <i class='bx bxs-edit-alt'></i> Edit Gift
                </a>
                <a href="{{ route('admin.gifts.index') }}" class="btn btn-secondary">
                    <i class='bx bx-arrow-back'></i> Back to Gifts
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
