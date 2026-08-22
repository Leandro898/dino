<!-- Centro de Ayuda - Inconvenientes con una orden View -->
<div class="pwa-view" id="helpCenterIssuesView">
    <div class="view-header" style="border-bottom: none; padding-bottom: 0;">
        <button class="icon-btn" style="box-shadow:none; background:transparent; margin-left: -8px;" onclick="closeView('helpCenterIssuesView')">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <h2 style="font-size: 1.15rem; font-weight: 700; color: #1e293b;">Inconvenientes con una orden</h2>
        <div style="width: 44px;"></div>
    </div>
    <div class="view-content" style="padding-top: 16px;">

        <div class="list-menu" style="margin-top: 8px;">
            <a href="javascript:void(0)" class="list-menu-item" id="btnCancelOrder" onclick="promptCancelOrder()" style="display: none;">
                <span class="list-menu-text">Tuve un problema y necesito cancelar el pedido</span>
                <svg class="list-menu-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            
            <div id="noCancelOptionsMsg" style="padding: 24px 16px; text-align: center; color: #6b7280; font-size: 0.95rem;">
                No tienes opciones de cancelación disponibles para tu pedido actual. Si ya lo retiraste, debes ponerte en contacto con Soporte.
            </div>
        </div>

    </div>
</div>
