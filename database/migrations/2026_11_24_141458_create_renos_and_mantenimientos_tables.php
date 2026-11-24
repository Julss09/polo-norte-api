<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->integer('edad');
            $table->integer('nivelEnergia');
            $table->string('estado')->default('DISPONIBLE');
            $table->timestamps();
        });

        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->string('zona');
            $table->string('tipo');
            $table->date('fechaProgramada');
            $table->string('responsable');
            $table->string('estado')->default('PROGRAMADO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
        Schema::dropIfExists('renos');
    }
};
