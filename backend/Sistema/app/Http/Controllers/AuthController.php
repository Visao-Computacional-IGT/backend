<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Autentica um usuário e retorna o token JWT.
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $user = Auth::guard('api')->user();
        if ($user->role === 'aluno') {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Alunos não possuem acesso ao sistema'], 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Retorna o perfil do usuário logado.
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Realiza o logout (invalida o token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    /**
     * Formata a resposta do token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user()
        ]);
    }
}
