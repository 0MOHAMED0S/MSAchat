@extends('layouts.app')

@section('content')
<div class="d-flex flex-column vh-100 p-2" style="gap:0.5rem;">
    <!-- Chat Header -->
    <div class="d-flex align-items-center p-2 rounded shadow-sm bg-white flex-shrink-0">
        <img src="{{ $receiver->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($receiver->name) . '&background=random' }}"
             alt="{{ $receiver->name }}"
             class="rounded-circle me-3"
             style="width:50px; height:50px; object-fit:cover;">
        <div class="flex-grow-1">
            <h5 class="fw-bold mb-0 d-flex align-items-center">
                <span id="partner-name">{{ $receiver->name }}</span>
                <span class="status-dot ms-2" id="status-{{ $receiver->id }}"
                      style="width:12px; height:12px; border-radius:50%; display:inline-block; background:gray;"></span>
            </h5>
            <small id="typing-indicator" class="text-muted fst-italic" style="display:none;">
                typing...
            </small>
        </div>
    </div>

    <!-- Chat Messages Box -->
    <div id="messages" class="flex-grow-1 border p-3 bg-light rounded-3 overflow-auto d-flex flex-column"
         style="gap:0.5rem;">
    </div>

    <!-- Chat Input -->
    <form id="chat-form" class="input-group flex-shrink-0">
        <input type="text" id="message" class="form-control rounded-start" placeholder="Type a message..." autocomplete="off">
        <button id="send" class="btn btn-primary rounded-end">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>
@endsection

@section('scripts')
<script type="module">
let conversationId = @json($conversation->id ?? null);
const myId = Number(@json(auth()->id()));
const partnerId = Number(@json($receiver->id));

const messagesEl = document.getElementById("messages");
const statusEl = document.getElementById(`status-${partnerId}`);
const input = document.getElementById("message");
const typingEl = document.getElementById("typing-indicator");
const chatForm = document.getElementById("chat-form");

let currentChatSubscription = null;

function scrollToBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function formatDate(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function renderMessage(msg, isTemp = false) {
    const senderId = Number(msg.sender?.id ?? msg.sender_id ?? 0);
    let isMine = senderId === myId;
    let ticks = isMine ? (msg.is_read ? "✓✓" : "✓") : "";
    let bubbleClass = isMine ? "bg-primary text-white align-self-end" : "bg-white border align-self-start";
    let alignClass = isMine ? "text-end" : "text-start";

    let wrapper = document.createElement("div");
    wrapper.className = `d-flex flex-column ${alignClass} mb-2`;
    wrapper.id = `msg-${msg.id}`;

    wrapper.innerHTML = `
        <div class="p-2 px-3 ${bubbleClass} rounded-3 shadow-sm message-bubble" style="max-width:75%; word-wrap:break-word;">
            ${msg.message}
        </div>
        <div class="small text-muted mt-1">
            ${formatDate(msg.created_at)} ${isMine ? `<span id="ticks-${msg.id}">${ticks}</span>` : ""}
        </div>
    `;
    if (isTemp) wrapper.classList.add("opacity-50");
    messagesEl.appendChild(wrapper);
    scrollToBottom();
}

// Load history
(@json($messages ?? [])).forEach(m => renderMessage(m));
scrollToBottom();

// Typing indicator
Echo.private(`typing.${myId}`)
    .listen('.UserTyping', e => {
        if (e.senderId === partnerId) typingEl.style.display = e.isTyping ? "inline" : "none";
    });

// Chat subscription
function subscribeToChat(convoId) {
    if (!convoId) return;
    if (currentChatSubscription === convoId) return;

    if (currentChatSubscription && currentChatSubscription !== convoId) {
        try { Echo.leave(`chat.${currentChatSubscription}`); } catch {}
    }

    currentChatSubscription = convoId;

    Echo.private(`chat.${convoId}`)
        .listen('MessageSent', e => {
            if (Number(e.message.sender?.id ?? e.message.sender_id ?? 0) !== myId) {
                renderMessage(e.message);
            }
            markAsRead();
        })
        .listen('MessageRead', e => {
            if (Number(e.readerId ?? e.reader?.id ?? 0) === partnerId) {
                if (e.message_id) {
                    const tickEl = document.getElementById(`ticks-${e.message_id}`);
                    if (tickEl) tickEl.textContent = "✓✓";
                } else {
                    document.querySelectorAll("[id^='ticks-']").forEach(t => t.textContent = "✓✓");
                }
            }
        });

    markAsRead();
}

// Presence
Echo.join('online-users')
    .here(users => {
        statusEl.style.background = users.some(u => Number(u.id) === partnerId) ? 'green' : 'gray';
    })
    .joining(user => { if (Number(user.id) === partnerId) statusEl.style.background = 'green'; })
    .leaving(user => { if (Number(user.id) === partnerId) statusEl.style.background = 'gray'; });

// Typing endpoint
function sendTyping(isTyping) {
    fetch(`/chat/typing/${partnerId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        body: JSON.stringify({ isTyping })
    }).catch(()=>{});
}

let typingTimeout;
input.addEventListener('input', () => {
    sendTyping(true);
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => sendTyping(false), 2000);
});

// Send message
chatForm.addEventListener('submit', async e => {
    e.preventDefault();
    let text = input.value.trim();
    if (!text) return;
    sendTyping(false);

    let tempId = "temp-" + Date.now();
    renderMessage({id: tempId, sender: {id: myId}, message: text, created_at: new Date().toISOString(), is_read:false}, true);

    input.value = "";
    scrollToBottom();

    try {
        let res = await fetch(conversationId ? `/chat/${conversationId}/send` : `/chat/send`, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ message: text, receiver_id: partnerId })
        });
        let data = await res.json();

        if (!conversationId && data.conversation_id) {
            conversationId = data.conversation_id;
            subscribeToChat(conversationId);
        }

        if (data.message) {
            let tempEl = document.getElementById(`msg-${tempId}`);
            if (tempEl) tempEl.remove();
            renderMessage(data.message);
        }
    } catch (err) { console.error(err); }
});

// Mark as read
function markAsRead() {
    if (!conversationId) return;
    fetch(`/chat/${conversationId}/read`, { method:'POST', headers:{'X-CSRF-TOKEN': "{{ csrf_token() }}"} });
}

// Boot
subscribeToChat(conversationId);

</script>

<style>
/* Professional Mobile Styles */
.message-bubble { font-size: 15px; line-height: 1.4; }
.opacity-50 { opacity: 0.5; }
#messages::-webkit-scrollbar { width: 6px; }
#messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius:3px; }
@media (max-width: 768px) {
    .vh-100 { height: 100vh !important; }
    #messages { padding-bottom: 0.5rem; }
}
</style>
@endsection
