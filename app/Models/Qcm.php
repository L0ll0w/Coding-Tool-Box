<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qcm extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'generated_qcm', /* autres colonnes */];

    public function questions()
    {
        return $this->hasMany(QcmQuestion::class);
    }
}
