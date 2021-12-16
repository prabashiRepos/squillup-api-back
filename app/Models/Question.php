<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Traits\LogsActivity;

class Question extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'question';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'test_type',
        'test_referrer_id',
        'past_paper_type',
        'past_paper_year',
        'past_paper_number',
        'user_id',
        'lesson_id',
        'chapter_id',
        'question_type',
        'question_content_type',
        'question_number',
        'question',
        'parent_question_id',
        'answers',
        'answer_type',
        'one_word_answer',
        'one_word_correct_answer',
        'correct_answers',
        'mark',
        'solution',
        'knowledge',
        'marking_scheme',
        'video_explanation',
        'status',
    ];

    protected $hidden = [
        'childrenQuestion'
    ];

    protected $appends = [
        'children'
    ];

    protected $casts = [
        'answers' => 'array',
        'correct_answers' => 'array',
        'video_explanation' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} question";
    }

    public function getChildrenAttribute()
    {
        return $this->childrenQuestion;
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_question_id')->with('childrenQuestion');
    }

    public function childrenQuestion()
    {
        return $this->hasMany(self::class, 'parent_question_id')->with('children');
    }

    public function getQuestionAttribute()
    {
        if(array_key_exists('question', $this->attributes) && array_key_exists('question_content_type', $this->attributes)){
            if((!is_null($this->attributes['question'])) && ($this->attributes['question_content_type'] == 'url')){
                if(Storage::exists('public/images/question/'.$this->attributes['question'])) {
                    return asset('storage/images/question/'.$this->attributes['question']);
                }
            }
        }

        return $this->attributes['question'];
    }

    // public function getAnswersAttribute()
    // {
    //     $returnAnswers = [];
    //     if(array_key_exists('answers', $this->attributes)){
    //         $answers = json_decode($this->attributes['answers']);
    //         if(value($answers) && is_array($answers)){
    //             foreach ($answers as $answer) {
    //                 if($answer->type == 'image'){
    //                     if(Storage::exists('public/images/question/'.$answer->answer)) {
    //                         array_push($returnAnswers, [
    //                             'type' => 'url',
    //                             'answer' => asset('storage/images/question/'.$answer->answer),
    //                             'id' => isset($answer->id) ? $answer->id : null,
    //                         ]);
    //                     }
    //                 }
    //                 else array_push($returnAnswers, [
    //                     'type' => 'string',
    //                     'answer' => $answer->answer,
    //                     'id' => isset($answer->id) ? $answer->id : null,
    //                 ]);
    //             }
    //         }
    //     }

    //     return $returnAnswers;
    // }

    // public function getCorrectAnswersAttribute()
    // {
    //     $returnAnswers = [];
    //     if(array_key_exists('correct_answers', $this->attributes)){
    //         $answers = json_decode($this->attributes['correct_answers']);
    //         if(value($answers) && is_array($answers)){
    //             foreach ($answers as $answer) {
    //                 if($answer->type == 'image'){
    //                     if(Storage::exists('public/images/question/'.$answer->answer)) {
    //                         array_push($returnAnswers, [
    //                             'type' => 'url',
    //                             'answer' => asset('storage/images/question/'.$answer->answer),
    //                             'id' => isset($answer->id) ? $answer->id : null,
    //                         ]);
    //                     }
    //                 }
    //                 else array_push($returnAnswers, [
    //                     'type' => 'string',
    //                     'answer' => $answer->answer,
    //                     'id' => isset($answer->id) ? $answer->id : null,
    //                 ]);
    //             }
    //         }
    //     }

    //     return $returnAnswers;
    // }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\Chapter');
    }
}
