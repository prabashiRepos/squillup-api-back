<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class QnA extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'question and answer';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'student_id',
        'user_id',
        'chapter_id',
        'lesson_id',
        'subject_id',
        'question',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} question and answer";
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

    public function chapter()
    {
        return $this->belongsTo('App\Models\Chapter');
    }

    public function reply()
    {
        return $this->hasMany('App\Models\Reply','question_id','id');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\User','student_id','id');
    }
}
