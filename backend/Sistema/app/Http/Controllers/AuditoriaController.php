<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditoriaController extends Controller
{
    /**
     * Lista o log de auditoria (Apenas Administradores).
     */
    public function index()
    {
        $user = Auth::guard('api')->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado: Apenas administradores podem ver a auditoria'], 403);
        }

        $auditoria = Auditoria::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json($auditoria);
    }
}
