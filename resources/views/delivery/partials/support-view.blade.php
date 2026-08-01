<!-- Full Screen PWA View: Support Chat -->
<div class="pwa-view" id="supportView">
    <div class="pwa-header">
        <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('supportView')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </button>
        <h2>Soporte</h2>
    </div>
    <div class="pwa-content" style="display: flex; flex-direction: column; height: calc(100% - 76px); padding: 12px; background: #f9fafb;">
        <!-- Message Area -->
        <div id="supportChatMessages" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-bottom: 12px; margin-bottom: 12px;">
            <!-- Messages will be injected here dynamically -->
        </div>
        
        <!-- Send Wrapper -->
        <div style="display: flex; gap: 8px; background: white; padding: 8px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: env(safe-area-inset-bottom, 12px);">
            <input type="text" id="supportChatInput" placeholder="Escribe un mensaje..." style="flex: 1; border: none; outline: none; padding: 10px; font-size: 0.95rem; background: transparent;" onkeydown="if(event.key === 'Enter') sendSupportChat()">
            <button onclick="sendSupportChat()" style="background: var(--primary); border: none; border-radius: 12px; padding: 10px 16px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </div>
</div>
