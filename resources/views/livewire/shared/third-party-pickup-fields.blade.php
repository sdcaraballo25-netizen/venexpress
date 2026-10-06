{{-- Retiro por un tercero autorizado (App\Livewire\Concerns\HandlesThirdPartyPickup). --}}
<label class="flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" wire:model.live="byThirdParty" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
    Lo retira un tercero autorizado por el destinatario
</label>

@if ($byThirdParty)
    <div class="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-xs text-amber-800">
            Escribe la cédula de quien retira. Pide su cédula y una copia de la cédula del destinatario, y fotografía ambas.
        </p>

        <div>
            <label for="thirdPartyName" class="text-sm font-medium text-slate-700">Nombre de quien retira</label>
            <input id="thirdPartyName" type="text" wire:model="thirdPartyName" autocomplete="off"
                   class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
            @error('thirdPartyName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label for="thirdPartyIdPhoto" class="text-sm font-medium text-slate-700">Foto de la cédula de quien retira</label>
                <input id="thirdPartyIdPhoto" type="file" accept="image/*" capture="environment" wire:model="thirdPartyIdPhoto"
                       class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium">
                @error('thirdPartyIdPhoto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="recipientIdCopy" class="text-sm font-medium text-slate-700">Foto de la copia de la cédula del destinatario</label>
                <input id="recipientIdCopy" type="file" accept="image/*" capture="environment" wire:model="recipientIdCopy"
                       class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium">
                @error('recipientIdCopy') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
@endif
