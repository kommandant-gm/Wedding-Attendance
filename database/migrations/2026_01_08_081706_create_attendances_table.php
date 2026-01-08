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
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guest_id')->constrained()->onDelete('cascade');
                $table->timestamp('checked_in_at');
                $table->string('checked_in_by')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->index(['guest_id', 'checked_in_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
