<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            // Estado operativo original de Access: Activo | Baja | Fuera de Servicio | Stock.
            // Baja y Fuera de Servicio se administran en listados aparte (no ensucian el catálogo).
            $table->string('status')->nullable()->index()->after('is_operational');
        });
    }

    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
