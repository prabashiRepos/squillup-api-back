<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailAdmin extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $name;
    public $messages;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$name,$messages)
    {


        $this->email = $email;
        $this->name = $name;
        $this->messages = $messages;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.admin_mail')->with([ 'email' => $this->email ,'name' => $this->name ,'messages' => $this->messages ]);
    }
}
