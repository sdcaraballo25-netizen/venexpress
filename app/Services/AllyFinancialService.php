<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AllyFinancialTransaction;
use App\Models\AllySettlement;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class AllyFinancialService
{
    /**
     * Obtiene el saldo disponible real del aliado.
     */
    public function getBalance(int $allyId): float
    {
        $credit = AllyFinancialTransaction::query()
            ->where('ally_id', $allyId)
            ->where(
                'direction',
                AllyFinancialTransaction::DIRECTION_CREDIT
            )
            ->sum('amount_usd');

        $debit = AllyFinancialTransaction::query()
            ->where('ally_id', $allyId)
            ->where(
                'direction',
                AllyFinancialTransaction::DIRECTION_DEBIT
            )
            ->sum('amount_usd');

        return round(
            (float) $credit - (float) $debit,
            2
        );
    }

    /**
     * Obtiene el total histórico generado.
     */
    public function getGeneratedCommission(
        int $allyId
    ): float {
        return round(
            (float) AllyFinancialTransaction::query()
                ->where('ally_id', $allyId)
                ->where(
                    'type',
                    AllyFinancialTransaction::TYPE_COMMISSION
                )
                ->where(
                    'direction',
                    AllyFinancialTransaction::DIRECTION_CREDIT
                )
                ->sum('amount_usd'),
            2
        );
    }

    /**
     * Total pagado al aliado.
     */
    public function getPaidAmount(
        int $allyId
    ): float {
        return round(
            (float) AllyFinancialTransaction::query()
                ->where('ally_id', $allyId)
                ->where(
                    'type',
                    AllyFinancialTransaction::TYPE_SETTLEMENT
                )
                ->where(
                    'direction',
                    AllyFinancialTransaction::DIRECTION_DEBIT
                )
                ->sum('amount_usd'),
            2
        );
    }

    /**
     * Registra una comisión generada por una guía.
     *
     * Es idempotente: la misma guía no puede generar dos
     * créditos financieros.
     */
    public function recordPackageCommission(
        Package $package
    ): ?AllyFinancialTransaction {
        $amount = round(
            (float) ($package->commission_amount_usd ?? 0),
            2
        );

        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(
            function () use ($package, $amount) {

                $existing =
                    AllyFinancialTransaction::query()
                        ->where('ally_id', $package->ally_id)
                        ->where(
                            'type',
                            AllyFinancialTransaction::TYPE_COMMISSION
                        )
                        ->where(
                            'source_type',
                            Package::class
                        )
                        ->where(
                            'source_id',
                            $package->id
                        )
                        ->lockForUpdate()
                        ->first();

                if ($existing) {
                    return $existing;
                }

                return AllyFinancialTransaction::create([
                    'ally_id' => $package->ally_id,
                    'direction' =>
                        AllyFinancialTransaction::DIRECTION_CREDIT,
                    'type' =>
                        AllyFinancialTransaction::TYPE_COMMISSION,
                    'amount_usd' => $amount,
                    'source_type' => Package::class,
                    'source_id' => $package->id,
                    'reference' =>
                        'COM-' . $package->tracking_number,
                    'description' =>
                        'Comisión generada por la guía '
                        . $package->tracking_number,
                    'metadata' => [
                        'tracking_number' =>
                            $package->tracking_number,
                        'commission_percentage_used' =>
                            $package->commission_percentage_used,
                    ],
                ]);
            }
        );
    }

    /**
     * Crea una solicitud de liquidación.
     *
     * Las solicitudes pendientes reservan saldo.
     * El descuento financiero ocurre cuando se marca como pagada.
     */
    public function createSettlement(
        int $allyId,
        float $amountUsd,
        ?string $paymentMethod,
        ?string $reference,
        ?string $notes,
        ?int $userId
    ): AllySettlement {
        $amountUsd = round($amountUsd, 2);

        if ($amountUsd <= 0) {
            throw new InvalidArgumentException(
                'El monto de la liquidación debe ser mayor que cero.'
            );
        }

        if (
            $paymentMethod !== null
            && ! in_array(
                $paymentMethod,
                AllySettlement::PAYMENT_METHODS,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'El método de pago seleccionado no es válido.'
            );
        }

        return DB::transaction(
            function () use (
                $allyId,
                $amountUsd,
                $paymentMethod,
                $reference,
                $notes,
                $userId
            ) {
                Ally::query()
                    ->whereKey($allyId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $balance = $this->getBalance($allyId);

                /*
                * Las solicitudes pendientes todavía no son débitos,
                * pero deben reservar el dinero para evitar duplicaciones.
                */
                $pendingAmount = AllySettlement::query()
                    ->where('ally_id', $allyId)
                    ->where(
                        'status',
                        AllySettlement::STATUS_PENDING
                    )
                    ->sum('amount_usd');

                $availableBalance = round(
                    $balance - (float) $pendingAmount,
                    2
                );

                if ($amountUsd > $availableBalance) {
                    throw new RuntimeException(
                        sprintf(
                            'Saldo disponible para solicitar: $%.2f.',
                            $availableBalance
                        )
                    );
                }

                return AllySettlement::create([
                    'ally_id' => $allyId,

                    'amount_usd' => $amountUsd,

                    'status' =>
                        AllySettlement::STATUS_PENDING,

                    'payment_method' =>
                        $paymentMethod,

                    'payment_reference' =>
                        $reference,

                    'notes' => $notes,

                    'requested_by_user_id' =>
                        $userId,

                    'requested_at' => now(),
                ]);
            }
        );
    }
    /**
     * Marca una liquidación como pagada y descuenta el saldo.
     *
     * Las operaciones están dentro de una misma transacción
     * y el aliado queda bloqueado durante la operación.
     */
    public function markSettlementPaid(
        int $settlementId,
        int $adminUserId,
        ?string $paymentMethod = null,
        ?string $paymentReference = null
    ): AllySettlement {
        return DB::transaction(
            function () use (
                $settlementId,
                $adminUserId,
                $paymentMethod,
                $paymentReference
            ) {
                $settlement =
                    AllySettlement::query()
                        ->lockForUpdate()
                        ->findOrFail($settlementId);

                if (! $settlement->isPending()) {
                    throw new RuntimeException(
                        'Solo una liquidación pendiente puede marcarse como pagada.'
                    );
                }

                $ally =
                    Ally::query()
                        ->whereKey($settlement->ally_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                $balance =
                    $this->getBalance(
                        $ally->id
                    );

                $amount =
                    round(
                        (float) $settlement->amount_usd,
                        2
                    );

                if ($amount > $balance) {
                    throw new RuntimeException(
                        sprintf(
                            'La liquidación supera el saldo disponible de $%.2f.',
                            $balance
                        )
                    );
                }

                $transaction =
                    AllyFinancialTransaction::query()
                        ->where('ally_id', $ally->id)
                        ->where(
                            'type',
                            AllyFinancialTransaction::TYPE_SETTLEMENT
                        )
                        ->where(
                            'source_type',
                            AllySettlement::class
                        )
                        ->where(
                            'source_id',
                            $settlement->id
                        )
                        ->lockForUpdate()
                        ->first();

                if ($transaction) {
                    throw new RuntimeException(
                        'Esta liquidación ya posee un movimiento financiero.'
                    );
                }

                $transaction =
                    AllyFinancialTransaction::create([
                        'ally_id' => $ally->id,
                        'direction' =>
                            AllyFinancialTransaction::DIRECTION_DEBIT,
                        'type' =>
                            AllyFinancialTransaction::TYPE_SETTLEMENT,
                        'amount_usd' => $amount,
                        'source_type' =>
                            AllySettlement::class,
                        'source_id' =>
                            $settlement->id,
                        'reference' =>
                            $paymentReference
                            ?: $settlement->payment_reference
                            ?: 'LIQ-' . $settlement->id,
                        'description' =>
                            'Liquidación pagada al aliado '
                            . $ally->business_name,
                        'metadata' => [
                            'payment_method' =>
                                $paymentMethod
                                ?: $settlement->payment_method,
                        ],
                        'created_by_user_id' =>
                            $adminUserId,
                    ]);

                $settlement->update([
                    'status' =>
                        AllySettlement::STATUS_PAID,
                    'payment_method' =>
                        $paymentMethod
                        ?: $settlement->payment_method,
                    'payment_reference' =>
                        $paymentReference
                        ?: $settlement->payment_reference,
                    'paid_by_user_id' =>
                        $adminUserId,
                    'paid_at' => now(),
                ]);

                return $settlement->fresh();
            }
        );
    }

    /**
     * Cancela una liquidación pendiente.
     *
     * No afecta el saldo porque todavía no había sido descontada.
     */
    public function cancelSettlement(
        int $settlementId,
        int $adminUserId
    ): AllySettlement {
        return DB::transaction(
            function () use (
                $settlementId,
                $adminUserId
            ) {
                $settlement =
                    AllySettlement::query()
                        ->lockForUpdate()
                        ->findOrFail($settlementId);

                if (! $settlement->isPending()) {
                    throw new RuntimeException(
                        'Solo una liquidación pendiente puede cancelarse.'
                    );
                }

                $settlement->update([
                    'status' =>
                        AllySettlement::STATUS_CANCELLED,
                ]);

                return $settlement->fresh();
            }
        );
    }

    /**
     * Revierte una liquidación ya pagada.
     *
     * El reverso NO modifica el movimiento original.
     * Crea un crédito nuevo en el ledger.
     */
    public function reverseSettlement(
        int $settlementId,
        int $adminUserId,
        ?string $reason = null
    ): AllySettlement {
        return DB::transaction(
            function () use (
                $settlementId,
                $adminUserId,
                $reason
            ) {
                $settlement =
                    AllySettlement::query()
                        ->lockForUpdate()
                        ->findOrFail($settlementId);

                if (! $settlement->isPaid()) {
                    throw new RuntimeException(
                        'Solo una liquidación pagada puede revertirse.'
                    );
                }

                $ally =
                    Ally::query()
                        ->whereKey($settlement->ally_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                $original =
                    AllyFinancialTransaction::query()
                        ->where('ally_id', $ally->id)
                        ->where(
                            'type',
                            AllyFinancialTransaction::TYPE_SETTLEMENT
                        )
                        ->where(
                            'source_type',
                            AllySettlement::class
                        )
                        ->where(
                            'source_id',
                            $settlement->id
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $original) {
                    throw new RuntimeException(
                        'No se encontró el movimiento financiero original de la liquidación.'
                    );
                }

                $existingReversal =
                    AllyFinancialTransaction::query()
                        ->where(
                            'reversed_transaction_id',
                            $original->id
                        )
                        ->lockForUpdate()
                        ->first();

                if ($existingReversal) {
                    throw new RuntimeException(
                        'Esta liquidación ya fue revertida.'
                    );
                }

                $reversal =
                    AllyFinancialTransaction::create([
                        'ally_id' => $ally->id,
                        'direction' =>
                            AllyFinancialTransaction::DIRECTION_CREDIT,
                        'type' =>
                            AllyFinancialTransaction::TYPE_REVERSAL,
                        'amount_usd' =>
                            $original->amount_usd,
                        'source_type' =>
                            AllySettlement::class,
                        'source_id' =>
                            $settlement->id,
                        'reversed_transaction_id' =>
                            $original->id,
                        'reference' =>
                            'REV-LIQ-' . $settlement->id,
                        'description' =>
                            'Reverso de liquidación '
                            . $settlement->id
                            . (
                                $reason
                                    ? ': ' . $reason
                                    : ''
                            ),
                        'metadata' => [
                            'reason' => $reason,
                        ],
                        'created_by_user_id' =>
                            $adminUserId,
                    ]);

                $settlement->update([
                    'status' =>
                        AllySettlement::STATUS_REVERSED,
                    'reversed_by_user_id' =>
                        $adminUserId,
                    'reversed_at' => now(),
                    'reversal_transaction_id' =>
                        $reversal->id,
                ]);

                return $settlement->fresh();
            }
        );
    }

    /**
     * Ajuste manual administrativo.
     */
    public function createAdjustment(
        int $allyId,
        float $amountUsd,
        string $direction,
        string $description,
        int $adminUserId,
        ?string $reference = null
    ): AllyFinancialTransaction {
        $amountUsd = round(
            $amountUsd,
            2
        );

        if ($amountUsd <= 0) {
            throw new InvalidArgumentException(
                'El monto del ajuste debe ser mayor que cero.'
            );
        }

        if (! in_array(
            $direction,
            [
                AllyFinancialTransaction::DIRECTION_CREDIT,
                AllyFinancialTransaction::DIRECTION_DEBIT,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'La dirección del ajuste no es válida.'
            );
        }

        if (trim($description) === '') {
            throw new InvalidArgumentException(
                'Debes indicar el motivo del ajuste.'
            );
        }

        return DB::transaction(
            function () use (
                $allyId,
                $amountUsd,
                $direction,
                $description,
                $adminUserId,
                $reference
            ) {
                $ally =
                    Ally::query()
                        ->whereKey($allyId)
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $direction
                    === AllyFinancialTransaction::DIRECTION_DEBIT
                ) {
                    $balance =
                        $this->getBalance(
                            $ally->id
                        );

                    if ($amountUsd > $balance) {
                        throw new RuntimeException(
                            sprintf(
                                'El ajuste supera el saldo disponible de $%.2f.',
                                $balance
                            )
                        );
                    }
                }

                return AllyFinancialTransaction::create([
                    'ally_id' => $ally->id,
                    'direction' => $direction,
                    'type' =>
                        AllyFinancialTransaction::TYPE_ADJUSTMENT,
                    'amount_usd' => $amountUsd,
                    'reference' =>
                        $reference,
                    'description' =>
                        $description,
                    'created_by_user_id' =>
                        $adminUserId,
                ]);
            }
        );
    }

    /**
     * Historial financiero paginado.
     */
    public function history(
        int $allyId,
        int $perPage = 25
    ) {
        return AllyFinancialTransaction::query()
            ->where(
                'ally_id',
                $allyId
            )
            ->with('createdBy:id,name,email')
            ->latest('created_at')
            ->latest('id')
            ->paginate(
                $perPage
            );
    }
}
