<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presenca_id')->constrained('presencas')->onDelete('cascade');
            $table->text('descricao');
            $table->string('arquivo_path')->nullable(); // Caminho do anexo
            $table->enum('status', ['PENDENTE', 'APROVADO', 'REJEITADO'])->default('PENDENTE');
            $table->foreignId('aprovado_por')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativas');
    }
};
