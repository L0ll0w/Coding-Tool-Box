<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class CommonLifeController extends Controller
{
    public function index()
    {

        $tasks = Task::all();

        return view('pages.commonLife.index', compact('tasks'));
    }
}
