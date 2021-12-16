<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends LaratrustRole
{
    use LogsActivity;
    
    public $guarded = [];

    protected static $logName = 'role';

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
        'display_name',
        'description',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} role";
    }
}
