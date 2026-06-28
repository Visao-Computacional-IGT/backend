<?php

namespace App\Http\Controllers;

use App\Models\Presenca;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresencaController extends Controller
{
    /**
     * Lista presenças.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $query = Presenca::with(['aluno', 'atividade']);

        if ($user->role === 'professor') {
            $turmaIds = $user->turmas()->pluck('turmas.id');
            $query->whereHas('aluno', function($q) use ($turmaIds) {
                $q->whereIn('turma_id', $turmaIds);
            });
        }

        if ($request->has('data')) {
            $query->whereHas('atividade', function($q) use ($request) {
                $q->where('data', $request->data);
            });
        }

        return response()->json($query->get());
    }

    /**
     * Atualização manual de presença com log de auditoria (RN11).
     */
    public function manualUpdate(Request $request)
    {
        $this->validate($request, [
            'aluno_id' => 'required|exists:alunos,id',
            'atividade_id' => 'required|exists:atividades,id',
            'status' => 'required|in:PRESENTE,FALTA,FALTA JUSTIFICADA,FALTA AVISADA',
        ]);

        $user = Auth::guard('api')->user();

        // Professor só pode alterar alunos de suas turmas
        if ($user->role === 'professor') {
            $aluno = \App\Models\Aluno::findOrFail($request->aluno_id);
            $turmaIds = $user->turmas()->pluck('turmas.id')->toArray();
            if (!in_array($aluno->turma_id, $turmaIds)) {
                return response()->json(['error' => 'Acesso negado: Aluno não pertence às suas turmas'], 403);
            }
        }

        // Buscar presença existente ou criar uma nova
        $presenca = Presenca::firstOrNew([
            'aluno_id' => $request->aluno_id,
            'atividade_id' => $request->atividade_id,
        ]);

        $valorAntigo = $presenca->exists ? $presenca->status : 'NENHUM';
        
        $presenca->status = $request->status;
        $presenca->atualizado_em = date('Y-m-d H:i:s');
        $presenca->save();

        // Registrar Auditoria Obrigatória (RN11)
        Auditoria::create([
            'user_id' => $user->id,
            'acao' => 'CORRECAO_MANUAL_PRESENCA',
            'valor_antigo' => "Status: $valorAntigo",
            'valor_novo' => "Status: {$request->status}",
        ]);

        return response()->json([
            'message' => 'Presença atualizada com sucesso',
            'presenca' => $presenca
        ]);
    }
}
