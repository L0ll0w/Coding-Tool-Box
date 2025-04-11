<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskSubmission;
use Illuminate\Support\Facades\Auth;

class TaskSubmissionController extends Controller
{
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validated = $request->validate([
            'task_id'     => 'required|exists:tasks,id',
            'comment'     => 'nullable|string',
        ]);

        $task = \App\Models\Task::find($validated['task_id']);

        // Créer la soumission pour l'étudiant connecté
        $submission = TaskSubmission::create([
            'task_id'    => $validated['task_id'],
            'original_title'      => $task ? $task->title : '',
            'original_description'=> $task ? $task->description : '',
            'user_id'    => Auth::id(),
            'is_completed' => true,
            'comment'    => $validated['comment'] ?? null,
        ]);

        return response()->json($submission);
    }

    public function update(Request $request, $id)
    {
        // Trouver la soumission existante
        $submission = TaskSubmission::findOrFail($id);

        // S’assurer que l’étudiant connecté est bien le propriétaire
        if ($submission->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $submission->update($validated);

        return response()->json($submission);
    }

    // Méthode pour afficher l'historique des soumissions de l'utilisateur connecté
    public function history()
    {
        // Récupère les soumissions de l'utilisateur connecté où la tâche est accomplie
        $submissions = \App\Models\TaskSubmission::where('user_id', auth()->id())
            ->where('is_completed', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('my-task-history', compact('submissions'));
    }

}
