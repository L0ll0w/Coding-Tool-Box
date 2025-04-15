<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KnowledgeController extends Controller
{
    /**
     * Display the page
     *
     * @return Factory|View|Application|object
     */
    public function index() {
        return view('pages.knowledge.index');
    }

    public function qcm() {

        $prompt = "C'est quoi le gland de lait ?";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
                    //add prompt in the request
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);
        if ($response->failed()) {
            \Log::error('Gemini API error', ['response' => $response->body()]);
            return response()->json(['message' => 'Erreur avec l\'API Gemini'], 500);
        }
        $generatedText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Aucun texte généré.';
        return response()->json([
            'qcm' => $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}'
        ]);

    }
}
