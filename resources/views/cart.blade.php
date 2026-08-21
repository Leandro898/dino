<x-front-layout title="Tu Carrito - Marketplace Bariloche" bodyClass="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">
    <main class="max-w-7xl mx-auto px-6 py-10 lg:py-20 pb-36 md:pb-10">

        <h1 class="text-4xl font-black dark:text-white uppercase mb-10 tracking-tighter">
            Tu Carrito
        </h1>

        @if (session('cart') && count(session('cart')) > 0)

            @php $total = 0; @endphp

            <div class="grid lg:grid-cols-3 gap-10">

                <!-- TABLA PRODUCTOS -->
                <div class="lg:col-span-2 bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6">

                    <div
                        class="hidden md:grid grid-cols-6 font-bold text-sm uppercase tracking-widest text-gray-400 pb-4 border-b">
                        <div class="col-span-3">Producto</div>
                        <div>Precio</div>
                        <div>Cantidad</div>
                        <div>Subtotal</div>
                    </div>

                    @foreach (session('cart') as $id => $details)
                        @php
                            $subtotal = $details['price'] * $details['quantity'];
                            $total += $subtotal;
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-6 items-center gap-4 py-6 border-b last:border-0">

                            <!-- PRODUCTO -->
                            <div class="md:col-span-3 flex items-center gap-4">

                                @if ($details['image'])
                                    <img src="{{ asset('storage/' . $details['image']) }}"
                                        class="w-20 h-20 object-cover rounded-xl" loading="lazy">
                                @endif

                                <div>
                                    <h3 class="font-bold dark:text-white">
                                        {{ $details['name'] }}
                                    </h3>

                                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 mt-2">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- PRECIO -->
                            <div class="font-semibold">
                                ${{ number_format($details['price'], 0, ',', '.') }}
                            </div>

                            <!-- CANTIDAD -->
                            <div class="flex items-center gap-2">
                                    <form action="{{ route('cart.update', $id) }}" method="POST"
                                        class="flex items-center gap-2">
                                        @csrf
                                        <button type="button" onclick="decreaseQuantity(this)"
                                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#0f0f0f] hover:bg-gray-200 dark:hover:bg-[#1a1a1a] flex items-center justify-center font-bold transition-colors">
                                            −
                                        </button>
                                        <input type="hidden" name="quantity" class="quantity-input"
                                            value="{{ $details['quantity'] }}">
                                        <span
                                            class="quantity-display w-8 text-center font-semibold">{{ $details['quantity'] }}</span>
                                        <button type="button" onclick="increaseQuantity(this)"
                                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#0f0f0f] hover:bg-gray-200 dark:hover:bg-[#1a1a1a] flex items-center justify-center font-bold transition-colors">
                                            +
                                        </button>
                                        <button type="submit" class="hidden submit-btn">Actualizar</button>
                                    </form>
                            </div>

                            <!-- SUBTOTAL -->
                            <div class="font-bold">
                                ${{ number_format($subtotal, 0, ',', '.') }}
                            </div>

                        </div>
                    @endforeach

                </div>


                <!-- RESUMEN DEL PEDIDO -->
                <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-sm p-6 h-fit">

                    <h2 class="text-xl font-bold mb-6 dark:text-white">
                        Resumen del pedido
                    </h2>

                    <div class="flex justify-between mb-4 text-sm">
                        <span>Subtotal</span>
                        <span>${{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between mb-6 text-lg font-bold border-t pt-4 dark:text-white">
                        <span>Total</span>
                        <span>${{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}"
                        class="checkout-button block w-full text-center bg-black hover:bg-gradient-to-r hover:from-purple-600 hover:to-purple-700 transition-colors text-white py-4 rounded-xl font-bold uppercase text-sm mb-36 md:mb-0">
                        Finalizar Compra
                    </a>

                </div>

            </div>
        @else
            <div class="text-center py-20 border-2 border-dashed border-gray-200 rounded-3xl">
                <p class="text-gray-400 font-bold uppercase tracking-widest mb-6">
                    El carrito está vacío
                </p>

                <a href="{{ route('home') }}"
                    class="inline-block bg-black text-white px-8 py-4 rounded-xl font-bold uppercase text-sm">
                    Explorar productos
                </a>
            </div>

        @endif

    </main>

    @push('scripts')
    <script>
        function increaseQuantity(button) {
            const form = button.closest('form');
            const input = form.querySelector('.quantity-input');
            const display = form.querySelector('.quantity-display');
            let quantity = parseInt(input.value);
            quantity++;
            updateCartQuantity(form, quantity, display);
        }

        function decreaseQuantity(button) {
            const form = button.closest('form');
            const input = form.querySelector('.quantity-input');
            const display = form.querySelector('.quantity-display');
            let quantity = parseInt(input.value);
            if (quantity > 1) {
                quantity--;
                updateCartQuantity(form, quantity, display);
            }
        }

        function setQuantityButtonsState(form, disabled) {
            form.querySelectorAll('button[type="button"]').forEach(button => {
                button.disabled = disabled;
                button.classList.toggle('opacity-50', disabled);
                button.classList.toggle('cursor-not-allowed', disabled);
            });
            document.querySelectorAll('.checkout-button').forEach(checkoutButton => {
                checkoutButton.classList.toggle('opacity-50', disabled);
                checkoutButton.classList.toggle('cursor-not-allowed', disabled);
                checkoutButton.classList.toggle('pointer-events-none', disabled);
                if (disabled) {
                    checkoutButton.setAttribute('aria-disabled', 'true');
                } else {
                    checkoutButton.removeAttribute('aria-disabled');
                }
            });
        }

        function updateCartQuantity(form, quantity, display) {
            const input = form.querySelector('.quantity-input');
            const formData = new FormData(form);
            formData.set('quantity', quantity);

            setQuantityButtonsState(form, true);

            // Obtener el precio y calcular el nuevo subtotal
            const row = form.closest('.grid');
            if (!row) {
                setQuantityButtonsState(form, false);
                return;
            }

            const cells = row.querySelectorAll(':scope > div');
            if (cells.length < 4) {
                setQuantityButtonsState(form, false);
                return;
            }

            // Precio está en la 2da celda (índice 1)
            const priceCell = cells[1];
            const priceText = priceCell.textContent.replace(/[$,.]/g, '').trim();
            const price = parseInt(priceText) || 0;
            const newSubtotal = price * quantity;

            // Subtotal está en la última celda
            const subtotalCell = cells[cells.length - 1];

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar la cantidad del input y la cantidad mostrada
                        if (input) {
                            input.value = quantity;
                        }
                        display.textContent = quantity;

                        // Actualizar el subtotal en la UI
                        if (subtotalCell) {
                            subtotalCell.textContent = '$' + newSubtotal.toLocaleString('es-AR');
                        }

                        // Recalcular el total general
                        updateTotalPrice();
                    }
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    setQuantityButtonsState(form, false);
                });
        }

        function updateTotalPrice() {
            // Obtener todas las filas del carrito
            const rows = document.querySelectorAll('.grid.grid-cols-1');
            let total = 0;

            rows.forEach(row => {
                const cells = row.querySelectorAll(':scope > div');
                if (cells.length >= 4) {
                    const subtotalCell = cells[cells.length - 1];
                    const text = subtotalCell.textContent.replace(/[$,.]/g, '').trim();
                    const value = parseInt(text) || 0;
                    total += value;
                }
            });

            const subtotalElement = document.querySelector('.flex.justify-between.mb-4 span:last-child');
            const totalElement = document.querySelector('.flex.justify-between.mb-6 span:last-child');
            if (subtotalElement) subtotalElement.textContent = '$' + total.toLocaleString('es-AR');
            if (totalElement) totalElement.textContent = '$' + total.toLocaleString('es-AR');
        }
    </script>
    @endpush
</x-front-layout>
