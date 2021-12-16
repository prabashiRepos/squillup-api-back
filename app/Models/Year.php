<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Year extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'year';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'name',
        'description',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} year";
    }

    public function key_stage()
    {
        return $this->belongsToMany('App\Models\KeyStage', 'key_stages_years', 'year_id' ,'key_stage_id');
    }
}
