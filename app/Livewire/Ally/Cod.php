<?php
namespace App\Livewire\Ally;
use App\Models\Package;
use App\Services\PackageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
#[Layout('layouts.ally')]
class Cod extends Component {
 use WithPagination; public string $status='pending';
 public function liquidate(int $id): void { try { $ally=auth()->user()->resolveAlly(); if(!$ally) abort(403); $p=Package::where('ally_id',$ally->id)->findOrFail($id); app(PackageService::class)->liquidateCod($p,(int)auth()->id()); session()->flash('success','COD liquidado correctamente.'); } catch(RuntimeException $e){session()->flash('error',$e->getMessage());}}
 public function render(){ $ally=auth()->user()->resolveAlly(); if(!$ally) abort(403); $q=Package::where('ally_id',$ally->id)->where('is_cod',true)->latest(); if($this->status==='pending')$q->where('cod_status',Package::COD_PENDIENTE); elseif($this->status==='liquidated')$q->where('cod_status',Package::COD_LIQUIDADO); return view('livewire.ally.cod',['packages'=>$q->paginate(15)]);}
}
