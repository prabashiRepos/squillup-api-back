<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Reply extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'reply';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'question_id',
        'reply',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} reply";
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\QnA');
    }


}
