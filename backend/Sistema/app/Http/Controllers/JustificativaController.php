<?php

namespace App\Http\Controllers;

use App\Models\Justificativa;
use App\Models\Presenca;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JustificativaController extends Controller
{
    /**
     * Lista todas as justificativas (Admin vê todas, Professor vê de suas turmas).
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        $query = Justificativa::with(['presenca.aluno', 'aprovador']);

        if ($user->role === 'professor') {
            $turmaIds = $user->turmas()->pluck('turmas.id');
            $query->whereHas('presenca.aluno', function($q) use ($turmaIds) {
                $q->whereIn('turma_id', $turmaIds);
            });
        }

        return response()->json($query->get());
    }

    /**
     * Cadastra uma nova justificativa.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'presenca_id' => 'required|exists:presencas,id',
            'descricao' => 'required|string',
            'arquivo' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        $presenca = Presenca::findOrFail($request->presenca_id);
        
        // Caminho do arquivo se houver upload
        $path = null;
        if ($request->hasFile('arquivo')) {
            $path = $request->file('arquivo')->store('justificativas', 'local');
        }

        $justificativa = Justificativa::create([
            'presenca_id' => $request->presenca_id,
            'descricao' => $request->descricao,
            'arquivo_path' => $path,
            'status' => 'PENDENTE',
        ]);

        return response()->json($justificativa, 201);
    }

    /**
     * Aprova ou Rejeita uma justificativa (Apenas Admin).
     */
    public function decide(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:APROVADO,REJEITADO',
        ]);

        $user = Auth::guard('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Apenas administradores podem aprovar justificativas'], 403);
        }

        $justificativa = Justificativa::findOrFail($id);
        $justificativa->update([
            'status' => $request->status,
            'aprovado_por' => $user->id,
        ]);

        // Se aprovado, atualizar o status da presença para 'FALTA JUSTIFICADA'
        if ($request->status === 'APROVADO') {
            $presenca = $justificativa->presenca;
            $presenca->update(['status' => 'FALTA JUSTIFICADA']);
            
            // Auditoria
            Auditoria::create([
                'user_id' => $user->id,
                'acao' => 'APROVACAO_JUSTIFICATIVA',
                'valor_novo' => "Justificativa ID: $id aprovada para Presença ID: {$presenca->id}",
            ]);
        }

        return response()->json($justificativa);
    }
}
