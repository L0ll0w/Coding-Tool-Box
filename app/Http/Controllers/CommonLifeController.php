<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class CommonLifeController extends Controller
{
    public function index()
    {
        // Récupère toutes les tâches de la base de données
        $tasks = Task::all();

        // Retourne la vue 'common-life.index' en passant la variable $tasks
        return view('pages.commonLife.index', compact('tasks'));
    }
}
