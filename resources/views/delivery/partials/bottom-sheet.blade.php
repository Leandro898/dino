<!-- Bottom Action Sheet -->
<div class="bottom-sheet" id="bottomSheet">
    <div class="sheet-handle" id="sheetHandle"></div>
    
    <div class="bottom-sheet-content">
        <div class="install-banner" id="installBanner">
            <p>Instala la app para mejor experiencia</p>
            <button class="btn-install" id="installAppBtn">Instalar</button>
        </div>

        <div class="info-card" id="infoCard">
            <h3 id="infoTitle">¡Hola, {{ auth()->user()->name ?? 'Rider' }}!</h3>
            <p id="infoDesc">Presiona Comenzar para recibir notificaciones de nuevos pedidos en tu zona.</p>
        </div>

        <div class="action-row" id="disconnectedActions">
            <button class="btn btn-primary" id="startBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Comenzar
            </button>
        </div>

        <div class="action-row" id="connectedActions" style="display: none;">
            <button class="btn btn-danger" id="stopBtn">
                Detener
            </button>
        </div>
        
        <div style="flex: 1; min-height: 100px;"></div>
    </div>
</div>
