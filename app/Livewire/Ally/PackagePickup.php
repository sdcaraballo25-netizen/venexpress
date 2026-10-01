<?php
namespace App\Livewire\Ally;
use App\Models\Ally;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
#[Layout('layouts.ally')]
class PackagePickup extends Component {
 public string $trackingNumber=''; public string $recipientIdDoc=''; public ?Package $package=null; public ?string $message=null; public ?string $error=null;

 // Un paquete solo puede retirarse en la agencia que el cliente eligió
 // como punto de retiro (pickup_ally_id), no en cualquier agencia que
 // adivine/enumere el número de guía — mismo criterio que
 // PackageReception::isAuthorizedPickupPoint().
 protected function isAuthorizedPickupPoint(Package $package, Ally $ally): bool
 {
     return $package->pickup_ally_id !== null
         && (int) $package->pickup_ally_id === (int) $ally->id;
 }

 public function search():void{$this->package=null;$this->error=null; $ally=auth()->user()->resolveAlly(); if(!$ally)abort(403); $p=Package::where('tracking_number',trim($this->trackingNumber))->first(); if(!$p||!$this->isAuthorizedPickupPoint($p,$ally)){$this->error='Guía no encontrada.';return;} if($p->current_status!==Package::STATUS_LISTO_RETIRO){$this->error='La guía no está lista para retiro.';return;} $this->package=$p;}
 public function deliver():void{ $this->message=null;$this->error=null;$this->validate(['trackingNumber'=>'required','recipientIdDoc'=>'required|string|max:50']); try{ $ally=auth()->user()->resolveAlly(); if(!$ally)abort(403); $p=Package::where('tracking_number',trim($this->trackingNumber))->where('current_status',Package::STATUS_LISTO_RETIRO)->firstOrFail(); if(!$this->isAuthorizedPickupPoint($p,$ally)){throw new RuntimeException('Esta guía no está asignada a tu agencia.');} if($p->requires_delivery){throw new RuntimeException('Este envío requiere entrega a domicilio; no puede retirarse en agencia.');} if(trim($p->recipient_id_doc)!==trim($this->recipientIdDoc))throw new RuntimeException('El documento del receptor no coincide.');
     // completeAgencyPickup() (no changeStatus()) también registra el
     // cobro COD y delivery_completed_at; sin eso un COD retirado en
     // agencia nunca podía liquidarse (liquidateCod exige
     // cod_collected_at) y el cliente lo seguía viendo como pendiente.
     $this->package=app(PackageService::class)->completeAgencyPickup($p,(int)auth()->id(),$this->recipientIdDoc,'Retiro confirmado en agencia'); $this->message='Retiro confirmado. El paquete quedó ENTREGADO.'; $this->recipientIdDoc=''; }catch(RuntimeException $e){$this->error=$e->getMessage();}}
 public function render(){return view('livewire.ally.package-pickup');}
}
