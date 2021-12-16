<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiryRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->foreign('inquiry_id')->references('id')->on('inquiries')->onDelete('cascade')->onUpdate('cascade');
            $table->longText('reply');
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
        Schema::dropIfExists('inquiry_replies');
    }
}
