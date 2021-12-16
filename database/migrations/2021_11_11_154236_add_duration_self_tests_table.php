<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationSelfTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('self_tests', function (Blueprint $table) {
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
        Schema::table('self_tests', function (Blueprint $table) {
            $table->dropColumn(['duration_hours','duration_minutes']);
        });
    }
}
