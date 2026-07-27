<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

it('renders cart view correctly', function () {
    $view = $this->view('cart');
    $view->assertSee('Tu Carrito');
    $view->assertSee('El carrito está vacío');
});

it('renders food-vendors index correctly', function () {
    $vendors = collect([]);
    $view = $this->view('food-vendors.index', ['vendors' => $vendors]);
    $view->assertSee('Food & Delivery');
});

it('renders food-vendors menu correctly', function () {
    $vendor = new User(['id' => 1, 'name' => 'Test Vendor', 'store_name' => 'Test Store', 'store_description' => 'Test', 'store_logo' => null, 'store_banner' => null, 'is_open' => true, 'delivery_time' => 30, 'delivery_fee' => 100]);
    $categories = collect([]);
    $view = $this->view('food-vendors.menu', ['vendor' => $vendor, 'categories' => $categories]);
    $view->assertSee('Test Store');
});

it('renders products show correctly', function () {
    $product = new Product(['id' => 1, 'name' => 'Test Product', 'slug' => 'test-product', 'price' => 100, 'description' => 'Desc', 'vendor_id' => 1]);
    $relatedProducts = collect([]);
    $view = $this->view('products.show', ['product' => $product, 'relatedProducts' => $relatedProducts]);
    $view->assertSee('Test Product');
});

it('renders tracking view correctly', function () {
    $order = new Order(['id' => 12345, 'status' => 'pending', 'total' => 100, 'customer_name' => 'Test', 'customer_address' => 'Test']);
    $view = $this->view('order.tracking', ['order' => $order]);
    $view->assertSee('Seguimiento Pedido #12345');
});

it('renders voice order result correctly', function () {
    $suggestedProducts = collect([]);
    $view = $this->view('voice-order-result', ['suggestedProducts' => $suggestedProducts]);
    $view->assertSee('Pedido por voz');
});

it('renders delivery app view correctly', function () {
    $user = new User(['id' => 1, 'name' => 'Rider Test', 'email' => 'rider@test.com']);
    $this->actingAs($user);
    $view = $this->view('delivery.app');
    $view->assertSee('Hola, Rider Test');
});

it('renders pending approval view correctly', function () {
    $view = $this->view('delivery.pending-approval');
    $view->assertSee('Cuenta en Revisión');
});
