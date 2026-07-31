<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle incoming WhatsApp Webhooks
     */
    public function handle(Request $request)
    {
        // Log all incoming data to a specific file (storage/logs/whatsapp.log)
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/whatsapp.log'),
        ])->info('--- Nuevo Mensaje Recibido ---', $request->all());

        // We must return a 200 OK so the provider knows we received it successfully
        return response()->json(['status' => 'success']);
    }
}
