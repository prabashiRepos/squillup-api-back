<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaptersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('number')->nullable();

            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('year_id')->nullable();
            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('exam_board_id')->nullable();
            $table->foreign('exam_board_id')->references('id')->on('exam_boards')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chapters');
    }
}
