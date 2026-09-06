<?php

namespace App\Livewire\Client;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Muestra al cliente los paquetes de pago contra entrega (COD) que
 * tiene a su nombre como destinatario y todavía no ha cancelado.
 *
 * Los botones de "Pago móvil" e "Inmediato" se muestran deshabilitados
 * a propósito: el flujo de cobro en línea todavía no está habilitado
 * (así lo pidió el negocio). Cuando se active, este componente debe:
 *
 *   1. Reutilizar App\Models\PaymentOrder (ya existe, lo usa el
 *      módulo Aliado/Admin) creando una orden con
 *      purpose = PaymentOrder::PURPOSE_COD y payer_type = Customer.
 *   2. Redirigir o abrir el modal de captura de referencia bancaria,
 *      igual que ya hace el flujo de Aliado en Cod.php.
 *   3. Confirmar la orden vía el mismo PaymentWebhookController /
 *      flujo administrativo que ya procesa pagos móviles hoy.
 *
 * No se duplica lógica de PaymentOrder aquí: solo se deja el punto
 * de extensión documentado.
 */
#[Layout('layouts.client')]
class PendingPayments extends Component
{
    /**
     * @return list<string>
     */
    protected function customerIdDocsForCurrentUser(): array
    {
        $user = Auth::user();

        return Customer::query()
            ->where('email', $user->email)
            ->pluck('id_doc')
            ->all();
    }

    public function render()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        $packages = collect();

        if (! empty($idDocs)) {
            $packages = Package::query()
                ->whereIn('recipient_id_doc', $idDocs)
                ->where('is_cod', true)
                ->where('cod_status', Package::COD_PENDIENTE)
                ->latest()
                ->get();
        }

        $totalPendingUsd = $packages->sum(
            fn (Package $package) => (float) $package->cod_amount_usd
        );

        return view('livewire.client.pending-payments', [
            'packages' => $packages,
            'totalPendingUsd' => $totalPendingUsd,
        ]);
    }
}
