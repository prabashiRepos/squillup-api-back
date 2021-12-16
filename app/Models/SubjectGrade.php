<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SubjectGrade extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'subject grade';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} subject grade";
    }
    // public function grade()
    // {
    //     return $this->belongsTo('App\Models\Grade');
    // }
}
