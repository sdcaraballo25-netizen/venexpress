<?php

namespace App\Observers;

use App\Models\Package;
use App\Services\AllyFinancialService;

class PackageObserver
{
    public function created(Package $package): void
    {
        if (
            ! $package->ally_id
            || ! $package->commission_amount_usd
        ) {
            return;
        }

        app(
            AllyFinancialService::class
        )->recordPackageCommission(
            $package
        );
    }
}
