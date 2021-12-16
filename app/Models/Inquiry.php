<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Inquiry extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'inquiry';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'attachment',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} inquiry";
    }
}
