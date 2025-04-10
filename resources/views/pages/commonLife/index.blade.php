<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vie Commune - Espace Tâches</title>
    <link rel="stylesheet" href="{{ asset('css/commonLife.css') }}">
</head>
<body>
<x-app-layout>
    <x-slot name="header">
        @auth
            @if (auth()->user()->is_admin)
                <div class="flex items-center gap-2">
                    <h1 class="admin-greeting text-sm font-normal">
                        Bonjour administrateur {{ auth()->user()->first_name }}
                    </h1>
                    <!-- Bouton "Ajouter une tâche" -->
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

    <!-- Section affichant les tâches -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="tasksContainer" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <div class="task-card-cyan {{ auth()->check() && auth()->user()->is_admin ? 'draggable-task' : '' }}"
                             data-id="{{ $task->id }}"
                             data-title="{{ $task->title }}"
                             data-description="{{ $task->description }}">
                            @if(auth()->check() && auth()->user()->is_admin)
                                <!-- Bouton de suppression -->
                                <button class="delete-btn">&times;</button>
                                <!-- Bouton de modification -->
                                <button class="edit-btn">Modifier</button>
                            @endif
                            <h3 class="font-bold text-lg">{{ $task->title }}</h3>
                            <p>{{ $task->description }}</p>
                        </div>
                    @endforeach
                @else
                    <p>Aucune tâche à afficher.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Modale contenant le formulaire pour créer/modifier une tâche (initialement cachée) -->
    <div id="modalForm" class="fixed inset-0 flex items-center justify-center modal-overlay hidden">
        <div class="modal-container">
            <button id="closeModal" class="modal-close">&times;</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Créer une tâche</h2>
            <!-- Formulaire -->
            <form id="taskForm">
                @csrf
                <!-- Champ caché pour l'id de la tâche (mode édition) -->
                <input type="hidden" id="taskId" name="taskId" value="">
                <!-- Champ caché pour override _method (sera rempli en mode édition) -->
                <input type="hidden" id="overrideMethod" name="_method" value="">
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
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                    Enregistrer
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Fonction pour afficher une notification
            function showNotification(message) {
                const notif = document.createElement('div');
                notif.classList.add('notif');  // La classe notif sera stylisée dans le CSS
                notif.textContent = message;
                document.body.appendChild(notif);
                setTimeout(() => {
                    notif.style.opacity = '0';
                    setTimeout(() => {
                        notif.remove();
                    }, 500);
                }, 3000);
            }

            // Variables pour la modale et le formulaire
            const openModal = document.getElementById('openModal');
            const closeModal = document.getElementById('closeModal');
            const modalForm = document.getElementById('modalForm');
            const taskForm = document.getElementById('taskForm');
            const tasksContainer = document.getElementById('tasksContainer');
            const modalTitle = document.getElementById('modalTitle');
            const taskIdInput = document.getElementById('taskId');
            const overrideMethodInput = document.getElementById('overrideMethod');
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');

            // Fonction pour réinitialiser le formulaire
            function resetForm() {
                taskForm.reset();
                taskIdInput.value = "";
                overrideMethodInput.value = "";
                modalTitle.textContent = "Créer une tâche";
            }

            // Ouvrir la modale en mode création
            openModal.addEventListener('click', function () {
                resetForm();
                modalForm.classList.remove('hidden');
            });

            // Fermer la modale
            closeModal.addEventListener('click', function () {
                modalForm.classList.add('hidden');
            });
            modalForm.addEventListener('click', function (e) {
                if (e.target === modalForm) {
                    modalForm.classList.add('hidden');
                }
            });

            // Traitement de la soumission du formulaire (création/modification)
            taskForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(taskForm);
                let url, method;

                if (taskIdInput.value) {
                    // Mode modification : remplir le champ _method avec "PUT"
                    overrideMethodInput.value = "PUT";
                    // On envoie la requête en POST (le champ _method indiquera PUT)
                    url = "{{ url('/tasks') }}/" + taskIdInput.value;
                    method = "POST";
                } else {
                    // Mode création
                    url = "{{ route('tasks.store') }}";
                    method = "POST";
                }

                fetch(url, {
                    method: method,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (taskIdInput.value) {
                            // Mise à jour de la carte existante
                            const taskCard = document.querySelector(`.task-card-cyan[data-id="${data.id}"]`);
                            if (taskCard) {
                                taskCard.querySelector('h3').textContent = data.title;
                                taskCard.querySelector('p').textContent = data.description;
                                taskCard.setAttribute('data-title', data.title);
                                taskCard.setAttribute('data-description', data.description);
                            }
                            showNotification("Tâche modifiée avec succès");
                        } else {
                            // Création d'une nouvelle carte
                            const taskCard = document.createElement('div');
                            taskCard.classList.add('task-card-cyan');
                            taskCard.setAttribute('data-id', data.id);
                            taskCard.setAttribute('data-title', data.title);
                            taskCard.setAttribute('data-description', data.description);
                            @if(auth()->check() && auth()->user()->is_admin)
                            taskCard.classList.add('draggable-task');
                            taskCard.setAttribute('draggable', 'true');
                            // Bouton de suppression
                            const deleteBtn = document.createElement('button');
                            deleteBtn.classList.add('delete-btn');
                            deleteBtn.textContent = '×';
                            taskCard.appendChild(deleteBtn);
                            // Bouton de modification
                            const editBtn = document.createElement('button');
                            editBtn.classList.add('edit-btn');
                            editBtn.textContent = 'Modifier';
                            taskCard.appendChild(editBtn);
                            @endif
                            const titleElement = document.createElement('h3');
                            titleElement.classList.add('font-bold', 'text-lg');
                            titleElement.textContent = data.title;
                            const descriptionElement = document.createElement('p');
                            descriptionElement.textContent = data.description;
                            taskCard.appendChild(titleElement);
                            taskCard.appendChild(descriptionElement);
                            tasksContainer.appendChild(taskCard);
                        }
                        resetForm();
                        modalForm.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error("Erreur:", error);
                    });
            });

            // Ouvrir la modale en mode édition lors du clic sur "Modifier"
            tasksContainer.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('edit-btn')) {
                    const taskCard = e.target.closest('.task-card-cyan');
                    const id = taskCard.getAttribute('data-id');
                    const title = taskCard.getAttribute('data-title');
                    const description = taskCard.getAttribute('data-description');

                    taskIdInput.value = id;
                    titleInput.value = title;
                    descriptionInput.value = description;
                    modalTitle.textContent = "Modifier la tâche";
                    modalForm.classList.remove('hidden');
                }
            });

            // Suppression d'une tâche
            tasksContainer.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('delete-btn')) {
                    if (confirm("Voulez-vous vraiment supprimer cette tâche ?")) {
                        const taskCard = e.target.closest('.task-card-cyan');
                        const taskId = taskCard.getAttribute('data-id');

                        fetch("{{ url('/tasks') }}/" + taskId, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                                "Accept": "application/json"
                            },
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    taskCard.remove();
                                } else {
                                    alert("Une erreur est survenue lors de la suppression.");
                                }
                            })
                            .catch(error => {
                                console.error("Erreur :", error);
                            });
                    }
                }
            });

            // Activation du Drag & Drop pour l'administrateur
            @if(auth()->check() && auth()->user()->is_admin)
            const draggableCards = document.querySelectorAll('.draggable-task');
            draggableCards.forEach(card => { card.setAttribute('draggable', 'true'); });
            let draggedElement = null;
            tasksContainer.addEventListener('dragstart', function (e) {
                if (e.target && e.target.classList.contains('draggable-task')) {
                    draggedElement = e.target;
                    e.target.style.opacity = 0.5;
                }
            });
            tasksContainer.addEventListener('dragend', function (e) {
                if (e.target && e.target.classList.contains('draggable-task')) {
                    e.target.style.opacity = "";
                }
            });
            tasksContainer.addEventListener('dragover', function (e) {
                e.preventDefault();
            });
            tasksContainer.addEventListener('dragenter', function (e) {
                if (e.target && e.target.classList.contains('draggable-task')) {
                    e.target.classList.add('drag-over');
                }
            });
            tasksContainer.addEventListener('dragleave', function (e) {
                if (e.target && e.target.classList.contains('draggable-task')) {
                    e.target.classList.remove('drag-over');
                }
            });
            tasksContainer.addEventListener('drop', function (e) {
                e.preventDefault();
                if (e.target && e.target.classList.contains('draggable-task')) {
                    e.target.classList.remove('drag-over');
                    tasksContainer.insertBefore(draggedElement, e.target.nextSibling);
                }
            });
            @endif
        });
    </script>
</x-app-layout>
</body>
</html>
