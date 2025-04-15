<!-- resources/views/task/create-task-modal.blade.php -->

@extends('layouts.modal', [
    'id'    => 'modal-qcm',
    'title' => 'Génerer un qcm',
])

@section('modal-content')
    <form action="{{ route('knowledge.qcm') }}" method="GET" id="qcmForm">
        @csrf
        <div class="mb-4">
            <label for="subject" class="block text-gray-700 mb-1">Sujet</label>
            <input type="text" name="subject" id="subject" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Entrez le sujet (ex: chat)" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
            Générer le QCM
        </button>
    </form>
@endsection
