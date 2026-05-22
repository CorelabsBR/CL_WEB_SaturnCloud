<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request, MercadoPagoService $mercadoPago)
    {
        if (! $mercadoPago->validateWebhookSignature($request)) {
            Log::warning('Mercado Pago webhook com assinatura inválida', [
                'query' => $request->query(),
                'body' => $request->all(),
            ]);

            return response()->json(['ok' => false], 401);
        }

        $type = $request->input('type') ?? $request->query('type') ?? $request->query('topic');

        if ($type && $type !== 'payment') {
            return response()->json(['ok' => true]);
        }

        $paymentId = data_get($request->input('data'), 'id')
            ?? $request->query('data.id')
            ?? $request->input('id')
            ?? $request->query('id');

        if (!$paymentId) {
            return response()->json(['ok' => true]);
        }

        try {
            $payment = $mercadoPago->getPayment((string) $paymentId);
            $mercadoPago->syncPayment($payment);
        } catch (\Throwable $e) {
            Log::warning('Mercado Pago webhook erro', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false], 400);
        }

        return response()->json(['ok' => true]);
    }
}
