<div class="chat-box" id="chat-widget">
    <div class="chat-header">
        <button type="button" class="chat-title" id="chat-toggle" aria-expanded="true" aria-controls="chat-panel">
            <i class="fa-solid fa-robot"></i>
            <span>Chatbot AI</span>
        </button>
        <div class="chat-header-actions">
            <span class="small chat-ready">Gemini/OpenAI ready</span>
            <button type="button" class="chat-close" id="chat-close" aria-label="Dong chat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
    <div class="chat-panel" id="chat-panel">
        <div class="chat-body" id="chat-box">
            <div class="mb-2"><span class="badge text-bg-secondary">Xin chào, mình có thể gợi ý source theo nhu cầu của bạn.</span></div>
        </div>
        <div class="chat-footer">
            <div class="input-group">
                <input type="text" id="message" class="form-control" placeholder="Nhập tin nhắn...">
                <button onclick="sendMessage()" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>
