<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'nodes',
            function (Blueprint $table) {
                $table->id();
                $table->string('accession')->index();
                $table->string('name')->index();
                $table->text('aliases');
                $table->foreignId('organism_id')->constrained()->cascadeOnDelete();
                $table->unique(['accession', 'organism_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
}
