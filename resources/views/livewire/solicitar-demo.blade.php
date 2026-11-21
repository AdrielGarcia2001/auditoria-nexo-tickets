<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
    @if ($enviado)
        <div class="flex min-h-[22rem] flex-col items-center justify-center text-center" x-data>
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-600">✓</div>
            <h3 class="mt-5 text-2xl font-bold text-slate-900">¡Solicitud enviada!</h3>
            <p class="mt-2 max-w-sm text-sm text-slate-500">Gracias por tu interés. Nuestro equipo se pondrá en contacto con vos para coordinar la demo de SIGO-NEXO.</p>
            <button type="button" wire:click="$set('enviado', false)" class="mt-6 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Enviar otra solicitud</button>
        </div>
    @else
        <form wire:submit="enviar" novalidate class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Nombre y apellido *</label>
                <input type="text" wire:model.live="nombre" autocomplete="name" placeholder="Ej. María González"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('nombre') border-red-400 @enderror">
                @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Correo corporativo *</label>
                <input type="email" wire:model.live="email" autocomplete="email" placeholder="nombre@empresa.com"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('email') border-red-400 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Empresa</label>
                    <input type="text" wire:model="empresa" autocomplete="organization" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Teléfono</label>
                    <input type="tel" wire:model="telefono" autocomplete="tel" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">¿Qué querés ver en la demo?</label>
                <textarea wire:model="mensaje" rows="3" placeholder="Contanos sobre tu operación…" class="w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></textarea>
            </div>
            <button type="submit" wire:loading.attr="disabled" class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800 disabled:opacity-60">
                <span wire:loading.remove wire:target="enviar">Solicitar demo →</span>
                <span wire:loading wire:target="enviar" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Enviando…
                </span>
            </button>
            <p class="text-center text-xs text-slate-400">Sin compromiso · Respondemos en menos de 24 h</p>
        </form>
    @endif
</div>
