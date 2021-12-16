<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UserAssignLimit extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'user assign limit';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'subject_id',
        'how_many_question',
        'duration',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} user assign limit";
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject');
    }

}
