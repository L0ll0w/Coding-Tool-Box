
<x-app-layout>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vie Commune - espace tache</title>
        <link rel="stylesheet" href="{{ asset('css/commonLife.css') }}">
    </head>
    <x-slot name="header">


    @auth
            @if (auth()->user()->is_admin)
                <div class="flex items-center gap-2">
                    <h1 class="admin-greeting text-sm font-normal">
                        Bonjour administrateur {{ auth()->user()->first_name }}
                    </h1>
                    <!-- Bouton modifié avec id pour l'ouverture de la modale -->
                    <button id="openModal" type="button" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        Ajouter une tâche
                    </button>
                </div>
            @else
                <h1 class="flex items-center gap-1 text-sm font-normal">
                    Bonjour {{ auth()->user()->first_name }}
                </h1>
            @endif
        @endauth
    </x-slot>

    {{-- Section affichant les tâches --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="tasksContainer" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <div class="bg-white rounded-lg p-4 shadow">
                            <h3 class="font-bold text-lg">{{ $task->title }}</h3>
                            <p class="text-gray-700">{{ $task->description }}</p>
                        </div>
                    @endforeach
                @else
                    <p>Aucune tâche à afficher.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Modale contenant le formulaire pour créer une tâche (initialement cachée) --}}
    <div id="modalForm" class="fixed inset-0 flex items-center justify-center modal-overlay hidden">
        <div class="modal-container">
            <!-- Bouton de fermeture -->
            <button id="closeModal" class="modal-close">&times;</button>
            <h2 class="text-xl font-bold mb-4">Créer une tâche</h2>
            <!-- Formulaire -->
            <form id="taskForm">
                @csrf
                <!-- Champs du formulaire -->
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 mb-1">Titre</label>
                    <input type="text" name="title" id="title"
                           class="w-full px-3 py-2 border border-gray-300 rounded"
                           placeholder="Titre de la tâche" required>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description"
                              class="w-full px-3 py-2 border border-gray-300 rounded"
                              placeholder="Description de la tâche" required></textarea>
                </div>
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                    Créer
                </button>
            </form>
        </div>
    </div>

    {{-- Script pour gérer l'ouverture/fermeture de la modale et la soumission AJAX --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const openModal = document.getElementById('openModal');
            const closeModal = document.getElementById('closeModal');
            const modalForm = document.getElementById('modalForm');
            const taskForm = document.getElementById('taskForm');
            const tasksContainer = document.getElementById('tasksContainer');

            // Ouvrir la modale lors du clic sur le bouton "Ajouter une tâche"
            openModal.addEventListener('click', function () {
                modalForm.classList.remove('hidden');
            });

            // Fermer la modale lorsque le bouton de fermeture est cliqué
            closeModal.addEventListener('click', function () {
                modalForm.classList.add('hidden');
            });

            // Fermer la modale si clic à l'extérieur du contenu
            modalForm.addEventListener('click', function (e) {
                if (e.target === modalForm) {
                    modalForm.classList.add('hidden');
                }
            });

            // Gestion de la soumission du formulaire avec AJAX
            taskForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(taskForm);

                fetch("{{ route('tasks.store') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        // Création de la nouvelle "carte" pour la tâche créée
                        const taskCard = document.createElement('div');
                        taskCard.classList.add('bg-white', 'rounded-lg', 'p-4', 'shadow');
                        const titleElement = document.createElement('h3');
                        titleElement.classList.add('font-bold', 'text-lg');
                        titleElement.textContent = data.title;
                        const descriptionElement = document.createElement('p');
                        descriptionElement.classList.add('text-gray-700');
                        descriptionElement.textContent = data.description;
                        taskCard.appendChild(titleElement);
                        taskCard.appendChild(descriptionElement);
                        tasksContainer.appendChild(taskCard);

                        // Réinitialisation du formulaire et fermeture de la modale
                        taskForm.reset();
                        modalForm.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error("Erreur:", error);
                    });
            });
        });
    </script>
</x-app-layout>
