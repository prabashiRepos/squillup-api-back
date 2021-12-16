<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Lesson extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'lesson';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'grade_id',
        'subject_id',
        'exam_board_id',
        'chapter_id',
        'name',
        'number',
        'video_url',
        'overview',
        'data',
        'description',
        'short_notes',
        'presentation',
        'glossary',
    ];

    protected $casts = [
        'data' => 'array',
        // 'video_url' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} lesson";
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\Chapter');
    }

    public function getVideoUrlAttribute()
    {
        return json_decode($this->attributes['video_url']);
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
}
