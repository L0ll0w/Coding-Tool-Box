<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Bilans de connaissances') }}
            </span>
        </h1>
    </x-slot>
    <form action="{{route('knowledge.qcm')}}" type="GET">
        @csrf
        <x-forms.primary-button type="button" data-modal-toggle="#modal-qcm">
            Génerer un qcm
        </x-forms.primary-button>
    </form>

    @include('pages.knowledge.modal-qcm')
</x-app-layout>

