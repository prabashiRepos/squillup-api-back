<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewReadFieldsToQnASTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qn_a_s', function (Blueprint $table) {
            $table->string('isread')->nullable()->after('question');
            $table->string('sms_reminder')->nullable()->after('isread');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qn_a_s', function (Blueprint $table) {
            $table->dropColumn(['isread','sms_reminder']);
        });
    }
}
