<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Subscribed extends Mailable
{
    use Queueable, SerializesModels;
    public $period;
    public $jobs;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($period, $jobs)
    {
        $this->period = $period;
        $this->jobs = $jobs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Jobs Alert Was Set')->view('emails.Subscribed');
    }
}
