<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivated extends Mailable
{
    use Queueable, SerializesModels;

    private $balance;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($balance)
    {
        $this->balance = $balance;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.accountActivated')->with([
            'balance' => $this->balance,
        ]);
    }
}
