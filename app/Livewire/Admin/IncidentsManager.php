<?php
namespace App\Livewire\Admin;
use App\Models\Incident;
use App\Services\IncidentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
#[Layout('layouts.app')]
class IncidentsManager extends Component { use WithPagination; public string $status='abierta'; public string $search=''; public function updatingSearch(){ $this->resetPage(); } public function updateStatus(int $id,string $status):void{if(!in_array($status,Incident::STATUSES,true))return;$i=Incident::findOrFail($id);$i->update(['status'=>$status,'resolved_at'=>in_array($status,[Incident::STATUS_RESOLVED,Incident::STATUS_CLOSED],true)?now():null]);session()->flash('success','Incidencia actualizada.');} public function render(){ $q=Incident::with(['package','ally','reportedByUser'])->latest(); if($this->status!=='all')$q->where('status',$this->status); if(trim($this->search)!==''){$s=trim($this->search);$q->where(function($x)use($s){$x->where('type','like',"%$s%")->orWhere('description','like',"%$s%")->orWhereHas('package',fn($p)=>$p->where('tracking_number','like',"%$s%"));});} return view('livewire.admin.incidents-manager',['incidents'=>$q->paginate(15)]);}}
