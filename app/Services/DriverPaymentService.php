<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DriverPaymentService
{
    public function amount(): float
    {
        return round((float) config('venexpress.driver_remuneration_usd', env('VENEXPRESS_DRIVER_REMUNERATION_USD', 1.00)), 2);
    }

    public function createForDeliveredPackage(Package $package, Driver $driver): DriverPayment
    {
        return DB::transaction(function () use ($package, $driver) {
            $existing = DriverPayment::query()->where('package_id', $package->id)->lockForUpdate()->first();
            if ($existing) return $existing;

            $amount = $this->amount();
            $payment = DriverPayment::create([
                'driver_id' => $driver->id,
                'package_id' => $package->id,
                'amount_usd' => $amount,
                'status' => DriverPayment::STATUS_PENDING,
            ]);

            AuditLog::create([
                'actor_user_id' => $driver->user_id,
                'action' => 'driver.payment.created',
                'target_type' => DriverPayment::class,
                'target_id' => $payment->id,
                'description' => "Generó remuneración pendiente para la guía {$package->tracking_number}.",
                'metadata' => [
                    'package_id' => $package->id,
                    'driver_id' => $driver->id,
                    'amount_usd' => $amount,
                ],
                'ip_address' => request()?->ip(),
            ]);

            $package->forceFill([
                'driver_remuneration_usd' => $amount,
                'driver_remuneration_status' => Package::REMUNERATION_PENDING,
            ])->save();

            return $payment;
        });
    }

    public function cancel(DriverPayment $payment, int $userId, ?string $notes = null): DriverPayment
    {
        return DB::transaction(function () use ($payment, $userId, $notes) {
            $locked = DriverPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== DriverPayment::STATUS_PENDING) {
                throw new RuntimeException('Solo se puede cancelar una remuneración pendiente.');
            }

            $locked->update([
                'status' => DriverPayment::STATUS_CANCELLED,
                'notes' => $notes,
            ]);

            $locked->package()->update([
                'driver_remuneration_status' => Package::REMUNERATION_CANCELLED,
            ]);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'driver.payment.cancelled',
                'target_type' => DriverPayment::class,
                'target_id' => $locked->id,
                'description' => 'Canceló una remuneración de repartidor pendiente.',
                'metadata' => [
                    'package_id' => $locked->package_id,
                    'driver_id' => $locked->driver_id,
                    'amount_usd' => (float) $locked->amount_usd,
                    'notes' => $notes,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $locked->fresh();
        });
    }

    public function markPaid(DriverPayment $payment, int $userId, ?string $notes=null): DriverPayment
    {
        return DB::transaction(function () use ($payment,$userId,$notes) {
            $locked=DriverPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== DriverPayment::STATUS_PENDING) throw new RuntimeException('Esta remuneración ya fue procesada.');
            $locked->update(['status'=>DriverPayment::STATUS_PAID,'paid_at'=>now(),'paid_by_user_id'=>$userId,'notes'=>$notes]);
            $locked->package()->update([
                'driver_remuneration_status' => Package::REMUNERATION_PAID,
                'driver_remuneration_paid_at' => now(),
            ]);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'driver.payment.paid',
                'target_type' => DriverPayment::class,
                'target_id' => $locked->id,
                'description' => 'Marcó una remuneración de repartidor como pagada.',
                'metadata' => [
                    'package_id' => $locked->package_id,
                    'driver_id' => $locked->driver_id,
                    'amount_usd' => (float) $locked->amount_usd,
                    'notes' => $notes,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $locked->fresh();
        });
    }
}
