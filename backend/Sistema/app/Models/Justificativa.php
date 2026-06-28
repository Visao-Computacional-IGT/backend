<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificativa extends Model
{
    protected $fillable = [
        'presenca_id',
        'descricao',
        'arquivo_path',
        'status',
        'aprovado_por',
    ];

    public function presenca()
    {
        return $this->belongsTo(Presenca::class);
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }
}
