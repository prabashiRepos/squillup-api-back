<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('exam_board_id');
            $table->foreign('exam_board_id')->references('id')->on('exam_boards')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('key_stage_id');
            $table->foreign('key_stage_id')->references('id')->on('key_stages')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('grade_id');
            $table->foreign('grade_id')->references('id')->on('years')->onDelete('cascade')->onUpdate('cascade');
            $table->string('dob')->nullable();
            $table->string('user_name')->nullable();
            $table->string('school_name')->nullable();
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
        Schema::dropIfExists('student_details');
    }
}
