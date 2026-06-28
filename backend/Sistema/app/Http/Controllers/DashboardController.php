<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Calcula a quantidade absoluta de alunos aptos ao benefício social (RN09).
     * Mínimo de 75% de presença.
     */
    public function beneficios()
    {
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();
        $query = Aluno::withCount(['presencas as total_presencas' => function ($query) {
            $query->whereIn('status', ['PRESENTE', 'FALTA JUSTIFICADA', 'PRESENTE (Sobrescrito)']);
        }])
        ->withCount(['presencas as total_atividades']);

        if ($user->role === 'professor') {
            $turmaIds = $user->turmas()->pluck('turmas.id');
            $query->whereIn('turma_id', $turmaIds);
        }

        $alunos = $query->get();

        $aptos = 0;
        $detalhes = [];

        foreach ($alunos as $aluno) {
            $frequencia = $aluno->total_atividades > 0 
                ? ($aluno->total_presencas / $aluno->total_atividades) * 100 
                : 100; // Se não teve aula, está apto por padrão

            if ($frequencia >= 75) {
                $aptos++;
            }

            $detalhes[] = [
                'id' => $aluno->id,
                'nome' => $aluno->nome_completo,
                'frequencia' => round($frequencia, 2) . '%',
                'apto' => $frequencia >= 75
            ];
        }

        return response()->json([
            'quantidade_absoluta_aptos' => $aptos,
            'total_alunos' => $alunos->count(),
            'detalhes' => $detalhes
        ]);
    }
}
