@extends('admin.layouts.app')

@section('title', 'Chats')

@section('content')

@section('title', 'Chats Management')

@section('content_header')
    <h1>Chats Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Chats</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chats as $chat)
                    <tr>
                        <td>{{ $chat->id }}</td>
                        <td>{{ $chat->sender->name ?? 'N/A' }}</td>
                        <td>{{ $chat->receiver->name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($chat->message, 50) }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ ucfirst($chat->message_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $chat->is_read ? 'success' : 'warning' }}">
                                {{ $chat->is_read ? 'Read' : 'Unread' }}
                            </span>
                        </td>
                        <td>{{ $chat->sent_at ? $chat->sent_at->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No chats found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($chats->hasPages())
        <div class="mt-3">
            {{ $chats->links() }}
        </div>
        @endif
    </div>
</div>
@stop
