<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    protected $fillable = ['nome'];

    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }

    public function professores()
    {
        return $this->belongsToMany(User::class, 'professor_turma', 'turma_id', 'user_id');
    }
}
