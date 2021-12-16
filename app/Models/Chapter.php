<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Chapter extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'chapter';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'number',
        'subject_id',
        'year_id',
        'exam_board_id',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} chapter";
    }

    public function lesson()
    {
        return $this->hasMany('App\Models\Lesson');
    }

    public function year()
    {
        return $this->belongsTo('App\Models\Year');
    }

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject');
    }

    public function exam_board()
    {
        return $this->belongsTo('App\Models\ExamBoard');
    }
}
