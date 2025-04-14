@extends('layouts.modal', [
    'id'    => 'submission-modal',
    'title' => 'Tâche terminée',
])

@section('modal-content')
    <form id="submissionForm">
        @csrf
        <!-- Champ caché pour l'ID de la tâche -->
        <input type="hidden" id="submissionTaskId" name="task_id" value="">
        <div class="mb-4">
            <label for="submissionComment" class="block text-gray-700 mb-1">Commentaire</label>
            <textarea name="comment" id="submissionComment" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Décrivez ce que vous avez accompli" required></textarea>
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
            Valider ma participation
        </button>
    </form>
@endsection
