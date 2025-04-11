
<x-app-layout>
    <x-slot name="header">

        <script>
            window.Laravel = {
                baseUrl: "{{ url('') }}",
                routes: {
                    tasksStore: "{{ route('tasks.store') }}",
                    taskSubmissionsStore: "{{ route('task.submissions.store') }}"
                },
                isAdmin: {{ auth()->check() && auth()->user()->is_admin ? 'true' : 'false' }}
            };
        </script>

        @auth
            @if (auth()->user()->is_admin)
                <!-- Header pour admin -->
                <div class="flex items-center gap-2">
                    <h1 class="admin-greeting text-sm font-normal ">
                        Bonjour administrateur {{ auth()->user()->first_name }}
                    </h1>
                    <x-forms.primary-button type="button" dataAttributes="data-modal-toggle=#create-task-modal">
                        Ajouter une tâche
                    </x-forms.primary-button>
                </div>
            @else
                <!-- Header pour étudiant -->
                <div class="flex items-center gap-2">
                    <h1 class="flex items-center gap-1 text-sm font-normal">
                        Bonjour {{ auth()->user()->first_name }}
                    </h1>
                    <!-- Bouton pour accéder à l'historique des tâches pointées -->
                    <a href="{{ route('my-task-history') }}" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        Voir mon historique
                    </a>
                </div>
            @endif
        @endauth

    <!-- Affichage des tâches -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="tasksContainer" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <div class="task-card-cyan {{ auth()->check() && auth()->user()->is_admin ? 'draggable-task' : '' }}"
                             data-id="{{ $task->id }}"
                             data-title="{{ $task->title }}"
                             data-description="{{ $task->description }}">
                            <!-- Pour Admin : boutons d'édition et de suppression -->
                            @if(auth()->check() && auth()->user()->is_admin)
                                <button class="delete-btn">&times;</button>
                                <button class="edit-btn">Modifier</button>
                            @endif
                            <!-- Pour Étudiant : bouton "Tâche terminée" -->
                            @if(auth()->check() && !auth()->user()->is_admin)
                                <button class="complete-task-btn">Tâche terminée</button>
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

    <!-- Modale pour admin : Créer ou modifier une tâche -->
    @include('pages.commonLife.create-modal')

    <!-- Modale pour étudiant : Pointer la tâche comme terminée et ajouter un commentaire -->
    <div id="submissionModal" class="fixed inset-0 flex items-center justify-center modal-overlay hidden">
        <div class="modal-container">
            <button id="closeSubmissionModal" class="modal-close">&times;</button>
            <h2 class="text-xl font-bold mb-4">Tâche terminée</h2>
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
        </div>
    </div>
    <script src="{{ asset('js/CommonLife.js') }}"></script>
</x-app-layout>


