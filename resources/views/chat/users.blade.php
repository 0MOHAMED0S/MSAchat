@extends('layouts.app')

@section('content')
    <h4 class="fw-bold mb-4">Friends List</h4>

    <ul class="list-group list-group-flush" id="users-list" style="border-radius: 12px; overflow: hidden;">
        @foreach($users as $user)
            <li class="list-group-item d-flex justify-content-between align-items-center py-3 user-item"
                id="user-{{ $user->id }}"
                style="cursor: pointer; transition: 0.2s;"
                onclick="window.location='{{ route('chat.show', $user->id) }}'">

                <div class="d-flex align-items-center">
                    <!-- Avatar with status dot -->
                    <div class="position-relative me-3">
                        <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/48?u='.$user->id }}"
                             class="rounded-circle shadow-sm" width="48" height="48">
                        <span class="position-absolute bottom-0 end-0 translate-middle p-1 border border-white rounded-circle"
                              id="status-{{ $user->id }}"
                              style="background:gray; width:12px; height:12px;"></span>
                    </div>

                    <!-- User info -->
                    <div>
                        <span class="fw-semibold">{{ $user->name }}</span>
                    </div>
                </div>

                <!-- Chat button -->
                <a href="{{ route('chat.show', $user->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chat-dots"></i> Chat
                </a>
            </li>
        @endforeach
    </ul>
@endsection

@section('scripts')
<script type="module">
    // current user id
    let myId = parseInt("{{ auth()->id() }}");

    // presence channel for online/offline users
    Echo.join('online-users')
        .here((users) => {
            users.forEach(user => {
                let el = document.getElementById(`status-${user.id}`);
                if (el) el.style.background = 'limegreen';
            });
        })
        .joining((user) => {
            let el = document.getElementById(`status-${user.id}`);
            if (el) el.style.background = 'limegreen';
        })
        .leaving((user) => {
            let el = document.getElementById(`status-${user.id}`);
            if (el) el.style.background = 'gray';
        });
</script>

<style>
/* Hover effect */
.user-item:hover {
    background-color: rgba(106, 17, 203, 0.08);
}
</style>
@endsection
