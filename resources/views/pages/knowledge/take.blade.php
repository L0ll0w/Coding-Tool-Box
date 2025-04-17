<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold">Passer le QCM: {{ $qcm->subject }}</h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto">
            <form action="{{ route('knowledge.submit', $qcm->id) }}" method="POST" id="qcmSubmitForm">
                @csrf
                @foreach($questions as $question)
                    <div class="mb-6">
                        <p class="text-lg font-semibold">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                        @foreach($question->choices as $choice)
                            <label class="flex items-center my-2">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice->id }}" class="mr-2">
                                <span>{{ $choice->choice_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                    Valider mes réponses
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
