<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'user_id', 
        'acao', 
        'valor_antigo', 
        'valor_novo', 
        'data_hora'
    ];

    protected $casts = [
        'data_hora' => 'datetime:d/m/Y H:i:s',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
