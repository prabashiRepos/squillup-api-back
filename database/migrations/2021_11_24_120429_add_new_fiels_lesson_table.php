<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFielsLessonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('grade_id')->after('id');
            $table->foreign('grade_id')->references('id')->on('years')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('subject_id')->after('grade_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('exam_board_id')->after('subject_id');
            $table->foreign('exam_board_id')->references('id')->on('exam_boards')->onDelete('cascade')->onUpdate('cascade');
            $table->longText('description')->nullable()->after('data');
            $table->longText('short_notes')->nullable()->after('description');
            $table->string('presentation')->nullable()->after('short_notes');
            $table->string('glossary')->nullable()->after('presentation');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['grade_id','subject_id','exam_board_id','description','short_notes','presentation','glossary']);
        });
    }
}
