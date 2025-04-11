<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            // Supprimer la contrainte existante sur task_id.
            $table->dropForeign(['task_id']);
            // Réajouter la contrainte avec onDelete('set null')
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            // En cas d'annulation, vous pouvez rétablir la suppression en cascade
            $table->dropForeign(['task_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

};
