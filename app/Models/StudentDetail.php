<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentDetail extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'student detail';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'parent_id',
        'exam_board_id',
        'key_stage_id',
        'grade_id',
        'dob',
        'user_name',
        'school_name',
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
        return "You have {$eventName} student detail";
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\User','parent_id');
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Year');
    }

    public function exam_board()
    {
        return $this->belongsTo('App\Models\ExamBoard');
    }

    public function key_stage()
    {
        return $this->belongsTo('App\Models\keyStage');
    }

    public function key_stage_year()
    {
        return $this->belongsToMany('App\Models\KeyStage', 'key_stages_years', 'year_id' ,'key_stage_id');
    }

}
