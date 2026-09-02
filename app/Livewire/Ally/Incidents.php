<?php
namespace App\Livewire\Ally;
use App\Models\Incident;
use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
#[Layout('layouts.ally')]
class Incidents extends Component { use WithPagination; public string $tracking=''; public string $type='OTRA'; public string $description=''; public function create():void{$this->validate(['tracking'=>'required','type'=>'required|max:100','description'=>'required|min:5|max:2000']);$ally=auth()->user()->resolveAlly();if(!$ally)abort(403);$p=Package::where('ally_id',$ally->id)->where('tracking_number',trim($this->tracking))->first();if(!$p){$this->addError('tracking','La guía no pertenece a tu agencia.');return;}Incident::create(['ally_id'=>$ally->id,'package_id'=>$p->id,'reported_by_user_id'=>auth()->id(),'type'=>$this->type,'description'=>$this->description,'status'=>Incident::STATUS_OPEN]);$this->reset(['tracking','description']);session()->flash('success','Incidencia registrada.');} public function render(){ $ally=auth()->user()->resolveAlly();if(!$ally)abort(403);return view('livewire.ally.incidents',['incidents'=>Incident::where('ally_id',$ally->id)->with('package')->latest()->paginate(15)]);}}
