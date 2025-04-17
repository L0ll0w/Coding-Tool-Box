<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Bilans de connaissances') }}
            </span>
        </h1>
    </x-slot>

    <!-- Bouton pour ouvrir le modal de génération de QCM -->
    <x-forms.primary-button type="button" data-modal-toggle="#modal-qcm">
        Générer un QCM
    </x-forms.primary-button>

    @include('pages.knowledge.modal-qcm')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-4">Mes QCM générés</h2>
            @if($qcms && $qcms->count() > 0)
                <div class="grid grid-cols-1 gap-6">
                    @foreach($qcms as $qcm)
                        <div class="bg-white shadow rounded p-4">
                            <h3 class="text-xl font-bold">{{ $qcm->subject }}</h3>
                            <!-- Ici vous pouvez ajouter un lien ou un bouton pour passer le QCM -->
                            <a href="{{ route('knowledge.take', $qcm->id) }}" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Passer le QCM
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-600">Aucun QCM généré pour le moment.</p>
            @endif
        </div>
    </div>
</x-app-layout>
