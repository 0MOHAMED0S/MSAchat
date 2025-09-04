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
    <div id="messages" class="border p-3 mb-2 bg-light"
         style="height:400px; overflow-y:auto; border-radius:12px; display:flex; flex-direction:column;">
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
let myId = parseInt("{{ auth()->id() }}");
let partnerId = parseInt("{{ $receiver->id }}");

const messagesEl = document.getElementById("messages");
const statusEl = document.getElementById(`status-${partnerId}`);
const input = document.getElementById("message");
const typingEl = document.getElementById("typing-indicator");

// --- Format date ---
function formatDate(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

// --- Render message ---
function renderMessage(msg, isTemp = false) {
    let isMine = msg.sender.id == myId;
    let ticks = isMine ? (msg.is_read ? "✓✓" : "✓") : "";
    let bubbleClass = isMine ? "bg-primary text-white align-self-end" : "bg-white border align-self-start";
    let alignClass = isMine ? "text-end" : "text-start";

    let wrapper = document.createElement("div");
    wrapper.className = `d-flex flex-column ${alignClass} mb-3`;
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
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

// --- Load history ---
(@json($messages ?? [])).forEach(m => renderMessage(m));
messagesEl.scrollTop = messagesEl.scrollHeight;

// --- Real-time messaging ---
if (conversationId) {
    Echo.private(`chat.${conversationId}`)
        .listen("MessageSent", (e) => {
            if (e.message.sender.id !== myId) renderMessage(e.message);
            markAsRead();
        })
        .listen("MessageRead", (e) => {
            if (e.readerId === partnerId) {
                document.querySelectorAll("[id^='ticks-']").forEach(t => t.textContent = "✓✓");
            }
        });

    // Typing indicator from server
    Echo.private(`typing.${myId}`)
        .listen(".UserTyping", (e) => {
            typingEl.style.display = e.isTyping ? "inline" : "none";
        });

    markAsRead();
}

// --- Typing indicator (focus only, independent of sending) ---
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

// --- Send message instantly, independent of typing ---
async function sendMessage() {
    let text = input.value.trim();
    if (!text) return;

    sendTyping(false); // stop typing immediately

    let tempId = "temp-" + Date.now();
    let tempMsg = { id: tempId, sender: { id: myId }, message: text, created_at: new Date().toISOString(), is_read: false };
    renderMessage(tempMsg, true);

    input.value = "";
    input.blur(); // remove focus immediately

    fetch(conversationId ? `/chat/${conversationId}/send` : `/chat/send`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ message: text, receiver_id: partnerId })
    })
    .then(res => res.json())
    .then(data => {
        if (!conversationId && data.conversation_id) conversationId = data.conversation_id;
        if (data.message) {
            let tempEl = document.getElementById(`msg-${tempId}`);
            if (tempEl) tempEl.remove();
            renderMessage(data.message);
        }
    }).catch(err => console.error("Send failed", err));
}

document.getElementById("send").addEventListener("click", sendMessage);
input.addEventListener("keydown", e => { if (e.key === "Enter") { e.preventDefault(); sendMessage(); } });

// --- Mark as read ---
function markAsRead() {
    if (!conversationId) return;
    fetch(`/chat/${conversationId}/read`, { method: "POST", headers: {"Content-Type": "application/json","X-CSRF-TOKEN": "{{ csrf_token() }}"} }).catch(()=>{});
}

// --- Online/offline ---
Echo.join('online-users')
    .here(users => { statusEl.style.background = users.some(u => u.id === partnerId) ? 'green' : 'gray'; })
    .joining(user => { if (user.id === partnerId) statusEl.style.background = 'green'; })
    .leaving(user => { if (user.id === partnerId) statusEl.style.background = 'gray'; });

</script>

<style>
.message-bubble { font-size: 15px; line-height: 1.4; }
.opacity-50 { opacity: 0.5; }
</style>
@endsection
