<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->enum('form', [
                'Instrumento Electrónico',
                'Instrumento Mecánico',
                'Instrumento Simple',
                'Reactivo',
                'Otro',
            ])->default('Instrumento Simple')->change();

            $table->string('variable_unit_of_measure')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->enum('form', [
                'Instrumento Electronico',
                'Instrumento Mecánico',
                'Instrumento Simple',
            ])->default('Instrumento Simple')->change();

            $table->enum('variable_unit_of_measure', [
                'Conductividad', 'Concentración', 'Flujo', 'Humedad',
                'Indice de Refracción', 'KVA', 'MVP', 'N/A', 'Peso',
                'Presión', 'Temperatura', 'Transmitancia', 'pH',
            ])->default('N/A')->nullable()->change();
        });
    }
};
