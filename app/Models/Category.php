<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'category';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'name',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} category";
    }
}
