<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlink_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backlink_id')->constrained()->onDelete('cascade');
            $table->integer('status_code')->nullable();
            $table->boolean('is_active');
            $table->boolean('is_dofollow');
            $table->string('anchor_text')->nullable();
            $table->integer('response_time')->nullable(); // en millisecondes
            $table->timestamp('checked_at');
            $table->text('error_message')->nullable();
            $table->boolean('exact_match')->nullable(); // Nouveau champ
            $table->timestamps();

            $table->index(['backlink_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlink_checks');
    }
};
