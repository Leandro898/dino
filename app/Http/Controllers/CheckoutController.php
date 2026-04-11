<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\OrderEmailService;
// Importaciones de Mercado Pago SDK v3
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'address' => 'required',
            'phone' => 'required',
        ]);

        $cart = session()->get('cart', []);
        \Log::info('Cart contents: ' . json_encode($cart));
        if (empty($cart)) return redirect()->back()->with('error', 'El carrito está vacío');

        try {
            DB::beginTransaction();

            $totalGeneral = 0;
            $itemsMP = []; // Array para Mercado Pago

            foreach ($cart as $id => $details) {
                $subtotal = $details['price'] * $details['quantity'];
                $totalGeneral += $subtotal;

                // Preparar items para MP
                $itemsMP[] = [
                    "id" => $id,
                    "title" => $details['name'] ?? "Producto $id", // Asegúrate de tener el nombre en el carro
                    "quantity" => (int) $details['quantity'],
                    "unit_price" => (float) $details['price'],
                    "currency_id" => "ARS"
                ];
            }

            // Crear la Orden
            $order = Order::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $totalGeneral,
                'status' => 'pending',
                'payment_method' => 'mercadopago', // Actualizado
            ]);

            foreach ($cart as $id => $details) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $id,
                    'price'      => $details['price'],
                    'quantity'   => $details['quantity'],
                    'subtotal'   => $details['price'] * $details['quantity'],
                ]);
            }

            // --- LÓGICA DE MERCADO PAGO ---
            $this->configureMercadoPago();

            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $itemsMP,
                "back_urls" => [
                    "success" => url(route('mercadopago.callback')),
                    "failure" => url(route('mercadopago.callback')),
                    "pending" => url(route('mercadopago.callback')),
                ],
                "external_reference" => (string) $order->id,
                "notification_url" => route('mercadopago.webhook'),
            ]);

            // Guardamos el ID de la preferencia en nuestra orden
            $order->mercadopago_preference_id = $preference->id;
            $order->save();

            DB::commit();

            return view('checkout.mercadopago', [
                'preferenceId' => $preference->id,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());
            if (method_exists($e, 'getApiResponse')) {
                $response = $e->getApiResponse();
                \Log::error('Mercado Pago API Response Status: ' . $response->getStatusCode());
                \Log::error('Mercado Pago API Response Content: ' . json_encode($response->getContent()));
            }
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function handleMercadoPagoCallback(Request $request)
    {
        // Este callback es la redirección del usuario después de pagar.
        // No debemos usarlo como única prueba de pago final porque su status puede venir del cliente.
        // La confirmación real se aplica en el webhook de Mercado Pago.
        if (!$request->has('external_reference')) {
            return redirect()->route('checkout.index')->with('error', 'No se pudo encontrar la referencia de la orden.');
        }

        $order = Order::where('id', $request->external_reference)->firstOrFail();

        if ($request->status === 'approved') {
            return redirect()->route('checkout.success');
        }

        return redirect()->route('checkout.index')->with('error', 'El pago fue rechazado o cancelado. Por favor, intenta de nuevo.');
    }

    public function thankyou()
    {
        return view('checkout.thankyou');
    }

    private function configureMercadoPago(): void
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        // En desarrollo local usamos el modo LOCAL para evitar errores de certificado.
        if (app()->environment('local')) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }
    }

    // Este método es para manejar los webhooks de Mercado Pago (notificaciones IPN)
    public function handleWebhook(Request $request, OrderEmailService $orderEmailService)
    {
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (!$paymentId) {
            \Log::warning('Webhook de Mercado Pago recibido sin payment ID.');
            return response()->json(['status' => 'ok'], 200);
        }

        $this->configureMercadoPago();
        $paymentClient = new PaymentClient();

        try {
            $payment = $paymentClient->get((int) $paymentId);
        } catch (\Exception $e) {
            \Log::error('Error al consultar el pago de Mercado Pago desde el webhook.', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }

        $externalReference = $payment->external_reference ?? null;
        if (!$externalReference) {
            \Log::warning('Pago de Mercado Pago sin external_reference.', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $order = Order::where('id', $externalReference)->first();
        if (!$order) {
            \Log::warning('Orden no encontrada para el webhook de Mercado Pago.', [
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $status = strtolower($payment->status ?? '');
        if ($status === 'approved' && $order->status !== 'completed') {
            $order->status = 'completed';
            $order->mercadopago_payment_id = $paymentId;
            $order->save();

            $orderEmailService->sendOrderConfirmation($order);
            \Log::info('Orden marcada como completada y email enviado.', [
                'order_id' => $order->id,
                'payment_id' => $paymentId,
            ]);
        }

        if ($status !== 'approved' && $order->status === 'pending') {
            // Guardamos el estado real del pago si no está aprobado.
            $order->status = $status;
            $order->save();
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
