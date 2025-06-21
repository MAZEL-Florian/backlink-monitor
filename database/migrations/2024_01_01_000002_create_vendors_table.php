<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->enum('category', ['catering', 'decoration', 'photography', 'music', 'venue', 'makeup', 'transport']);
            $table->text('description');
            $table->string('price_range');
            $table->string('location');
            $table->decimal('rating', 2, 1)->default(0);
            $table->string('image')->nullable();
            $table->json('services')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
