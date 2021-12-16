<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOneWordAnswersToQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('one_word_answer')->nullable()->after('answers');
            $table->string('one_word_correct_answer')->nullable()->after('correct_answers');
            $table->longText('filling_in_the_blank')->nullable()->after('one_word_correct_answer');
            $table->longText('matching_pair')->nullable()->after('filling_in_the_blank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['one_word_answer','one_word_correct_answer','filling_in_the_blank','matching_pair']);
        });
    }
}
