@extends('layouts.qc-admin')
@section('title', 'Energy Chatbot')

@section('content')
@php
    $chatbotStats = $chatbotStats ?? [
        'total_facilities' => 0,
        'active_meters' => 0,
        'records_this_year' => 0,
        'high_alerts' => 0,
    ];
@endphp

<style>
    .chatbot-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 20px;
        height: 85vh;
        padding: 20px;
    }

    .chatbot-messages {
        background: #f0f2f5;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(31, 38, 135, 0.06);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chatbot-header {
        background: #ffffff;
        color: #1f2937;
        padding: 18px 20px;
        font-weight: 700;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .chatbot-header i {
        font-size: 1.3rem;
    }

    .messages-area {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background: #e5e7eb;
    }

    .message {
        display: flex;
        width: 100%;
        margin-bottom: 0;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message.user {
        justify-content: flex-end;
    }

    .message.bot {
        justify-content: flex-start;
    }

    .message > div {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: flex-start;
        max-width: 100%;
    }

    .message.user > div {
        align-items: flex-end;
    }

    .message-bubble {
        display: inline-block;
        max-width: min(70%, 520px);
        min-width: 90px;
        width: auto;
        padding: 12px 16px;
        border-radius: 18px;
        line-height: 1.5;
        word-break: normal;
        overflow-wrap: break-word;
        white-space: normal;
        text-align: left;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    .message.user .message-bubble {
        background: #2563eb;
        color: #fff;
        border-radius: 18px 18px 6px 18px;
    }

    .message.bot .message-bubble {
        background: #fff;
        color: #1e293b;
        border-radius: 18px 18px 18px 6px;
    }

    .message.typing .message-bubble {
        color: #64748b;
        font-style: italic;
    }

    .message-time {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .input-area {
        padding: 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
        background: #fff;
    }

    .input-field {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .input-field:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .send-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .send-btn:hover {
        background: #1d4ed8;
    }

    .send-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }

    .sidebar {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(31, 38, 135, 0.06);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow-y: auto;
    }

    .sidebar-title {
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-questions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .quick-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.85rem;
        cursor: pointer;
        text-align: left;
        color: #1e293b;
        transition: all 0.2s;
    }

    .quick-btn:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .suggestions {
        background: #eff6ff;
        border-left: 4px solid #2563eb;
        padding: 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        color: #1d4ed8;
        line-height: 1.55;
    }

    .suggestions strong {
        display: block;
        margin-bottom: 6px;
    }

    .stat-card {
        padding: 10px;
        background: #f1f5f9;
        border-radius: 8px;
    }

    .stat-label {
        color: #64748b;
    }

    .stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2563eb;
    }

    .stat-value.alert {
        color: #e11d48;
    }

    @media (max-width: 1024px) {
        .chatbot-container {
            grid-template-columns: 1fr;
            height: auto;
            min-height: 85vh;
        }

        .chatbot-messages {
            min-height: 70vh;
        }

        .message-bubble {
            max-width: 90%;
        }
    }
</style>

<div class="chatbot-container">
    <div class="chatbot-messages">
        <div class="chatbot-header">
            <i class="fa fa-robot"></i>
            Energy System Assistant
        </div>

        <div class="messages-area" id="messagesArea"></div>

        <div class="input-area">
            <input type="text" class="input-field" id="messageInput" placeholder="Ask me anything about your energy system...">
            <button class="send-btn" type="button" onclick="sendMessage()" aria-label="Send message">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <div class="sidebar">
        <div>
            <div class="sidebar-title"><i class="fa fa-lightbulb"></i> Quick Questions</div>
            <div class="quick-questions">
                <button class="quick-btn" type="button" onclick="setQuestion('Show me facilities with high alerts')">High Alert Facilities</button>
                <button class="quick-btn" type="button" onclick="setQuestion('Show me energy recommendations')">Recommendations</button>
                <button class="quick-btn" type="button" onclick="setQuestion('What is my monthly energy cost?')">Monthly Cost</button>
                <button class="quick-btn" type="button" onclick="setQuestion('What are energy alerts?')">Alerts Explained</button>
                <button class="quick-btn" type="button" onclick="setQuestion('What is in my account?')">My Account</button>
                <button class="quick-btn" type="button" onclick="setQuestion('How do I generate reports?')">Reports</button>
                <button class="quick-btn" type="button" onclick="setQuestion('Explain main meters and sub-meters')">Meters</button>
                <button class="quick-btn" type="button" onclick="setQuestion('How do archive restore and delete work?')">Archive</button>
            </div>
        </div>

        <div>
            <div class="sidebar-title"><i class="fa fa-book"></i> About This Bot</div>
            <div class="suggestions">
                <strong>I can help with:</strong>
                - Energy consumption data<br>
                - Facility and meter info<br>
                - System navigation<br>
                - How-to guides<br>
                - Report generation<br>
                - Alert explanations<br>
                - Maintenance and archive actions
            </div>
        </div>

        <div>
            <div class="sidebar-title"><i class="fa fa-chart-simple"></i> System Stats</div>
            <div style="display:grid;gap:8px;font-size:0.9rem;">
                <div class="stat-card">
                    <div class="stat-label">Total Facilities</div>
                    <div class="stat-value">{{ number_format((int) $chatbotStats['total_facilities']) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Meters</div>
                    <div class="stat-value">{{ number_format((int) $chatbotStats['active_meters']) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Records This Year</div>
                    <div class="stat-value">{{ number_format((int) $chatbotStats['records_this_year']) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">High Alerts</div>
                    <div class="stat-value alert">{{ number_format((int) $chatbotStats['high_alerts']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (!message) return;

    addMessage(escapeHtml(message), 'user');
    input.value = '';

    const button = document.querySelector('.send-btn');
    if (button) button.disabled = true;

    const typingId = addMessage('Thinking...', 'bot typing');

    try {
        const response = await fetch('{{ route('modules.chatbot.respond') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message })
        });

        removeMessage(typingId);

        if (!response.ok) {
            addMessage('Sorry, I could not read the system response right now. Please try again.', 'bot');
            return;
        }

        const data = await response.json();
        addMessage((data.message || 'I can help with questions about this energy system.').replace(/\n/g, '<br>'), 'bot');
    } catch (error) {
        removeMessage(typingId);
        addMessage('Sorry, I could not respond right now. Please try again.', 'bot');
    } finally {
        if (button) button.disabled = false;
        input.focus();
    }
}

function addMessage(text, sender) {
    const messagesArea = document.getElementById('messagesArea');
    const messageDiv = document.createElement('div');
    const messageId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    messageDiv.className = `message ${sender}`;
    messageDiv.dataset.messageId = messageId;

    const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    messageDiv.innerHTML = `
        <div>
            <div class="message-bubble">${text}</div>
            <div class="message-time">${time}</div>
        </div>
    `;

    messagesArea.appendChild(messageDiv);
    messagesArea.scrollTop = messagesArea.scrollHeight;
    return messageId;
}

function removeMessage(messageId) {
    if (!messageId) return;
    const node = document.querySelector(`[data-message-id="${messageId}"]`);
    if (node) node.remove();
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function setQuestion(question) {
    document.getElementById('messageInput').value = question;
    sendMessage();
}

document.getElementById('messageInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

document.addEventListener('DOMContentLoaded', function() {
    addMessage('Hi! I can answer questions about this energy system: facilities, meters, monthly records, alerts, reports, maintenance, archives, users, and energy-saving recommendations.', 'bot');
    document.getElementById('messageInput')?.focus();
});
</script>
@endsection
