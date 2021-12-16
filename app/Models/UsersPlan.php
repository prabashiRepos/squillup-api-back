<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class UsersPlan extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'user plan';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'price',
        'data',
        'invoices',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'invoices' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} user plan";
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }
}
