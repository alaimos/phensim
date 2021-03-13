<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePathwayNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pathway_nodes', function (Blueprint $table) {
            $table->integer('pathway_id')->unsigned();
            $table->integer('node_id')->unsigned();
            $table->primary(['pathway_id', 'node_id']);
            $table->foreign('pathway_id')->references('id')->on('pathways')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pathway_nodes');
    }
}
