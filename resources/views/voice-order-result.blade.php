<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bari Tienda | Pedido por voz</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|outfit:300,400,600,700" rel="stylesheet" />
    <style>
        :root {
            --brand: #6d28d9;
            --brand-dark: #4c1d95;
            --panel: #ffffff;
            --ink: #1f2937;
            --muted: #66547f;
            --soft: #f4f0ff;
            --border: #e5dbff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 460px at -15% -20%, rgba(124, 58, 237, 0.18) 0%, transparent 65%),
                radial-gradient(560px 320px at 110% 0%, rgba(34, 197, 94, 0.1) 0%, transparent 70%),
                linear-gradient(160deg, #f8f7ff 0%, #f2efff 45%, #eef7ff 100%);
            display: grid;
            place-items: center;
            padding: 1rem;
        }

        .card {
            width: min(100%, 720px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 30px;
            box-shadow: 0 20px 44px rgba(69, 36, 140, 0.12);
            padding: 1.2rem;
        }

        .top {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .badge {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            background: linear-gradient(90deg, #f0eaff 0%, #e6f8ed 100%);
            color: #3b2070;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #16a34a;
        }

        h1 {
            margin: 0.2rem 0 0.6rem;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.8rem, 5vw, 3rem);
            line-height: 0.95;
            letter-spacing: -0.04em;
        }

        .sub {
            color: var(--muted);
            font-size: 1rem;
            margin: 0 0 1rem;
            max-width: 52ch;
        }

        .result {
            border-radius: 22px;
            background: linear-gradient(180deg, #fcfaff 0%, #f7f4ff 100%);
            border: 1px solid var(--border);
            padding: 1rem;
        }

        .label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #7b61c3;
            margin-bottom: 0.55rem;
        }

        .text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.35rem, 4vw, 2.2rem);
            font-weight: 700;
            color: #24154a;
            line-height: 1.12;
            word-break: break-word;
        }

        .empty {
            color: #8477a4;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            border: 0;
            border-radius: 14px;
            padding: 0.78rem 1rem;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .primary {
            color: #fff;
            background: linear-gradient(145deg, var(--brand) 0%, var(--brand-dark) 100%);
            box-shadow: 0 12px 24px rgba(70, 30, 152, 0.2);
        }

        .secondary {
            color: #4b2c86;
            background: #f5f2ff;
            border: 1px solid #ddd0ff;
        }

        .meta {
            margin-top: 0.9rem;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .suggestions {
            margin-top: 1rem;
        }

        .suggestions-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .suggestions-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #24154a;
            margin: 0;
        }

        .suggestions-count {
            font-size: 0.84rem;
            font-weight: 800;
            color: #6d28d9;
            background: #f4f0ff;
            border: 1px solid #e5dbff;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.85rem;
        }

        .product-card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: #fff;
            border: 1px solid #e9e0ff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(69, 36, 140, 0.08);
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(69, 36, 140, 0.12);
        }

        .product-media {
            aspect-ratio: 1 / 1;
            background: linear-gradient(180deg, #faf7ff 0%, #f3efff 100%);
            display: grid;
            place-items: center;
            padding: 0.8rem;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .product-empty {
            color: #8a84a5;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .product-body {
            padding: 0.85rem;
        }

        .product-name {
            margin: 0 0 0.45rem;
            font-size: 0.92rem;
            font-weight: 800;
            line-height: 1.25;
            color: #27144b;
            min-height: 2.4em;
        }

        .product-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .price {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #4c1d95;
        }

        .view-more {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6d28d9;
        }

        .no-suggestions {
            margin-top: 0.9rem;
            padding: 0.85rem 0.95rem;
            border-radius: 18px;
            background: #faf7ff;
            border: 1px solid #e5dbff;
            color: #5e4b84;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        $pedido = request('pedido', '');
    @endphp

    <main class="card">
        <div class="top">
            <div class="badge"><span class="dot" aria-hidden="true"></span> Pedido por voz</div>
            <a class="btn secondary" href="{{ route('home.parallel') }}">Volver a la home paralela</a>
        </div>

        <h1>Esto es lo que entendimos.</h1>
        <p class="sub">La voz se transcribió y te mostramos el texto para que puedas confirmarlo antes de seguir.</p>

        <section class="result" aria-label="Resultado del pedido por voz">
            <div class="label">Tu pedido</div>
            <div class="text {{ $pedido ? '' : 'empty' }}">
                {{ $pedido ?: 'No llegó ningún texto todavía.' }}
            </div>
        </section>

        <div class="actions">
            <a class="btn primary" href="{{ route('home.parallel') }}?q={{ urlencode($pedido) }}#productos">Buscar esto
                en el catálogo</a>
            <a class="btn secondary" href="{{ route('home.parallel') }}">Dictar otro pedido</a>
        </div>

        <section class="suggestions" aria-label="Productos sugeridos">
            <div class="suggestions-head">
                <h2 class="suggestions-title">Algunos productos que coinciden</h2>
                <span class="suggestions-count">{{ isset($suggestedProducts) ? $suggestedProducts->count() : 0 }}
                    sugerencias</span>
            </div>

            @if (!empty($suggestedProducts) && $suggestedProducts->isNotEmpty())
                <div class="cards">
                    @foreach ($suggestedProducts as $product)
                        <a class="product-card" href="{{ route('products.show', ['product' => $product->slug]) }}">
                            <div class="product-media">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="product-empty">Sin imagen</div>
                                @endif
                            </div>
                            <div class="product-body">
                                <p class="product-name">{{ $product->name }}</p>
                                <div class="product-price">
                                    <span
                                        class="price">${{ number_format($product->adjusted_price, 0, ',', '.') }}</span>
                                    <span class="view-more">Ver</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="no-suggestions">
                    Todavía no encontré productos claros para ese pedido. Probá decir una bebida, un snack, farmacia o
                    algo de súper.
                </div>
            @endif
        </section>

        <p class="meta">Si querés, en el siguiente paso puedo hacer que este texto se convierta automáticamente en
            categorías o productos concretos de tu tienda.</p>
    </main>

</body>

</html>
