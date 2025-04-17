<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up()
    {
        Schema::create('qcm_student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_id')->constrained('qcms')->onDelete('cascade');              // Le QCM auquel appartient la réponse
            $table->foreignId('qcm_question_id')->constrained('qcm_questions')->onDelete('cascade'); // La question répondue
            $table->foreignId('user_id')->constrained()->onDelete('cascade');                     // L'utilisateur qui répond
            // Optionnel : si la réponse choisie est déjà enregistrée dans qcm_choices, on la lie ; sinon, vous pouvez l'enregistrer en texte.
            $table->foreignId('qcm_choice_id')->nullable()->constrained('qcm_choices')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down()
    {
        Schema::dropIfExists('qcm_student_answers');
    }
};
