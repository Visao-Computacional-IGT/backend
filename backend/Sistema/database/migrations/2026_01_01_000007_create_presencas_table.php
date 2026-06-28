<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('atividade_id')->constrained('atividades')->onDelete('cascade');
            $table->enum('status', ['PRESENTE', 'FALTA', 'FALTA JUSTIFICADA', 'FALTA AVISADA', 'PRESENTE (Sobrescrito)']);
            $table->timestamp('atualizado_em')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};
