<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFielsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('past_paper_type')->nullable()->after('test_type');
            $table->string('past_paper_year')->nullable()->after('past_paper_type');
            $table->longText('marking_scheme')->nullable()->after('knowledge');
            $table->longText('answer_type')->nullable()->after('question_type');
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
            $table->dropColumn(['past_paper_type','past_paper_year','marking_scheme']);
        });
    }
}
