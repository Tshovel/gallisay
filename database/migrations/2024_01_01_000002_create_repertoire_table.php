<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repertoire', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('composer');
            $table->string('pdf_path')->nullable();
            $table->string('audio_url')->nullable();
            $table->string('period_tag');
            $table->enum('difficulty_tag', ['Facile', 'Medio', 'Difficile'])->default('Medio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repertoire');
    }
};
