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
        // Récupère la tâche par son ID ou échoue si elle n'existe pas
        $task = Task::findOrFail($id);

        // Valide les données envoyées par la requête
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Mise à jour de la tâche avec les données validées
        $task->update($validated);

        // Retourne une réponse JSON ou redirige selon vos besoins
        return response()->json($task);
    }


    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }

}
