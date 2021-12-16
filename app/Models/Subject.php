<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Traits\LogsActivity;

class Subject extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'subject';

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
        'logo',
        'color',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} subject";
    }

    public function getLogoAttribute()
    {
        if(array_key_exists('logo', $this->attributes)){
            if(! is_null($this->attributes['logo'])){
                if(Storage::exists('public/images/subjects/'.$this->attributes['logo'])) {
                    return asset('storage/images/subjects/'.$this->attributes['logo']);
                }
            }
        }

        return asset('storage/images/subjects//noimage.jpg');
    }

    public function grade()
    {
        return $this->belongsToMany('App\Models\Year', 'subject_grades', 'subject_id', 'grade_id');
    }
}


