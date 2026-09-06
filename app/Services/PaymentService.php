<?php

namespace App\Services;

use App\Models\PaymentOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PaymentService
{
    /**
     * Crea una orden de pago pendiente.
     *
     * Todavía NO confirma el pago ni descuenta saldos.
     */
    public function createOrder(array $data): PaymentOrder
    {
        $amount = round(
            (float) ($data['amount_usd'] ?? 0),
            2
        );

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'El monto del pago debe ser mayor que cero.'
            );
        }

        if (empty($data['payer_type'])) {
            throw new InvalidArgumentException(
                'El tipo de pagador es obligatorio.'
            );
        }

        if (empty($data['payer_id'])) {
            throw new InvalidArgumentException(
                'El pagador es obligatorio.'
            );
        }

        if (empty($data['purpose'])) {
            throw new InvalidArgumentException(
                'El concepto del pago es obligatorio.'
            );
        }

        if (empty($data['payment_method'])) {
            throw new InvalidArgumentException(
                'El método de pago es obligatorio.'
            );
        }

        return PaymentOrder::create([
            'order_number' => $this->generateOrderNumber(),

            'payer_type' => $data['payer_type'],
            'payer_id' => $data['payer_id'],

            'purpose' => $data['purpose'],

            'ally_id' => $data['ally_id'] ?? null,
            'package_id' => $data['package_id'] ?? null,

            'amount_usd' => $amount,

            'payment_method' => $data['payment_method'],

            'status' => PaymentOrder::STATUS_PENDING,

            'expires_at' => $data['expires_at'] ?? null,

            'metadata' => $data['metadata'] ?? null,

            'created_by_user_id' =>
                $data['created_by_user_id'] ?? null,
        ]);
    }

    /**
     * Confirma una orden de pago.
     *
     * Este método NO debe recibir confirmaciones
     * directamente desde el navegador.
     *
     * La confirmación real vendrá del banco,
     * de un webhook o de un proceso autorizado.
     */
    public function confirmOrder(
        PaymentOrder $order,
        string $bankReference,
        ?string $bankCode = null,
        ?string $bankName = null,
        ?int $confirmedByUserId = null
    ): PaymentOrder {
        return DB::transaction(function () use (
            $order,
            $bankReference,
            $bankCode,
            $bankName,
            $confirmedByUserId
        ) {
            $order = PaymentOrder::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            /*
             * Si ya está confirmado, devolvemos el mismo registro.
             * Esto evita duplicar pagos si el banco reenvía
             * una confirmación.
             */

            if ($order->isConfirmed()) {
                return $order;
            }

            if (
                $order->status === PaymentOrder::STATUS_REVERSED
                || $order->status === PaymentOrder::STATUS_REJECTED
            ) {
                throw new RuntimeException(
                    'Esta orden no puede confirmarse.'
                );
            }

            if (empty($bankReference)) {
                throw new InvalidArgumentException(
                    'La referencia bancaria es obligatoria.'
                );
            }

            /*
             * La referencia bancaria debe ser única.
             */

            $duplicate = PaymentOrder::query()
                ->where('bank_reference', $bankReference)
                ->where('id', '!=', $order->id)
                ->exists();

            if ($duplicate) {
                throw new RuntimeException(
                    'La referencia bancaria ya fue utilizada.'
                );
            }

            $order->update([
                'status' => PaymentOrder::STATUS_CONFIRMED,

                'bank_reference' => $bankReference,
                'bank_code' => $bankCode,
                'bank_name' => $bankName,

                'confirmed_at' => now(),

                'confirmed_by_user_id' =>
                    $confirmedByUserId,
            ]);

            /*
             * IMPORTANTE:
             *
             * Aquí todavía NO descontamos deudas.
             * La conciliación financiera será el siguiente paso.
             */

            return $order->fresh();
        });
    }

    /**
     * Genera una referencia interna única.
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'PAY-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(6));
        } while (
            PaymentOrder::query()
                ->where('order_number', $number)
                ->exists()
        );

        return $number;
    }
}
