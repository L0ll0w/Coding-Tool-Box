<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmStudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['qcm_id', 'qcm_question_id', 'user_id', 'qcm_choice_id'];

    public function qcm()
    {
        return $this->belongsTo(Qcm::class);
    }

    public function question()
    {
        return $this->belongsTo(QcmQuestion::class);
    }

    public function choice()
    {
        return $this->belongsTo(QcmChoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
