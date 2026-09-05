<?php

namespace App\Livewire\Admin;

use App\Models\DriverPayment;
use App\Services\DriverPaymentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
class DriverPayments extends Component
{
    use WithPagination;
    public string $status='pendiente';
    public string $search='';
    public function updatingSearch(): void { $this->resetPage(); }
    public function markPaid(int $id): void {
        try { app(DriverPaymentService::class)->markPaid(DriverPayment::findOrFail($id),(int)auth()->id()); session()->flash('success','Remuneración marcada como pagada.'); }
        catch(RuntimeException $e){ session()->flash('error',$e->getMessage()); }
    }
    public function cancelPayment(int $id): void {
        try { app(DriverPaymentService::class)->cancel(DriverPayment::findOrFail($id),(int)auth()->id()); session()->flash('success','Remuneración cancelada.'); }
        catch(RuntimeException $e){ session()->flash('error',$e->getMessage()); }
    }
    public function render(){
        $q=DriverPayment::query()->with(['driver.user','package'])->latest();
        if($this->status!=='all') $q->where('status',$this->status);
        if(trim($this->search)!==''){ $s=trim($this->search); $q->where(function($x)use($s){$x->whereHas('package',fn($p)=>$p->where('tracking_number','like',"%$s%"))->orWhereHas('driver.user',fn($u)=>$u->where('name','like',"%$s%"));}); }
        return view('livewire.admin.driver-payments',['payments'=>$q->paginate(15)]);
    }
}
