<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class QuestionUser extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'question user';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'question_id',
        'subject_id',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} question user";
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
