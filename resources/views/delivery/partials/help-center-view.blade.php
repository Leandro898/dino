<!-- Centro de Ayuda View -->
<div class="pwa-view" id="helpCenterView">
    <div class="pwa-header" style="color: var(--text-main);">
        <button class="icon-btn" style="box-shadow:none; background:transparent;" onclick="closeView('helpCenterView')">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <h2 style="font-size: 1.15rem; font-weight: 700; color: currentColor;">Soporte</h2>
        <div style="width: 44px;"></div>
    </div>
    <div class="pwa-content" style="padding: 16px;">

        <div class="menu-section">
            <h4 class="menu-section-title">Pedidos</h4>
            <div class="list-menu">
                <a href="javascript:void(0)" class="list-menu-item" onclick="openView('helpCenterIssuesView')">
                    <div class="list-menu-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
                    </div>
                    <span class="list-menu-text">Inconvenientes con una orden</span>
                    <svg class="list-menu-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        </div>

        <div class="menu-section" style="margin-top: 24px;">
            <h4 class="menu-section-title">Soporte técnico</h4>
            <div class="list-menu">
                <a href="javascript:void(0)" class="list-menu-item" onclick="openView('supportView')">
                    <div class="list-menu-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <span class="list-menu-text">Chat con Soporte</span>
                    <svg class="list-menu-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        </div>
    </div>
</div>
