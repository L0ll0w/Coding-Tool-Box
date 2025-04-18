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
use Illuminate\Support\Facades\Log;
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
        // 1) Validation du sujet
        $request->validate([
            'subject' => 'required|string|max:255',
        ]);
        $subject = $request->input('subject');

        // 2) Prompt pour l'API
        $prompt = "Génère un QCM structuré au format JSON sur le sujet suivant : {$subject}.\n"
            . " Le JSON doit commencé par { et finir par } sans et contenir une clé \"questions\" comme ceci :\n"
            . "{\n"
            . "  \"questions\": [\n"
            . "    { \"question\": \"...\", \"choices\": [\"...\",\"...\",\"...\",\"...\"], \"correct\": \"...\" },\n"
            . "  ]\n"
            . "}";

        // 3) Appel à l’API Gemini
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='
                . config('services.gemini.api_key'),
                ['contents' => [['parts' => [['text' => $prompt]]]]]
            );

        if ($response->failed()) {
            Log::error('Gemini API error', ['body' => $response->body()]);
            return back()->withErrors('Erreur lors de la génération du QCM.');
        }

        // 4) Récupérer la chaîne JSON générée
        $generatedJSON = data_get(
            $response->json(),
            'candidates.0.content.parts.0.text',
            '{"questions":[]}'
        );

        // 1) On récupère la chaîne brute
        $raw = $response->json('candidates.0.content.parts.0.text', '');

        // 2) On retire les fences Markdown ```json et ```
        $clean = preg_replace('#```(?:json)?#', '', $raw);

        // 3) On trim pour ôter espaces et retours à la ligne superflus
        $clean = trim($clean);

        // 4) On extrait tout ce qui est entre la première accolade ouvrante et la dernière fermante
        if (preg_match('/\{.*\}/s', $clean, $m)) {
            $jsonOnly = $m[0];
        } else {
            // si pas de match, on garde tout
            $jsonOnly = $clean;
        }

        // 5) On décode
        $data = json_decode($jsonOnly, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON invalide après nettoyage : '. json_last_error_msg(), ['string'=>$jsonOnly]);
            return back()->withErrors('Le QCM généré n’est pas un JSON valide.');
        }

        // 6) Créer le QCM
        $qcm = Qcm::create([
            'subject'       => $subject,
            'generated_qcm' => $generatedJSON,
        ]);

        // 7) Boucler sur les questions + choix
        if (!empty($data['questions']) && is_array($data['questions'])) {

            Log::debug('Début des questions', ['count' => count($data['questions'])]);

            foreach ($data['questions'] as $idx => $qItem) {
                // Sanity check
                if (empty($qItem['question']) || empty($qItem['choices']) || !is_array($qItem['choices'])) {
                    Log::warning("Question #{$idx} ignorée (format invalide)", $qItem);
                    continue;
                }

                // a) Création de la question
                $question = QcmQuestion::create([
                    'qcm_id'        => $qcm->id,
                    'question_text' => $qItem['question'],
                ]);
                Log::debug("Question #{$idx} enregistrée", ['id'=>$question->id]);

                // b) Création des choix
                foreach ($qItem['choices'] as $cIdx => $text) {
                    $choice = QcmChoice::create([
                        'qcm_question_id' => $question->id,
                        'choice_text'     => $text,
                        'is_correct'      => isset($qItem['correct']) && $text === $qItem['correct'],
                    ]);
                    Log::debug("Choix #{$cIdx} créé pour question {$question->id}", [
                        'choice_id' => $choice->id,
                        'correct'   => $choice->is_correct,
                    ]);
                }
            }

            Log::debug('Fin des questions');
        } else {
            Log::info('Aucune question à traiter', ['data' => $data]);
        }

        // 8) Redirection vers la page de passage du QCM
        return redirect()
            ->route('knowledge.index', $qcm->id)
            ->with('success', 'QCM généré et enregistré.');
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


        return redirect()->route('knowledge.index', $qcm->id)
            ->with('success', 'Vos réponses ont été enregistrées.');
    }
}
