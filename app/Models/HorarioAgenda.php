<?php

namespace App\Models;

 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class HorarioAgenda extends Model
 {
     use HasFactory;

     protected $fillable = [
            'dia_semana',
            'horario_inicio',
            'horario_fim',
            'inscricao_id',
     ];

     public function inscricao()
     {
         return $this->belongsTo(Inscricao::class);
     }
 }
