<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationWorkSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_sheets', function (Blueprint $table) {
            $table->string('duration_hours')->after('questions');
            $table->string('duration_minutes')->after('duration_hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_sheets', function (Blueprint $table) {
            $table->dropColumn(['duration_hours','duration_minutes']);
        });
    }
}
