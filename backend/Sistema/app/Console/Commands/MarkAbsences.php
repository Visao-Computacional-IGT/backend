<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aluno;
use App\Models\Atividade;
use App\Models\Presenca;

class MarkAbsences extends Command
{
    protected $signature = 'absences:mark {turno}';
    protected $description = 'Marca falta automática para alunos de um turno específico';

    public function handle()
    {
        $turno = $this->argument('turno');
        $today = date('Y-m-d');

        $this->info("Iniciando marcação de faltas para o turno: $turno");

        // 1. Buscar ou criar atividade para o turno hoje
        $atividade = Atividade::firstOrCreate(
            ['data' => $today, 'turno' => $turno],
            ['teve_aula' => true]
        );

        if (!$atividade->teve_aula) {
            $this->warn("Hoje não teve aula para o turno $turno. Nenhuma falta marcada.");
            return;
        }

        // 2. Buscar todos os alunos do turno
        $alunos = Aluno::where('turno', $turno)->get();

        foreach ($alunos as $aluno) {
            // 3. Verificar se já existe presença (PRESENTE ou outra)
            $presenca = Presenca::where('aluno_id', $aluno->id)
                ->where('atividade_id', $atividade->id)
                ->first();

            if (!$presenca) {
                // Se não existe nada, marca falta
                Presenca::create([
                    'aluno_id' => $aluno->id,
                    'atividade_id' => $atividade->id,
                    'status' => 'FALTA',
                ]);
                $this->line("Falta marcada para: {$aluno->nome_completo}");
            }
        }

        $this->info("Processo concluído para o turno $turno.");
    }
}
