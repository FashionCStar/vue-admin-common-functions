<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmUserMail extends Mailable
{
  use Queueable, SerializesModels;
  public $email;
  public $token;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($email, $token)
  {
    //
    $this->email = $email;
    $this->token = $token;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
//        return $this->view('view.name');
    return $this->markdown('emails.confirmUser')->with([
      'email' => $this->email,
      'token' => $this->token
    ]);
  }
}
