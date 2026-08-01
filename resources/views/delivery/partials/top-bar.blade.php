<!-- Top Floating Bar -->
<div class="top-bar">
    <button class="icon-btn" onclick="toggleDrawer()" title="Menú">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>

    <div class="status-badge" id="topStatusBadge">
        <span>Estado</span>
        <div class="status-indicator">
            <div class="dot" id="topStatusDot"></div>
            <div id="topStatusText">Desconectado</div>
        </div>
    </div>

    <button class="icon-btn" onclick="openView('supportView')" title="Soporte" style="position: relative;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        <span id="supportBadge" style="display: none; position: absolute; top: 2px; right: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></span>
    </button>
</div>
