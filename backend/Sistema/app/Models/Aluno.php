<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    protected $fillable = [
        'nome_completo', 
        'data_nascimento', 
        'nome_responsavel', 
        'turma_id', 
        'turno', 
        'face_id'
    ];
    
    protected $casts = [
        'data_nascimento' => 'date:d/m/Y',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function presencas()
    {
        return $this->hasMany(Presenca::class);
    }
}
