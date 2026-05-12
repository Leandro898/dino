<button id="globalVoiceFab" class="global-voice-fab" type="button" aria-label="Activar pedido por voz">
    <span class="global-voice-fab-icon">🎙️</span>
    <span class="global-voice-fab-label">Pedir con voz</span>
</button>

<style>
    .global-voice-fab {
        position: fixed;
        left: 16px;
        bottom: 92px;
        z-index: 85;
        border: 0;
        border-radius: 999px;
        min-width: 60px;
        height: 60px;
        padding: 0 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: linear-gradient(145deg, #7c3aed 0%, #4c1d95 100%);
        color: #fff;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        box-shadow: 0 14px 26px rgba(70, 30, 152, 0.34);
        cursor: pointer;
        transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        outline: 3px solid rgba(255, 255, 255, 0.9);
        outline-offset: 0;
    }

    .global-voice-fab:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(70, 30, 152, 0.4);
    }

    .global-voice-fab.is-listening {
        background: linear-gradient(145deg, #ef4444 0%, #b91c1c 100%);
        box-shadow: 0 14px 26px rgba(185, 28, 28, 0.34);
    }

    .global-voice-fab[disabled] {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .global-voice-fab-icon {
        font-size: 1.14rem;
        line-height: 1;
    }

    .global-voice-fab-label {
        white-space: nowrap;
    }

    @media (min-width: 640px) {
        .global-voice-fab {
            left: 24px;
            bottom: 100px;
        }
    }
</style>

<script>
    (() => {
        if (window.__globalVoiceFabInitialized) return;
        window.__globalVoiceFabInitialized = true;

        const voiceFab = document.getElementById('globalVoiceFab');
        if (!voiceFab) return;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        let isListening = false;

        const setLabel = (text) => {
            const label = voiceFab.querySelector('.global-voice-fab-label');
            if (label) label.textContent = text;
        };

        const goToVoiceResult = (query) => {
            const text = (query || '').trim();
            if (!text) return;
            window.location.href = '/pedido-voz?pedido=' + encodeURIComponent(text);
        };

        const stopListening = () => {
            if (recognition && isListening) {
                recognition.stop();
            }
            isListening = false;
            voiceFab.classList.remove('is-listening');
            setLabel('Pedir con voz');
        };

        if (!SpeechRecognition) {
            voiceFab.disabled = true;
            setLabel('Voz no disponible');
            return;
        }

        recognition = new SpeechRecognition();
        recognition.lang = 'es-AR';
        recognition.interimResults = true;
        recognition.continuous = false;

        recognition.onstart = () => {
            isListening = true;
            voiceFab.classList.add('is-listening');
            setLabel('Escuchando...');
        };

        recognition.onresult = (event) => {
            let transcript = '';
            for (let i = 0; i < event.results.length; i += 1) {
                transcript += event.results[i][0].transcript;
            }

            const cleaned = transcript.trim();
            const lastResult = event.results[event.results.length - 1];
            if (lastResult.isFinal && cleaned) {
                stopListening();
                goToVoiceResult(cleaned);
            }
        };

        recognition.onerror = () => {
            stopListening();
        };

        recognition.onend = () => {
            stopListening();
        };

        voiceFab.addEventListener('click', () => {
            if (isListening) {
                stopListening();
                return;
            }

            try {
                recognition.start();
            } catch (error) {
                stopListening();
            }
        });
    })();
</script>
