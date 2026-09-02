<?php
namespace App\Livewire\Ally;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Services\PackageService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
#[Layout('layouts.ally')]
class PackagePickup extends Component {
 public string $trackingNumber=''; public string $recipientIdDoc=''; public ?Package $package=null; public ?string $message=null; public ?string $error=null;
 public function search():void{$this->package=null;$this->error=null;$p=Package::where('tracking_number',trim($this->trackingNumber))->first(); if(!$p){$this->error='Guía no encontrada.';return;} if($p->current_status!==Package::STATUS_LISTO_RETIRO){$this->error='La guía no está lista para retiro.';return;} $this->package=$p;}
 public function deliver():void{ $this->message=null;$this->error=null;$this->validate(['trackingNumber'=>'required','recipientIdDoc'=>'required|string|max:50']); try{ $ally=auth()->user()->resolveAlly(); if(!$ally)abort(403); $p=Package::where('tracking_number',trim($this->trackingNumber))->where('current_status',Package::STATUS_LISTO_RETIRO)->firstOrFail(); if($p->requires_delivery){throw new RuntimeException('Este envío requiere entrega a domicilio; no puede retirarse en agencia.');} if(trim($p->recipient_id_doc)!==trim($this->recipientIdDoc))throw new RuntimeException('El documento del receptor no coincide.'); $this->package=app(PackageService::class)->changeStatus($p,Package::STATUS_ENTREGADO,(int)auth()->id(),'Retiro confirmado en agencia',null,PackageHistory::EVENT_ENTREGA,'Agencia destino','Destinatario'); $this->message='Retiro confirmado. El paquete quedó ENTREGADO.'; $this->recipientIdDoc=''; }catch(RuntimeException $e){$this->error=$e->getMessage();}}
 public function render(){return view('livewire.ally.package-pickup');}
}
