<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Revision extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'revison';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'lesson_id',
        'chapter_id',
        'grade_id',
        'subject_id',
        'exam_board_id',
        'resource',
        'presentation',
        'short_note',
        'status',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} revision";
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
}
