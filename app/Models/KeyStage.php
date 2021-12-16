<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
class KeyStage extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'key stage';

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
        return "You have {$eventName} key stage";
    }

    public function year()
    {
        return $this->belongsToMany('App\Models\Year', 'key_stages_years', 'key_stage_id', 'year_id');
    }
}
