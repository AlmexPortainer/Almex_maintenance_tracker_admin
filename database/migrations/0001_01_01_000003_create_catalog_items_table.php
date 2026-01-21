<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // INSTRUMENTO
        Schema::create('intruments', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->enum('type', ['INSTRUMENTO', 'PLANO'])->default('INSTRUMENTO');

            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->enum('form', ['Instrumento Electronico', 'Instrumento Mecánico', 'Instrumento Simple'])->default('Instrumento Simple');

            $table->enum('Variable Unidad De Medida', ['Conductividad', 'Concentración', 'Flujo', 'Humedad', 'Indice de Refracción', 'KVA', 'MVP', 'N/A', 'Peso', 'Presión', 'Temperatura', 'Transmitancia', 'pH'])->default('N/A')->nullable();
            // ⁉️ traslate
            $table->string('equipo')->nullable(); // ⁉️ traslate
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('code')->unique();

            $table->string('emt_value')->nullable();
            $table->decimal('emt_value_decimal', 12, 4)->nullable();
            $table->string('emt_unit')->nullable();
            $table->boolean('emt_symmetry')->nullable()->default(false);

            $table->string('file_manual')->default('N/A');
            $table->enum('types_of_criticality', ['NO_CRITICO', 'CRITICO'])->default('NO_CRITICO');
            $table->enum('level_of_criticality', ['BAJA', 'MEDIA', 'ALTA'])->default('BAJA');

            // Frecuencias de eventos
            $table->unsignedSmallInteger('calibration_periodicity_days')->default(0);
            $table->unsignedSmallInteger('validation_periodicity_days')->default(0);
            $table->unsignedSmallInteger('maintenance_periodicity_days')->default(0);

            // Snapshot de calibración
            $table->date('last_calibration_date')->nullable();
            $table->string('last_calibration_user')->nullable();
            $table->date('next_calibration_date')->nullable();

            // Snapshot de verificacion
            $table->date('last_validation_date')->nullable();
            $table->string('last_validation_user')->nullable();
            $table->date('next_validation_date')->nullable();

            // Snapshot de Mantenimiento
            $table->date('last_maintenance_date')->nullable();
            $table->string('last_maintenance_user')->nullable();
            $table->date('next_maintenance_date')->nullable();

            $table->boolean('is_operational')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->index(['name', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
