<?php

use App\Enums\InboundStatus;
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
        Schema::create('inbound', function (Blueprint $table) {
            $table->id();
            $table->string('inbound_code')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', InboundStatus::values())->default(InboundStatus::PENDING->value); // PENDING, COMPLETED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound');
    }
};
