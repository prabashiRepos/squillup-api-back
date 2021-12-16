<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsSelfTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('self_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('chapter_id')->after('lesson_id');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('subject_id')->after('chapter_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('grade_id')->after('subject_id');
            $table->foreign('grade_id')->references('id')->on('years')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('exam_board_id')->after('grade_id');
            $table->foreign('exam_board_id')->references('id')->on('exam_boards')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('self_tests', function (Blueprint $table) {
            $table->dropColumn(['chapter_id','subject_id','exam_board_id','grade_id']);
        });
    }
}
