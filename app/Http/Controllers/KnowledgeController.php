<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Qcm;
use App\Models\QcmQuestion;
use App\Models\QcmChoice;
use App\Models\QcmStudentAnswer;
use Symfony\Component\ErrorHandler\Debug;

class KnowledgeController extends Controller
{
    /**
     * Affiche la page d'accueil des QCM.
     *
     * @return Factory|View|Application|object
     */
    public function index()
    {
        // Récupérer tous les QCM, triés par date de création décroissante.
        $qcms = Qcm::orderBy('created_at', 'desc')->get();
        return view('pages.knowledge.index', compact('qcms'));
    }

    /**
     * Génère un QCM via l'API à partir du sujet soumis, enregistre le QCM et sa structure dans la base de données.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function qcm(Request $request)
    {
        // Valider que le sujet est bien fourni
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
        ]);

        $subject = $validated['subject'];

        // Construit un prompt précis demandant un format JSON structuré
        $prompt = "Génère un QCM structuré au format JSON sur le sujet suivant : " . $subject . ".
Le JSON doit être strictement conforme au format suivant :
{
  \"questions\": [
    {
      \"question\": \"Texte de la question 1\",
      \"choices\": [\"Choix A\", \"Choix B\", \"Choix C\", \"Choix D\"],
      \"correct\": \"Choix B\"
    },
    {
      \"question\": \"Texte de la question 2\",
      \"choices\": [\"Choix A\", \"Choix B\", \"Choix C\", \"Choix D\"],
      \"correct\": \"Choix D\"
    }
    // etc.
  ]
}";

        // Appel à l'API avec le prompt
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.api_key'), [
            'contents' => [
                [
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

        $generatedJSON = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;

        // Si aucun texte n'est généré, on fournit un fallback
        if (!$generatedJSON) {
            $generatedJSON = '{"questions": []}';
        }

        // Nettoyer et décoder le JSON

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Erreur JSON lors du parsing du QCM généré', [
                'error' => json_last_error_msg(),
                'json' => $generatedJSON,
            ]);
            return response()->json(['message' => 'Le format du QCM généré est invalide'], 500);
        }

        // Créer le QCM dans la base de données
        $qcm = Qcm::create([
            'subject' => $subject,
            'generated_qcm' => $generatedJSON,
        ]);

        // Boucler sur les questions et les choix pour enregistrer la structure
        if (!empty($data['questions']) && is_array($data['questions'])) {
            foreach ($data['questions'] as $questionData) {
                if (!isset($questionData['question']) || !isset($questionData['choices'])) {
                    continue;
                }

                $question = \App\Models\QcmQuestion::create([
                    'qcm_id' => $qcm->id,
                    'question_text' => $questionData['question'],

                ]);

                foreach ($questionData['choices'] as $choiceText) {
                    $isCorrect = false;
                    if (isset($questionData['correct']) && $choiceText === $questionData['correct']) {
                        $isCorrect = true;
                    }
                    \App\Models\QcmChoice::create([
                        'qcm_question_id' => $question->id,
                        'choice_text' => $choiceText,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
        }


        // Optionnel : retourner un JSON ou rediriger la page
        return redirect()->back();
    }

    /**
     * Affiche la page permettant de passer un QCM, avec ses questions et ses choix.
     *
     * @param int $id
     * @return Factory|View|Application
     */
    public function take($id)
    {
        $qcm = Qcm::findOrFail($id);
        $questions = $qcm->questions()->with('choices')->get();
        return view('pages.knowledge.take', compact('qcm', 'questions'));
    }

    /**
     * Enregistre les réponses de l'étudiant pour un QCM donné.
     *
     * @param Request $request
     * @param int $id QCM ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, $id)
    {
        $qcm = Qcm::findOrFail($id);

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        foreach ($validated['answers'] as $questionId => $choiceId) {
            QcmStudentAnswer::create([
                'qcm_id' => $qcm->id,
                'qcm_question_id' => $questionId,
                'user_id' => Auth::id(),
                'qcm_choice_id' => $choiceId,
            ]);
        }

        // Optionnel : vous pouvez calculer un score ici et sauvegarder le résultat.

        return redirect()->route('knowledge.take', $qcm->id)
            ->with('success', 'Vos réponses ont été enregistrées.');
    }
}
