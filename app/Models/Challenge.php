<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Challenge extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'challenge';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'grade_id',
        'subject_id',
        'age_limit',
        'challenge_board',
        'year',
        'questions',
        'duration_hours',
        'duration_minutes',
        'logo',
        'level',
        'status',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    protected $appends = [
        'questions_data',
        'questions_count',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} challenge";
    }

    public function getQuestionsDataAttribute()
    {
        $questionsData = [];
        if(isset($this->attributes['questions'])){
            $questionIds = json_decode($this->attributes['questions']);

            if(value($questionIds) && is_array($questionIds)){
                foreach ($questionIds as $questionId) {
                    $question = Question::find($questionId);
                    if($question) array_push($questionsData, $question);
                }
            }
        }

        return $questionsData;
    }

    public function getQuestionsCountAttribute()
    {
        if(isset($this->attributes['questions'])){
            $questionIds = json_decode($this->attributes['questions']);
            if(value($questionIds) && is_array($questionIds)) return count($questionIds);
        }

        return 0;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject');
    }

    public function grade()
    {
        return $this->belongsTo('App\Models\Year');
    }
}
