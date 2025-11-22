<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('recipient')->nullable()->after('name')->comment('Nama Penerima/Instansi');
            $table->string('city')->nullable()->after('address')->comment('Kota/Kabupaten');
            $table->string('province')->nullable()->after('city')->comment('Provinsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['recipient', 'city', 'province']);
        });
    }
};
