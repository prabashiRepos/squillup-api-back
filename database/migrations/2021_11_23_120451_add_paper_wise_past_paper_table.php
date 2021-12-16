<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaperWisePastPaperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('past_papers', function (Blueprint $table) {
            $table->string('paper_wise')->after('paper_type');
            $table->longText('paper_file')->after('duration_type');
            $table->longText('paper_marking_scheme')->after('paper_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('past_papers', function (Blueprint $table) {
            $table->dropColumn(['paper_wise','paper_file','paper_marking_scheme']);
        });
    }
}
