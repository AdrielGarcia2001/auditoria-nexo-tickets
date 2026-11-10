<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // ====== CAMPOS EXACTOS DEL EXCEL ======
            $table->string('mda')->nullable();                       // MDA / Notificacion / MDA (MESA DE AYUDA)
            $table->string('numero_orden', 30);                      // N° ORDEN / Orden (se repite entre sitios)
            $table->string('clase_orden', 10);                       // FSMA = correctivo | FSMC = preventivo
            $table->date('fecha_entrada')->nullable();               // FECHA ENTRADA
            $table->date('fecha_modificacion_maestro')->nullable();  // Fecha modific.maestro orden (hoja MANT CORRECTIVO 2022)
            $table->string('grupo_planificador', 10)->nullable();    // GRUPO PLANIFICADOR (M01 / M02)
            $table->string('texto_breve')->nullable();               // TEXTO BREVE
            $table->text('descripcion')->nullable();                 // DESCRIPCION
            $table->string('calle')->nullable();                     // CALLE
            $table->string('ubicacion')->nullable();                 // UBICACION (texto libre del Excel)
            $table->foreignId('sitio_id')->nullable()->constrained('sitios')->nullOnDelete();
            $table->string('provincia_codigo', 10)->default('A36');  // PROVINCIA (codigo A32..A48)
            $table->string('provincia_nombre', 60)->default('FORMOSA');
            $table->integer('anio')->nullable();                     // Año (hoja MANT CORRECTIVO 2022)
            $table->string('area_empresa')->nullable();              // Área de empresa (hoja MANT CORRECTIVO 2022)
            $table->string('doc_adjunto')->nullable();               // Doc. Adj. (hoja MANT CORRECTIVO 2022)
            $table->date('inicio_extrema')->nullable();              // INICIO EXTREMA
            $table->date('fin_extrema')->nullable();                 // FIN EXTREMA
            $table->string('estatus', 30)->nullable();               // ESTATUS (FIEJ, ENEJ, ENEJ RECL...)
            $table->string('prioridad', 30)->nullable();             // PRIORIDAD (24 horas, 48 horas, 72 horas, 7 Días, 15 Días, 30 Días, Programado Mensual)
            $table->string('estado', 30)->nullable();                // ESTADO del Excel (REALIZADO, DERIVADO, EN EJECUCION...)
            $table->string('lo_resolvio')->nullable();               // LO RESOLVIO
            $table->date('fecha_resolucion')->nullable();            // FECHA DE RESOLUCION
            $table->text('documentacion')->nullable();               // DOCUMENTACION (FOTO O DOC) -> URL de Drive
            $table->text('comentarios')->nullable();                 // COMENTARIOS SOBRE LA RESOLUCION DEL TICKET
            $table->text('fotos_texto')->nullable();                 // columna "Fotos" (hoja LIMP)
            $table->text('comentarios_extra')->nullable();           // columna "Comentarios" (hoja LIMP)

            // ====== TIPO Y WORKFLOW DEL SISTEMA ======
            $table->string('tipo', 20)->default('correctivo');       // correctivo | preventivo (FSMA/FSMC)
            $table->boolean('reabierto')->default(false);            // el admin lo reabrio (puede cambiar preventivo->correctivo)
            $table->string('estado_workflow', 20)->default('nuevo'); // nuevo | resuelto | cerrado
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelto_at')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrado_at')->nullable();

            // ====== GOOGLE DRIVE ======
            $table->string('drive_folder_url')->nullable();          // carpeta creada por el supervisor al resolver

            // ====== SLA ======
            $table->timestamp('sla_vencimiento')->nullable();        // fecha_entrada + horas de PRIORIDAD
            $table->timestamp('sla_alertado_at')->nullable();

            // ====== TRAZABILIDAD DE IMPORTACION ======
            $table->string('origen_hoja')->nullable();               // hoja de la que vino
            $table->integer('fila_origen')->nullable();              // numero de fila en el Excel
            $table->foreignId('importado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('importado_at')->nullable();

            $table->timestamps();

            $table->index('numero_orden');
            $table->index('estado_workflow');
            $table->index('fecha_entrada');
            $table->index('sla_vencimiento');
            $table->index(['provincia_codigo', 'estado_workflow']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
