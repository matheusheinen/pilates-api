<?php

namespace App\Models;

 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;

 class HorarioAgenda extends Model
 {
     use HasFactory;

     /**
      * O nome da tabela associada ao model.
      *
      * @var string
      */
     protected $table = 'horarios_agenda'; // <-- ADICIONE ESTA LINHA

     protected $fillable = [
            'dia_semana',
            'horario_inicio',
            'duracao_minutos',
            'inscricao_id',
     ];

     public function inscricao()
     {
         return $this->belongsTo(Inscricao::class);
     }
 }
