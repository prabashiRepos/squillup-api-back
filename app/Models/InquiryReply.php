<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class InquiryReply extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'inquiry reply';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'inquiry_id',
        'reply',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} inquiry reply";
    }

    public function inquiry()
    {
        return $this->belongsTo('App\Models\Inquiry');
    }
}
