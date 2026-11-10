<div class="space-y-5">

    {{-- ============================================================
         1) SUBIR ARCHIVO
    ============================================================ --}}
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">1. Subí el Excel</h2>
        <p class="mt-1 text-sm text-slate-500">
            Formato <code class="rounded bg-slate-100 px-1">.xlsx</code> (o el export de Google Sheets).
            Cada fila se inserta como <strong>ticket nuevo</strong>: nunca se actualizan los existentes,
            porque el N° de orden se repite en distintos sitios.
        </p>

        <div class="mt-4">
            <input type="file" wire:model.live="archivo" accept=".xlsx,.csv"
                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700">
            <div wire:loading wire:subject="archivo" class="mt-2 text-sm text-slate-500">
                ⏳ Leyendo archivo…
            </div>
            @error('archivo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ============================================================
         2) ELEGIR HOJA
    ============================================================ --}}
    @if ($hojas)
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">2. Elegí la hoja</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($hojas as $nombre => $filas)
                    @php($esTicket = in_array($nombre, \App\Services\Importacion\ImportadorTickets::HOJAS_TICKETS, true))
                    <button wire:click="seleccionarHoja('{{ $nombre }}')"
                            class="rounded-full px-3 py-1.5 text-sm ring-1 transition
                                   {{ $hojaSeleccionada === $nombre
                                      ? 'bg-slate-900 text-white ring-slate-900'
                                      : ($esTicket ? 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50'
                                                   : 'bg-slate-50 text-slate-400 ring-slate-200') }}">
                        {{ $nombre }}
                        <span class="ml-1 text-xs opacity-70">({{ $filas }})</span>
                        @unless($esTicket)
                            <span class="ml-1 text-xs">· no es ticket</span>
                        @endunless
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============================================================
         3) PREVIEW (todos los datos del Excel)
    ============================================================ --}}
    @if ($hojaSeleccionada && $columnas)
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-900">3. Revisá los datos</h2>
                    <p class="text-sm text-slate-500">
                        Hoja <strong>{{ $hojaSeleccionada }}</strong> ·
                        {{ number_format($totalFilas, 0, ',', '.') }} tickets para importar
                        @if ($ignoradas) · {{ $ignoradas }} fila(s) vacías o sin N° de orden ignoradas @endif
                        @if ($duplicados) · <span class="text-amber-600">{{ $duplicados }} posible(s) repetido(s) (se importan igual como nuevo)</span> @endif
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @if ($resultado)
                        <span class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                            ✅ {{ number_format($resultado['importados'], 0, ',', '.') }} tickets importados
                            @if (count($resultado['fallidos']))
                                · ❌ {{ count($resultado['fallidos']) }} fallidos
                            @endif
                        </span>
                        <button wire:click="reiniciar"
                                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                            Importar otra hoja
                        </button>
                    @else
                        <button wire:click="importar" wire:loading.attr="disabled"
                                @unless($totalFilas) disabled @endunless
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">
                            <span wire:loading.remove wire:target="importar">
                                ⬇️ Importar {{ number_format($totalFilas, 0, ',', '.') }} tickets
                            </span>
                            <span wire:loading wire:target="importar">⏳ Importando…</span>
                        </button>
                    @endif
                </div>
            </div>

            @if ($resultado && count($resultado['fallidos']))
                <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 ring-1 ring-red-200">
                    <strong>Filas con error:</strong>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach (array_slice($resultado['fallidos'], 0, 20) as $fila => $error)
                            <li>Fila {{ $fila }}: {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- tabla con TODAS las columnas del Excel --}}
            <div class="mt-4 max-h-[28rem] overflow-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="sticky top-0 bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-2 py-2 text-left font-semibold text-slate-600">#</th>
                            @foreach ($columnas as $columna)
                                <th class="whitespace-nowrap px-2 py-2 text-left font-semibold text-slate-600"
                                    title="{{ $columna }}">
                                    {{ $columna }}
                                </th>
                            @endforeach
                            <th class="whitespace-nowrap px-2 py-2 text-left font-semibold text-slate-600">aviso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-2 py-1.5 text-slate-400">{{ $fila['_fila'] ?? '' }}</td>
                                @foreach ($columnas as $indice => $columna)
                                    <td class="max-w-[22rem] truncate px-2 py-1.5 text-slate-700"
                                        title="{{ $fila[$this->campoDe($columna)] ?? '' }}">
                                        {{ $fila[$this->campoDe($columna)] ?? '' }}
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-2 py-1.5">
                                    @if ($fila['_duplicado'] ?? false)
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-amber-700">repetido</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($totalFilas > $filasMostradas)
                <p class="mt-2 text-xs text-slate-400">
                    Mostrando las primeras {{ $filasMostradas }} de {{ number_format($totalFilas, 0, ',', '.') }} filas.
                    Al importar se insertan <strong>todas</strong>.
                </p>
            @endif
        </div>
    @endif
</div>
