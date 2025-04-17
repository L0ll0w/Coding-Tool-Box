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
        Schema::create('qcm_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_id')->constrained('qcms')->onDelete('cascade');
            $table->text('question_text');       // Texte de la question
            $table->timestamps();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down()
    {
        Schema::dropIfExists('qcm_questions');
    }
};
