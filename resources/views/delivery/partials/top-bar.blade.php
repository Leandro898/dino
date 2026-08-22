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

    <button class="icon-btn" onclick="openView('helpCenterView')" title="Soporte" style="position: relative;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></svg>
        <span id="supportBadge" style="display: none; position: absolute; top: 2px; right: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></span>
    </button>
</div>
