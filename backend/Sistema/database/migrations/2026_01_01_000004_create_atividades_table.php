<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->enum('turno', ['MANHÃ', 'TARDE']);
            $table->boolean('teve_aula')->default(true);
            $table->string('descricao')->nullable(); // Ex: Feriado, Recesso
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
