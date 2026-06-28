<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Presenca;
use App\Models\Atividade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekognitionController extends Controller
{
    /**
     * Registra uma presença através do reconhecimento facial
     * Integração com Amazon Rekognition
     */
    public function registerFacialPresence(Request $request)
    {
        $this->validate($request, [
            'face_id' => 'required|string',
        ]);

        // Buscar aluno pelo face_id
        $aluno = Aluno::where('face_id', $request->face_id)->first();

        if (!$aluno) {
            return response()->json(['error' => 'Aluno não encontrado'], 404);
        }

        // Garantir que existe atividade para hoje (Criação Automática - RN03)
        $today = date('Y-m-d');
        $atividade = Atividade::firstOrCreate(
            ['data' => $today, 'turno' => $aluno->turno],
            ['teve_aula' => true]
        );

        // Se a atividade foi criada agora, inicializar faltas para os outros alunos do turno
        if ($atividade->wasRecentlyCreated) {
            $alunosDoTurno = Aluno::where('turno', $aluno->turno)->get();
            foreach ($alunosDoTurno as $a) {
                Presenca::firstOrCreate([
                    'aluno_id' => $a->id,
                    'atividade_id' => $atividade->id,
                ], ['status' => 'FALTA']);
            }
        }

        // Verificar se já existe presença
        $presenca = Presenca::where('aluno_id', $aluno->id)
            ->where('atividade_id', $atividade->id)
            ->first();

        if ($presenca) {
            if ($presenca->status === 'FALTA') {
                // Sobrescrever falta (Regra de Negócio: 10h/14h)
                $presenca->update([
                    'status' => 'PRESENTE (Sobrescrito)',
                    'atualizado_em' => now()
                ]);
                $message = 'Falta sobrescrita por presença facial';
            } else {
                return response()->json([
                    'message' => 'Presença já registrada',
                    'presenca' => $presenca
                ]);
            }
        } else {
            // Criar nova presença
            $presenca = Presenca::create([
                'aluno_id' => $aluno->id,
                'atividade_id' => $atividade->id,
                'status' => 'PRESENTE',
                'atualizado_em' => now(),
            ]);
            $message = 'Presença registrada com sucesso';
        }

        return response()->json([
            'message' => $message,
            'presenca' => $presenca,
            'aluno' => $aluno
        ], 201);
    }

    /**
     * Registra o ID facial de um aluno vindo do Amazon Rekognition.
     */
    public function registerFace(Request $request)
    {
        $this->validate($request, [
            'aluno_id' => 'required|exists:alunos,id',
            'face_id' => 'required|string',
        ]);

        $aluno = Aluno::findOrFail($request->aluno_id);
        
        // Verificar permissão
        $user = Auth::guard('api')->user();
        if ($user->role !== 'admin') {
            $turmaIds = $user->turmas()->pluck('turmas.id')->toArray();
            if (!in_array($aluno->turma_id, $turmaIds)) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
        }

        $aluno->update(['face_id' => $request->face_id]);

        return response()->json([
            'message' => 'Rosto registrado com sucesso',
            'aluno' => $aluno
        ]);
    }

    /**
     * Lista todos os alunos que possuem um rosto cadastrado no sistema.
     */
    public function listRegisteredFaces()
    {
        $user = Auth::guard('api')->user();

        if ($user->role === 'admin') {
            $alunos = Aluno::whereNotNull('face_id')->with('turma')->get();
        } else {
            $turmaIds = $user->turmas()->pluck('turmas.id');
            $alunos = Aluno::whereNotNull('face_id')
                ->whereIn('turma_id', $turmaIds)
                ->with('turma')
                ->get();
        }

        return response()->json($alunos);
    }
}
