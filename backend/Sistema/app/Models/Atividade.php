<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    protected $fillable = ['data', 'turno', 'teve_aula', 'descricao'];

    protected $casts = [
        'data' => 'date:d/m/Y',
        'teve_aula' => 'boolean',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function presencas()
    {
        return $this->hasMany(Presenca::class);
    }
}
