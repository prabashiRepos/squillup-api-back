<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Traits\LogsActivity;

class QA extends Model
{
    use HasFactory, LogsActivity;

    protected static $logName = 'qa';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'chapter_id',
        'parent_q_a_id',
        'contant',
        'attachment',
    ];

    protected $hidden = [
        'childrenReply'
    ];

    protected $appends = [
        'children'
    ];

    protected $casts = [
        'attachment' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "You have {$eventName} qa";
    }

    public function getAttachmentAttribute()
    {
        $returnPath = [];
        if(array_key_exists('attachment', $this->attributes)){
            $attachments = json_decode($this->attributes['attachment']);
            if(value($attachments) && is_array($attachments)){
                foreach ($attachments as $attachment) {
                    if(Storage::exists('public/images/q_a/'.$attachment)) {
                        array_push($returnPath, asset('storage/images/q_a/'.$attachment));
                    }
                }
            }
        }

        return $returnPath;
    }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\Chapter');
    }

    public function getChildrenAttribute()
    {
        return $this->childrenReply;
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_q_a_id')
                        ->with('childrenReply')
                        ->with('user')
                        ->with('lesson', function($query){
                            $query->select('id', 'name','number', 'chapter_id');
                        })
                        ->with('chapter');
    }

    public function childrenReply()
    {
        return $this->hasMany(self::class, 'parent_q_a_id')
                        ->with('children')
                        ->with('user')
                        ->with('lesson', function($query){
                            $query->select('id', 'name','number', 'chapter_id');
                        })
                        ->with('chapter');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function post()
    {
        return $this->belongsTo('App\Models\BlogPost');
    }
}
