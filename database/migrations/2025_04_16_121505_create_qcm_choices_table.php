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
        Schema::create('qcm_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_question_id')->constrained('qcm_questions')->onDelete('cascade');
            $table->string('choice_text');       // Texte du choix
            $table->boolean('is_correct')->default(false); // Indique si ce choix est la bonne réponse
            $table->timestamps();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down()
    {
        Schema::dropIfExists('qcm_choices');
    }
};
