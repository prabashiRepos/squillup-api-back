<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

class AddNewFieldsSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_user_id_foreign');
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('student_id')->after('id');
            $table->unsignedBigInteger('teacher_id')->Nullable()->after('student_id');
            $table->unsignedBigInteger('exam_board_id')->after('teacher_id');
            $table->foreign('exam_board_id')->references('id')->on('exam_boards')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('grade_id')->after('exam_board_id');
            $table->foreign('grade_id')->references('id')->on('years')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('chapter_id')->after('subject_id');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('lesson_id')->after('chapter_id');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->string('answer_type')->nullable()->after('work_sheet_id');
            $table->{$this->jsonable()}('student_answers', 10000)->after('answer_type');
            $table->longText('remark')->nullable()->after('student_answers');
            $table->longText('attachment')->nullable()->after('remark');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->dropColumn(['user_id','student_id','teacher_id','exam_board_id','grade_id','chapter_id','lesson_id','student_answers']);
        });
    }

    protected function jsonable(): string
    {
        $driverName = DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $dbVersion = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        $isOldVersion = version_compare($dbVersion, '5.7.8', 'lt');

        return $driverName === 'mysql' && $isOldVersion ? 'text' : 'json';
    }
}
