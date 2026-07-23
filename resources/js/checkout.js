document.addEventListener('DOMContentLoaded', function () {
    // =====================================================================
    // 1) CONFIGURACIÓN INICIAL
    // =====================================================================
    const configEl = document.getElementById('checkout-config');
    if (!configEl) return;

    let shippingZones = {};
    try {
        shippingZones = JSON.parse(configEl.dataset.shippingZones || '{}');
    } catch (e) {
        console.error('Error parseando zonas de envío:', e);
    }

    const reverseGeocodeUrl = configEl.dataset.reverseGeocodeUrl;
    const streetSuggestionsUrl = configEl.dataset.streetSuggestionsUrl;
    const detectZoneUrl = configEl.dataset.detectZoneUrl;

    // =====================================================================
    // 2) MANEJO DEL MAPA CON LEAFLET
    // =====================================================================
    const latCentro = -41.133472;
    const lngCentro = -71.310278;
    const initialZoom = 13;

    const mapContainer = document.getElementById('delivery-map');
    let map = null;
    let currentMarker = null;

    if (mapContainer && window.L) {
        map = L.map('delivery-map').setView([latCentro, lngCentro], initialZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            addMarker(lat, lng, 'Ubicación seleccionada');
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('map-status').textContent = "📍 Ubicación guardada";
            reverseGeocode(lat, lng);
        });
    }

    function addMarker(lat, lng, title) {
        if (!map) return;
        if (currentMarker) {
            map.removeLayer(currentMarker);
        }
        currentMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
        
        currentMarker.on('dragend', function (event) {
            const position = event.target.getLatLng();
            document.getElementById('lat').value = position.lat;
            document.getElementById('lng').value = position.lng;
            
            const statusEl = document.getElementById('map-status');
            if (statusEl) statusEl.textContent = "📍 Ubicación guardada";
            
            reverseGeocode(position.lat, position.lng);
        });

        if (title) {
            currentMarker.bindPopup(title).openPopup();
        }
        map.setView([lat, lng], 16);
    }

    // =====================================================================
    // 3) COMPORTAMIENTO DEL SELECT DE ENVÍOS Y TOTALES
    // =====================================================================
    const shippingSelect = document.getElementById('shipping-zone-hidden');

    function updateTotals(price) {
        const subtotalEl = document.getElementById('summary-subtotal');
        const shippingEl = document.getElementById('summary-shipping');
        const totalEl = document.getElementById('summary-total');
        if (!subtotalEl || !shippingEl || !totalEl) return;

        const subtotal = parseInt(subtotalEl.dataset.value || 0, 10);

        if (price !== null) {
            shippingEl.textContent = '$' + price.toLocaleString('es-AR');
            totalEl.textContent = '$' + (subtotal + price).toLocaleString('es-AR');
        } else {
            shippingEl.textContent = 'Seleccioná zona';
            totalEl.textContent = '$' + subtotal.toLocaleString('es-AR');
        }
    }

    let detectTimeout = null;
    let suggestTimeout = null;

    // Inicialización
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    if (latInput && lngInput && latInput.value && lngInput.value) {
        addMarker(latInput.value, lngInput.value, 'Dirección previa');
    }
    
    if (shippingSelect && shippingSelect.value) {
        const z = shippingZones[shippingSelect.value];
        if (z) {
            updateTotals(z.price);
        }
    }

    if (shippingSelect) {
        shippingSelect.addEventListener('change', function () {
            const z = shippingZones[this.value];
            if (z) {
                updateTotals(z.price);
                const statusEl = document.getElementById('zone-detection-msg');
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-gray-500 text-sm">Zona seleccionada manualmente</span>';
                    statusEl.classList.remove('hidden');
                }
            } else {
                updateTotals(null);
            }
        });
    }

    // =====================================================================
    // 4) AUTOCOMPLETADO Y AUTO-SELECCIÓN POR DIRECCIÓN (CALLE Y NÚMERO)
    // =====================================================================
    const streetNameInput = document.getElementById('street-name-input');
    const streetNumberInput = document.getElementById('street-number-input');
    const suggestionsList = document.getElementById('street-suggestions-list');

    if (streetNameInput && suggestionsList) {
        document.addEventListener('click', function (e) {
            if (e.target !== streetNameInput && e.target !== suggestionsList) {
                suggestionsList.classList.add('hidden');
            }
        });

        streetNameInput.addEventListener('input', function () {
            const q = this.value.trim();
            if (q.length < 3) {
                suggestionsList.innerHTML = '';
                suggestionsList.classList.add('hidden');
                return;
            }

            clearTimeout(suggestTimeout);
            suggestTimeout = setTimeout(() => {
                fetch(`${streetSuggestionsUrl}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        suggestionsList.innerHTML = '';
                        if (data.results && data.results.length > 0) {
                            data.results.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b last:border-b-0';
                                li.textContent = item.street;
                                li.addEventListener('click', () => {
                                    streetNameInput.value = item.street;
                                    suggestionsList.classList.add('hidden');
                                    triggerZoneDetection();
                                });
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.classList.remove('hidden');
                        } else {
                            suggestionsList.classList.add('hidden');
                        }
                    })
                    .catch(err => console.error(err));
            }, 400);
        });

        streetNameInput.addEventListener('input', () => {
            clearTimeout(detectTimeout);
            detectTimeout = setTimeout(triggerZoneDetection, 800);
        });

        if (streetNumberInput) {
            streetNumberInput.addEventListener('input', () => {
                clearTimeout(detectTimeout);
                detectTimeout = setTimeout(triggerZoneDetection, 800);
            });
        }
    }

    function triggerZoneDetection() {
        if (!streetNameInput || !streetNumberInput) return;
        const stName = streetNameInput.value.trim();
        const stNum = streetNumberInput.value.trim();

        if (stName.length > 2 && stNum.length > 0) {
            const statusEl = document.getElementById('zone-detection-msg');
            if (statusEl) {
                statusEl.innerHTML = '<span class="text-blue-500 animate-pulse">Calculando envío...</span>';
                statusEl.classList.remove('hidden');
            }

            fetch(`${detectZoneUrl}?street=${encodeURIComponent(stName)}&number=${encodeURIComponent(stNum)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.zone_key && shippingSelect) {
                        shippingSelect.value = data.zone_key;

                        if (data.lat && data.lng) {
                            addMarker(data.lat, data.lng, data.address_formatted || (stName + ' ' + stNum));
                            document.getElementById('lat').value = data.lat;
                            document.getElementById('lng').value = data.lng;
                        } else {
                            // Fetch coordinates via Nominatim to move the map
                            const query = `${stName} ${stNum}, San Carlos de Bariloche, Rio Negro, Argentina`;
                            fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`)
                                .then(r => r.json())
                                .then(results => {
                                    if (results && results.length > 0) {
                                        const loc = results[0];
                                        addMarker(loc.lat, loc.lon, stName + ' ' + stNum);
                                        document.getElementById('lat').value = loc.lat;
                                        document.getElementById('lng').value = loc.lon;
                                    }
                                }).catch(e => console.error("Error geocoding address:", e));
                        }

                        if (statusEl) {
                            statusEl.innerHTML = '<span class="text-green-600 font-semibold">✓ Envío calculado automáticamente</span>';
                            statusEl.classList.remove('hidden');
                        }

                        const z = shippingZones[data.zone_key];
                        if (z) {
                            updateTotals(z.price);
                        }
                    } else {
                        if (statusEl) {
                            statusEl.innerHTML = '<span class="text-yellow-600">No se detectó la zona.</span>';
                            statusEl.classList.remove('hidden');
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="text-red-500 text-sm">Error conectando con el servicio de mapas.</span>';
                        statusEl.classList.remove('hidden');
                    }
                });
        }
    }

    // =====================================================================
    // 5) REVERSE GEOCODING (Click en mapa -> Completar calle y número)
    // =====================================================================
    function reverseGeocode(lat, lng) {
        const statusEl = document.getElementById('zone-detection-msg');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-blue-500 animate-pulse">Obteniendo dirección desde el mapa...</span>';
            statusEl.classList.remove('hidden');
        }

        fetch(`${reverseGeocodeUrl}?lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.street && streetNameInput) streetNameInput.value = data.street;
                    if (data.number && streetNumberInput) streetNumberInput.value = data.number;

                    if (data.zone_key && shippingSelect) {
                        shippingSelect.value = data.zone_key;
                        const z = shippingZones[data.zone_key];
                        if (z) {
                            updateTotals(z.price);
                        }
                        if (statusEl) statusEl.innerHTML = '<span class="text-green-600 font-semibold">✓ Zona actualizada desde el mapa</span>';
                    } else {
                        if (statusEl) statusEl.innerHTML = '<span class="text-yellow-600">✓ Dirección obtenida, pero no coincide con ninguna zona de envío</span>';
                    }
                } else {
                    if (statusEl) statusEl.innerHTML = '<span class="text-yellow-600">No se pudo identificar la calle exacta.</span>';
                }
            })
            .catch(err => console.error(err));
    }

    // =====================================================================
    // 6) ACTUALIZACIÓN DE CANTIDADES (AJAX)
    // =====================================================================
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
    
    document.querySelectorAll('.btn-decrease-qty, .btn-increase-qty').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.id;
            const url = this.dataset.url;
            const inputEl = document.getElementById(`quantity-input-${productId}`);
            const displayEl = document.getElementById(`quantity-display-${productId}`);
            if (!inputEl || !displayEl) return;
            
            let currentQty = parseInt(inputEl.value, 10);
            
            if (this.classList.contains('btn-increase-qty')) {
                currentQty++;
            } else if (this.classList.contains('btn-decrease-qty')) {
                if (currentQty <= 1) return;
                currentQty--;
            }
            
            inputEl.value = currentQty;
            displayEl.textContent = currentQty;
            
            // Actualizar en backend
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ quantity: currentQty })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload(); 
                }
            });
        });
    });

    // =====================================================================
    // 7) SUBMIT DEL FORMULARIO CON ESTADO DE CARGA
    // =====================================================================
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Procesando... <svg class="animate-spin h-5 w-5 ml-2 inline-block text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            }
        });
    }
});
