<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Traits\LogsActivity;

class ExamBoard extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'exam board';

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
        'description',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} exam board";
    }

    public function getLogoAttribute()
    {
        if(array_key_exists('logo', $this->attributes)){
            if(! is_null($this->attributes['logo'])){
                if(Storage::exists('public/images/exam_boards/'.$this->attributes['logo'])) {
                    return asset('storage/images/exam_boards/'.$this->attributes['logo']);
                }
            }
        }

        return asset('storage/images/exam_boards//noimage.jpg');
    }
}
