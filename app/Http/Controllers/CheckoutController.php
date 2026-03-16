<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// Importaciones de Mercado Pago SDK v3
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

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
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

            // En desarrollo local usamos el modo LOCAL para que la SDK permita conexiones sin validar el SSL.
            // Esto evita el error "unable to get local issuer certificate" en entornos sin CA configurado.
            if (app()->environment('local')) {
                MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
            }

            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $itemsMP,
                "back_urls" => [
                    "success" => url(route('mercadopago.callback')),
                    "failure" => url(route('mercadopago.callback')),
                    "pending" => url(route('mercadopago.callback')),
                ],
                "external_reference" => (string) $order->id,
                "notification_url" => "https://954b-181-110-104-182.ngrok-free.app/api/mercadopago/webhook",
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
        // Validamos que la referencia externa exista
        if (!$request->has('external_reference')) {
            return redirect()->route('checkout.index')->with('error', 'No se pudo encontrar la referencia de la orden.');
        }

        $order = Order::where('id', $request->external_reference)->firstOrFail();

        // Si el pago es aprobado, actualizamos la orden
        if ($request->status == 'approved') {
            $order->status = 'completed';
            $order->mercadopago_payment_id = $request->payment_id;
            $order->save();

            // Vaciamos el carrito
            session()->forget('cart');

            // Redirigimos a la página de éxito
            return redirect()->route('checkout.success');
        }

        // Si el pago es rechazado o tiene otro estado
        // Opcional: Podrías querer guardar este estado en tu BD
        // $order->status = 'failed';
        // $order->save();

        return redirect()->route('checkout.index')->with('error', 'El pago fue rechazado o cancelado. Por favor, intenta de nuevo.');
    }

    public function thankyou()
    {
        return view('checkout.thankyou');
    }

    // Este método es para manejar los webhooks de Mercado Pago (notificaciones IPN)
    public function handleWebhook(Request $request)
    {
        // Mercado Pago envía el ID del pago en el query string o body
        // según el tipo de notificación (IPN o Webhook)
        $paymentId = $request->data['id'] ?? $request->id;

        if ($paymentId) {
            // Aquí deberías consultar a la API de MP el estado real del pago
            // usando el ID para mayor seguridad antes de marcar como completada.
            \Log::info("Webhook recibido para el pago: " . $paymentId);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
