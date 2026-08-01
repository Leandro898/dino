<!-- Full Screen PWA View: Settings -->
<div class="pwa-view" id="settingsView">
    <div class="pwa-header">
        <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('settingsView')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </button>
        <h2>Configuración</h2>
    </div>
    <div class="pwa-content">
        <div class="info-card" style="text-align: left; padding: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 1.05rem;">Notificaciones Push</h4>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Recibir alertas de nuevos pedidos</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" id="pushToggle" onchange="togglePush(this)">
                <span class="toggle-slider"></span>
            </label>
        </div>
        
        <button class="btn btn-primary" style="width: 100%; box-shadow: none;" onclick="testNotification()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            Comprobar notificaciones
        </button>
    </div>
</div>
