<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->date('data_nascimento');
            $table->string('nome_responsavel');
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->enum('turno', ['MANHÃ', 'TARDE']);
            $table->string('face_id')->nullable(); // ID da face no Amazon Rekognition
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
