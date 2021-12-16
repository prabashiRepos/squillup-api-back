<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('years', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('key_stages_years', function($table){
            $table->integer('key_stage_id')->unsigned();
            $table->integer('year_id')->unsigned();
            $table->primary(['key_stage_id', 'year_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('years');
        Schema::dropIfExists('key_stages_years');
    }
}
