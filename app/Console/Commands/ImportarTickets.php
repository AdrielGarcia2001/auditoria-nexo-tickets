<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Importacion\ImportadorTickets;
use Illuminate\Console\Command;

/**
 * Importa tickets desde la linea de comandos:
 *
 *   php artisan tickets:importar "ruta/al/archivo.xlsx"
 *   php artisan tickets:importar "archivo.xlsx" --hoja="Tks Correctivos"
 *   php artisan tickets:importar "archivo.xlsx" --preview
 */
class ImportarTickets extends Command
{
    protected $signature = 'tickets:importar
        {archivo : Ruta del archivo .xlsx}
        {--hoja= : Nombre exacto de la hoja a importar}
        {--preview : Solo muestra el preview, no importa nada}
        {--force : No pregunta confirmacion}
        {--user=admin@nexo.com : Usuario que registra la importacion}';

    protected $description = 'Importa tickets desde el Excel de Formosa (inserta como tickets nuevos, nunca actualiza)';

    public function handle(ImportadorTickets $importador): int
    {
        $ruta = $this->argument('archivo');

        if (! is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");

            return self::FAILURE;
        }

        $hojas = $importador->hojas($ruta);

        $this->info('Hojas encontradas:');
        foreach ($hojas as $nombre => $filas) {
            $esTicket = in_array($nombre, ImportadorTickets::HOJAS_TICKETS, true);
            $this->line(sprintf('  %s%s (%d filas)', $nombre, $esTicket ? '' : '  [no-ticket]', $filas));
        }

        $hoja = $this->option('hoja')
            ?? collect(ImportadorTickets::HOJAS_TICKETS)->first(fn ($h) => isset($hojas[$h]))
            ?? array_key_first($hojas);

        $this->newLine();
        $this->info("Parseando hoja: {$hoja}");

        $parseo = $importador->parsear($ruta, $hoja);

        $this->info(sprintf(
            '  %d tickets listos, %d filas ignoradas, %d posibles repetidos.',
            count($parseo['filas']),
            $parseo['ignoradas'],
            collect($parseo['filas'])->where('_duplicado', true)->count()
        ));

        // muestra las primeras filas
        $this->newLine();
        $this->table(
            ['Fila', 'N° Orden', 'Clase', 'Fecha entrada', 'Prioridad', 'Ubicacion', 'Estado Excel'],
            collect($parseo['filas'])->take(10)->map(fn ($f) => [
                $f['_fila'],
                $f['numero_orden'] ?? '',
                $f['clase_orden'] ?? '',
                $f['fecha_entrada'] ?? '',
                $f['prioridad'] ?? '',
                mb_substr((string) ($f['ubicacion'] ?? ''), 0, 30),
                $f['estado'] ?? '',
            ])->all()
        );

        if ($this->option('preview')) {
            $this->comment('Modo preview: no se importó nada.');

            return self::SUCCESS;
        }

        $usuario = User::where('email', $this->option('user'))->first();

        if (! $usuario) {
            $this->error("No existe el usuario: {$this->option('user')}");

            return self::FAILURE;
        }

        $this->newLine();
        if (! $this->option('force') && ! $this->confirm(sprintf('Importar %d tickets como NUEVOS?', count($parseo['filas'])))) {
            $this->comment('Cancelado.');

            return self::SUCCESS;
        }

        $resultado = $importador->importar($parseo['filas'], $usuario, $hoja);

        $this->info("✅ Importados: {$resultado['importados']}");
        if ($resultado['fallidos']) {
            $this->warn('Fallidos: '.count($resultado['fallidos']));
            foreach (array_slice($resultado['fallidos'], 0, 20) as $fila => $error) {
                $this->line("  fila {$fila}: {$error}");
            }
        }

        return self::SUCCESS;
    }
}
