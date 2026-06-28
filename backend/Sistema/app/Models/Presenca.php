<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presenca extends Model
{
    protected $fillable = ['aluno_id', 'atividade_id', 'status', 'atualizado_em'];

    protected $casts = [
        'atualizado_em' => 'datetime:d/m/Y H:i:s',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function atividade()
    {
        return $this->belongsTo(Atividade::class);
    }
}
