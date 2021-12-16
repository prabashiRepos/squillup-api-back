<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ParentDetail extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'parent detail';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'country',
        'city',
        'user_name',
        'plan_id',
        'payment_statement',
    ];

    protected $guarded = [
        'first_name',
        'last_name',
        'email',
        'country_code',
        'phone',
        'password',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} parent deatil";
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
