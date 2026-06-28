<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Turma;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar turmas conforme o requisito (Preto, Azul, Branco)
        $turmas = ['Preto', 'Azul', 'Branco'];
        foreach ($turmas as $nome) {
            Turma::firstOrCreate(['nome' => $nome]);
        }

        // Criar Administrador padrão
        User::firstOrCreate(
            ['email' => 'admin@facial.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ]
        );

        // Criar Professor de exemplo
        $professor = User::firstOrCreate(
            ['email' => 'professor@facial.com'],
            [
                'name' => 'Professor Exemplo',
                'password' => Hash::make('prof123'),
                'role' => 'professor'
            ]
        );

        // Vincular professor à turma Preto
        $turmaPreto = Turma::where('nome', 'Preto')->first();
        if ($turmaPreto) {
            $professor->turmas()->syncWithoutDetaching([$turmaPreto->id]);
        }
    }
}
