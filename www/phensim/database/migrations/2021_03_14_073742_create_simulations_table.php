<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimulationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'simulations',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->tinyInteger('status');
                $table->string('input_parameters_file')->nullable();
                $table->string('enrichment_database_file')->nullable();
                $table->string('node_types_file')->nullable();
                $table->string('edge_types_file')->nullable();
                $table->string('edge_subtypes_file')->nullable();
                $table->string('non_expressed_nodes_file')->nullable();
                $table->jsonb('parameters');
                $table->string('output_file')->nullable();
                $table->string('pathway_output_file')->nullable();
                $table->string('nodes_output_file')->nullable();
                $table->longText('logs');
                $table->boolean('public')->default(false);
                $table->string('public_key')->nullable()->unique();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('organism_id')->constrained()->restrictOnDelete();
                $table->timestamps();
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
        Schema::dropIfExists('simulations');
    }
}
