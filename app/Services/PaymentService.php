<?php

namespace App\Services;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class PaymentService
{
    public function configureMercadoPago(): void
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token') ?? '');

        if (app()->environment('local')) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }
    }

    public function createPreference(array $items, string $orderId, string $successUrl, string $failureUrl, string $pendingUrl, string $webhookUrl)
    {
        $this->configureMercadoPago();

        $client = new PreferenceClient();

        return $client->create([
            "items" => $items,
            "back_urls" => [
                "success" => $successUrl,
                "failure" => $failureUrl,
                "pending" => $pendingUrl,
            ],
            "external_reference" => $orderId,
            "notification_url" => $webhookUrl,
        ]);
    }

    public function getPaymentDetails(int $paymentId)
    {
        $this->configureMercadoPago();
        $paymentClient = new PaymentClient();
        return $paymentClient->get($paymentId);
    }
}
