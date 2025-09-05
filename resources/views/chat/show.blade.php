@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Chat Header -->
    <div class="d-flex align-items-center mb-3 p-2 rounded shadow-sm bg-white">
        <img src="{{ $receiver->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($receiver->name) . '&background=random' }}"
             alt="{{ $receiver->name }}"
             class="rounded-circle me-3"
             style="width:50px; height:50px; object-fit:cover;">
        <div>
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
    <div id="messages" class="border p-2 mb-2 bg-light"
         style="height:auto; max-height:400px; overflow-y:auto; border-radius:12px; display:flex; flex-direction:column-reverse;">
    </div>

    <!-- Chat Input -->
    <div class="input-group mt-2">
        <input type="text" id="message" class="form-control" placeholder="Type a message...">
        <button id="send" class="btn btn-primary">
            <i class="bi bi-send"></i>
        </button>
    </div>
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

let currentChatSubscription = null;

// Fix browser back/forward restoring old state
window.addEventListener("pageshow", function (event) {
    if (event.persisted || (performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
        window.location.reload();
    }
});

// Utilities
function formatDate(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function scrollToBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

// Render message
function renderMessage(msg, isTemp = false, prepend = false) {
    const senderId = Number(msg.sender?.id ?? msg.sender_id ?? 0);
    let isMine = senderId === myId;
    let ticks = isMine ? (msg.is_read ? "✓✓" : "✓") : "";
    let bubbleClass = isMine ? "bg-primary text-white align-self-end" : "bg-white border align-self-start";
    let alignClass = isMine ? "text-end" : "text-start";

    let wrapper = document.createElement("div");
    wrapper.className = `d-flex flex-column ${alignClass} mb-2`;
    wrapper.id = `msg-${msg.id}`;

    wrapper.innerHTML = `
        <div class="p-2 px-3 ${bubbleClass} rounded-3 shadow-sm message-bubble"
             style="max-width:75%; word-wrap:break-word;">
            ${msg.message}
        </div>
        <div class="small text-muted mt-1">
            ${formatDate(msg.created_at)} ${isMine ? `<span id="ticks-${msg.id}">${ticks}</span>` : ""}
        </div>
    `;

    if (isTemp) wrapper.classList.add("opacity-50");

    if (prepend) messagesEl.prepend(wrapper);
    else messagesEl.appendChild(wrapper);

    // Keep only last 7 messages
    const allMsgs = messagesEl.querySelectorAll(".d-flex.flex-column");
    if (allMsgs.length > 7) {
        for (let i = 0; i < allMsgs.length - 7; i++) allMsgs[i].remove();
    }

    scrollToBottom();
}

// Load history - last 7 messages
const history = @json($messages ?? []);
history.slice(-7).forEach(m => renderMessage(m));
scrollToBottom();

// Typing subscription
function subscribeToTyping() {
    Echo.private(`typing.${myId}`)
        .listen('.UserTyping', (e) => {
            if (e.senderId === partnerId) {
                typingEl.style.display = e.isTyping ? "inline" : "none";
            }
        });
}

// Chat subscription
function subscribeToChat(convoId) {
    if (!convoId) return;
    if (currentChatSubscription && Number(currentChatSubscription) === Number(convoId)) return;

    if (currentChatSubscription && Number(currentChatSubscription) !== Number(convoId)) {
        try { Echo.leave(`chat.${currentChatSubscription}`); } catch {}
    }

    currentChatSubscription = Number(convoId);

    Echo.private(`chat.${convoId}`)
        .listen('MessageSent', (e) => {
            if (Number(e.message.sender?.id ?? e.message.sender_id ?? 0) !== myId) {
                renderMessage(e.message);
            }
            markAsRead();
        })
        .listen('MessageRead', (e) => {
            const readerId = Number(e.readerId ?? e.reader?.id ?? 0);
            if (readerId === partnerId) {
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

// User channel subscription
function subscribeToUserChannel() {
    Echo.private(`user.${myId}`)
        .listen('NewPrivateMessage', (e) => {
            if (!e.message) return;
            if (conversationId && Number(e.message.conversation_id ?? 0) === Number(conversationId)) {
                renderMessage(e.message);
                markAsRead();
            }
        });
}

// Presence
Echo.join('online-users')
    .here(users => {
        statusEl.style.background = users.some(u => Number(u.id) === partnerId) ? 'green' : 'gray';
    })
    .joining(user => { if (Number(user.id) === partnerId) statusEl.style.background = 'green'; })
    .leaving(user => { if (Number(user.id) === partnerId) statusEl.style.background = 'gray'; });

// Boot subscriptions
subscribeToTyping();
subscribeToUserChannel();
if (conversationId) subscribeToChat(conversationId);

// Typing endpoint
function sendTyping(isTyping) {
    const url = `/chat/typing/${partnerId}`;
    const payload = JSON.stringify({ isTyping, _token: "{{ csrf_token() }}" });
    if (navigator.sendBeacon) {
        try { navigator.sendBeacon(url, new Blob([payload], { type: 'application/json' })); return; } catch {}
    }
    fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" }, body: payload, keepalive: true }).catch(()=>{});
}

input.addEventListener('focus', () => sendTyping(true));
input.addEventListener('blur', () => sendTyping(false));

// Send message
async function sendMessage() {
    let text = input.value.trim();
    if (!text) return;

    sendTyping(false);

    let tempId = "temp-" + Date.now();
    let tempMsg = { id: tempId, sender: { id: myId }, message: text, created_at: new Date().toISOString(), is_read: false };
    renderMessage(tempMsg, true);

    input.value = "";
    input.blur();

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
    } catch (err) {
        console.error("Send failed", err);
    }
}

document.getElementById("send").addEventListener("click", sendMessage);
input.addEventListener("keydown", e => { if (e.key === "Enter") { e.preventDefault(); sendMessage(); } });

// Mark as read
function markAsRead() {
    if (!conversationId) return;
    fetch(`/chat/${conversationId}/read`, {
        method: "POST",
        headers: {"Content-Type": "application/json","X-CSRF-TOKEN": "{{ csrf_token() }}"}
    }).catch(()=>{});
}
</script>

<style>
#messages {
    display: flex;
    flex-direction: column-reverse; /* newest at bottom */
    gap: 4px;
}
.message-bubble {
    font-size: 15px;
    line-height: 1.4;
}
.opacity-50 {
    opacity: 0.5;
}
</style>
@endsection
