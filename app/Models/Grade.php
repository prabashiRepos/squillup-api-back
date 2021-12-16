<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Grade extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'grade';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'name',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} grade";
    }

    public function subject()
    {
        return $this->belongsToMany('App\Models\Subject', 'subject_grades', 'grade_id' ,'subject_id');
    }
}
