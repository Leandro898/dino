(() => {
    const searchInput = document.getElementById('quickSearch');
    const tiles = document.querySelectorAll('[data-query]');
    const resultsGallery = document.getElementById('searchResultsGallery');
    
    // Config values passed from Blade
    const searchEndpoint = window.HomeConfig?.searchEndpoint || '';
    const bebidasUrl = window.HomeConfig?.bebidasUrl || '';
    const pharmacyUrl = window.HomeConfig?.pharmacyUrl || '';
    const almacenUrl = window.HomeConfig?.almacenUrl || '';
    const comidasUrl = window.HomeConfig?.comidasUrl || '';
    
    const quickMenu = document.getElementById('quickMenu');
    const quickMenuTrigger = document.getElementById('quickMenuTrigger');
    const quickMenuBackdrop = document.getElementById('quickMenuBackdrop');
    const quickMenuItems = document.querySelectorAll('.quick-menu-item');
    const quickMenuVoiceAction = document.getElementById('quickMenuVoiceAction');
    const galleryMicBtn = document.getElementById('galleryMicBtn');
    const micStopLoader = document.getElementById('micStopLoader');

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    let searchTimer = null;
    let activeController = null;
    let isQuickMenuOpen = false;
    let recognition = null;
    let isListening = false;
    let isVoiceMode = false;
    let voiceSearchTimer = null;
    let isPaused = false;
    let micStopTimer = null;

    const showVoiceToast = (message) => {
        let toast = document.getElementById('voiceToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'voiceToast';
            toast.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);' +
                'background:rgba(30,30,30,0.92);color:#fff;padding:10px 18px;border-radius:20px;' +
                'font-size:14px;z-index:9999;max-width:88vw;text-align:center;pointer-events:none;' +
                'transition:opacity .3s;';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
        }, 3500);
    };

    const normalize = (value) => (value || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const goToCatalog = (query) => {
        const text = normalize(query);

        if (text.includes('bebida') || text.includes('cerveza') || text.includes('vino') || text.includes(
                'gaseosa')) {
            window.location.href = bebidasUrl;
            return;
        }

        if (text.includes('comida') || text.includes('menu') || text.includes('hamburguesa') || text.includes(
                'pizza')) {
            window.location.href = comidasUrl;
            return;
        }

        if (text.includes('farmacia') || text.includes('remedio') || text.includes('medicamento')) {
            window.location.href = pharmacyUrl;
            return;
        }

        window.location.href = almacenUrl;
    };

    const escapeHtml = (value) => (value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    let voiceCountdownInterval = null;

    const clearVoiceSearchTimer = () => {
        if (voiceSearchTimer) {
            clearTimeout(voiceSearchTimer);
            voiceSearchTimer = null;
        }
        if (voiceCountdownInterval) {
            clearInterval(voiceCountdownInterval);
            voiceCountdownInterval = null;
        }
    };

    const hasPendingVoiceSearch = () => Boolean(voiceSearchTimer);

    const setMicStoppingState = (active) => {
        if (micStopTimer) {
            clearTimeout(micStopTimer);
            micStopTimer = null;
        }

        if (micStopLoader) {
            micStopLoader.classList.toggle('is-active', active);
        }

        if (galleryMicBtn) {
            galleryMicBtn.disabled = active;
        }

        if (quickMenuVoiceAction) {
            quickMenuVoiceAction.disabled = active;
        }

        if (active) {
            micStopTimer = setTimeout(() => {
                setMicStoppingState(false);
            }, 560);
        }
    };

    const resetMicToInitialState = () => {
        clearVoiceSearchTimer();
        isVoiceMode = false;
        isListening = false;
        setMicState('idle');
        renderResults([], '');
    };

    const cancelPendingVoiceSearch = () => {
        if (!hasPendingVoiceSearch()) return;

        clearVoiceSearchTimer();
        setMicState('paused');
        const query = searchInput ? searchInput.value.trim() : '';
        if (query && resultsGallery) {
            resultsGallery.classList.remove('is-empty', 'has-no-results');
            if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');
            resultsGallery.innerHTML =
                '<div class="voice-transcript is-final">' +
                '<p class="voice-text">' + escapeHtml(query) + '</p>' +
                '<div class="voice-actions">' +
                '<button class="voice-action-btn is-new" data-voice-action="new">🎤 Buscar de nuevo</button>' +
                '<button class="voice-action-btn is-resume" data-voice-action="resume">▶️ Continuar</button>' +
                '</div>' +
                '</div>';
        }
    };

    const renderResults = (products, query) => {
        if (!resultsGallery) return;

        if (!query || query.length < 2) {
            resultsGallery.classList.add('is-empty');
            resultsGallery.innerHTML = '<div class="results-track is-placeholder"></div>';
            if (galleryMicBtn) galleryMicBtn.classList.remove('is-hidden');
            return;
        }

        if (!products.length) {
            resultsGallery.classList.add('is-empty', 'has-no-results');
            resultsGallery.innerHTML = '<div class="results-status" style="position: absolute; top: 15px; width: 100%; left: 0; text-align: center; z-index: 20;">' +
                '<p style="margin-bottom: 12px; font-size: 1rem;">No encontramos resultados para "' + escapeHtml(query) + '".</p>' +
                '<button type="button" onclick="window.Livewire.dispatch(\'open-live-chat\')" class="tile-primary" style="padding: 10px 20px; border-radius: 20px; border: none; font-weight: bold; cursor: pointer; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">Consulta por chat si no encuentras lo que buscas</button></div>';
            if (galleryMicBtn) galleryMicBtn.classList.remove('is-hidden');
            return;
        }

        if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');

        const cards = products.map((product) => {
            const imageHtml = product.image ?
                '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) +
                '">' :
                '<span class="results-status">Sin imagen</span>';

            return '<a class="result-card" href="' + escapeHtml(product.url) + '">' +
                '<div class="result-image">' + imageHtml + '</div>' +
                '<div class="result-meta">' +
                '<p class="result-name">' + escapeHtml(product.name) + '</p>' +
                '<p class="result-price">' + escapeHtml(product.price) + '</p>' +
                '</div>' +
                '</a>';
        }).join('');

        resultsGallery.classList.remove('is-empty', 'has-no-results');
        resultsGallery.innerHTML = '<div class="results-track">' + cards + '</div>';
    };

    const renderVoiceTranscript = (text, isFinal, hintText = 'Tocá el micrófono para pausar') => {
        if (!resultsGallery) return;
        resultsGallery.classList.remove('is-empty', 'has-no-results');
        if (galleryMicBtn) galleryMicBtn.classList.add('is-hidden');

        if (!text) {
            resultsGallery.innerHTML = '<div class="voice-transcript is-listening">' +
                '<span class="voice-pulse">🎙️</span>' +
                '</div>';
            return;
        }

        const escaped = escapeHtml(text);
        if (isFinal) {
            resultsGallery.innerHTML = '<div class="voice-transcript is-final">' +
                '<p class="voice-text">' + escaped + '</p>' +
                '<p class="voice-hint" id="voiceHintLabel">' + escapeHtml(hintText) + '</p>' +
                '</div>';
        } else {
            resultsGallery.innerHTML = '<div class="voice-transcript is-interim">' +
                '<p class="voice-text">' + escaped + '</p>' +
                '<p class="voice-hint" id="voiceHintLabel">🎤 Tocá para pausar</p>' +
                '</div>';
        }
    };

    const scheduleVoiceResults = (query) => {
        const cleaned = (query || '').trim();
        clearVoiceSearchTimer();

        if (cleaned.length < 2) return;

        setMicState('pending');

        let secs = 3;

        const updateHint = () => {
            const hint = document.getElementById('voiceHintLabel');
            if (hint) {
                hint.className = 'voice-hint is-pending';
                hint.innerHTML = '⏳ Buscando en <span class="voice-countdown">' + secs +
                    '</span>s · tocá para pausar';
            }
        };

        renderVoiceTranscript(cleaned, true, '');
        updateHint();

        voiceCountdownInterval = setInterval(() => {
            secs -= 1;
            if (secs > 0) {
                updateHint();
            } else {
                clearVoiceSearchTimer();
                isVoiceMode = false;
                setMicState('idle');
                fetchLiveResults(cleaned, 5);
            }
        }, 1000);

        voiceSearchTimer = setTimeout(() => {
            // fallback — should already be triggered by interval
        }, 3200);
    };

    const fetchLiveResults = (query, limit = null) => {
        if (activeController) {
            activeController.abort();
        }

        if (!resultsGallery) return;

        activeController = new AbortController();
        resultsGallery.classList.remove('is-empty', 'has-no-results');
        resultsGallery.innerHTML = '<p class="results-status">Buscando...</p>';

        fetch(searchEndpoint + '?q=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: activeController.signal
            })
            .then((response) => {
                if (!response.ok) throw new Error('search-error');
                return response.json();
            })
            .then((data) => {
                const products = Array.isArray(data.products) ? data.products : [];
                renderResults(limit ? products.slice(0, limit) : products, query);
            })
            .catch((error) => {
                if (error.name === 'AbortError') return;
                resultsGallery.innerHTML = '<p class="results-status">No se pudo buscar ahora.</p>';
            });
    };

    const setQuickMenuOpen = (open) => {
        isQuickMenuOpen = open;
        if (quickMenu) {
            quickMenu.classList.toggle('is-open', open);
        }

        if (quickMenuTrigger) {
            quickMenuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        if (quickMenuBackdrop) {
            quickMenuBackdrop.hidden = !open;
            quickMenuBackdrop.classList.toggle('is-open', open);
        }
    };

    const setMicState = (state) => {
        // state: 'idle' | 'listening' | 'pending' | 'paused'
        isPaused = (state === 'paused');
        const micBtns = [quickMenuVoiceAction, galleryMicBtn].filter(Boolean);

        micBtns.forEach((btn) => {
            btn.classList.remove('is-listening', 'is-pending');
            if (state === 'listening') btn.classList.add('is-listening');
            if (state === 'pending') btn.classList.add('is-pending');
        });

        if (quickMenuVoiceAction) {
            const label = quickMenuVoiceAction.querySelector('.quick-menu-label');
            if (state === 'listening') {
                quickMenuVoiceAction.setAttribute('aria-label', 'Detener búsqueda por voz');
                if (label) label.textContent = 'Escuchando';
            } else if (state === 'pending') {
                quickMenuVoiceAction.setAttribute('aria-label', 'Pausar búsqueda por voz');
                if (label) label.textContent = 'Pausar';
            } else if (state === 'paused') {
                quickMenuVoiceAction.setAttribute('aria-label', 'Reanudar búsqueda');
                if (label) label.textContent = 'Reanudar';
            } else {
                quickMenuVoiceAction.setAttribute('aria-label', 'Buscar por voz');
                if (label) label.textContent = 'Buscar por voz';
            }
        }

        if (galleryMicBtn) {
            if (state === 'listening') galleryMicBtn.setAttribute('aria-label', 'Detener escucha');
            else if (state === 'pending') galleryMicBtn.setAttribute('aria-label', 'Pausar búsqueda');
            else if (state === 'paused') galleryMicBtn.setAttribute('aria-label', 'Reanudar búsqueda');
            else galleryMicBtn.setAttribute('aria-label', 'Buscar por voz');
        }
    };

    const stopListening = () => {
        if (recognition && isListening) {
            recognition.stop();
        }

        isListening = false;
        setMicState('idle');
    };

    const startListening = () => {
        if (!recognition || isListening) return;

        clearVoiceSearchTimer();

        try {
            recognition.start();
        } catch (error) {
            stopListening();
        }
    };

    tiles.forEach((tile) => {
        tile.addEventListener('click', () => {
            const query = tile.getAttribute('data-query') || '';
            if (searchInput) searchInput.value = query;
            goToCatalog(query);
        });
    });

    quickMenuItems.forEach((item) => {
        item.addEventListener('click', () => {
            const query = item.getAttribute('data-menu-query');
            if (!query) return;
            setQuickMenuOpen(false);
            if (searchInput) searchInput.value = query;
            goToCatalog(query);
        });
    });

    if (SpeechRecognition && (quickMenuVoiceAction || galleryMicBtn)) {
        recognition = new SpeechRecognition();
        recognition.lang = 'es-AR';
        recognition.interimResults = true;
        recognition.continuous = false;

        recognition.onstart = () => {
            isListening = true;
            isVoiceMode = true;
            setMicState('listening');
            renderVoiceTranscript('', false);
        };

        recognition.onresult = (event) => {
            let transcript = '';

            for (let i = 0; i < event.results.length; i += 1) {
                transcript += event.results[i][0].transcript;
            }

            const cleaned = transcript.trim();
            const lastResult = event.results[event.results.length - 1];

            if (searchInput) {
                searchInput.value = cleaned;
            }

            renderVoiceTranscript(cleaned, lastResult.isFinal);

            if (lastResult.isFinal) {
                stopListening();
                scheduleVoiceResults(cleaned);
            }
        };

        recognition.onerror = (event) => {
            stopListening();
            const code = event.error || '';
            if (code === 'not-allowed' || code === 'service-not-allowed' || code === 'network') {
                showVoiceToast('El micrófono requiere HTTPS. Usá la versión en línea.');
            } else if (code === 'no-speech') {
                showVoiceToast('No se escuchó nada. Intentá de nuevo.');
            } else {
                showVoiceToast('No se pudo iniciar el micrófono.');
            }
        };

        recognition.onend = () => {
            stopListening();
            setMicStoppingState(false);

            const query = searchInput ? searchInput.value.trim() : '';
            if (!hasPendingVoiceSearch() && query.length < 2) {
                resetMicToInitialState();
            }
        };

        const handleVoiceBtnClick = () => {
            if (isListening) {
                setMicStoppingState(true);
                stopListening();
                const query = searchInput ? searchInput.value.trim() : '';
                if (query.length < 2) {
                    resetMicToInitialState();
                }
                return;
            }
            if (hasPendingVoiceSearch()) {
                const query = searchInput ? searchInput.value.trim() : '';
                if (query.length < 2) {
                    resetMicToInitialState();
                    return;
                }
                cancelPendingVoiceSearch();
                return;
            }
            const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location
                .hostname === '127.0.0.1';
            if (!isSecure) {
                showVoiceToast('El micrófono necesita HTTPS. Funciona en la versión en línea.');
                return;
            }
            startListening();
        };

        if (quickMenuVoiceAction) {
            quickMenuVoiceAction.addEventListener('click', () => {
                setQuickMenuOpen(false);
                handleVoiceBtnClick();
            });
        }

        if (galleryMicBtn) {
            galleryMicBtn.addEventListener('click', handleVoiceBtnClick);
        }
    } else {
        if (quickMenuVoiceAction) {
            quickMenuVoiceAction.addEventListener('click', () => {
                setQuickMenuOpen(false);
                showVoiceToast('Tu navegador no soporta búsqueda por voz.');
            });
            const label = quickMenuVoiceAction.querySelector('.quick-menu-label');
            if (label) label.textContent = 'Sin voz';
        }
        if (galleryMicBtn) {
            galleryMicBtn.addEventListener('click', () => {
                showVoiceToast('Tu navegador no soporta búsqueda por voz.');
            });
        }
    }

    if (quickMenuTrigger) {
        quickMenuTrigger.addEventListener('click', () => {
            setQuickMenuOpen(!isQuickMenuOpen);
        });
    }

    if (quickMenuBackdrop) {
        quickMenuBackdrop.addEventListener('click', () => {
            setQuickMenuOpen(false);
        });
    }

    if (resultsGallery) {
        resultsGallery.addEventListener('click', (event) => {
            const actionBtn = event.target.closest('[data-voice-action]');
            if (actionBtn) {
                const action = actionBtn.getAttribute('data-voice-action');
                if (action === 'resume') {
                    const query = searchInput ? searchInput.value.trim() : '';
                    if (query.length >= 2) scheduleVoiceResults(query);
                } else if (action === 'new') {
                    if (searchInput) searchInput.value = '';
                    renderResults([], '');
                    const isSecure = location.protocol === 'https:' || location.hostname ===
                        'localhost' || location.hostname === '127.0.0.1';
                    if (!isSecure) {
                        showVoiceToast('El micrófono necesita HTTPS. Funciona en la versión en línea.');
                        return;
                    }
                    startListening();
                }
                return;
            }

            const transcript = event.target.closest('.voice-transcript');
            if (!transcript || !hasPendingVoiceSearch()) return;
            cancelPendingVoiceSearch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            isVoiceMode = false;
            clearVoiceSearchTimer();
            const query = searchInput.value.trim();

            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            if (query.length < 2) {
                renderResults([], '');
                return;
            }

            searchTimer = setTimeout(() => {
                fetchLiveResults(query);
            }, 260);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            if (isVoiceMode) {
                clearVoiceSearchTimer();
                isVoiceMode = false;
                const query = searchInput.value.trim();
                if (query.length >= 2) {
                    fetchLiveResults(query);
                }
            } else {
                goToCatalog(searchInput.value);
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setQuickMenuOpen(false);
        }
    });
})();
