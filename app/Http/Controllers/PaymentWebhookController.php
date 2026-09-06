<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentReconciliationService $reconciliationService
    ) {
    }

    /**
     * Endpoint temporal para pruebas internas.
     *
     * No debe utilizarse como integración bancaria definitiva.
     * En producción, la confirmación debe provenir de un webhook
     * autenticado o de una consulta directa al banco.
     */
    public function confirmForTesting(
        Request $request,
        PaymentOrder $paymentOrder
    ): JsonResponse {
        if (
            ! in_array(
                $request->user()?->role,
                [
                    'admin_principal',
                    'admin_operativo',
                ],
                true
            )
        ) {
            abort(403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'bank_reference' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'bank_code' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
                'bank_name' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($paymentOrder->isConfirmed()) {
            return response()->json([
                'message' => 'La orden ya estaba confirmada.',
                'payment_order' => $paymentOrder->fresh(),
            ]);
        }

        try {
            /*
             * La referencia se guarda únicamente para la prueba.
             * En producción debe validarse contra el banco antes
             * de llamar al servicio de conciliación.
             */
            $paymentOrder->update([
                'bank_reference' => $request->string(
                    'bank_reference'
                )->toString(),

                'bank_code' => $request->string(
                    'bank_code'
                )->toString() ?: null,

                'bank_name' => $request->string(
                    'bank_name'
                )->toString() ?: null,

                'status' => PaymentOrder::STATUS_PROCESSING,
            ]);

            $confirmedOrder = $this->reconciliationService
                ->reconcileConfirmedOrder(
                    $paymentOrder,
                    $request->user()->id
                );

            return response()->json([
                'message' => 'Pago conciliado correctamente.',
                'payment_order' => $confirmedOrder,
            ]);
        } catch (Throwable $exception) {
            Log::error('Error al conciliar pago de prueba', [
                'payment_order_id' => $paymentOrder->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
