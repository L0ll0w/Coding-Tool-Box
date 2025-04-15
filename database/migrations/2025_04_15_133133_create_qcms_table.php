<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQcmsTable extends Migration
{
    public function up()
    {

        Schema::create('qcms', function (Blueprint $table) {
            $table->id();
            $table->string('subject'); // Le sujet soumis (ex : "chat")
            $table->text('generated_qcm'); // Le QCM généré par l'IA
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qcms');
    }
}
