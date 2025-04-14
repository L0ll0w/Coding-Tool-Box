<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Autorise l'affectation massive pour les champs 'title' et 'description'
    protected $fillable = ['title', 'description'];

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function completedBy($userId)
    {
        return $this->submissions()->where('user_id', $userId)->exists();
    }

}
