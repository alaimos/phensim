<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePathwayEdgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pathway_edges', function (Blueprint $table) {
            $table->integer('pathway_id')->unsigned();
            $table->string('edge_id', 33);
            $table->primary(['pathway_id', 'edge_id']);
            $table->foreign('pathway_id')->references('id')->on('pathways')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('edge_id')->references('id')->on('edges')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pathway_edges');
    }
}
