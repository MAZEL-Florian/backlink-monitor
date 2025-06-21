<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlink_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backlink_id')->constrained()->onDelete('cascade');
            $table->enum('level', ['info', 'warning', 'error', 'debug'])->default('info');
            $table->text('message');
            $table->timestamp('created_at');
            
            $table->index(['backlink_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlink_logs');
    }
};
