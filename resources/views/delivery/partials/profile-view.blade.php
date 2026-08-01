<!-- Full Screen PWA View: Profile -->
<div class="pwa-view" id="profileView">
    <div class="pwa-header">
        <button class="icon-btn" style="box-shadow:none; background:#f3f4f6;" onclick="closeView('profileView')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </button>
        <h2>Mi Perfil</h2>
    </div>
    <div class="pwa-content">
        <div class="profile-box">
            <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}</div>
            <h3>{{ auth()->user()->name ?? 'Rider' }}</h3>
            <p>{{ auth()->user()->email ?? 'rider@bari.com' }}</p>
        </div>
        
        <div class="info-card" style="text-align: left; padding: 20px;">
            <h4 style="margin: 0 0 12px 0; font-size: 1.1rem; color: var(--text-main);">Información de Cuenta</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Rol</span>
                    <div style="font-weight: 500;">Repartidor Autorizado</div>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Registrado el</span>
                    <div style="font-weight: 500;">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
