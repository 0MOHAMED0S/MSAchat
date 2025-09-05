@extends('layouts.app')

@section('content')
<h4 class="fw-bold mb-4">Chats</h4>

@if($users->isEmpty())
    <div id="empty-state" class="text-center py-5">
        <i class="bi bi-search fs-1 text-muted mb-3"></i>
        <h6 class="fw-semibold text-muted">No chats yet</h6>
        <p class="text-muted mb-3">Start by searching for friends to chat with.</p>
        <a href="{{ route('chat.users') }}" class="btn btn-outline-primary">
            <i class="bi bi-person-plus me-2"></i> Find Friends
        </a>
    </div>
    <ul class="list-group list-group-flush d-none" id="users-list" style="border-radius: 12px; overflow: hidden;"></ul>
@else
    <!-- Scrollable container for 5 chats -->
    <div style="height: calc(5 * 72px); overflow-y: auto; border-radius: 12px; border: 1px solid #e0e0e0;">
        <ul class="list-group list-group-flush" id="users-list" style="border-radius: 12px; overflow: hidden;">
            @foreach ($users as $user)
                @php
                    $lastMessage = $user->last_message ?? null;
                    $unreadCount = $user->unread_count ?? 0;
                @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 user-item"
                    id="user-{{ $user->id }}"
                    style="cursor: pointer; transition: 0.2s;"
                    onclick="window.location='{{ route('chat.show', $user->id) }}'">

                    <div class="d-flex align-items-center">
                        <!-- Avatar with status dot -->
                        <div class="position-relative me-3">
                            <img src="{{ $user->avatar }}" class="rounded-circle shadow-sm" width="48" height="48">
                            <span class="position-absolute bottom-0 end-0 translate-middle p-1 border border-white rounded-circle"
                                  id="status-{{ $user->id }}"
                                  style="background:gray; width:12px; height:12px;"></span>
                        </div>

                        <!-- User info -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ $user->name }}</span>
                                @if($lastMessage)
                                    <small class="text-muted ms-2" id="last-message-date-{{ $user->id }}">
                                        {{ $lastMessage->created_at->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between">
                                <small id="last-message-{{ $user->id }}"
                                       class="{{ $unreadCount > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                                    @if ($lastMessage)
                                        {{ $lastMessage->sender_id === Auth::id() ? 'You' : $lastMessage->sender->name }}:
                                        {{ $lastMessage->message }}
                                    @else
                                        <em class="text-secondary">No messages yet</em>
                                    @endif
                                </small>
                                <small class="ms-1 text-purple fst-italic typing-status" style="display:none;">
                                    typing...
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Right side: unread badge -->
                    <div class="text-end">
                        @if ($unreadCount > 0)
                            <span id="unread-badge-{{ $user->id }}"
                                  class="badge rounded-pill bg-danger">
                                  {{ $unreadCount }}
                            </span>
                        @else
                            <span id="unread-badge-{{ $user->id }}"
                                  class="badge rounded-pill bg-danger"
                                  style="display:none;">
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection

@section('scripts')
<script type="module">
// --- Your existing JS here ---
</script>

<style>
.user-item:hover {
    background-color: rgba(106, 17, 203, 0.08);
}
.text-purple {
    color: #6a11cb !important;
}
</style>
@endsection
