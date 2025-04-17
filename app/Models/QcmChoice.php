<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmChoice extends Model
{
    use HasFactory;

    protected $fillable = ['qcm_question_id', 'choice_text', 'is_correct'];

    public function question()
    {
        return $this->belongsTo(QcmQuestion::class);
    }
}
