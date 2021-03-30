<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accession');
            $table->string('name');
            $table->string('type')->index();
            $table->integer('organism_id')->unsigned()->index();
            $table->longText('aliases');
            $table->foreign('organism_id')->references('id')->on('organisms')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['accession', 'organism_id']);
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nodes');
    }
}
