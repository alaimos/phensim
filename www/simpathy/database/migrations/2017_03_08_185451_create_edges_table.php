<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEdgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('edges', function (Blueprint $table) {
            $table->string('id', 33);
            $table->integer('start_id')->unsigned()->index();
            $table->integer('end_id')->unsigned()->index();
            $table->longText('types');
            $table->integer('organism_id')->unsigned()->index();
            $table->primary('id');
            $table->index(['start_id', 'end_id']);
            $table->foreign('organism_id')->references('id')->on('organisms')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('start_id')->references('id')->on('nodes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('end_id')->references('id')->on('nodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('edges');
    }
}
