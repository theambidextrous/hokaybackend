<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobPosted extends Mailable
{
    use Queueable, SerializesModels;
     public $edit;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($edit)
    {
        $this->edit = $edit;
    }

    /**
     * Build  the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Your Job Was Posted')->view('emails.JobPosted');
    }
}
