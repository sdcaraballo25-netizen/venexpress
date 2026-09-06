<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PaymentOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentReconciliationService
{
    public function __construct(
        protected AllyFinancialService $allyFinancialService
    ) {
    }

    /**
     * Conciliación de una orden previamente validada
     * por el banco o proveedor de pagos.
     *
     * Este método no debe exponerse directamente a un
     * formulario público sin validación bancaria.
     */
    public function reconcileConfirmedOrder(
        PaymentOrder $paymentOrder,
        ?int $confirmedByUserId = null
    ): PaymentOrder {
        return DB::transaction(function () use (
            $paymentOrder,
            $confirmedByUserId
        ) {
            $order = PaymentOrder::query()
                ->lockForUpdate()
                ->findOrFail($paymentOrder->id);

            /*
             * Idempotencia:
             * si ya fue conciliada, no se vuelve a procesar.
             */
            if ($order->status === PaymentOrder::STATUS_CONFIRMED) {
                return $order->fresh([
                    'ally',
                    'package',
                ]);
            }

            if (
                in_array(
                    $order->status,
                    [
                        PaymentOrder::STATUS_REJECTED,
                        PaymentOrder::STATUS_EXPIRED,
                        PaymentOrder::STATUS_REVERSED,
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'La orden de pago ya está cerrada y no puede '
                    . 'ser conciliada.'
                );
            }

            if (! $order->bank_reference) {
                throw new RuntimeException(
                    'La orden no tiene referencia bancaria.'
                );
            }

            /*
             * Primero se valida el concepto de la orden.
             * Si falla, toda la transacción se revierte.
             */
            if ($order->purpose === PaymentOrder::PURPOSE_COD) {
                $this->validateCodOrder($order);
            }

            if ($order->purpose === PaymentOrder::PURPOSE_ALLY_DEBT) {
                $this->validateAllyDebtOrder($order);
            }

            /*
             * Confirmación bancaria.
             */
            $order->update([
                'status' => PaymentOrder::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by_user_id' => $confirmedByUserId,
            ]);

            /*
             * El COD se actualiza solamente después de que
             * la orden haya sido validada.
             */
            if ($order->purpose === PaymentOrder::PURPOSE_COD) {
                $this->markCodAsCollected(
                    $order,
                    $confirmedByUserId
                );
            }

            /*
             * El pago de una deuda de aliado sí impacta el
             * ledger: genera un crédito que reduce (o salda)
             * la deuda registrada en AllyFinancialTransaction.
             */
            if ($order->purpose === PaymentOrder::PURPOSE_ALLY_DEBT) {
                $this->allyFinancialService->recordAllyDebtPayment(
                    $order,
                    $confirmedByUserId
                );
            }

            return $order->fresh([
                'ally',
                'package',
            ]);
        });
    }

    /**
     * Valida una orden de pago contra entrega.
     */
    protected function validateCodOrder(PaymentOrder $order): void
    {
        if (! $order->package_id) {
            throw new RuntimeException(
                'La orden COD no tiene paquete asociado.'
            );
        }

        $package = Package::query()
            ->lockForUpdate()
            ->findOrFail($order->package_id);

        if (! $package->is_cod) {
            throw new RuntimeException(
                'El paquete asociado no está configurado '
                . 'como pago contra entrega.'
            );
        }

        /*
         * Si ya está liquidado, no se permite registrar
         * otro pago para el mismo COD.
         */
        if ($package->cod_status === Package::COD_LIQUIDADO) {
            throw new RuntimeException(
                'El pago contra entrega de este paquete '
                . 'ya fue liquidado.'
            );
        }

        $expectedAmount = round(
            (float) $package->cod_amount_usd,
            2
        );

        $paidAmount = round(
            (float) $order->amount_usd,
            2
        );

        if ($expectedAmount !== $paidAmount) {
            throw new RuntimeException(
                'El monto del pago no coincide con el monto COD.'
            );
        }
    }

    /**
     * Valida un pago realizado por un aliado.
     *
     * Esta validación ocurre ANTES de confirmar la orden y no
     * modifica el saldo. El crédito en el ledger se registra
     * después, una vez confirmada, mediante
     * AllyFinancialService::recordAllyDebtPayment().
     */
    protected function validateAllyDebtOrder(
        PaymentOrder $order
    ): void {
        if (! $order->ally_id) {
            throw new RuntimeException(
                'La orden de pago no tiene aliado asociado.'
            );
        }

        if ((float) $order->amount_usd <= 0) {
            throw new RuntimeException(
                'El monto del pago debe ser mayor que cero.'
            );
        }
    }

    /**
     * Marca un COD como cobrado y liquidado.
     *
     * Esto actualiza el estado operativo del paquete.
     * No genera una comisión ni un crédito financiero.
     */
    protected function markCodAsCollected(
        PaymentOrder $order,
        ?int $confirmedByUserId
    ): void {
        $package = Package::query()
            ->lockForUpdate()
            ->findOrFail($order->package_id);

        if ($package->cod_status === Package::COD_LIQUIDADO) {
            throw new RuntimeException(
                'El pago contra entrega de este paquete '
                . 'ya fue liquidado.'
            );
        }

        $package->forceFill([
            'cod_status' => Package::COD_LIQUIDADO,
            'cod_liquidated_at' => now(),
            'cod_collected_at' => now(),
            'cod_collected_by_user_id' => $confirmedByUserId,
        ])->save();
    }
}
