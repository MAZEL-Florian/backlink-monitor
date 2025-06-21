<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('source_url');
            $table->string('target_url');
            $table->string('anchor_text')->nullable();
            $table->integer('domain_authority')->nullable();
            $table->integer('page_authority')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_dofollow')->default(true);
            $table->integer('status_code')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('first_found_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
            $table->index('source_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlinks');
    }
};
