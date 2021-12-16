<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Submission extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'submission';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'exam_board_id',
        'grade_id',
        'subject_id',
        'chapter_id',
        'lesson_id',
        'work_sheet_id',
        'answer_type',
        'student_answers',
        'date',
        'mark',
        'remark',
        'attachment',
        'status',
    ];

    protected $casts = [
        'student_answers' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} submission";
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson');
    }

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject');
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Year');
    }

    public function exam_board()
    {
        return $this->belongsTo('App\Models\ExamBoard');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\Chapter');
    }

    public function work_sheet()
    {
        return $this->belongsTo('App\Models\WorkSheet');
    }
}
