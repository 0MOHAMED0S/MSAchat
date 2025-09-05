@extends('layouts.app')

@section('content')
    <h4 class="fw-bold mb-4">Users List</h4>

    <!-- Search input -->
    <div class="mb-3">
        <input type="text" id="search-users" class="form-control" placeholder="Search friends by name...">
    </div>

    <!-- Scrollable Users list -->
    <div style="height: calc(5 * 72px); /* 5 items * approx height each item */
                overflow-y: auto; border-radius: 12px; border: 1px solid #e0e0e0;">
        <ul class="list-group list-group-flush" id="users-list" style="border-radius: 12px; overflow: hidden;">
            @foreach($users as $user)
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 user-item"
                    id="user-{{ $user->id }}"
                    style="cursor: pointer; transition: 0.2s;"
                    onclick="window.location='{{ route('chat.show', $user->id) }}'">

                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/48?u='.$user->id }}"
                                 class="rounded-circle shadow-sm" width="48" height="48">
                            <span class="position-absolute bottom-0 end-0 translate-middle p-1 border border-white rounded-circle"
                                  id="status-{{ $user->id }}"
                                  style="background:gray; width:12px; height:12px;"></span>
                        </div>

                        <div>
                            <span class="fw-semibold">{{ $user->name }}</span>
                        </div>
                    </div>

                    <a href="{{ route('chat.show', $user->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-chat-dots"></i> Chat
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection

@section('scripts')
<script type="module">
    let myId = parseInt("{{ auth()->id() }}");

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

    const searchInput = document.getElementById('search-users');
    const usersList = document.getElementById('users-list');

    searchInput.addEventListener('keyup', function() {
        const query = this.value.trim();

        fetch(`{{ route('chat.search') }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(users => {
                usersList.innerHTML = '';

                if (users.length === 0) {
                    usersList.innerHTML = '<li class="list-group-item text-center text-muted">No users found</li>';
                    return;
                }

                users.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center py-3 user-item';
                    li.style.cursor = 'pointer';
                    li.style.transition = '0.2s';
                    li.onclick = () => window.location = `/chat/${user.id}`;

                    li.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="position-relative me-3">
                                <img src="${user.avatar || 'https://i.pravatar.cc/48?u='+user.id}"
                                     class="rounded-circle shadow-sm" width="48" height="48">
                                <span class="position-absolute bottom-0 end-0 translate-middle p-1 border border-white rounded-circle"
                                      id="status-${user.id}" style="background:gray; width:12px; height:12px;"></span>
                            </div>
                            <div>
                                <span class="fw-semibold">${user.name}</span>
                            </div>
                        </div>
                        <a href="/chat/${user.id}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-chat-dots"></i> Chat
                        </a>
                    `;
                    usersList.appendChild(li);
                });
            });
    });
</script>

<style>
.user-item:hover {
    background-color: rgba(106, 17, 203, 0.08);
}
</style>
@endsection
