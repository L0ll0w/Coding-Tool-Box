@extends('layouts.modal', [
    'id'    => 'create-task-modal',
    'title'  => 'Créer une tâche',] )

@section('modal-content')
    <form id="taskForm">
        @csrf
        <!-- Champ caché pour l'ID de la tâche en mode édition -->
        <input type="hidden" id="taskId" name="taskId" value="">
        <!-- Champ caché pour l'override de méthode (_method sera mis à "PUT" en modification) -->
        <input type="hidden" id="overrideMethod" name="_method" value="">
        <div class="mb-4">
            <label for="title" class="block text-gray-700 mb-1">Titre</label>
            <input type="text" name="title" id="title" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Titre de la tâche" required>
        </div>
        <div class="mb-4">
            <label for="description" class="block text-gray-700 mb-1">Description</label>
            <textarea name="description" id="description" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Description de la tâche" required></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
            Enregistrer
        </button>
    </form>
@overwrite
