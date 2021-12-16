<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Faq extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'faq';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_type',
        'category',
        'question',
        'answer',
        'status',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} faq";
    }
}
