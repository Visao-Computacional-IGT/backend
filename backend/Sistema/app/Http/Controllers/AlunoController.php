<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlunoController extends Controller
{
    /**
     * Lista todos os alunos (Admin vê todos, Professor vê os de suas turmas).
     */
    public function index()
    {
        $user = Auth::guard('api')->user();

        if ($user->role === 'admin') {
            $alunos = Aluno::with('turma')->get();
        } else {
            // Professor vê apenas alunos das turmas vinculadas a ele
            $turmaIds = $user->turmas()->pluck('turmas.id');
            $alunos = Aluno::whereIn('turma_id', $turmaIds)->with('turma')->get();
        }

        return response()->json($alunos);
    }

    /**
     * Cadastra um novo aluno.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nome_completo' => 'required|string',
            'data_nascimento' => 'required|date',
            'nome_responsavel' => 'required|string',
            'turma_id' => 'required|exists:turmas,id',
            'turno' => 'required|in:MANHÃ,TARDE',
            'face_id' => 'nullable|string',
        ]);

        $aluno = Aluno::create($request->all());

        // Registrar na auditoria
        Auditoria::create([
            'user_id' => Auth::guard('api')->id(),
            'acao' => 'CADASTRO_ALUNO',
            'valor_novo' => json_encode($aluno),
        ]);

        return response()->json($aluno, 201);
    }

    /**
     * Exibe um aluno específico.
     */
    public function show($id)
    {
        $aluno = Aluno::with('turma')->findOrFail($id);
        
        // Verificar permissão
        $user = Auth::guard('api')->user();
        if ($user->role !== 'admin') {
            $turmaIds = $user->turmas()->pluck('turmas.id')->toArray();
            if (!in_array($aluno->turma_id, $turmaIds)) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
        }

        return response()->json($aluno);
    }

    /**
     * Atualiza os dados de um aluno.
     */
    public function update(Request $request, $id)
    {
        $aluno = Aluno::findOrFail($id);
        $valorAntigo = json_encode($aluno);

        $this->validate($request, [
            'nome_completo' => 'string',
            'data_nascimento' => 'date',
            'nome_responsavel' => 'string',
            'turma_id' => 'exists:turmas,id',
            'turno' => 'in:MANHÃ,TARDE',
            'face_id' => 'nullable|string',
        ]);

        $aluno->update($request->all());

        // Registrar na auditoria
        Auditoria::create([
            'user_id' => Auth::guard('api')->id(),
            'acao' => 'ATUALIZACAO_ALUNO',
            'valor_antigo' => $valorAntigo,
            'valor_novo' => json_encode($aluno),
        ]);

        return response()->json($aluno);
    }

    /**
     * Remove um aluno.
     */
    public function destroy($id)
    {
        $aluno = Aluno::findOrFail($id);
        
        if (Auth::guard('api')->user()->role !== 'admin') {
            return response()->json(['error' => 'Apenas administradores podem excluir alunos'], 403);
        }

        $aluno->delete();

        Auditoria::create([
            'user_id' => Auth::guard('api')->id(),
            'acao' => 'EXCLUSAO_ALUNO',
            'valor_antigo' => json_encode($aluno),
        ]);

        return response()->json(['message' => 'Aluno excluído com sucesso']);
    }
}
