<div id="quickMenuBackdrop" class="quick-menu-backdrop" hidden></div>

@php
    $quickMenuIndex = 1;
    $isHome = request()->routeIs('home', 'home.parallel');
    $isBebidas = request()->routeIs('categories.almacen.beverages')
        || (request()->routeIs('catalog') && str_contains(strtolower((string) request('q', '')), 'bebida'));
    $isAlmacen = request()->routeIs('categories.almacen');
    $isComidas = request()->routeIs('food-vendors.*');
    $isFarmacia = request()->routeIs('categories.pharmacy');
@endphp

<aside id="quickMenu" class="quick-menu" aria-label="Accesos rápidos">
    <button id="quickMenuTrigger" class="quick-menu-trigger" type="button" aria-label="Abrir accesos rápidos"
        aria-expanded="false" aria-controls="quickMenuItems">
        ☰
    </button>

    <div id="quickMenuItems">
        @unless ($isHome)
            <a href="{{ route('home') }}" class="quick-menu-item" data-index="{{ $quickMenuIndex++ }}" aria-label="Ir al inicio">
                <span class="quick-menu-item-icon" aria-hidden="true">🏠</span>
                <span class="quick-menu-label">Inicio</span>
            </a>
        @endunless

        @unless ($isBebidas)
            <a href="{{ route('categories.almacen.beverages') }}" class="quick-menu-item" data-index="{{ $quickMenuIndex++ }}"
                aria-label="Ir a Bebidas">
                <span class="quick-menu-item-icon" aria-hidden="true">🥤</span>
                <span class="quick-menu-label">Bebidas</span>
            </a>
        @endunless

        @unless ($isAlmacen)
            <a href="{{ route('categories.almacen') }}" class="quick-menu-item" data-index="{{ $quickMenuIndex++ }}"
                aria-label="Ir a Almacen">
                <span class="quick-menu-item-icon" aria-hidden="true">🛒</span>
                <span class="quick-menu-label">Almacen</span>
            </a>
        @endunless

        @unless ($isComidas)
            <a href="{{ route('food-vendors.index') }}" class="quick-menu-item" data-index="{{ $quickMenuIndex++ }}"
                aria-label="Ir a Comidas">
                <span class="quick-menu-item-icon" aria-hidden="true">🍽️</span>
                <span class="quick-menu-label">Comidas</span>
            </a>
        @endunless

        @unless ($isFarmacia)
            <a href="{{ route('categories.pharmacy') }}" class="quick-menu-item" data-index="{{ $quickMenuIndex++ }}"
                aria-label="Ir a Farmacia">
                <span class="quick-menu-item-icon" aria-hidden="true">💊</span>
                <span class="quick-menu-label">Farmacia</span>
            </a>
        @endunless
    </div>
</aside>

<style>
    .quick-menu-backdrop {
        position: fixed;
        inset: 0;
        z-index: 88;
        background: transparent;
        opacity: 0;
        pointer-events: none;
        transition: opacity 180ms ease;
    }

    .quick-menu-backdrop.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .quick-menu {
        position: fixed;
        right: 20px;
        bottom: 22px;
        z-index: 89;
        width: 220px;
        height: 240px;
        pointer-events: none;
    }

    .quick-menu button,
    .quick-menu a {
        border: 0;
        cursor: pointer;
        font: inherit;
        text-decoration: none;
    }

    .quick-menu-trigger,
    .quick-menu-item {
        position: absolute;
        right: 0;
        bottom: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 22px rgba(48, 37, 94, 0.22);
    }

    .quick-menu-trigger {
        width: 62px;
        height: 62px;
        color: #fff;
        background: linear-gradient(160deg, #7c3aed 0%, #4c1d95 100%);
        pointer-events: auto;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .quick-menu-trigger:hover {
        transform: scale(1.05);
        box-shadow: 0 14px 26px rgba(48, 37, 94, 0.28);
    }

    .quick-menu-item {
        width: 52px;
        height: 52px;
        color: #4c1d95;
        background: #fff;
        border: 1px solid #d9def3;
        opacity: 0;
        pointer-events: none;
        transform: translate3d(0, 0, 0) scale(0.7);
        transition: transform 220ms ease, opacity 220ms ease, box-shadow 220ms ease;
    }

    .quick-menu-item:hover {
        box-shadow: 0 14px 24px rgba(48, 37, 94, 0.2);
    }

    .quick-menu-item-icon {
        font-size: 1.35rem;
        line-height: 1;
    }

    .quick-menu-label {
        position: absolute;
        right: 58px;
        white-space: nowrap;
        padding: 0.35rem 0.55rem;
        border-radius: 999px;
        border: 1px solid #dde3f6;
        background: rgba(255, 255, 255, 0.98);
        color: #3f4a72;
        font-size: 0.72rem;
        font-weight: 700;
        opacity: 0;
        pointer-events: none;
        transition: opacity 140ms ease;
    }

    .quick-menu-item:hover .quick-menu-label,
    .quick-menu-item:focus-visible .quick-menu-label {
        opacity: 1;
    }

    .quick-menu.is-open .quick-menu-item {
        opacity: 1;
        pointer-events: auto;
    }

    .quick-menu.is-open .quick-menu-item[data-index='1'] {
        transform: translate3d(0, -104px, 0) scale(1);
    }

    .quick-menu.is-open .quick-menu-item[data-index='2'] {
        transform: translate3d(-60px, -88px, 0) scale(1);
    }

    .quick-menu.is-open .quick-menu-item[data-index='3'] {
        transform: translate3d(-98px, -52px, 0) scale(1);
    }

    .quick-menu.is-open .quick-menu-item[data-index='4'] {
        transform: translate3d(-112px, 0, 0) scale(1);
    }

    .quick-menu.is-open .quick-menu-item[data-index='5'] {
        transform: translate3d(-72px, -132px, 0) scale(1);
    }
</style>

<script>
    (() => {
        if (window.__quickMenuInitialized) return;
        window.__quickMenuInitialized = true;

        const quickMenu = document.getElementById('quickMenu');
        const quickMenuTrigger = document.getElementById('quickMenuTrigger');
        const quickMenuBackdrop = document.getElementById('quickMenuBackdrop');
        const quickMenuItems = document.querySelectorAll('.quick-menu-item');

        if (!quickMenu || !quickMenuTrigger) return;

        let isOpen = false;

        const setOpen = (open) => {
            isOpen = open;
            quickMenu.classList.toggle('is-open', open);
            quickMenuTrigger.setAttribute('aria-expanded', String(open));
            if (quickMenuBackdrop) {
                quickMenuBackdrop.hidden = !open;
                quickMenuBackdrop.classList.toggle('is-open', open);
            }
        };

        quickMenuTrigger.addEventListener('click', () => setOpen(!isOpen));

        if (quickMenuBackdrop) {
            quickMenuBackdrop.addEventListener('click', () => setOpen(false));
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen) setOpen(false);
        });
    })();
</script>
