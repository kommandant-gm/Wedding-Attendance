<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scan_logs')) {
            Schema::create('scan_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 40);
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('token_hash', 64)->nullable();
                $table->string('exception_class')->nullable();
                $table->text('error_message')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
