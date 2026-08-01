<!-- Custom Modal Overlay -->
<div id="customModal" class="modal-overlay">
    <div class="modal-content">
        <h3 id="modalTitle">Atención</h3>
        <p id="modalMessage">¿Estás seguro?</p>
        <div class="modal-actions">
            <button id="modalBtnCancel" class="btn-cancel">Cancelar</button>
            <button id="modalBtnConfirm" class="btn-confirm">Aceptar</button>
        </div>
    </div>
</div>

<!-- Order Alert Popup -->
<div class="order-alert" id="orderAlert" style="display: flex; flex-direction: column; gap: 10px;">
    <h3 class="order-alert-title" style="margin:0; font-size: 1.1rem; color: var(--primary);">¡Nuevo Pedido!</h3>
    <p class="order-alert-desc" id="orderAlertDesc" style="margin:0; font-size: 0.9rem; color: var(--text-main);">Pedido #123 de Juan Perez</p>
    <div style="display: flex; gap: 8px; width: 100%;">
        <button id="alertAcceptBtn" class="btn" style="flex: 1; padding: 10px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; height: 36px; box-shadow: none;">
            Aceptar
        </button>
        <button id="alertRejectBtn" class="btn" style="flex: 1; padding: 10px; background: #fee2e2; color: #ef4444; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; height: 36px; box-shadow: none;">
            Rechazar
        </button>
    </div>
</div>
