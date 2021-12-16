<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendReply extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $subjects;
    public $messages;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$subjects,$messages)
    {
        $this->email = $email;
        $this->subjects = $subjects;
        $this->messages = $messages;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.reply')
        ->with([ 'email' => $this->email ,'subjects' => $this->subjects, 'messages' => $this->messages ])
        ->replyTo($this->email, $this->subjects);
    }
}
