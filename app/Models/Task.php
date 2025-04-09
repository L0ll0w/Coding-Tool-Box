<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Autorise l'affectation massive pour les champs 'title' et 'description'
    protected $fillable = ['title', 'description'];
}
