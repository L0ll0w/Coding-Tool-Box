
<x-app-layout>
    <x-slot name="header">


            <div class="container mx-auto px-4 py-8">
                <h1 class="text-2xl font-bold mb-4">Historique de mes tâches accomplies</h1>

                @if($submissions->isEmpty())
                    <p>Vous n'avez accompli aucune tâche pour le moment.</p>
                @else
                    <table class="min-w-full bg-white border">
                        <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Titre</th>
                            <th class="py-2 px-4 border-b">Description</th>
                            <th class="py-2 px-4 border-b">Commentaire</th>
                            <th class="py-2 px-4 border-b">Date d'accomplissement</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($submissions as $submission)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $submission->original_title }}</td>
                                <td class="py-2 px-4 border-b">{{ $submission->original_description }}</td>
                                <td class="py-2 px-4 border-b">{{ $submission->comment }}</td>
                                <td class="py-2 px-4 border-b">{{ $submission->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

    </x-slot>
</x-app-layout>
