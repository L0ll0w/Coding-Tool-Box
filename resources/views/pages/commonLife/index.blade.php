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
            @if(auth()->user()->is_admin)
                <!-- Header pour administrateur -->
                <div class="flex items-center gap-2">
                    <h1 class="admin-greeting text-2xl font-bold">
                        Bonjour administrateur {{ auth()->user()->first_name }}
                    </h1>
                    <x-forms.primary-button  id="openModal" type="button" dataAttributes="data-modal-toggle=#create-task-modal">
                        Ajouter une tâche
                    </x-forms.primary-button>
                </div>
            @else
                <!-- Header pour étudiant -->
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">
                        Bonjour {{ auth()->user()->first_name }}
                    </h1>
                    <a href="{{ route('my-task-history') }}" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        Voir mon historique
                    </a>
                </div>
            @endif
        @endauth
    </x-slot>

    <!-- Affichage des tâches -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="tasksContainer" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <div class="card bg-white shadow-md rounded-lg p-4 transition hover:shadow-xl"
                             data-id="{{ $task->id }}"
                             data-title="{{ $task->title }}"
                             data-description="{{ $task->description }}">
                            <div class="flex justify-between items-center">
                                <h3 class="card-title text-xl font-bold">{{ $task->title }}</h3>
                                @if(auth()->check() && auth()->user()->is_admin)
                                    <div class="flex space-x-2">
                                        <button class="delete-btn text-red-500 hover:text-red-700" title="Supprimer">&times;</button>
                                        <button class="edit-btn px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none "  data-modal-toggle="#create-task-modal">
                                            Modifier
                                        </button>
                                    </div>
                                @elseif(auth()->check())
                                    <button class="complete-task-btn bg-green-500 hover:bg-green-600 text-white rounded px-2 py-1 text-sm">
                                        Tâche terminée
                                    </button>
                                @endif
                            </div>
                            <div class="card-body mt-3">
                                <p class="text-gray-700">{{ $task->description }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div id="noTasksMessage" class="col-span-full text-center text-gray-600 text-lg font-medium py-4">
                        Aucune tâche à afficher.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modale pour admin : Créer ou modifier une tâche -->
    @include('pages.commonLife.create-modal')

    <!-- Modale pour étudiant : Pointer la tâche comme terminée et ajouter un commentaire -->
    @include('pages.commonLife.create-modal')


</x-app-layout>
