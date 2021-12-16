<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->string('test_type')->nullable();
            $table->unsignedBigInteger('test_referrer_id')->nullable()->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade')->onUpdate('cascade');

            $table->string('question_type')->nullable();
            $table->string('question_content_type')->nullable()->default('string');
            $table->string('question_number')->nullable();

            $table->longText('question')->nullable();

            $table->unsignedBigInteger('parent_question_id')->nullable();
            $table->foreign('parent_question_id')->references('id')->on('questions')->onDelete('cascade')->onUpdate('cascade');

            $table->{$this->jsonable()}('answers', 10000)->nullable();

            $table->{$this->jsonable()}('correct_answers', 10000)->nullable();

            $table->integer('mark')->unsigned()->nullable();

            $table->longText('solution')->nullable();

            $table->{$this->jsonable()}('video_explanation', 50000)->nullable();

            $table->string('status')->default('publish');
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
        Schema::dropIfExists('questions');
    }

    protected function jsonable(): string
    {
        $driverName = DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $dbVersion = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        $isOldVersion = version_compare($dbVersion, '5.7.8', 'lt');

        return $driverName === 'mysql' && $isOldVersion ? 'text' : 'json';
    }
}
