<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validation des données du formulaire
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        // Création de la tâche dans la base de données
        $task = Task::create($validated);

        // Retourne la nouvelle tâche au format JSON (pour le traitement AJAX)
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {

        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // fonction pour destroy une tache de la bdd
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }

}
