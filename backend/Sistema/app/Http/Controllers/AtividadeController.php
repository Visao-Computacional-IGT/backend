<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\Aluno;
use App\Models\Presenca;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtividadeController extends Controller
{
    /**
     * Lista atividades.
     */
    public function index()
    {
        return response()->json(Atividade::orderBy('data', 'desc')->get());
    }

    /**
     * Cria uma atividade manualmente (Admin).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'data' => 'required|date',
            'turno' => 'required|in:MANHÃ,TARDE',
            'teve_aula' => 'required|boolean',
            'descricao' => 'nullable|string',
        ]);

        $user = Auth::guard('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Apenas administradores podem criar atividades manualmente'], 403);
        }

        $atividade = Atividade::create($request->all());

        // Se teve aula, inicializar faltas para todos os alunos do turno
        if ($atividade->teve_aula) {
            $alunos = Aluno::where('turno', $atividade->turno)->get();
            foreach ($alunos as $aluno) {
                Presenca::firstOrCreate([
                    'aluno_id' => $aluno->id,
                    'atividade_id' => $atividade->id,
                ], ['status' => 'FALTA']);
            }
        }

        Auditoria::create([
            'user_id' => $user->id,
            'acao' => 'CRIACAO_ATIVIDADE_MANUAL',
            'valor_novo' => json_encode($atividade),
        ]);

        return response()->json($atividade, 201);
    }

    /**
     * Remove uma atividade (Admin).
     */
    public function destroy($id)
    {
        $user = Auth::guard('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Apenas administradores podem excluir atividades'], 403);
        }

        $atividade = Atividade::findOrFail($id);
        $valorAntigo = json_encode($atividade);
        $atividade->delete();

        Auditoria::create([
            'user_id' => $user->id,
            'acao' => 'EXCLUSAO_ATIVIDADE',
            'valor_antigo' => $valorAntigo,
        ]);

        return response()->json(['message' => 'Atividade excluída com sucesso']);
    }
}
