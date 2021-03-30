<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSbmlOutputFieldToSimulationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'simulations',
            static function (Blueprint $table) {
                $table->string('sbml_output_file')->nullable()->after('nodes_output_file');
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
        Schema::table(
            'simulations',
            static function (Blueprint $table) {
                $table->removeColumn('sbml_output_file');
            }
        );
    }
}
