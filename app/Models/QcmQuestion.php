<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['qcm_id', 'question_text'];

    public function qcm()
    {
        return $this->belongsTo(Qcm::class);
    }

    public function choices()
    {
        return $this->hasMany(QcmChoice::class);
    }
}

