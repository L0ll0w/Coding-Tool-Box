<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">
                {{ __('Bilans de connaissances') }}
            </h1>
        </div>
        <div class="col-span-full">
            @if(auth()->check() && auth()->user()->is_admin)
                <x-forms.primary-button
                    type="button"
                    data-modal-toggle="#modal-qcm"
                    class="bg-green-600 hover:bg-green-700"
                >
                    Générer un QCM
                </x-forms.primary-button>
            @endif
        </div>

    </x-slot>

    {{-- Modale de génération de QCM --}}
    @include('pages.knowledge.modal-qcm')

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Mes QCM</h2>

            <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($qcms as $qcm)
                    <div class="card bg-white shadow-md rounded-lg p-4 transition hover:shadow-xl">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $qcm->subject }}
                        </h3>
                        <p class="text-gray-500 text-sm mt-2">
                            Généré le {{ $qcm->created_at->format('d/m/Y H:i') }}
                        </p>
                        <div class="mt-4 flex space-x-2">
                            <a
                                href="{{ route('knowledge.take', $qcm->id) }}"
                                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors"
                            >
                                Passer le QCM
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <p class="text-center text-gray-500">
                            Aucun QCM généré pour le moment.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
