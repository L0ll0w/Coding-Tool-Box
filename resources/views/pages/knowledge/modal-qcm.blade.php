<!-- resources/views/pages/knowledge/modal-qcm.blade.php -->

@extends('layouts.modal', [
    'id'    => 'modal-qcm',
    'title' => 'Générer un QCM',
])

@section('modal-content')
    <form action="{{ route('knowledge.qcm') }}" method="GET" id="qcmForm">
        @csrf

        <div class="mb-4">
            <label for="subject" class="block text-gray-700 mb-1">Choisissez un langage</label>
            <select name="subject"
                    id="subject"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring"
                    required>
                <option value="" disabled selected>-- Sélectionnez un langage --</option>
                <option value="PHP">PHP</option>
                <option value="JavaScript">JavaScript</option>
                <option value="Python">Python</option>
                <option value="Java">Java</option>
                <option value="C#">C#</option>
                <option value="Go">Go</option>
                <option value="Ruby">Ruby</option>
                <option value="Rust">Rust</option>
                <option value="TypeScript">TypeScript</option>
            </select>
        </div>

        <button type="submit"
                class="bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700 transition-colors">
            Générer le QCM
        </button>
    </form>
@endsection
